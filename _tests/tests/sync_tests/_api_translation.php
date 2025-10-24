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
class _api_translation_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('translation');

        set_option('bing_translator_enabled', '1');
        set_option('google_translate_enabled', '1');
        $this->load_key_options('azure');
        $this->load_key_options('google');
    }

    public function testTranslation()
    {
        $this->assertTrue(has_translation());

        $hooks = [
            'bing_translator',
            'google_translate',
        ];

        $from = 'EN';
        $to = 'FR';

        foreach ($hooks as $hook) {
            $translation_object = get_translation_object_for_hook($hook);
            $this->assertTrue(has_translation($from, $to, $translation_object));

            if ($hook == 'google_translate') {
                $this->assertTrue(get_translation_credit($from, $to, $hook) != '', 'Got empty translation credit for google_translate');
            }
        }

        $this->run_health_check('API connections', 'Translation');
    }
}
