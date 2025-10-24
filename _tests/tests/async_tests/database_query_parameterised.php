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
class database_query_parameterised_test_set extends cms_test_case
{
    public function testParameterisation()
    {
        if (strpos(get_db_type(), 'mysql') === false) {
            $this->assertTrue(false, 'Test only works on MySQL database backends');
            return;
        }

        $parameters = [
            'int' => 1,
            'float' => 1.34,
            'bool' => true,
            'null' => null,
            'string_dangerous_1' => "let's be unsafe",
            'string_dangerous_2' => 'a\b',
        ];

        $tests = [
            // Table prefix
            "SELECT * FROM {prefix}foobar" => "SELECT * FROM " . get_table_prefix() . "foobar",

            // Quotes Omitted
            "SELECT * FROM foobar WHERE x={int}" => "SELECT * FROM foobar WHERE x=1",
            "SELECT * FROM foobar WHERE x={float}" => "SELECT * FROM foobar WHERE x=1.3400000000",
            "SELECT * FROM foobar WHERE x={bool}" => "SELECT * FROM foobar WHERE x=1",
            "SELECT * FROM foobar WHERE x={null}" => "SELECT * FROM foobar WHERE x=NULL",
            "SELECT * FROM foobar WHERE x={string_dangerous_1}" => "SELECT * FROM foobar WHERE x='let\'s be unsafe'",
            "SELECT * FROM foobar WHERE x={string_dangerous_2}" => "SELECT * FROM foobar WHERE x='a\\\\b'",

            // Quotes given
            "SELECT * FROM foobar WHERE x='{int}'" => "SELECT * FROM foobar WHERE x='1'",
            "SELECT * FROM foobar WHERE x='{float}'" => "SELECT * FROM foobar WHERE x='1.3400000000'",
            "SELECT * FROM foobar WHERE x='{bool}'" => "SELECT * FROM foobar WHERE x='1'",
            "SELECT * FROM foobar WHERE x='{null}'" => "SELECT * FROM foobar WHERE x=NULL",
            "SELECT * FROM foobar WHERE x='{string_dangerous_1}'" => "SELECT * FROM foobar WHERE x='let\'s be unsafe'",
            "SELECT * FROM foobar WHERE x='{string_dangerous_2}'" => "SELECT * FROM foobar WHERE x='a\\\\b'",

            // Mixing
            "SELECT * FROM foobar WHERE x='{string_dangerous_1}' OR x={string_dangerous_2} OR 1=1" => "SELECT * FROM foobar WHERE x='let\'s be unsafe' OR x='a\\\\b' OR 1=1",
            "SELECT * FROM foobar WHERE a='b' OR x='{string_dangerous_2}'" => "SELECT * FROM foobar WHERE a='b' OR x='a\\\\b'",

            // Missing params
            "SELECT * FROM foobar WHERE x={missing}" => "SELECT * FROM foobar WHERE x={missing}",
            "SELECT * FROM foobar WHERE x='{missing}'" => "SELECT * FROM foobar WHERE x='{missing}'",
        ];

        foreach ($tests as $before => $expected) {
            $got = $GLOBALS['SITE_DB']->_query_parameterised($before, $parameters);
            $this->assertTrue($got == $expected, 'Incorrect result for: ' . $before . '; got: ' . $got);
        }
    }
}
