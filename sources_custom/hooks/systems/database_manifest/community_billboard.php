<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

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
class Hook_database_manifest_community_billboard
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
                'community_billboard' => [
                    'addon' => 'community_billboard',
                    'fields' => [
                        'id' => '*AUTO',
                        'activation_time' => '?TIME',
                        'active_now' => 'BINARY',
                        'days' => 'INTEGER',
                        'notes' => 'LONG_TEXT',
                        'member_id' => 'MEMBER',
                        'the_message' => 'SHORT_TRANS__COMCODE',
                        'order_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'community_billboard__#the_message' => [
                    'addon' => 'community_billboard',
                    'name' => 'the_message',
                    'table' => 'community_billboard',
                    'fields' => [
                        0 => 'the_message',
                    ],
                    'is_full_text' => true,
                ],
                'community_billboard__find_active_billboard_msg' => [
                    'addon' => 'community_billboard',
                    'name' => 'find_active_billboard_msg',
                    'table' => 'community_billboard',
                    'fields' => [
                        0 => 'active_now',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
