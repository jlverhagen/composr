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
class suphp_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);
    }

    public function testWritableDirectoriesWithPHP()
    {
        require_code('files2');
        require_code('file_permissions_check');

        $paths = get_directory_contents(get_file_base(), '', 0, true, false);
        $chmod_paths = Source_permissions_scanner::get_chmod_array(false, true);

        foreach ($paths as $path) {
            // Exceptions
            if (in_array($path, [
                'exports/static', // Has to be able to export a .php script into there
            ])) {
                continue;
            }

            foreach ($chmod_paths as $chmod_path) {
                if (preg_match('#^' . str_replace('\*\*', '[^/]+', preg_quote($chmod_path, '#')) . '$#', $path) != 0) {
                    $php_files = get_directory_contents(get_file_base() . '/' . $path, '', 0, false, true, ['php']);
                    $this->assertTrue(empty($php_files), get_file_base() . '/' . $path . ' is writable and contains PHP scripts.');
                }
            }
        }
    }
}
