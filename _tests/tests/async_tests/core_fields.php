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
class core_fields_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);
    }

    public function testCoreFields()
    {
        require_code('fields');
        require_code('form_templates');
        require_code('database_search');

        $fields = find_all_hook_obs('systems', 'fields', 'Hook_fields_');
        foreach ($fields as $field => $ob) {
            if (method_exists($ob, 'get_field_types')) {
                $types = $ob->get_field_types();
            } else {
                $types = [$field => ''];
            }

            $type_default_override = [
                'country' => 'GB',
                'region' => 'US-OH',
            ];

            foreach (array_keys($types) as $type) {
                $field = [
                    'id' => 1,
                    'c_name' => 'hosted',
                    'cf_type' => $type,
                    'cf_default' => '',
                    'cf_required' => 0,
                ] + insert_lang('cf_name', 'Test', 4);

                $test = $ob->get_search_inputter($field);
                $this->assertTrue(($test === null) || is_array($test), 'Failed get_search_inputter for ' . $type);

                $test = $ob->inputted_to_sql_for_search($field, 1);
                $this->assertTrue(($test === null) || is_array($test), 'Failed inputted_to_sql_for_search for ' . $type);

                $test = $ob->get_field_value_row_bits($field);
                $this->assertTrue(is_array($test), 'Failed get_field_value_row_bits for ' . $type);

                if (substr($type, 0, 3) == 'th_') {
                    $test = $ob->render_field_value($field, 'icons/status/warn', 0, null);
                } else {
                    $test = $ob->render_field_value($field, 'test', 0, null);
                }
                $this->assertTrue(is_string($test) || is_object($test), 'Failed render_field_value for ' . $type);

                $test = $ob->get_field_inputter('Test', 'Description.', $field, '', true);
                $this->assertTrue(($test === null) || is_object($test) || is_array($test), 'Failed get_field_inputter for ' . $type);

                $test = $ob->inputted_to_field_value(false, $field);
                $this->assertTrue(($test === null) || is_string($test), 'Failed inputted_to_field_value for ' . $type);
            }
        }
    }
}
