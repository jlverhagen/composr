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
class override_issues_test_set extends cms_test_case
{
    public function testOverrideIssues()
    {
        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_FLOATING | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE, true, true, ['php']);
        $files[] = 'install.php';

        $original_classes = [];
        $custom_classes = [];

        foreach ($files as $path) {
            // Exceptions
            $exceptions = array_merge(list_untouchable_third_party_directories(), [
                '_tests', // Tests cannot be overridden, so no sense testing them
                'mobiquo', // Does not support overrides
            ]);
            if (preg_match('#^(' . implode('|', $exceptions) . ')/#', $path) != 0) {
                continue;
            }
            $exceptions = array_merge(list_untouchable_third_party_files(), [
            ]);
            if (in_array($path, $exceptions)) {
                continue;
            }

            $_c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            if (strpos($_c, '/*CQC: No API check*/') !== false) {
                continue;
            }

            $matches = [];
            if (preg_match_all('#^(abstract\s|static\s)?class\s(Hook_|Source_|Block_|Module_|Hx_|Sx_|Bx_|Mx_)([a-zA-Z0-9\-\_]*)#m', $_c, $matches) > 0) {
                foreach ($matches[1] as $i => $prefix) {
                    $class_name = $matches[2][$i] . $matches[3][$i];

                    if (strpos($path, '_custom/') === false) {
                        $original_classes[] = $class_name;
                    } else {
                        $custom_classes[] = $class_name;
                    }
                }
            }

            $this->assertTrue((strpos($_c, 'function  ') === false) && (strpos($_c, "function\t") === false), 'Function definitions must have a single space after the word function: ' . $path);

            if ((strpos($path, '_custom/') === false) && (!in_array($path, ['sources/bootstrap.php', 'sources/global.php', 'sources/global2.php']))) {
                if (strpos($_c, 'function init__') !== false) {
                    $this->assertTrue((strpos($_c, "\n    define(") === false), '\'define\' commands need a defined guard, so whole code file can be overridden naively, where init function will run twice: ' . $path);
                }
            }

            if (strpos($path, '_custom/') === false) {
                $has_class = (preg_match('#^(abstract\s|static\s)?class\s#m', $_c) > 0);
                $has_function = (preg_match('#^function\s#m', $_c) > 0);

                $this->assertTrue(((!$has_class) || (!$has_function)), 'File defines both classes and functions (should be one or the other), in ' . $path);

                $this->assertTrue(preg_match('#^(abstract\s|static\s)?class\s(?!Hook_|Module_|Block_|Source_|Tempcode$|CMSException\sextends\sException$)#m', $_c) === 0, 'Class names in bundled files should start with Hook_, Module_, Block_, or Source_, in ' . $path);
            } else {
                $this->assertTrue(preg_match('#^(abstract\s|static\s)?class\s(Hook_|Module_|Block_|Source_)([a-zA-Z0-9\_\-]*)\sextends\s(Hx_|Mx_|Bx_|Sx_)#m', $_c) === 0, 'Class overrides should use the special "x" prefix, in ' . $path);
                if (preg_match('#^(abstract\s|static\s)?class\s(Hx_|Sx_|Bx_|Mx_)#m', $_c) > 0) {
                    $this->assertTrue((strpos($_c, '/*FORCE_ORIGINAL_LOAD_FIRST*/') !== false), 'Missing /*FORCE_ORIGINAL_LOAD_FIRST*/ in a custom file that overrides classes: ' . $path);
                }
            }

            $factory_exceptions = [
                // Files must be able to run outside of bootstrap, which means object_factory might not be available
                'fixperms.php',
                'sources/file_permissions_check.php',
            ];
            if (!in_array($path, $factory_exceptions)) {
                $this->assertTrue(preg_match('#\bnew\s(Hook_|Hx_|Module_|Mx_|Block_|Bx_|Source_|Sx_)([a-zA-Z0-9\-\_]*)\(#', $_c) === 0, 'Do not use "new" when constructing a Hook/Module/Block/Source class; use object_factory, in ' . $path);
            }

            $this->assertTrue(preg_match('#\bobject_factory\(\'(Hx_|Mx_|Bx_|Sx_)#', $_c) === 0, 'When using object_factory, define the full prefix as the class parameter, not the override prefix, in ' . $path);
        }

        foreach (array_intersect(array_unique($original_classes), array_unique($custom_classes)) as $intersect) {
            $this->assertTrue(false, 'Class in custom file also exists in bundled file; must use x prefix and extend the original class in the custom file (also /*FORCE_ORIGINAL_LOAD_FIRST*/): ' . $intersect);
        }
    }
}
