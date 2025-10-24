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
class addon_hook_quality_test_set extends cms_test_case
{
    public function testAddonTextParsing()
    {
        require_code('comcode_check');
        require_code('failure');

        set_throw_errors(true);

        $addons = find_all_hook_obs('systems', 'addon_registry', 'Hook_addon_registry_');
        foreach ($addons as $addon_name => $ob) {
            $this->assertTrue(method_exists($ob, 'get_chmod_array'), 'Missing get_chmod_array for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_version'), 'Missing get_version for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_min_cms_version'), 'Missing get_min_cms_version for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_max_cms_version'), 'Missing get_max_cms_version for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_description'), 'Missing get_description for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_applicable_tutorials'), 'Missing get_applicable_tutorials for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_dependencies'), 'Missing get_dependencies for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_default_icon'), 'Missing get_default_icon for ' . $addon_name);
            $this->assertTrue(method_exists($ob, 'get_file_list'), 'Missing get_file_list for ' . $addon_name);

            if (method_exists($ob, 'get_description')) {
                $description = $ob->get_description();

                try {
                    check_comcode($description);
                } catch (Exception $e) {
                    $this->assertTrue(false, 'Failed to parse addon description for ' . $addon_name . ', ' . $e->getMessage());
                }
            }
        }

        set_throw_errors(false);
    }
}
