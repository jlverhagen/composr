<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    patreon
 */

/**
 * Hook class.
 */
class Hook_database_manifest_patreon
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
                'patreon_patrons' => [
                    'addon' => 'patreon',
                    'fields' => [
                        'p_tier' => '*ID_TEXT',
                        'p_member_id' => '*MEMBER',
                        'p_id' => 'ID_TEXT',
                        'p_monthly' => 'INTEGER',
                        'p_name' => 'SHORT_TEXT',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
