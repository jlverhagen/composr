<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.
*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    achievements
 */

/*
    For point sponsorships, you will want to use the escrows qualification with the "tracker_issue" content type.
    For paid sponsorships, you will want to manually log those in the eCommerce system and then use the ecom_transactions qualification with the proper type code.

    Supported parameters for this qualification:
    1) count            -- The number of tracker issues a member must create before this qualification is satisfied (not specified: 5)
    2) handler          -- If 1, only count issues this member handled rather than reported; -1 only counts issues reported rather than handled (not specified: 0; either reported or handled)
    3) resolved_only    -- If 1, then only count resolved tracker issues (ignored for issues which the member handled) (not specified: 1)
    4) days             -- Only consider issues submitted within this many days (or last updated within this many days for handlers) (not specified: no filter)
*/

/**
 * Hook class.
 */
class Hook_achievement_qualifications_tracker
{
    /**
     * Get information about this qualification.
     *
     * @param  MEMBER $member_id The member we are viewing
     * @param  array $params Map of parameters which were specified on the XML for this qualification
     * @return ?array Map of details (null: qualification is disabled)
     */
    public function info(int $member_id, array $params) : ?array
    {
        if (!addon_installed('achievements')) {
            return null;
        }

        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        return [
            'supports_persist' => false, // too complicated given switching between add and updated time depending on criteria
            'persist_progress_default' => false,
        ];
    }

    /**
     * Run calculations on this qualification to see how much it has been completed.
     *
     * @param  MEMBER $member_id The member we are viewing
     * @param  array $params Map of parameters which were specified on the XML for this qualification
     * @param  ?TIME $last_time Only calculate results more recent than the given time (null: never calculated before, or not persisting progress)
     * @return ?array Double: the number accomplished, and the number needed for the qualification to be considered "complete" (null: qualification should be ignored)
     */
    public function run(int $member_id, array $params, ?int $last_time = null) : ?array
    {
        if (!addon_installed('achievements')) {
            return null;
        }

        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        require_lang('tracker');

        // Read in parameters
        $count_required = isset($params['count']) ? intval($params['count']) : 5;
        $handler = isset($params['handler']) ? $params['handler'] : '0';
        $resolved_only = isset($params['resolved_only']) ? ($params['resolved_only'] == '1') : true;
        $days = isset($params['days']) ? intval($params['days']) : null;

        // Build query
        $status_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('STATUS')]);
        $handler_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('HANDLER')]);
        $sql = 'SELECT COUNT(*) AS num_issues FROM {prefix}catalogue_entries e';
        $where = ' WHERE 1=1';
        $where_params = ['member_id' => $member_id];
        switch ($handler) {
            case '-1':
                $where .= ' AND e.ce_submitter={member_id}';
                if ($days !== null) {
                    $where_params['time_limit'] = time() - ($days * 24 * 60 * 60);
                    $where .= ' AND e.ce_add_date>={time_limit}';
                }
                break;
            case '0':
                if ($days !== null) {
                    $where_params['time_limit'] = time() - ($days * 24 * 60 * 60);
                    $where .= ' AND e.ce_add_date>={time_limit}';
                }
                $where_params['handler_field'] = $handler_field;
                $where .= ' AND (e.ce_submitter={member_id} OR EXISTS(SELECT 1 FROM {prefix}catalogue_efv_integer i WHERE i.ce_id=e.id AND i.cf_id={handler_field} AND i.cv_value={member_id}))';
                break;
            case '1':
                $where_params['handler_field'] = $handler_field;
                $where .= ' AND EXISTS(SELECT 1 FROM {prefix}catalogue_efv_integer i WHERE i.ce_id=e.id AND i.cf_id={handler_field} AND i.cv_value={member_id})';
                if ($days !== null) {
                    $where_params['time_limit'] = time() - ($days * 24 * 60 * 60);
                    $where .= ' AND e.ce_add_date>={time_limit}';
                }
                break;
        }
        if ($resolved_only) {
            $where_params['status_field'] = $status_field;
            $where_params['status_value'] = 'completed';
            $where .= ' AND EXISTS(SELECT 1 FROM {prefix}catalogue_efv_long i WHERE i.ce_id=e.id AND i.cf_id={status_field} AND i.cv_value={status_value})';
        }

        // Get results
        $_count_done = $GLOBALS['SITE_DB']->query_parameterised($sql . $where, $where_params);
        $count_done = $_count_done[0]['num_issues'];

        return [$count_done, $count_required];
    }

    /**
     * Convert information about the qualification into human-readable text where members can track their progress.
     *
     * @param  MEMBER $member_id The member we are viewing
     * @param  array $params Map of parameters which were specified on the XML for this qualification
     * @param  integer $count_done Count of items achieved for the qualification (from run)
     * @param  integer $count_required Count of items required for the qualification to be complete (from run)
     * @return ?Tempcode The text explaining this condition and the progress (null: hidden or disabled qualification)
     */
    public function to_text(int $member_id, array $params, int $count_done, int $count_required) : ?object
    {
        if (!addon_installed('achievements')) {
            return null;
        }

        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        require_lang('achievements');

        // Read in parameters
        $handler = isset($params['handler']) ? $params['handler'] : '0';
        $resolved_only = isset($params['resolved_only']) ? ($params['resolved_only'] == '1') : true;
        $days = isset($params['days']) ? intval($params['days']) : null;

        // Conditions
        $conditions = new Tempcode();
        if ($resolved_only === true) {
            $conditions->attach(do_lang_tempcode('ACHIEVEMENT_TRACKER_REQUIREMENT_RESOLVED'));
        }
        if ($days !== null) {
            $conditions->attach(do_lang_tempcode('ACHIEVEMENT_TRACKER_REQUIREMENT_DAYS', escape_html(integer_format($days))));
        }

        // Progress
        $progress = do_lang_tempcode('ACHIEVEMENT_PROGRESS', escape_html(integer_format($count_done)), escape_html(integer_format($count_required)));

        // Finalise
        switch ($handler) {
            case '-1':
                return do_lang_tempcode('ACHIEVEMENT_TRACKER_SUBMIT_REQUIREMENT', protect_from_escaping($conditions), protect_from_escaping($progress));
            case '1':
                return do_lang_tempcode('ACHIEVEMENT_TRACKER_HANDLE_REQUIREMENT', protect_from_escaping($conditions), protect_from_escaping($progress));
        }
        return do_lang_tempcode('ACHIEVEMENT_TRACKER_REQUIREMENT', protect_from_escaping($conditions), protect_from_escaping($progress));
    }
}
