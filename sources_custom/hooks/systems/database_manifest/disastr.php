<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    disastr
 */

/**
 * Hook class.
 */
class Hook_database_manifest_disastr
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
                'diseases' => [
                    'addon' => 'disastr',
                    'fields' => [
                        'id' => '*AUTO',
                        'enabled' => 'BINARY',
                        'cure_price' => 'INTEGER',
                        'immunisation_price' => 'INTEGER',
                        'points_per_spread' => 'INTEGER',
                        'spread_rate' => 'INTEGER',
                        'cure' => 'SHORT_TEXT',
                        'immunisation' => 'SHORT_TEXT',
                        'name' => 'SHORT_TEXT',
                        'last_spread_time' => 'TIME',
                        'image_url' => 'URLPATH',
                    ],
                ],
                'members_diseases' => [
                    'addon' => 'disastr',
                    'fields' => [
                        'disease_id' => '*AUTO_LINK',
                        'member_id' => '*MEMBER',
                        'cure' => 'BINARY',
                        'immunisation' => 'BINARY',
                        'sick' => 'BINARY',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [
                'members_diseases__disease_id||diseases__id' => [
                    'addon' => 'disastr',
                    'from_table' => 'members_diseases',
                    'from_field' => 'disease_id',
                    'to_table' => 'diseases',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
