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
 * @package    cms_homesite_tracker
 */

/**
 * Hook class.
 */
class Hook_upon_page_load_cms_homesite_tracker
{
    /**
     * Upon page load run.
     *
     * @param  ID_TEXT $codename The codename of the page to load
     * @param  boolean $required Whether it is required for this page to exist (shows an error if it doesn't) -- otherwise, it will just return null
     * @param  ID_TEXT $zone The zone the page is being loaded in
     * @param  ?ID_TEXT $page_type The type of page - for if you know it (null: don't know it)
     * @param  boolean $being_included Whether the page is being included from another
     */
    public function run(string $codename, bool $required, string $zone, ?string $page_type, bool $being_included, $details)
    {
        if (!addon_installed('cms_homesite_tracker')) {
            return;
        }
    }
}
