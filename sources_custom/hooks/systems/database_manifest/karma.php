<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    karma
 */

/**
 * Hook class.
 */
class Hook_database_manifest_karma
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
                'karma' => [
                    'addon' => 'karma',
                    'fields' => [
                        'id' => '*AUTO',
                        'k_reversed' => 'BINARY',
                        'k_content_id' => 'ID_TEXT',
                        'k_content_type' => 'ID_TEXT',
                        'k_type' => 'ID_TEXT',
                        'k_amount' => 'INTEGER',
                        'k_member_from' => 'MEMBER',
                        'k_member_to' => 'MEMBER',
                        'k_reason' => 'SHORT_TRANS__COMCODE',
                        'k_date_and_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'karma__#k_reason' => [
                    'addon' => 'karma',
                    'name' => 'k_reason',
                    'table' => 'karma',
                    'fields' => [
                        0 => 'k_reason',
                    ],
                    'is_full_text' => true,
                ],
                'karma__karmacontent' => [
                    'addon' => 'karma',
                    'name' => 'karmacontent',
                    'table' => 'karma',
                    'fields' => [
                        0 => 'k_content_type',
                        1 => 'k_content_id',
                    ],
                    'is_full_text' => false,
                ],
                'karma__karmamember' => [
                    'addon' => 'karma',
                    'name' => 'karmamember',
                    'table' => 'karma',
                    'fields' => [
                        0 => 'k_member_from',
                        1 => 'k_member_to',
                    ],
                    'is_full_text' => false,
                ],
                'karma__karmasystem' => [
                    'addon' => 'karma',
                    'name' => 'karmasystem',
                    'table' => 'karma',
                    'fields' => [
                        0 => 'k_member_to',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [
                'has_additional_karmic_influence' => [
                    'addon' => 'karma',
                    'section' => 'KARMA',
                    'default' => 0,
                ],
                'has_karmic_influence' => [
                    'addon' => 'karma',
                    'section' => 'KARMA',
                    'default' => 1,
                ],
                'moderate_karma' => [
                    'addon' => 'karma',
                    'section' => 'KARMA',
                    'default' => 0,
                ],
                'view_bad_karma' => [
                    'addon' => 'karma',
                    'section' => 'KARMA',
                    'default' => 0,
                ],
                'view_others_karma' => [
                    'addon' => 'karma',
                    'section' => 'KARMA',
                    'default' => 1,
                ],
            ],
        ];
    }
}
