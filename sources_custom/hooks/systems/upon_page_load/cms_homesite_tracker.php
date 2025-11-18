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
     * @param  ~array $details The page details (false: not found)
     *
     */
    public function run(string $codename, bool $required, string $zone, ?string $page_type, bool $being_included, $details)
    {
        if (!addon_installed('cms_homesite_tracker')) {
            return;
        }

        if ($codename == 'catalogues') {
            $type = get_param_string('type', $page_type);
            if ($type === 'entry') {
                $id = get_param_string('id', null);
                if ($id !== null) {
                    $catalogue = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'c_name', ['id' => intval($id)]);

                    if ($catalogue === 'tracker') {
                        // When viewing a tracker issue, force default comment sorting to oldest if not explicitly chosen by the user
                        $_POST['comments_sort'] = either_param_string('comments_sort', 'oldest');

                        require_lang('tracker');

                        // Only staff may view security issues
                        $type_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ISSUE_TYPE')]);
                        $type = $GLOBALS['SITE_DB']->query_select_value('catalogue_efv_long', 'cv_value', ['cf_id' => $type_field, 'ce_id' => intval($id)]);
                        if ($type == 'security') {
                            if (!$GLOBALS['FORUM_DRIVER']->is_staff(get_member())) {
                                warn_exit(do_lang_tempcode('TRACKER_SECURITY_NOT_STAFF'));
                            }
                            attach_message(do_lang_tempcode('TRACKER_SECURITY_STAFF'), 'notice');
                        }
                    }
                }
            }
        }

        // Prevent editing the catalogue or its categories because we strongly expect certain fields and categories (by name) to exist
        if ($codename == 'cms_catalogues') {
            $type = get_param_string('type', $page_type);
            if ($type === '_edit_catalogue') {
                $id = get_param_string('id', null);
                if ($id === 'tracker') {
                    require_lang('tracker');
                    warn_exit(do_lang_tempcode('TRACKER_CATALOGUE_NO_EDIT'));
                }
            }
            if (in_array($type, ['edit_category', '_edit_category', 'add_category', '_add_category'])) {
                $catalogue = get_param_string('catalogue_name', null);
                if ($catalogue == 'tracker') {
                    require_lang('tracker');
                    warn_exit(do_lang_tempcode('TRACKER_CATALOGUE_NO_EDIT'));
                }
            }
        }
    }
}
