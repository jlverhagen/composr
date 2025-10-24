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
class csrf_tags_test_set extends cms_test_case
{
    public function testTemplates()
    {
        $dirs = [
            get_file_base() . '/themes/default/templates',
            get_file_base() . '/themes/default/templates_custom',
        ];
        foreach ($dirs as $dir) {
            $dh = opendir($dir);
            while (($file = readdir($dh)) !== false) {
                if (($file === '.') || ($file === '..')) {
                    continue;
                }

                if (!is_file($dir . '/' . $file)) { // We might have sub-directories
                    continue;
                }

                if (strpos($dir, '/_old_backups') !== false) { // Do not check anything in backups
                    continue;
                }

                $_c = cms_file_get_contents_safe($dir . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);
                if ($_c === false) {
                    $this->assertTrue(false, 'Error opening file: ' . $dir . '/' . $file);
                    continue;
                }

                $c = strval($_c);

                if (strpos($c, '<form') !== false) {
                    if (strpos($c, 'button-hyperlink') !== false) {
                        continue;
                    }

                    if (strpos($c, 'method="get"') !== false) {
                        continue;
                    }

                    if (strpos($c, 'login-username') !== false) {
                        continue;
                    }

                    if (strpos($c, 'action="#"') !== false) {
                        continue;
                    }

                    $c = preg_replace('#<input[^<>]* type="(button|submit|image)"[^<>]*>#', '', $c);
                    if ((strpos($c, '<input') === false) && (strpos($c, '<select') === false) && (strpos($c, '<textarea') === false)) {
                        continue;
                    }

                    if (in_array($file, [
                        'INSTALLER_STEP_1.tpl',
                        'INSTALLER_STEP_2.tpl',
                        'INSTALLER_STEP_3.tpl',
                        'INSTALLER_STEP_9.tpl',
                        'TEMPCODE_TESTER_SCREEN.tpl',
                    ])) {
                        continue;
                    }
                    if (preg_match('#^ECOM_.*_VIA_.*#', $file) != 0) {
                        continue;
                    }

                    $this->assertTrue(strpos($c, '{$INSERT_FORM_POST_SECURITY') !== false, $file);
                }
            }
            closedir($dh);
        }
    }
}
