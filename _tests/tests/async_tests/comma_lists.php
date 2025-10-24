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
class comma_lists_test_set extends cms_test_case
{
    public function testSerialize()
    {
        // Empty test
        $map = [
        ];
        $str = '';
        $got = comma_list_arr_to_str($map);
        $this->assertTrue($got == $str, 'Got ' . $got . ' when expected ' . $str);

        // Test various cases
        $map = [
            'a' => 'b', // Simple
            '' => 'lorem', // Blank key
            'foo' => 'foo,bar', // Comma
            3 => 'hello=this=that', // Equals
            4 => 'foobar', // Numeric key
            5 => '', // Totally blank
        ];
        $str = '=lorem,hello=this\\=that,4=foobar,5=,a=b,foo=foo\\,bar';
        $got = comma_list_arr_to_str($map);
        $this->assertTrue($got == $str, 'Got ' . $got . ' when we expected ' . $str . '. This test may fail on PHP < 8.'); // From PHP: If two members compare as equal, they retain their original order. Prior to PHP 8.0.0, their relative order in the sorted array was undefined.
    }

    public function testDeserialize()
    {
        // Empty test
        $map = [
        ];
        $str = '';
        $got = comma_list_str_to_arr($str);
        ksort($map);
        ksort($got);
        $this->assertTrue($got == $map, 'Got ' . var_export($got, true) . ' when expected ' . var_export($map, true));

        // Test various cases with $block_symbol_style off
        $str = '=lorem,a=b,foo=foo\,bar,3=hello=this,4=foobar,,=lorem2,x=';
        $map = [
            'a' => 'b', // Simple
            '' => 'lorem', // Blank key
            'foo' => 'foo,bar', // Comma
            3 => 'hello=this', // Equals
            4 => 'foobar', // Numeric key
            5 => '', // Totally blank
            6 => 'lorem2', // Multiple blank keys
            'x' => '', // Blank value
        ];
        $got = comma_list_str_to_arr($str);
        ksort($map);
        ksort($got);
        $this->assertTrue($got == $map, 'Got ' . var_export($got, true) . ' when expected ' . var_export($map, true));

        // Test various cases with $block_symbol_style on
        $str = '=lorem,a=b,foo=foo\,bar,3=hello=this,4=foobar,,=lorem2,x=';
        $map = [
            'a=b', // Simple
            '=lorem', // Blank key
            'foo=foo\,bar', // Comma
            '3=hello=this', // Equals
            '4=foobar', // Numeric key
            '', // Totally blank
            '=lorem2', // Multiple blank keys
            'x=', // Blank value
        ];
        $got = comma_list_str_to_arr($str, true);
        sort($map);
        sort($got);
        $this->assertTrue($got == $map, 'Got ' . var_export($got, true) . ' when expected ' . var_export($map, true));
    }
}
