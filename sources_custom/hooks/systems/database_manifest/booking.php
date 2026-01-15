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
 * @package    booking
 */

/**
 * Hook class.
 */
class Hook_database_manifest_booking
{
    /**
     * Determine how we should handle database tables for things like backups, automated testing, import/export, and migration.
     *
     * @return array Map of table names to their TABLE_PURPOSE constants
     */
    public function get_table_purpose_flags() : array
    {
        require_code('database_relations');

        return [
            'bookable' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
            'bookable_blacked' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
            'bookable_blacked_for' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__SUBDATA/*under bookable*/,
            'bookable_codes' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__SUBDATA/*under bookable*/,
            'bookable_supplement' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
            'bookable_supplement_for' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__SUBDATA/*under bookable*/,
            'booking' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
            'booking_supplement' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
        ];
    }

    /**
     * Get a map of table descriptions.
     *
     * @return array Map of table descriptions
     */
    public function get_table_descriptions() : array
    {
        return [
        ];
    }

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
                'bookable' => [
                    'addon' => 'booking',
                    'fields' => [
                        'id' => '*AUTO',
                        'calendar_type' => '?AUTO_LINK',
                        'active_to_year' => '?INTEGER',
                        'active_to_day' => '?SHORT_INTEGER',
                        'active_to_month' => '?SHORT_INTEGER',
                        'edit_date' => '?TIME',
                        'dates_are_ranges' => 'BINARY',
                        'enabled' => 'BINARY',
                        'supports_notes' => 'BINARY',
                        'user_may_choose_code' => 'BINARY',
                        'cycle_type' => 'ID_TEXT',
                        'active_from_year' => 'INTEGER',
                        'sort_order' => 'INTEGER',
                        'the_description' => 'LONG_TRANS__COMCODE',
                        'submitter' => 'MEMBER',
                        'price' => 'REAL',
                        'active_from_day' => 'SHORT_INTEGER',
                        'active_from_month' => 'SHORT_INTEGER',
                        'cycle_pattern' => 'SHORT_TEXT',
                        'categorisation' => 'SHORT_TRANS__COMCODE',
                        'title' => 'SHORT_TRANS__COMCODE',
                        'add_date' => 'TIME',
                    ],
                ],
                'bookable_blacked' => [
                    'addon' => 'booking',
                    'fields' => [
                        'id' => '*AUTO',
                        'blacked_from_year' => 'INTEGER',
                        'blacked_to_year' => 'INTEGER',
                        'blacked_explanation' => 'LONG_TRANS__COMCODE',
                        'blacked_from_day' => 'SHORT_INTEGER',
                        'blacked_from_month' => 'SHORT_INTEGER',
                        'blacked_to_day' => 'SHORT_INTEGER',
                        'blacked_to_month' => 'SHORT_INTEGER',
                    ],
                ],
                'bookable_supplement' => [
                    'addon' => 'booking',
                    'fields' => [
                        'id' => '*AUTO',
                        'price_is_per_period' => 'BINARY',
                        'supports_notes' => 'BINARY',
                        'supports_quantities' => 'BINARY',
                        'promo_code' => 'ID_TEXT',
                        'sort_order' => 'INTEGER',
                        'price' => 'REAL',
                        'title' => 'SHORT_TRANS__COMCODE',
                    ],
                ],
                'booking' => [
                    'addon' => 'booking',
                    'fields' => [
                        'id' => '*AUTO',
                        'paid_trans_id' => '?AUTO_LINK',
                        'paid_at' => '?TIME',
                        'bookable_id' => 'AUTO_LINK',
                        'code_allocation' => 'ID_TEXT',
                        'b_year' => 'INTEGER',
                        'notes' => 'LONG_TEXT',
                        'member_id' => 'MEMBER',
                        'b_day' => 'SHORT_INTEGER',
                        'b_month' => 'SHORT_INTEGER',
                        'customer_email' => 'SHORT_TEXT',
                        'customer_mobile' => 'SHORT_TEXT',
                        'customer_name' => 'SHORT_TEXT',
                        'customer_phone' => 'SHORT_TEXT',
                        'booked_at' => 'TIME',
                    ],
                ],
                'bookable_blacked_for' => [
                    'addon' => 'booking',
                    'fields' => [
                        'blacked_id' => '*AUTO_LINK',
                        'bookable_id' => '*AUTO_LINK',
                    ],
                ],
                'bookable_codes' => [
                    'addon' => 'booking',
                    'fields' => [
                        'bookable_id' => '*AUTO_LINK',
                        'code' => '*ID_TEXT',
                    ],
                ],
                'bookable_supplement_for' => [
                    'addon' => 'booking',
                    'fields' => [
                        'bookable_id' => '*AUTO_LINK',
                        'supplement_id' => '*AUTO_LINK',
                    ],
                ],
                'booking_supplement' => [
                    'addon' => 'booking',
                    'fields' => [
                        'booking_id' => '*AUTO_LINK',
                        'supplement_id' => '*AUTO_LINK',
                        'quantity' => 'INTEGER',
                        'notes' => 'LONG_TEXT',
                    ],
                ],
            ],
            'indices' => [
                'bookable__#categorisation' => [
                    'addon' => 'booking',
                    'name' => 'categorisation',
                    'table' => 'bookable',
                    'fields' => [
                        0 => 'categorisation',
                    ],
                    'is_full_text' => true,
                ],
                'bookable__#the_description' => [
                    'addon' => 'booking',
                    'name' => 'the_description',
                    'table' => 'bookable',
                    'fields' => [
                        0 => 'the_description',
                    ],
                    'is_full_text' => true,
                ],
                'bookable__#title' => [
                    'addon' => 'booking',
                    'name' => 'title',
                    'table' => 'bookable',
                    'fields' => [
                        0 => 'title',
                    ],
                    'is_full_text' => true,
                ],
                'bookable_blacked__#blacked_explanation' => [
                    'addon' => 'booking',
                    'name' => 'blacked_explanation',
                    'table' => 'bookable_blacked',
                    'fields' => [
                        0 => 'blacked_explanation',
                    ],
                    'is_full_text' => true,
                ],
                'bookable_supplement__#title' => [
                    'addon' => 'booking',
                    'name' => 'title',
                    'table' => 'bookable_supplement',
                    'fields' => [
                        0 => 'title',
                    ],
                    'is_full_text' => true,
                ],
                'booking__member_id' => [
                    'addon' => 'booking',
                    'name' => 'member_id',
                    'table' => 'booking',
                    'fields' => [
                        0 => 'member_id',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [
                'bookable__calendar_type||calendar_types__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable',
                    'from_field' => 'calendar_type',
                    'to_table' => 'calendar_types',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'bookable_blacked_for__blacked_id||bookable_blacked__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable_blacked_for',
                    'from_field' => 'blacked_id',
                    'to_table' => 'bookable_blacked',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'bookable_blacked_for__bookable_id||bookable__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable_blacked_for',
                    'from_field' => 'bookable_id',
                    'to_table' => 'bookable',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'bookable_codes__bookable_id||bookable__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable_codes',
                    'from_field' => 'bookable_id',
                    'to_table' => 'bookable',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'bookable_supplement_for__bookable_id||bookable__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable_supplement_for',
                    'from_field' => 'bookable_id',
                    'to_table' => 'bookable',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'bookable_supplement_for__supplement_id||bookable_supplement__id' => [
                    'addon' => 'booking',
                    'from_table' => 'bookable_supplement_for',
                    'from_field' => 'supplement_id',
                    'to_table' => 'bookable_supplement',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'booking__bookable_id||bookable__id' => [
                    'addon' => 'booking',
                    'from_table' => 'booking',
                    'from_field' => 'bookable_id',
                    'to_table' => 'bookable',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'booking_supplement__booking_id||booking__id' => [
                    'addon' => 'booking',
                    'from_table' => 'booking_supplement',
                    'from_field' => 'booking_id',
                    'to_table' => 'booking',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'booking_supplement__supplement_id||bookable_supplement__id' => [
                    'addon' => 'booking',
                    'from_table' => 'booking_supplement',
                    'from_field' => 'supplement_id',
                    'to_table' => 'bookable_supplement',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
