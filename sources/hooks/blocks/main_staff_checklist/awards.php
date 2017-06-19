<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    awards
 */

/**
 * Hook class.
 */
class Hook_checklist_awards
{
    /**
     * Find items to include on the staff checklist.
     *
     * @return array An array of tuples: The task row to show, the number of seconds until it is due (or null if not on a timer), the number of things to sort out (or null if not on a queue), The name of the config option that controls the schedule (or null if no option).
     */
    public function run()
    {
        $award_types = $GLOBALS['SITE_DB']->query_select('award_types', array('*'));

        $out = array();

        foreach ($award_types as $award) {
            // Find out how many submissions we've had since the last award was given
            if ((!file_exists(get_file_base() . '/sources/hooks/systems/content_meta_aware/' . filter_naughty_harsh($award['a_content_type']) . '.php')) && (!file_exists(get_file_base() . '/sources_custom/hooks/systems/content_meta_aware/' . filter_naughty_harsh($award['a_content_type']) . '.php'))) {
                continue;
            }

            require_lang('awards');

            require_code('content');
            $hook_object = get_content_object($award['a_content_type']);
            if ($hook_object === null) {
                continue;
            }
            $details = $hook_object->info();
            if ($details !== null) {
                $date = $GLOBALS['SITE_DB']->query_select_value_if_there('award_archive', 'date_and_time', array('a_type_id' => $award['id']), 'ORDER BY date_and_time DESC');

                $seconds_ago = mixed();
                $limit_hours = $award['a_update_time_hours'];
                if ($date !== null) {
                    $seconds_ago = time() - $date;
                    $status = ($seconds_ago > $limit_hours * 60 * 60) ? 0 : 1;
                } else {
                    $status = 0;
                }

                $config_url = build_url(array('page' => 'admin_awards', 'type' => '_edit', 'id' => $award['id']), get_module_zone('admin_awards'));

                $_status = ($status == 0) ? do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_0') : do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_1');

                $url = $details['add_url'];
                if ($url !== null) {
                    $url = page_link_to_url($url);
                } else {
                    $url = '';
                }
                $url = str_replace('=!', '_ignore=1', $url);

                $task = do_lang_tempcode('_GIVE_AWARD', escape_html(get_translated_text($award['a_title'])));

                if (($date !== null) && ($details['date_field'] !== null)) {
                    $where = filter_naughty_harsh($details['date_field']) . '>' . strval($date);
                    $num_queue = $details['db']->query_value_if_there('SELECT COUNT(*) FROM ' . $details['db']->get_table_prefix() . str_replace('1=1', $where, $details['table']) . ' r WHERE ' . $where);
                    $_num_queue = integer_format($num_queue);
                    $num_new_since = do_lang_tempcode('NUM_NEW_SINCE', $_num_queue);
                } else {
                    $num_new_since = new Tempcode();
                }

                list($info, $seconds_due_in) = staff_checklist_time_ago_and_due($seconds_ago, $limit_hours);
                $info->attach($num_new_since);
                $tpl = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM', array('_GUID' => '4049affae5a6f38712ee3e0237a2e18e', 'CONFIG_URL' => $config_url, 'URL' => $url, 'STATUS' => $_status, 'TASK' => $task, 'INFO' => $info));
                $out[] = array($tpl, $seconds_due_in, null, null);
            }
        }

        return $out;
    }
}
