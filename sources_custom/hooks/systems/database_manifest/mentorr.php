<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    mentorr
 */

/**
 * Hook class.
 */
class Hook_database_manifest_mentorr
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
                'members_mentors' => [
                    'addon' => 'mentorr',
                    'fields' => [
                        'id' => '*AUTO',
                        'member_id' => '*MEMBER',
                        'mentor_member_id' => '*MEMBER',
                        'date_and_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'members_mentors__date_and_time' => [
                    'addon' => 'mentorr',
                    'name' => 'date_and_time',
                    'table' => 'members_mentors',
                    'fields' => [
                        0 => 'date_and_time',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
