<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    bankr
 */

/**
 * Hook class.
 */
class Hook_database_manifest_bankr
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
                'bank' => [
                    'addon' => 'bankr',
                    'fields' => [
                        'id' => '*AUTO',
                        'add_time' => '?TIME',
                        'amount' => 'INTEGER',
                        'dividend' => 'INTEGER',
                        'member_id' => 'MEMBER',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
