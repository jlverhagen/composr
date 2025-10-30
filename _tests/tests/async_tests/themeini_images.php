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
class themeini_images_test_set extends cms_test_case
{
    public function testThemeImageThere()
    {
        global $THEMEWIZARD_IMAGES;

        require_code('themes2');
        require_code('themewizard');

        $themes = find_all_themes();
        foreach (array_keys($themes) as $theme) {
            // Exceptions
            if (in_array($theme, [
                '_unnamed_',
                '_testing_',
            ])) {
                continue;
            }

            load_themewizard_params_from_theme($theme);

            foreach ($THEMEWIZARD_IMAGES as $theme_image) {
                if (strpos($theme_image, '*') === false) {
                    $this->assertTrue(find_theme_image($theme_image, true) != '', 'Missing but referenced in theme.ini: ' . $theme_image);
                } else { // This code branch is assumptive (that the '*' goes on the end), but it works with the current theme.ini...
                    $x = str_replace('/*', '', $theme_image);
                    $there = is_dir(get_file_base() . '/themes/default/images/' . $x) || is_dir(get_file_base() . '/themes/default/images/EN/' . $x) || is_dir(get_file_base() . '/themes/' . $theme . '/images/' . $x) || is_dir(get_file_base() . '/themes/' . $theme . '/images/EN/' . $x);
                    $this->assertTrue($there, 'Possible error on this theme.ini image wildcard: ' . $theme_image);
                }
            }
        }
    }
}
