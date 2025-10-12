<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    early_access
 */

/**
 * Hook class.
 */
class Hook_database_manifest_early_access
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
                'early_access_codes' => [
                    'addon' => 'early_access',
                    'fields' => [
                        'c_access_code' => '*ID_TEXT',
                        'c_num_views_allowed' => '?INTEGER',
                        'c_date_from' => '?TIME',
                        'c_date_to' => '?TIME',
                        'c_trigger_access' => 'ID_TEXT',
                        'c_num_views' => 'INTEGER',
                        'c_created_by' => 'MEMBER',
                        'c_label' => 'SHORT_TEXT',
                        'c_creation_time' => 'TIME',
                        'c_edit_time' => 'TIME',
                    ],
                ],
                'early_access_code_content' => [
                    'addon' => 'early_access',
                    'fields' => [
                        'a_access_code' => '*ID_TEXT',
                        'a_content_id' => '*ID_TEXT',
                        'a_content_type' => '*ID_TEXT',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [
                'early_access_code_content__a_access_code||early_access_codes__c_access_code' => [
                    'addon' => 'early_access',
                    'from_table' => 'early_access_code_content',
                    'from_field' => 'a_access_code',
                    'to_table' => 'early_access_codes',
                    'to_field' => 'c_access_code',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
