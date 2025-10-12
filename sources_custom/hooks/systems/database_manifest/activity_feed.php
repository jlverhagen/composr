<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    activity_feed
 */

/**
 * Hook class.
 */
class Hook_database_manifest_activity_feed
{

    /**
     * Database manifest for this addon.
     * This is automatically maintained by the software release tools if you are using it.
     *
     * @return array Map of tables, indices, foreign keys, and privileges
     */
    public function db_meta() : array
    {
        return [
            'tables' => [
                'activities' => [
                    'addon' => 'activity_feed',
                    'fields' => [
                        'id' => '*AUTO',
                        'a_language_string_code' => '*ID_TEXT',
                        'a_member_id' => '*MEMBER',
                        'a_also_involving' => '?MEMBER',
                        'a_is_public' => 'BINARY',
                        'a_addon' => 'ID_TEXT',
                        'a_label_1' => 'SHORT_TEXT',
                        'a_label_2' => 'SHORT_TEXT',
                        'a_label_3' => 'SHORT_TEXT',
                        'a_page_link_1' => 'SHORT_TEXT',
                        'a_page_link_2' => 'SHORT_TEXT',
                        'a_page_link_3' => 'SHORT_TEXT',
                        'a_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'activities__a_also_involving' => [
                    'addon' => 'activity_feed',
                    'name' => 'a_also_involving',
                    'table' => 'activities',
                    'fields' => [
                        0 => 'a_also_involving',
                    ],
                    'is_full_text' => false,
                ],
                'activities__a_filtered_ordered' => [
                    'addon' => 'activity_feed',
                    'name' => 'a_filtered_ordered',
                    'table' => 'activities',
                    'fields' => [
                        0 => 'a_member_id',
                        1 => 'a_time',
                    ],
                    'is_full_text' => false,
                ],
                'activities__a_member_id' => [
                    'addon' => 'activity_feed',
                    'name' => 'a_member_id',
                    'table' => 'activities',
                    'fields' => [
                        0 => 'a_member_id',
                    ],
                    'is_full_text' => false,
                ],
                'activities__a_time' => [
                    'addon' => 'activity_feed',
                    'name' => 'a_time',
                    'table' => 'activities',
                    'fields' => [
                        0 => 'a_time',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [
                'syndicate_site_activity' => [
                    'addon' => 'activity_feed',
                    'section' => 'SUBMISSION',
                    'default' => 0,
                ],
            ],
        ];
    }
}
