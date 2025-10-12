<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    group_points
 */

/**
 * Hook class.
 */
class Hook_database_manifest_group_points
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
                'group_points' => [
                    'addon' => 'group_points',
                    'fields' => [
                        'p_group_id' => '*GROUP',
                        'p_points_one_off' => 'INTEGER',
                        'p_points_per_month' => 'INTEGER',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
