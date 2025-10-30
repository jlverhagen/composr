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
class sorting_test_set extends cms_test_case
{
    public function testSortMapsByMultipleParameters()
    {
        $results = [
            ['a' => 1, 'b' => 1, 'expected' => 1],
            ['a' => 1, 'b' => 2, 'expected' => 2],
            ['a' => 2, 'b' => 2, 'expected' => 4],
            ['a' => 2, 'b' => 1, 'expected' => 3],
        ];

        $expected = [
            ['a' => 1, 'b' => 1, 'expected' => 1],
            ['a' => 1, 'b' => 2, 'expected' => 2],
            ['a' => 2, 'b' => 1, 'expected' => 3],
            ['a' => 2, 'b' => 2, 'expected' => 4],
        ];

        sort_maps_by($results, 'a,b');

        $this->assertTrue($results == $expected);

        $results = [
            ['a' => 1, 'b' => 1, 'expected' => 1],
            ['a' => 1, 'b' => 2, 'expected' => 2],
            ['a' => 2, 'b' => 2, 'expected' => 4],
            ['a' => 2, 'b' => 1, 'expected' => 3],
        ];

        $expected = [
            ['a' => 2, 'b' => 2, 'expected' => 4],
            ['a' => 2, 'b' => 1, 'expected' => 3],
            ['a' => 1, 'b' => 2, 'expected' => 2],
            ['a' => 1, 'b' => 1, 'expected' => 1],
        ];

        sort_maps_by($results, '!a,!b');

        $this->assertTrue($results == $expected);
    }

    public function testSortMapsByIncludingNulls()
    {
        $arr = [
            [1],
            [3],
            [2],
        ];

        // Ascending
        sort_maps_by($arr, 0);
        $expected = [
            [1],
            [2],
            [3],
        ];
        $this->assertTrue($arr == $expected);

        // Descending
        sort_maps_by($arr, '!0');
        $expected = [
            [3],
            [2],
            [1],
        ];
        $this->assertTrue($arr == $expected);

        // Now with nulls...

        $arr = [
            [null],
            [3],
            [2],
        ];

        // Ascending
        sort_maps_by($arr, 0);
        $expected = [
            [null],
            [2],
            [3],
        ];
        $this->assertTrue($arr == $expected);

        // Descending
        sort_maps_by($arr, '!0');
        $expected = [
            [3],
            [2],
            [null],
        ];
        $this->assertTrue($arr == $expected);
    }
}
