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
 * @package    giftr
 */

/**
 * Hook class.
 */
class Hook_contentious_overrides_giftr
{
    public function compile_template(&$data, $template_name, $theme, $lang, $suffix, $directory)
    {
        if (($template_name != 'BLOCK_MAIN_BOTTOM_BAR') || ($suffix != '.tpl')) {
            return;
        }

        if (!addon_installed('giftr')) {
            return;
        }

        $data = str_replace(
            'href="{BIRTHDAY_URL*}" title="{!CREATE_BIRTHDAY_TOPIC}: {$DISPLAYED_USERNAME*,{USERNAME}}">{$DISPLAYED_USERNAME*,{USERNAME}}</a>{+START,IF_PASSED,AGE} ({AGE*}){+END}',
            'href="{PROFILE_URL*}" title="{!CREATE_BIRTHDAY_TOPIC}: {$DISPLAYED_USERNAME*,{USERNAME}}">{$DISPLAYED_USERNAME*,{USERNAME}}</a>&nbsp;<a href="{$PAGE_LINK*,_SEARCH:purchase:browse:category=giftr:username={USERNAME}}" title="{!giftr:GIFT_GIFT}"><img alt="{!giftr:GIFT_GIFT}" width="14" height="14" src="{$IMG*,icons/birthday}" /></a>',
            $data
        );
    }
}
