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

// Use dev_check to find new PHP functions which might need to be added to phpstub; should be used when changing the PHP compat

/**
 * Composr test case class (unit testing).
 */
class phpstub_accuracy_test_set extends cms_test_case
{
    public function testFunctionsNeeded()
    {
        $phpstub = cms_file_get_contents_safe(get_file_base() . '/sources_custom/phpstub.php', FILE_READ_LOCK);
        $matches = [];
        $num_matches = preg_match_all('#^function (\w+)\(#m', $phpstub, $matches);
        $declared_functions = [];
        for ($i = 0; $i < $num_matches; $i++) {
            $function = $matches[1][$i];
            $declared_functions[] = $function;
        }
        sort($declared_functions);

        $c = cms_file_get_contents_safe(get_file_base() . '/sources/hooks/systems/health_checks/install_env_php_lock_down.php', FILE_READ_LOCK);
        $num_matches = preg_match_all('#<<<END(.*)END;#Us', $c, $matches);
        $c = '';
        for ($i = 0; $i < $num_matches; $i++) {
            $c .= $matches[1][$i] . "\n";
        }
        $c = str_replace("\n", ' ', $c);
        $c = trim(preg_replace('#\s+#', ' ', $c));
        $required_functions = explode(' ', $c);
        sort($required_functions);

        foreach ($declared_functions as $function) {
            // ocProducts PHP functions should not be tested for requirement as they are specific to the ocProducts PHP-dev.
            if (preg_match('#^(ocp)_#', $function) != 0) {
                continue;
            }
            $this->assertTrue(in_array($function, $required_functions), 'Missing from install_env_php_lock_down.php? ' . $function);
        }

        foreach ($required_functions as $function) {
            $this->assertTrue((in_array($function, $declared_functions)) || (strpos($phpstub, "\n" . $function . "\n") !== false), 'Missing from phpstub.php? ' . $function);
        }

        if (get_param_integer('dev_check', 0) == 1) { // This extra switch let's us automatically find new functions in PHP we aren't coding for
            $will_never_define = [
                // Extensions, inconsistent prefix
                'read_exif_data', // LEGACY
                'hash',

                // FreeType needed
                'imagefttext',
                'imageftbbox',
            ];

            $defined = get_defined_functions();
            foreach ($defined['internal'] as $function) {
                if (!in_array($function, $will_never_define)) {
                    // Extensions (note: ocp not excluded as it should be defined so IDEs do not complain about undefined functions)
                    if (preg_match('#^(pdo|dom|exif|token|apache|zip|xmlwriter|xml|simplexml|session|pspell|posix|mysqli|imap|hash|ftp|filter|finfo|curl|ctype|libxml)_#', $function) != 0) {
                        continue;
                    }
                    if (preg_match('#^(bz|mb)#', $function) != 0) {
                        continue;
                    }

                    $this->assertTrue((in_array($function, $declared_functions)) || (strpos($phpstub, "\n" . $function . "\n") !== false), 'Should be defined? ' . $function);
                }
            }
        }
    }
}
