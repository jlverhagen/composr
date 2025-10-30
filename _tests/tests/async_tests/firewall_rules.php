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
class firewall_rules_test_set extends cms_test_case
{
    public function testFirewallRulesDownload()
    {
        $rules_path = get_custom_file_base() . '/data_custom/firewall_rules.txt';

        if (cms_is_writable($rules_path)) {
            require_code('version2');

            $new_contents = @http_get_contents('https://composr.app/data_custom/firewall_rules.txt?version=' . urlencode(get_version_dotted()), ['convert_to_internal_encoding' => true, 'trigger_error' => false]);
            $this->assertTrue(($new_contents !== false), 'Unable to download dynamic firewall rules from the Composr site.');
        } else {
            $this->assertTrue(false, 'data_custom/firewall_rules.txt is not writable.');
        }
    }

    public function testFirewallRulesFilter()
    {
        $rules_path = get_custom_file_base() . '/data_custom/firewall_rules.txt';

        if (cms_is_writable($rules_path)) {
            require_code('files');
            require_code('input_filter');

            $backup = cms_file_get_contents_safe($rules_path, FILE_READ_LOCK | FILE_READ_BOM | FILE_READ_UNIXIFIED_TEXT);

            $contents = 'username=test' . "\n" . '#^(password|secret)$#=#^\d*$#';
            cms_file_put_contents_safe($rules_path, $contents, FILE_WRITE_FIX_PERMISSIONS);

            // [key, input value, expected value]
            $test_cases = [
                ['username', 'test', 'test'],
                ['username', 'admin', 'filtered'],
                ['name', 'admin', 'admin'],
                ['password', 'butt', 'filtered'],
                ['password', '123', '123'],
                ['secret', 'butt', 'filtered'],
                ['secret', '123', '123'],
                ['#^(password|secret)$#', 'duck', 'duck'], // Testing that we are not matching a literal regexp
                ['secret', '#^\d*$#', 'filtered'], // Testing that we are not matching a literal regexp
            ];

            foreach ($test_cases as $test_case) {
                list($key, $input_value, $expected_value) = $test_case;
                $value = '' . $input_value;
                hard_filter_input_data__dynamic_firewall($key, $value);
                $this->assertTrue(($value == $expected_value), 'Expected (key=value) ' . $key . '=' . $input_value . ' to have a value of ' . $expected_value . ', but instead got ' . $value);
            }

            cms_file_put_contents_safe($rules_path, $backup, FILE_WRITE_FIX_PERMISSIONS);
        } else {
            $this->assertTrue(false, 'data_custom/firewall_rules.txt is not writable.');
        }
    }
}
