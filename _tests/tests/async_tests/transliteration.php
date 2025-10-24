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
class transliteration_test_set extends cms_test_case
{
    public function testTransliterationAddon()
    {
        if (!addon_installed('transliteration')) {
            $this->assertTrue(false, 'The transliteration addon must be installed for this test to run');
            return;
        }

        require_code('character_sets');

        $expect = [
            ['foo', ['foo']],
            ["gl\u{00FC}ckliche", ['gluckliche']],
            ["caf\u{00E9}", ['cafe']],
            ["\u{6E90}\u{660C}\u{9686}\u{5496}\u{5561}\u{5E97}", ['yuan chang long ka fei dian', 'yuan-chang-long-ka-pei-dian']],
        ];

        foreach ($expect as $_) {
            list($from, $to) = $_;

            $got = transliterate_string($from);
            $this->assertTrue(in_array($got, $to), 'Failed to get one of ' . json_encode($to) . ', got ' . $got);
        }
    }
}
