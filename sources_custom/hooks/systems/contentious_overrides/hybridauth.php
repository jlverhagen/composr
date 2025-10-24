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
 * @package    hybridauth
 */

/**
 * Hook class.
 */
class Hook_contentious_overrides_hybridauth
{
    public function compile_template(&$data, $template_name, $theme, $lang, $suffix, $directory)
    {
        if (($template_name != 'global') || ($suffix != '.css')) {
            return;
        }

        if (!addon_installed('hybridauth')) {
            return;
        }

        $c = 'hybridauth';
        $found = find_template_place($c, $theme, '.css', 'css');
        if ($found !== null) {
            $full_path = get_custom_file_base() . '/themes/' . $found[0] . $found[1] . $c . $found[2];
            $data .= cms_file_get_contents_safe($full_path);
        }
    }
}
