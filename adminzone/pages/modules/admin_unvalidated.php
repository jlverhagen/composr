<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    unvalidated
 */

/**
 * Module page class.
 */
class Module_admin_unvalidated
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return NULL to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        return array(
            '!' => array('UNVALIDATED_RESOURCES', 'menu/adminzone/audit/unvalidated'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know meta-data for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('unvalidated');

        set_helper_panel_tutorial('tut_censor');

        $this->title = get_screen_title('UNVALIDATED_RESOURCES');

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        $out = new Tempcode();
        require_code('form_templates');

        $_hooks = find_all_hooks('modules', 'admin_unvalidated');
        foreach (array_keys($_hooks) as $hook) {
            require_code('hooks/modules/admin_unvalidated/' . filter_naughty_harsh($hook));
            $object = object_factory('Hook_unvalidated_' . filter_naughty_harsh($hook), true);
            if (is_null($object)) {
                continue;
            }
            $info = $object->info();
            if (is_null($info)) {
                continue;
            }

            $identifier_select = is_array($info['db_identifier']) ? implode(',', $info['db_identifier']) : $info['db_identifier'];
            $db = array_key_exists('db', $info) ? $info['db'] : $GLOBALS['SITE_DB'];
            $rows = $db->query_select($info['db_table'], array($identifier_select . (array_key_exists('db_title', $info) ? (',' . $info['db_title']) : '')), array($info['db_validated'] => 0), '', 100);
            if (count($rows) == 100) {
                attach_message(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'), 'warn');
            }
            $content = new Tempcode();
            foreach ($rows as $row) {
                if (is_array($info['db_identifier'])) {
                    $id = '';
                    foreach ($info['db_identifier'] as $_id) {
                        if ($id != '') {
                            $id .= ':';
                        }
                        $id .= $row[$_id];
                    }
                } else {
                    $id = $row[$info['db_identifier']];
                }
                if (array_key_exists('db_title', $info)) {
                    $_title = $row[$info['db_title']];
                    if ($info['db_title_dereference']) {
                        $_title = get_translated_text($_title, $db); // May actually be comcode (can't be certain), but in which case it will be shown as source
                    }
                } else {
                    $_title = '#' . (is_integer($id) ? strval($id) : $id);
                }
                if ($_title == '') {
                    $_title = '#' . strval($id);
                }
                $content->attach(form_input_list_entry(is_integer($id) ? strval($id) : $id, false, strip_comcode($_title)));
            }

            if (!$content->is_empty()) {
                $post_url = build_url(array('page' => $info['edit_module'], 'type' => $info['edit_type'], 'validated' => 1/*, 'redirect'=>get_self_url(true)*/), get_module_zone($info['edit_module']), null, false, true);
                $fields = form_input_list(do_lang_tempcode('CONTENT'), '', $info['edit_identifier'], $content, null, true);

                // Could debate whether to include "'TARGET' => '_blank',". However it does redirect back, so it's a nice linear process like this. If it was new window it could be more efficient, but also would confuse people with a lot of new windows opening and not closing.
                $content = do_template('FORM', array('_GUID' => '51dcee39273a0fee29569190344f2e41', 'SKIP_REQUIRED' => true, 'GET' => true, 'HIDDEN' => '', 'SUBMIT_ICON' => 'buttons__save', 'SUBMIT_NAME' => do_lang_tempcode('EDIT'), 'FIELDS' => $fields, 'URL' => $post_url, 'TEXT' => ''));
            }

            $out->attach(do_template('UNVALIDATED_SECTION', array('_GUID' => '838240008e190b9cbaa0280fbddd6baf', 'TITLE' => $info['title'], 'CONTENT' => $content)));
        }

        return do_template('UNVALIDATED_SCREEN', array('_GUID' => '4e971f1c8851b821af030b5c7bbcb3fb', 'TITLE' => $this->title, 'TEXT' => do_lang_tempcode('UNVALIDATED_PAGE_TEXT'), 'SECTIONS' => $out));
    }
}
