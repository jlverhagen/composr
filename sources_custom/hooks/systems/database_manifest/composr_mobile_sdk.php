<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    composr_mobile_sdk
 */

/**
 * Hook class.
 */
class Hook_database_manifest_composr_mobile_sdk
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
                'device_token_details' => [
                    'addon' => 'composr_mobile_sdk',
                    'fields' => [
                        'id' => '*AUTO',
                        'token_type' => 'ID_TEXT',
                        'member_id' => 'MEMBER',
                        'device_token' => 'SHORT_TEXT',
                    ],
                ],
            ],
            'indices' => [
                'device_token_details__member_id' => [
                    'addon' => 'composr_mobile_sdk',
                    'name' => 'member_id',
                    'table' => 'device_token_details',
                    'fields' => [
                        0 => 'member_id',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
