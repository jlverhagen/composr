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
class form_reserved_names_test_set extends cms_test_case
{
    public function testReservedNames()
    {
        require_code('files2');

        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        $reserved_names = [ // Also see .eslintrc.json
            'method',
            'action',
            'target',
        ];

        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_NONBUNDLED | IGNORE_FLOATING, true, true, ['php', 'tpl']);
        $files[] = 'install.php';
        foreach ($files as $path) {
            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            $matches = [];
            if (substr($path, -4) == '.php') {
                $does_match = (preg_match('#form_input_(.*)\(.*\'(' . implode('|', $reserved_names) . ')\'#', $c, $matches) != 0);
                $this->assertTrue(!$does_match, 'Reserved field name input (' . @strval($matches[2]) . ', in ' . $path . ')');

                $does_match = (preg_match('#post_param(.*)\(.*\'(' . implode('|', $reserved_names) . ')\'#', $c, $matches) != 0);
                $this->assertTrue(!$does_match, 'Reserved field name read (' . @strval($matches[2]) . ', in ' . $path . ')');
            } elseif (substr($path, -4) == '.tpl') {
                $does_match = (preg_match('#(name|id)="(' . implode('|', $reserved_names) . ')"#', $c, $matches) != 0);
                $this->assertTrue(!$does_match, 'Reserved field name input (' . @strval($matches[2]) . ', in ' . $path . ')');
            }
        }
    }
}
