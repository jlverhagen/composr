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
class addon_screenshots_test_set extends cms_test_case
{
    public function testNoUnmatchedScreenshots()
    {
        $dh = opendir(get_file_base() . '/data_custom/images/addon_screenshots');
        while (($file = readdir($dh)) !== false) {
            if ((substr($file, -5) != '.html') && ($file[0] != '.')) {
                $hook = preg_replace('#\..*$#', '', $file);
                $this->assertTrue(addon_installed($hook, false, false, false), 'Unrecognised addon screenshot: ' . $file);
            }
        }
        closedir($dh);
    }

    public function testNoMissingScreenshots()
    {
        $hooks = find_all_hooks('systems', 'addon_registry');
        foreach ($hooks as $hook => $place) {
            if ($place == 'sources_custom') {
                $ob = get_hook_ob('systems', 'addon_registry', filter_naughty_harsh($hook), 'Hook_addon_registry_', true);

                if ($ob === null) {
                    fatal_exit('Could not initiate ' . $hook);
                }

                $exists = false;
                foreach (['png', 'gif', 'jpg', 'jpeg'] as $ext) {
                    if (is_file(get_file_base() . '/data_custom/images/addon_screenshots/' . $hook . '.' . $ext)) {
                        $exists = true;
                    }
                }

                if ($ob->get_category() != 'Development') {
                    // These are defined as exceptions where we won't enforce our screenshot rule
                    if (in_array($hook, [
                        'enhanced_spreadsheets',
                        'pagination_protection',
                    ])) {
                        continue;
                    }

                    $this->assertTrue($exists, 'Missing addon screenshot: ' . $hook);
                }
            }
        }
    }
}
