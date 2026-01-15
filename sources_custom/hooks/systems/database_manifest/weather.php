<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    weather
 */

/**
 * Hook class.
 */
class Hook_database_manifest_weather
{
    /**
     * Determine how we should handle database tables for things like backups, automated testing, import/export, and migration.
     *
     * @return array Map of table names to their TABLE_PURPOSE constants
     */
    public function get_table_purpose_flags() : array
    {
        require_code('database_relations');

        return [
            'cached_weather_codes' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__NO_BACKUPS | TABLE_PURPOSE__FLUSHABLE,
        ];
    }

    /**
     * Get a map of table descriptions.
     *
     * @return array Map of table descriptions
     */
    public function get_table_descriptions() : array
    {
        return [
        ];
    }

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
                'cached_weather_codes' => [
                    'addon' => 'weather',
                    'fields' => [
                        'id' => '*AUTO',
                        'w_code' => 'INTEGER',
                        'w_string' => 'SHORT_TEXT',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
