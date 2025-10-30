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
 * @package    composr_tutorials
 */

/**
 * Hook class.
 */
class Hook_resource_meta_aware_tutorials_external
{
    /**
     * Get content type details.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect)
     * @param  boolean $get_extended_data Populate additional data that is somewhat costly to compute (add_url, archive_url)
     * @return ?array Map of content-type info (null: disabled)
     */
    public function info(?string $zone = null, bool $get_extended_data = false) : ?array
    {
        if (!addon_installed('composr_tutorials')) {
            return null;
        }

        return [
            'support_custom_fields' => false,

            'content_type_label' => 'tutorials:TUTORIAL',
            'content_type_universal_label' => 'Tutorial',

            'db' => get_db_for('tutorials_external'),
            'table' => 'tutorials_external',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_meta_aware_type' => null,
            'is_category' => false,
            'is_entry' => true,
            'category_field' => null, // For category permissions
            'permission_module' => null, // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_is_string' => false,

            'title_field' => 't_title',
            'title_field_dereference' => false,
            'description_field' => 't_summary',
            'description_field_dereference' => false,
            'description_field_supports_comcode' => false,
            'image_field' => 't_icon',
            'image_field_is_theme_image' => true,
            'alternate_icon_theme_image' => 'icons/help',

            'view_page_link_pattern' => 'docs:_WILD',
            'edit_page_link_pattern' => '_SEARCH:cms_tutorials:_edit:_WILD',
            'view_category_page_link_pattern' => null,
            'add_url' => '_SEARCH:cms_tutorials:add',
            'archive_url' => '_SEARCH:tutorials',

            'support_url_monikers' => false,

            'views_field' => 't_views',
            'order_field' => null,
            'submitter_field' => 't_submitter',
            'author_field' => null, // NB: t_author is not an actual reference to a CMS author
            'add_time_field' => 't_add_date',
            'edit_time_field' => 't_edit_date',
            'date_field' => null,
            'validated_field' => null,
            'validation_time_field' => null,
            'additional_sort_fields' => [
                't_author' => null, // NB: t_author is not an actual reference to a CMS author
                't_media_type' => null,
                't_pinned' => null,
            ],

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'search_hook' => null,
            'rss_hook' => null,
            'attachment_hook' => null,
            'notification_hook' => null,
            'sitemap_hook' => null,

            'addon_name' => 'composr_tutorials',

            'cms_page' => 'cms_tutorials',
            'module' => 'tutorials',

            'commandr_filesystem_hook' => null,
            'commandr_filesystem__is_folder' => false,

            'support_revisions' => false,

            'support_privacy' => false,

            'support_content_reviews' => false,

            'support_spam_heuristics' => null,

            'actionlog_regexp' => '\w+_TUTORIAL',

            'default_prominence_weight' => PROMINENCE_WEIGHT_HIGH,
            'default_prominence_flags' => 0,
        ];
    }
}
