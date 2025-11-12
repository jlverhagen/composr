<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite
 */

/**
 * Hook class.
 */
class Hook_cron_cmsusers
{
    public $label = 'cms_homesite:CMSUSERS_CRON';
    public $fallback_label = 'Composr installs check';

    /**
     * Get info from this hook.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     * @param  ?boolean $calculate_num_queued Calculate the number of items queued, if possible (null: the hook may decide / low priority)
     * @return ?array Return a map of info about the hook (null: disabled)
     */
    public function info(?int $last_run, ?bool $calculate_num_queued) : ?array
    {
        if (!addon_installed('cms_homesite')) {
            return null;
        }

        return [
            'num_queued' => 10, // We only check up to 10 at a time
            'minutes_between_runs' => 15,
            'enabled_by_default' => true,
        ];
    }

    /**
     * Run function for system scheduler hooks. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run(?int $last_run)
    {
        require_lang('cms_homesite');

        $start = 0;
        $max = 100;
        $count_checked = 0;
        do {
            // Get installed sites which have not been checked in the last 24 hours
            $where = ' AND (last_checked IS NULL OR last_checked<' . strval(time() - (60 * 60 * 24)) . ') AND website_url NOT LIKE \'%.composr.info%\'';

            // Ignore local installs
            $where .= ' AND ' . db_string_not_equal_to('website_url', '%://localhost%') . ' AND ' . db_string_not_equal_to('website_url', '%://127.0.0.1%') . ' AND ' . db_string_not_equal_to('website_url', '%://192.168.%') . ' AND ' . db_string_not_equal_to('website_url', '%://10.0.%');

            $rows = $GLOBALS['SITE_DB']->query_select('telemetry_sites', ['*'], [], $where, $max, $start);

            foreach ($rows as $i => $r) {
                // That's enough for this Cron iteration
                if ($count_checked >= 10) {
                    break;
                }

                // Check if the site is still installed
                $test_2 = cms_http_request($r['website_url'] . '/data/installed.php', ['convert_to_internal_encoding' => true, 'trigger_error' => false, 'byte_limit' => (1024 * 4), 'ua' => get_brand_base_url() . ' telemetry service', 'timeout' => 6.0]);
                $count_checked++;

                if ($test_2->data === 'Remove me!') { // The site wants us to remove them immediately, so let's do so.
                    $GLOBALS['SITE_DB']->query_delete('telemetry_stats', ['s_site' => $r['id']]);
                    $GLOBALS['SITE_DB']->query_delete('telemetry_errors', ['e_site' => $r['id']]);
                    $GLOBALS['SITE_DB']->query_delete('telemetry_sites', ['id' => $r['id']]);
                    continue;
                } elseif ($test_2->data === 'Yes') {
                    $GLOBALS['SITE_DB']->query_update('telemetry_sites', ['last_checked' => time(), 'website_installed' => do_lang('YES')], ['id' => $r['id']]);
                } else {
                    $active = @strval($test_2->message);
                    if ($active == '') { // File exists but did not explicitly say 'Yes' the software is still installed, so certainly it is not.
                        $active = do_lang('NO');
                    } else {
                        $active .= do_lang('CMS_WHEN_CHECKING');
                    }

                    $last_adminzone_access = $GLOBALS['SITE_DB']->query_select_value('telemetry_stats', 'MAX(date_and_time)', ['s_site' => $r['id']]);
                    $last_error = $GLOBALS['SITE_DB']->query_select_value('telemetry_errors', 'MAX(e_last_date_and_time)', ['e_site' => $r['id']]);

                    $last_telemetry = max($last_adminzone_access, $last_error, $r['add_date_and_time']);

                    // Forget non-installed sites which were registered over a year ago and also sent no telemetry data in the last year
                    if (($last_telemetry <= (time() - (60 * 60 * 24 * 365))) && ($r['website_installed'] != do_lang('YES'))) {
                        $GLOBALS['SITE_DB']->query_delete('telemetry_stats', ['s_site' => $r['id']]);
                        $GLOBALS['SITE_DB']->query_delete('telemetry_errors', ['e_site' => $r['id']]);
                        $GLOBALS['SITE_DB']->query_delete('telemetry_sites', ['id' => $r['id']]);
                        continue;
                    }

                    $GLOBALS['SITE_DB']->query_update('telemetry_sites', ['last_checked' => time(), 'website_installed' => $active], ['id' => $r['id']]);
                }
            }

            $start += $max;
        } while (!empty($rows));
    }
}
