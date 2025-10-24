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
class import_test_set extends cms_test_case
{
    public function testImportCyclicDependencies()
    {
        require_code('import');
        require_code('zones');

        require_lang('import');

        // Test to see if any imports have a cyclic dependency
        $hooks = find_all_hook_obs('modules', 'admin_import', 'Hook_import_');
        foreach ($hooks as $hook => $obj) {
            $info = $obj->info();
            if (isset($info['import'])) {
                $imports = $info['import'];
                $dependencies = isset($info['dependencies']) ? $info['dependencies'] : null;
                $sort = sort_imports_by_dependencies($imports, $dependencies, true);
                $this->assertTrue(($sort !== null), 'Import hook ' . $hook . ' probably has a cyclic dependency.');
            }
        }
    }
}
