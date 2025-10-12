<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    achievements
 */

/**
 * Hook class.
 */
class Hook_database_manifest_achievements
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
                'achievements_earned' => [
                    'addon' => 'achievements',
                    'fields' => [
                        'id' => '*AUTO',
                        'a_achievement' => '*ID_TEXT',
                        'a_member_id' => '*MEMBER',
                        'a_date_and_time' => 'TIME',
                    ],
                ],
                'achievements_progress' => [
                    'addon' => 'achievements',
                    'fields' => [
                        'id' => '*AUTO',
                        'ap_count_done' => 'INTEGER',
                        'ap_count_required' => 'INTEGER',
                        'ap_member_id' => 'MEMBER',
                        'ap_qualification_hash' => 'SHORT_TEXT',
                        'ap_date_and_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
