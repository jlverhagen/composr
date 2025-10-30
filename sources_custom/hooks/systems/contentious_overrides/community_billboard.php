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
 * @package    community_billboard
 */

/**
 * Hook class.
 */
class Hook_contentious_overrides_community_billboard
{
    public function compile_template(&$data, $template_name, $theme, $lang, $suffix, $directory)
    {
        if (($template_name != 'GLOBAL_HTML_WRAP') || ($theme == 'admin') || ($suffix != '.tpl')) {
            return;
        }

        if (!addon_installed('community_billboard')) {
            return;
        }

        $data = override_str_replace_exactly(
            '{$,extra_footer_right_goes_here}',
            "<ditto>
            {+START,INCLUDE,COMMUNITY_BILLBOARD_FOOTER}{+END}",
            $data,
            1,
            true
        );
    }
}
