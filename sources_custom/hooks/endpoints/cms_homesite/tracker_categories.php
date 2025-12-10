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
class Hook_endpoint_cms_homesite_tracker_categories
{
    /**
     * Return information about this endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return ?array Info about the hook (null: endpoint is disabled)
     */
    public function info(?string $type, ?string $id) : ?array
    {
        if (!addon_installed('catalogues')) {
            return null;
        }
        if (!addon_installed('cms_homesite')) {
            return null;
        }
        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        return [
            'authorization' => false,
        ];
    }

    /**
     * Run an API endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return array Data structure that will be converted to correct response type
     */
    public function run(?string $type, ?string $id) : array
    {
        require_code('cms_homesite');
        require_code('addons2');
        require_lang('tracker');

        // Categories
        $category_rows = $GLOBALS['SITE_DB']->query_select('catalogue_categories', ['id', 'cc_title'], ['c_name' => 'tracker']);
        $categories = [];
        foreach ($category_rows as $row) {
            $categories[$row['id']] = get_translated_text($row['cc_title']);
        }

        // FUDGE: Severities
        $severities = [];
        foreach (['feature', 'trivial', 'minor', 'major', 'security'] as $severity) {
            $severities[$severity] = do_lang('TRACKER_CATALOGUE_ISSUE_TYPE_DEFAULT_' . $severity);
        }

        // Addons
        $available_addons = array_keys(find_available_addons(false, false, [], false, true));
        $installed_addons = array_keys(find_installed_addons(false, false, false));
        $addons = array_unique(array_merge($available_addons, $installed_addons));

        return [$categories, $severities, $addons];
    }
}
