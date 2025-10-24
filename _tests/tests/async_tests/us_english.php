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
class us_english_test_set extends cms_test_case
{
    public function testUSEnglish()
    {
        // Test British English
        set_option('yeehaw', '0');
        $this->clear_caches();
        $this->assertTrue(do_lang('COLOUR') == 'Colour', 'Failed with ' . do_lang('COLOUR') . ', expected British English');

        // Test US English
        set_option('yeehaw', '1');
        $this->clear_caches();
        $this->assertTrue(do_lang('COLOUR') == 'Color', 'Failed with ' . do_lang('COLOUR') . ', expected US English');
    }

    protected function clear_caches()
    {
        global $ALLOW_DOUBLE_DECACHE;
        $ALLOW_DOUBLE_DECACHE = true;

        // Flush main caches
        require_code('caches3');
        erase_persistent_cache();
        erase_cached_language();

        // Do it again
        erase_persistent_cache();
        erase_cached_language();
    }
}
