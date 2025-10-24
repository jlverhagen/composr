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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class log_refs_test_set extends cms_test_case
{
    public function testNoUnexpectedLogs()
    {
        require_code('files2');

        disable_php_memory_limit();
        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        $all_code = '';
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, ['php']);
        foreach ($files as $path) {
            $all_code .= cms_file_get_contents_safe(get_file_base() . '/' . $path);
        }

        $codebook = cms_file_get_contents_safe(get_file_base() . '/docs/pages/comcode_custom/EN/codebook_3.txt', FILE_READ_LOCK | FILE_READ_BOM);

        $web_config = cms_file_get_contents_safe(get_file_base() . '/web.config', FILE_READ_LOCK | FILE_READ_BOM);

        $defined_logs = [];
        $hook_obs = find_all_hook_obs('systems', 'logs', 'Hook_logs_');
        foreach ($hook_obs as $hook_ob) {
            $defined_logs = array_merge($defined_logs, array_keys($hook_ob->enumerate_logs()));
        }

        $matches = [];
        $logs_in_code = [];
        $num_matches = preg_match_all('#data_custom/\w+\.log#', $all_code, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $log = $matches[0][$i];

            // Exceptions
            if (in_array(basename($log), [
                // For development
                'performance.log',
                'performance_warnings.log',
                'template_previews.log',

                // For non-bundled addons
                'tapatalk.log',
            ])) {
                continue;
            }

            $logs_in_code[] = $log;
        }
        $logs_in_code = array_unique($logs_in_code);

        foreach ($logs_in_code as $log) {
            $this->assertTrue(strpos($codebook, $log) !== false, 'Missing Codebook 3 listing for ' . $log);

            $this->assertTrue(in_array(basename($log), $defined_logs), 'Missing logs hook reference for ' . $log);

            $this->assertTrue(strpos($web_config, '<add segment="' . basename($log) . '" />') !== false, 'Missing web.config skip for ' . $log);
        }
    }
}
