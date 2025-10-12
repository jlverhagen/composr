<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    content_read_tracking
 */

/**
 * Hook class.
 */
class Hook_database_manifest_content_read_tracking
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
                'content_read' => [
                    'addon' => 'content_read_tracking',
                    'fields' => [
                        'r_content_id' => '*ID_TEXT',
                        'r_content_type' => '*ID_TEXT',
                        'r_member_id' => '*MEMBER',
                        'r_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'content_read__content_read' => [
                    'addon' => 'content_read_tracking',
                    'name' => 'content_read',
                    'table' => 'content_read',
                    'fields' => [
                        0 => 'r_content_type',
                        1 => 'r_content_id',
                    ],
                    'is_full_text' => false,
                ],
                'content_read__content_read_cleanup' => [
                    'addon' => 'content_read_tracking',
                    'name' => 'content_read_cleanup',
                    'table' => 'content_read',
                    'fields' => [
                        0 => 'r_time',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
