<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    hybridauth
 */

/**
 * Hook class.
 */
class Hook_database_manifest_hybridauth
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
                'hybridauth_content_map' => [
                    'addon' => 'hybridauth',
                    'fields' => [
                        'h_content_id' => '*ID_TEXT',
                        'h_content_type' => '*ID_TEXT',
                        'h_provider' => '*ID_TEXT',
                        'h_provider_id' => 'SHORT_TEXT',
                        'h_sync_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
