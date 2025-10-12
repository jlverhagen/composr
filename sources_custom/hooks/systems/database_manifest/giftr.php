<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

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
class Hook_database_manifest_giftr
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
                'giftr' => [
                    'addon' => 'giftr',
                    'fields' => [
                        'id' => '*AUTO',
                        'enabled' => 'BINARY',
                        'price' => 'INTEGER',
                        'category' => 'SHORT_TEXT',
                        'name' => 'SHORT_TEXT',
                        'image' => 'URLPATH',
                    ],
                ],
                'members_gifts' => [
                    'addon' => 'giftr',
                    'fields' => [
                        'id' => '*AUTO',
                        'gift_id' => 'AUTO_LINK',
                        'is_anonymous' => 'BINARY',
                        'gift_message' => 'LONG_TEXT',
                        'from_member_id' => 'MEMBER',
                        'to_member_id' => 'MEMBER',
                        'add_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [
                'members_gifts__gift_id||giftr__id' => [
                    'addon' => 'giftr',
                    'from_table' => 'members_gifts',
                    'from_field' => 'gift_id',
                    'to_table' => 'giftr',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
