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
 * @package    actionlog
 */

/*
RevisionsEngineDatabase is normally used, but for some very special actions RevisionEngineFiles will be used instead.
This only works for some pre-specified actions that are hard-coded into this class: EDIT_CSS, etc.

RevisionsEngineDatabase and RevisionEngineFiles are not API-compatible but are designed to work similarly.
*/

/**
 * Revisions via very simple file management.
 */
class RevisionEngineFiles
{
    /**
     * Find whether revisions are enabled for the current user.
     *
     * @param  boolean $check_privilege Whether to check privileges.
     * @return boolean Whether revisions are enabled.
     */
    public function enabled($check_privilege)
    {
        if (get_option('store_revisions') == '0') {
            return false;
        }

        if ($check_privilege) {
            if (!has_privilege(get_member(), 'view_revisions')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a revision.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what is being revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @param  ?string $original_text Text before revision (null: work out from disk).
     * @param  ?TIME $original_timestamp The creation timestamp for what was just replaced (null: work out from disk).
     */
    public function add_revision($directory, $filename_id, $ext, $original_text, $original_timestamp)
    {
        if (!$this->enabled(false)) {
            return;
        }

        if (is_null($original_text) || is_null($original_timestamp)) {
            $existing_path = get_custom_file_base() . '/' . filter_naughty($directory . '/' . $filename_id . '.' . $ext);
            $existing_path = zone_black_magic_filterer($existing_path);
            if (!is_file($existing_path)) {
                $existing_path = get_file_base() . '/' . filter_naughty($directory . '/' . $filename_id . '.' . $ext);
                $existing_path = zone_black_magic_filterer($existing_path);
            }
            if (!is_file($existing_path)) {
                return;
            }

            if (is_null($original_text)) {
                $original_text = file_get_contents($existing_path);
            }

            if (is_null($original_timestamp)) {
                $original_timestamp = filemtime($existing_path);
            }
        }

        $stub = get_custom_file_base() . '/';

        if (substr($directory, 0, strlen($stub)) == $stub) {
            $directory = substr($directory, strlen($stub));
        }

        $revision_path = $stub . filter_naughty($directory . '/' . $filename_id . '.' . $ext . '.' . strval($original_timestamp));
        $revision_path = zone_black_magic_filterer($revision_path);

        @file_put_contents($revision_path, $original_text) or intelligent_write_error($revision_path);
        fix_permissions($revision_path);
        sync_file($revision_path);
    }

    /**
     * Delete a particular revision.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what is being revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @param  TIME $id Revision ID.
     */
    public function delete_revision($directory, $filename_id, $ext, $id)
    {
        $revisions = $this->find_revisions($directory, $filename_id, $ext, null, $id);
        if (!isset($revisions[0])) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }

        $revision_path = get_custom_file_base() . '/' . $directory . '/' . $filename_id . '.' . $ext . '.' . strval($revisions[0]['r_time']);
        if (!is_file($revision_path)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        unlink($revision_path);

        fix_permissions($revision_path);
        sync_file($revision_path);
    }

    /**
     * Retrieve revisions of something.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what was revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @param  ?string $action The action the revision is for, a language string (null: no filter).
     * @param  ?TIME $revision_time The creation timestamp for a particular revision to retrieve (null: no filter).
     * @param  ?integer $max Maximum to return (null: no limit).
     * @param  integer $start Start offset.
     * @param  boolean $limited_data Whether to only collect IDs and other simple low-bandwidth data.
     * @return array List of revision maps.
     */
    public function find_revisions($directory, $filename_id, $ext, $action = null, $revision_time = null, $max = 100, $start = 0, $limited_data = false)
    {
        if (!$this->enabled(true)) {
            return array();
        }

        $base = get_custom_file_base() . '/' . $directory;

        $times = array();
        $quick_match = @glob(get_custom_file_base() . '/' . $directory . '/' . $filename_id . '.' . $ext . '.*', GLOB_NOSORT);
        if ($quick_match === false) {
            $quick_match = array();
        }
        foreach ($quick_match as $f) {
            $_ext = get_file_extension($f);
            if (is_numeric($_ext)) {
                $time = intval($_ext);
                if ((!is_null($revision_time)) && ($revision_time != filemtime($f))) {
                    continue;
                }

                $times[] = $time;
            }
        }

        rsort($times); // Sort into reverse time order
        array_splice($times, 0, $start, array()); // Remove before start
        if (!is_null($max)) {
            array_splice($times, $max, count($times), array()); // Remove after max
        }

        $ret = array();
        foreach ($times as $time) {
            $full_path = $base . '/' . $filename_id . '.' . $ext . '.' . strval($time);

            $mtime = filemtime($full_path);

            if ($limited_data) {
                $ret[$time] = array(
                    'id' => $mtime,
                    'r_time' => $time,
                );

                continue;
            }

            $original_text = file_get_contents($full_path);

            $ret[$time] = array(
                'id' => $mtime,
                'r_original_text' => $original_text,
                'r_time' => $time,

                'revision_type' => serialize(array($directory, $filename_id, $ext)),

                'r_actionlog_id' => null,

                'log_action' => $action,
                'log_param_a' => null,
                'log_param_b' => null,
                'log_member_id' => null,
                'log_ip' => null,
                //'log_time' => null, Same as id
                'log_reason' => '',
            );

            if (!is_null($action)) {
                $test = $GLOBALS['SITE_DB']->query_select('actionlogs', array('*'), array('date_and_time' => $mtime, 'the_type' => $action), '', 1);
                if (array_key_exists(0, $test)) {
                    $ret[$time] = array(
                        'r_actionlog_id' => $test[0]['id'],

                        'log_action' => $test[0]['the_type'],
                        'log_param_a' => $test[0]['param_a'],
                        'log_param_b' => $test[0]['param_b'],
                        'log_member_id' => $test[0]['member_id'],
                        'log_ip' => $test[0]['ip'],
                        //'log_time' => $test[0]['date_and_time'], Same as id
                        'log_reason' => '',
                    ) + $ret[$time];
                }
            }
        }

        return array_values($ret);
    }

    /**
     * Find if there are revisions of something.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what was revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @return boolean Whether there are revisions.
     */
    public function has_revisions($directory, $filename_id, $ext)
    {
        if (!$this->enabled(true)) {
            return false;
        }

        $revisions = $this->find_revisions($directory, $filename_id, $ext, null, null, 1, 0, true);
        return count($revisions) > 0;
    }

    /**
     * Find number of revisions of something.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what was revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @return integer Number of revisions.
     */
    public function total_revisions($directory, $filename_id, $ext)
    {
        if (!$this->enabled(true)) {
            return 0;
        }

        $revisions = $this->find_revisions($directory, $filename_id, $ext, null, null, null, 0, true);
        return count($revisions);
    }

    /**
     * Retrieve revisions for a particular action log entry.
     *
     * @param  AUTO_LINK $actionlog_id The action log entry's ID.
     * @return ?array A revision map (null: not found).
     */
    public function find_revision_for_log($actionlog_id)
    {
        if (!$this->enabled(true)) {
            return null;
        }

        $rows = $GLOBALS['SITE_DB']->query_select('actionlogs', array('date_and_time', 'the_type', 'param_a', 'param_b'), array('id' => $actionlog_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            return null;
        }

        $row = $rows[0];

        $revision_time = $row['date_and_time'];

        switch ($row['the_type']) {
            case 'COMCODE_PAGE_EDIT':
                $directory = $row['param_b'] . (($row['param_b'] == '') ? '' : '/') . 'pages/comcode_custom/' . get_site_default_lang();
                $filename_id = $row['param_a'];
                $ext = 'txt';
                break;

            case 'EDIT_CSS':
                $directory = 'themes/' . $row['param_a'] . '/css_custom';
                $filename_id = basename($row['param_b'], '.' . get_file_extension($row['param_b']));
                $ext = 'css';
                break;

            case 'EDIT_TEMPLATES':
                $directory = 'themes/' . $row['param_b'] . '/' . dirname($row['param_a']);
                $ext = get_file_extension($row['param_a']);
                $filename_id = basename($row['param_a'], '.' . $ext);
                break;

            default:
                return null;
        }

        $logs = $this->find_revisions($directory, $filename_id, $ext, null, $revision_time);
        if (!array_key_exists(0, $logs)) {
            return null;
        }

        $logs[0]['r_actionlog_id'] = $actionlog_id;

        return $logs[0];
    }

    /**
     * Browse revisions to undo one.
     * More details are shown in the actionlog, which is linked from here.
     *
     * @param  PATH $directory Directory where revisions are stored.
     * @param  string $filename_id ID of what was revised (=base filename, no extension).
     * @param  string $ext File extension for revisable files.
     * @param  string $action The action the revision is for, a language string.
     * @param  string $text Current resource text (may be altered by reference).
     * @param  ?boolean $revision_loaded Whether a revision was loaded, passed by reference (null: initial value).
     * @return Tempcode UI.
     */
    public function ui_revision_undoer($directory, $filename_id, $ext, $action, &$text, &$revision_loaded = null)
    {
        $revision_loaded = false;

        if (!$this->enabled(true)) {
            return new Tempcode();
        }

        require_lang('actionlog');

        // Revisions
        $undo_revision = get_param_integer('undo_revision', null);
        $restore_from_path = get_param_string('restore_from_path', null);
        if ($undo_revision === null && $restore_from_path === null) {
            require_code('files');
            require_code('diff');
            require_code('templates_results_table');

            $start = get_param_integer('revisions_start', 0);
            $max = get_param_integer('revisions_max', 5);

            $sortables = array('r_time' => do_lang_tempcode('DATE'));
            $test = explode(' ', get_param_string('revisions_sort', 'r_time DESC'), 2);
            if (count($test) == 1) {
                $test[1] = 'DESC';
            }
            list($sortable, $sort_order) = $test;
            if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
                log_hack_attack_and_exit('ORDERBY_HACK');
            }

            $max_rows = $this->total_revisions($directory, $filename_id, $ext);
            if (!has_js()) {
                $max = $max_rows; // No AJAX pagination if no JS
            }
            $revisions = $this->find_revisions($directory, $filename_id, $ext, $action, null, $max, $start);

            $do_actionlog = has_actual_page_access(get_member(), 'admin_actionlog');

            $_fields_titles = array(
                do_lang_tempcode('DATE_TIME'),
                do_lang_tempcode('MEMBER'),
                do_lang_tempcode('SIZE_CHANGE'),
                do_lang_tempcode('CHANGE_MICRO'),
                do_lang_tempcode('UNDO'),
            );
            if ($do_actionlog) {
                $_fields_titles[] = do_lang_tempcode('LOG');
            }

            $more_recent_text = $text;
            $field_rows = new Tempcode();
            foreach ($revisions as $revision) {
                $date = get_timezoned_date($revision['id']);

                $size_change = strlen($more_recent_text) - strlen($revision['r_original_text']);

                if (is_null($revision['log_member_id'])) {
                    $member_link = do_lang_tempcode('UNKNOWN_EM');
                } else {
                    $member_link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($revision['log_member_id']);
                }

                if (function_exists('diff_simple_2')) {
                    $rendered_diff = diff_simple_2($revision['r_original_text'], $more_recent_text);
                    $diff_icon = do_template('REVISIONS_DIFF_ICON', array(
                        'RENDERED_DIFF' => $rendered_diff,
                    ));
                } else {
                    $diff_icon = new Tempcode();
                }

                $undo_url = get_self_url(false, false, array('undo_revision' => $revision['id']));
                $undo_link = hyperlink($undo_url, do_lang_tempcode('UNDO'), false, false, $date);

                if (is_null($revision['r_actionlog_id'])) {
                    $actionlog_link = do_lang_tempcode('UNKNOWN_EM');
                } else {
                    $actionlog_url = build_url(array('page' => 'admin_actionlog', 'type' => 'view', 'id' => $revision['r_actionlog_id'], 'mode' => 'cms'), get_module_zone('admin_actionlog'));
                    $actionlog_link = hyperlink($actionlog_url, do_lang_tempcode('LOG'), false, false, '#' . strval($revision['r_actionlog_id']));
                }

                $_revision = array(
                    escape_html($date),
                    $member_link,
                    escape_html(clean_file_size($size_change)),
                    $diff_icon,
                    $undo_link,
                );
                if ($do_actionlog) {
                    $_revision[] = $actionlog_link;
                }
                $field_rows->attach(results_entry($_revision, false));

                $more_recent_text = $revision['r_original_text']; // For next iteration
            }

            if ($field_rows->is_empty()) {
                return new Tempcode();
            }

            $fields_titles = results_field_title($_fields_titles, $sortables, 'revisions_sort', $sortable . ' ' . $sort_order);
            $results = results_table(
                do_lang_tempcode('REVISIONS'),
                $start,
                'revisions_start',
                $max,
                'revisions_max',
                $max_rows,
                $fields_titles,
                $field_rows,
                $sortables,
                $sortable,
                $sort_order,
                'revisions_sort'
            );

            $revisions_tpl = do_template('REVISIONS_WRAP', array(
                '_GUID' => '2fc38d9d7ec57af110759352446e533d',
                'RESULTS' => $results,
            ));

        } else {
            $revisions_tpl = new Tempcode();

            if ($restore_from_path !== null) {
                if (dirname(filter_naughty($restore_from_path)) == $directory && file_exists(get_custom_file_base() . '/' . filter_naughty($restore_from_path))) {
                    $text = file_get_contents(get_custom_file_base() . '/' . filter_naughty($restore_from_path));
                    $revision_loaded = true;

                    $revisions_tpl = do_template('REVISION_UNDO');
                } else {
                    // Should not happen
                }
            }

            if ($undo_revision !== null) {
                $revisions = $this->find_revisions($directory, $filename_id, $ext, $action, $undo_revision);
                if (array_key_exists(0, $revisions)) {
                    $text = $revisions[0]['r_original_text'];
                    $revision_loaded = true;

                    $revisions_tpl = do_template('REVISION_UNDO');
                } else {
                    // Should not happen
                }
            }
        }

        return $revisions_tpl;
    }
}
