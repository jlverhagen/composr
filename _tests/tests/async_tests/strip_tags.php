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
class strip_tags_test_set extends cms_test_case
{
    public function testCmsStripTags()
    {
        $x = 'Hello <br /> <p>test</p><x>y</x>';
        $keep = '<x>';
        $expected = 'Hello  test<x>y</x>';
        $this->assertTrue(strip_tags($x, $keep) == $expected);
        $got = cms_strip_tags($x, $keep, true);
        $this->assertTrue($got == $expected, 'Got ' . $got . ' but expected ' . $expected);

        $x = 'Hello <br /> <p>test</p><x>y</x>';
        $lose = '<x>';
        $expected = 'Hello <br /> <p>test</p>y';
        $got = cms_strip_tags($x, $lose, false);
        $this->assertTrue($got == $expected, 'Got ' . $got . ' but expected ' . $expected);

        // This is annoying, but it's how strip_tags in PHP works too
        $x = '<h1>This is a title</h1><p>This is some text</p>';
        $expected = 'This is a titleThis is some text';
        $this->assertTrue(strip_tags($x, $keep) == $expected);
        $got = cms_strip_tags($x);
        $this->assertTrue($got == $expected, 'Got ' . $got . ' but expected ' . $expected);

        // ... but strip_html is smarter
        $x = '<h1>This is a title</h1><p>This is some text</p>';
        $expected = 'This is a title This is some text';
        $got = strip_html($x);
        $this->assertTrue($got == $expected, 'Got ' . $got . ' but expected ' . $expected);
    }
}
