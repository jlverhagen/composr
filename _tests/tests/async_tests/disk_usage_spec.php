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
class disk_usage_spec_test_set extends cms_test_case
{
    public function testUsage()
    {
        $size = 0;

        $all_files = [];

        $hooks = find_all_hooks('systems', 'addon_registry');
        ksort($hooks);
        foreach ($hooks as $hook => $dir) {
            if ($dir == 'sources_custom') {
                continue;
            }
            if ($this->only === 'core') {
                if (($hook != 'core') && (substr($hook, 0, 5) != 'core_')) {
                    continue;
                }
            }

            require_code('hooks/systems/addon_registry/' . $hook);
            $ob = object_factory('Hook_addon_registry_' . $hook, true);
            if ($ob !== null) {
                $files = $ob->get_file_list();

                foreach ($files as $path) {
                    $s = @filesize($path);
                    if (($s === null) || ($s === false)) {
                        if ($this->debug) {
                            $this->dump($path, 'This file was skipped as it could not be found or accessed.');
                        }
                        continue;
                    }

                    if ($s % 512 != 0) {
                        $s += 512; // Round up to nearest block
                    }
                    $s += 512; // Assume a block for directory entry data
                    $file_size = $s;
                    $all_files[$path] = $file_size;
                    $size += $file_size;
                }
            }
        }

        arsort($all_files);
        if ($this->debug) {
            var_dump(['Average file size' => array_sum($all_files) / count($all_files), 'Total files' => count($all_files)]);

            var_dump($all_files);
        }

        $size *= 2; // For quick installer

        $size += 5 * 1024 * 1024; // Some overhead for installer PHP code, etc

        $this->assertTrue($size < 250 * 1024 * 1024, 'Install size is ' . integer_format($size) . ', which is above the defined system requirements; update requirements (tut_webhosting, downloads page of the homesite, and install_env health check) and this test');
    }
}
