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
class addon_dependency_naming_test_set extends cms_test_case
{
    protected $special_dep_codes = [
        'MySQL',
        'System scheduler',
        //'CNS', We want people to write conversr
        'Conversr',
        'SSL',
        'PHP curl extension',
        'PHP simplexml extension',
        'PHP openssl extension',
        'PHP sessions extension',
        'PHP xml extension',
        'PHP zip extension',
        'PHP mbstring extension',
        'PHP pdo_mysql extension',
        'PHP fileinfo extension',
        'PHP gettext extension',
        'UTF-8',
    ];

    public function testAddonDependencyNaming()
    {
        $addons = find_all_hook_obs('systems', 'addon_registry', 'Hook_addon_registry_');
        foreach ($addons as $addon_name => $ob) {
            $dependencies = $ob->get_dependencies();
            $deps = array_merge($dependencies['requires'], $dependencies['recommends'], $dependencies['conflicts_with']);
            foreach ($deps as $dep) {
                $bits = explode(' ', $dep, 2);
                $ok = (in_array($dep, $this->special_dep_codes)) || (addon_installed($bits[0])) || (preg_match('#^(php) [\d\.]+$#i', $dep) != 0) || (preg_match('#^(ie|safari) [\d\.]+\+$#i', $dep) != 0);
                $this->assertTrue($ok, 'Unknown addon dependency, ' . $dep . ', in ' . $addon_name . '. It should be defined in this test and then checked in the health check and upgrader2.php has_feature().');
            }
        }
    }

    public function testHasFeatureDefinition()
    {
        $c = file_get_contents(get_file_base() . '/sources/addons2.php');
        foreach ($this->special_dep_codes as $dep_code) {
            $this->assertTrue((strpos($c, '(cms_strtolower_ascii($dependency) == \'' . cms_strtolower_ascii($dep_code) . '\')') !== false), 'Could not find a check for special dependency "' . $dep_code . '" in upgrader2.php has_feature().');
        }
    }
}
