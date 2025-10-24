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
class hyperlink_targets_test_set extends cms_test_case
{
    public function testHyperlinkTargets()
    {
        require_code('files2');
        $dirs = [
            'themes/default/templates',
            'themes/default/templates_custom',
            'lang/' . fallback_lang(),
            'lang_custom/' . fallback_lang(),
        ];
        foreach ($dirs as $path) {
            $files = get_directory_contents(get_file_base() . '/' . $path, get_file_base() . '/' . $path, IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING | IGNORE_CUSTOM_THEMES);
            foreach ($files as $_path) {
                $c = cms_file_get_contents_safe($_path, FILE_READ_LOCK | FILE_READ_BOM);
                $this->assertTrue(strpos($c, ' target="blank"') === false, 'Uses a "blank" target rather than a "_blank" target');
            }
        }
    }
}
