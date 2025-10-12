<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    buildr
 */

/**
 * Hook class.
 */
class Hook_database_manifest_buildr
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
                'w_attempts' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'id' => '*AUTO',
                        'realm' => 'INTEGER',
                        'x' => 'INTEGER',
                        'y' => 'INTEGER',
                        'attempt' => 'SHORT_TEXT',
                        'a_datetime' => 'TIME',
                    ],
                ],
                'w_messages' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'id' => '*AUTO',
                        'location_realm' => 'INTEGER',
                        'location_x' => 'INTEGER',
                        'location_y' => 'INTEGER',
                        'destination' => 'MEMBER',
                        'originator_id' => 'MEMBER',
                        'm_message' => 'SHORT_TEXT',
                        'm_datetime' => 'TIME',
                    ],
                ],
                'w_inventory' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'item_name' => '*ID_TEXT',
                        'item_owner' => '*MEMBER',
                        'item_count' => 'INTEGER',
                    ],
                ],
                'w_itemdef' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'name' => '*ID_TEXT',
                        'bribable' => 'BINARY',
                        'healthy' => 'BINARY',
                        'replicateable' => 'BINARY',
                        'max_per_player' => 'INTEGER',
                        'owner' => 'MEMBER',
                        'the_description' => 'SHORT_TEXT',
                        'picture_url' => 'URLPATH',
                    ],
                ],
                'w_items' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'name' => '*ID_TEXT',
                        'location_realm' => '*INTEGER',
                        'location_x' => '*INTEGER',
                        'location_y' => '*INTEGER',
                        'copy_owner' => '*MEMBER',
                        'not_infinite' => 'BINARY',
                        'i_count' => 'INTEGER',
                        'price' => 'INTEGER',
                    ],
                ],
                'w_members' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'id' => '*INTEGER',
                        'banned' => 'BINARY',
                        'health' => 'INTEGER',
                        'location_realm' => 'INTEGER',
                        'location_x' => 'INTEGER',
                        'location_y' => 'INTEGER',
                        'trolled' => 'INTEGER',
                        'lastactive' => 'TIME',
                    ],
                ],
                'w_portals' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'end_location_realm' => '*INTEGER',
                        'start_location_realm' => '*INTEGER',
                        'start_location_x' => '*INTEGER',
                        'start_location_y' => '*INTEGER',
                        'owner' => '?MEMBER',
                        'name' => 'ID_TEXT',
                        'p_text' => 'ID_TEXT',
                        'end_location_x' => 'INTEGER',
                        'end_location_y' => 'INTEGER',
                    ],
                ],
                'w_realms' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'id' => '*INTEGER',
                        'owner' => '?MEMBER',
                        'r_private' => 'BINARY',
                        'q1' => 'LONG_TEXT',
                        'q10' => 'LONG_TEXT',
                        'q11' => 'LONG_TEXT',
                        'q12' => 'LONG_TEXT',
                        'q13' => 'LONG_TEXT',
                        'q14' => 'LONG_TEXT',
                        'q15' => 'LONG_TEXT',
                        'q16' => 'LONG_TEXT',
                        'q17' => 'LONG_TEXT',
                        'q18' => 'LONG_TEXT',
                        'q19' => 'LONG_TEXT',
                        'q2' => 'LONG_TEXT',
                        'q20' => 'LONG_TEXT',
                        'q21' => 'LONG_TEXT',
                        'q22' => 'LONG_TEXT',
                        'q23' => 'LONG_TEXT',
                        'q24' => 'LONG_TEXT',
                        'q25' => 'LONG_TEXT',
                        'q26' => 'LONG_TEXT',
                        'q27' => 'LONG_TEXT',
                        'q28' => 'LONG_TEXT',
                        'q29' => 'LONG_TEXT',
                        'q3' => 'LONG_TEXT',
                        'q30' => 'LONG_TEXT',
                        'q4' => 'LONG_TEXT',
                        'q5' => 'LONG_TEXT',
                        'q6' => 'LONG_TEXT',
                        'q7' => 'LONG_TEXT',
                        'q8' => 'LONG_TEXT',
                        'q9' => 'LONG_TEXT',
                        'a1' => 'SHORT_TEXT',
                        'a10' => 'SHORT_TEXT',
                        'a11' => 'SHORT_TEXT',
                        'a12' => 'SHORT_TEXT',
                        'a13' => 'SHORT_TEXT',
                        'a14' => 'SHORT_TEXT',
                        'a15' => 'SHORT_TEXT',
                        'a16' => 'SHORT_TEXT',
                        'a17' => 'SHORT_TEXT',
                        'a18' => 'SHORT_TEXT',
                        'a19' => 'SHORT_TEXT',
                        'a2' => 'SHORT_TEXT',
                        'a20' => 'SHORT_TEXT',
                        'a21' => 'SHORT_TEXT',
                        'a22' => 'SHORT_TEXT',
                        'a23' => 'SHORT_TEXT',
                        'a24' => 'SHORT_TEXT',
                        'a25' => 'SHORT_TEXT',
                        'a26' => 'SHORT_TEXT',
                        'a27' => 'SHORT_TEXT',
                        'a28' => 'SHORT_TEXT',
                        'a29' => 'SHORT_TEXT',
                        'a3' => 'SHORT_TEXT',
                        'a30' => 'SHORT_TEXT',
                        'a4' => 'SHORT_TEXT',
                        'a5' => 'SHORT_TEXT',
                        'a6' => 'SHORT_TEXT',
                        'a7' => 'SHORT_TEXT',
                        'a8' => 'SHORT_TEXT',
                        'a9' => 'SHORT_TEXT',
                        'name' => 'SHORT_TEXT',
                        'troll_name' => 'SHORT_TEXT',
                    ],
                ],
                'w_rooms' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'location_realm' => '*INTEGER',
                        'location_x' => '*INTEGER',
                        'location_y' => '*INTEGER',
                        'owner' => '?MEMBER',
                        'allow_portal' => 'BINARY',
                        'locked_down' => 'BINARY',
                        'locked_left' => 'BINARY',
                        'locked_right' => 'BINARY',
                        'locked_up' => 'BINARY',
                        'name' => 'ID_TEXT',
                        'password_question' => 'LONG_TEXT',
                        'r_text' => 'LONG_TEXT',
                        'password_answer' => 'SHORT_TEXT',
                        'password_fail_message' => 'SHORT_TEXT',
                        'required_item' => 'SHORT_TEXT',
                        'picture_url' => 'URLPATH',
                    ],
                ],
                'w_travelhistory' => [
                    'addon' => 'buildr',
                    'fields' => [
                        'realm' => '*INTEGER',
                        'x' => '*INTEGER',
                        'y' => '*INTEGER',
                        'member_id' => '*MEMBER',
                    ],
                ],
            ],
            'indices' => [
                'w_messages__destination' => [
                    'addon' => 'buildr',
                    'name' => 'destination',
                    'table' => 'w_messages',
                    'fields' => [
                        0 => 'destination',
                    ],
                    'is_full_text' => false,
                ],
                'w_messages__originator_id' => [
                    'addon' => 'buildr',
                    'name' => 'originator_id',
                    'table' => 'w_messages',
                    'fields' => [
                        0 => 'originator_id',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [
                'administer_buildr' => [
                    'addon' => 'buildr',
                    'section' => 'BUILDR',
                    'default' => 0,
                ],
            ],
        ];
    }
}
