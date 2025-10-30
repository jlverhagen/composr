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

/*EXTRA FUNCTIONS: shell_exec*/

// php _tests/index.php cli_tests/_bash_parser

/**
 * Composr test case class (unit testing).
 */
class _bash_parser_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        if (!is_cli()) {
            warn_exit('This test should be run on the command line: php _tests/index.php cli_tests/_bash_parser');
        }
    }

    public function testValidCode()
    {
        require_code('files2');
        $php_path = find_php_path();

        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, ['php']);
        $files[] = 'install.php';
        foreach ($files as $path) {
            if (basename($path) == 'phpstub.php') {
                continue;
            }
            if (basename($path) == 'test_with_parse_error.php') { // Intentionally has an error in it
                continue;
            }

            cms_set_time_limit(5);

            // NB: php-no-ext bit works around bug in Windows version of PHP with slow startup. Make a ../php-no-ext/php.ini file with no extensions listed for loading
            $cmd = $php_path . ' -l ' . cms_escapeshellarg(get_file_base() . '/' . $path) . ' -c ' . cms_escapeshellarg(get_file_base() . '/../php-no-ext');
            $message = shell_exec($cmd);
            if ($message === null) {
                $message = '';
            }

            $this->assertTrue(strpos($message, 'No syntax errors detected') !== false, $message . ' (' . $path . ')');
        }
    }
}
