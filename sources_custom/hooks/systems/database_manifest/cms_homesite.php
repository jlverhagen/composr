<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite
 */

/**
 * Hook class.
 */
class Hook_database_manifest_cms_homesite
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
                'telemetry_errors' => [
                    'addon' => 'cms_homesite',
                    'fields' => [
                        'id' => '*AUTO',
                        'e_site' => 'AUTO_LINK',
                        'e_refs_compiled' => 'BINARY',
                        'e_resolved' => 'BINARY',
                        'e_version' => 'ID_TEXT',
                        'e_error_count' => 'INTEGER',
                        'e_error_message' => 'LONG_TEXT',
                        'e_note' => 'LONG_TRANS__COMCODE',
                        'e_guid' => 'MINIID_TEXT',
                        'e_error_hash' => 'SHORT_TEXT',
                        'e_first_date_and_time' => 'TIME',
                        'e_last_date_and_time' => 'TIME',
                    ],
                ],
                'telemetry_errors_ignore' => [
                    'addon' => 'cms_homesite',
                    'fields' => [
                        'id' => '*AUTO',
                        'resolve_message' => 'LONG_TRANS__COMCODE',
                        'ignore_string' => 'SHORT_TEXT',
                    ],
                ],
                'telemetry_sites' => [
                    'addon' => 'cms_homesite',
                    'fields' => [
                        'id' => '*AUTO',
                        'last_checked' => '?TIME',
                        'may_feature' => 'BINARY',
                        'addons_installed' => 'SERIAL',
                        'public_key' => 'SHORT_TEXT',
                        'sign_public_key' => 'SHORT_TEXT',
                        'software_version' => 'SHORT_TEXT',
                        'website_installed' => 'SHORT_TEXT',
                        'website_name' => 'SHORT_TEXT',
                        'add_date_and_time' => 'TIME',
                        'website_url' => 'URLPATH',
                    ],
                ],
                'telemetry_stats' => [
                    'addon' => 'cms_homesite',
                    'fields' => [
                        'id' => '*AUTO',
                        's_site' => 'AUTO_LINK',
                        'count_daily_hits' => 'INTEGER',
                        'count_members' => 'INTEGER',
                        'software_version' => 'SHORT_TEXT',
                        'date_and_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'telemetry_errors__#e_note' => [
                    'addon' => 'cms_homesite',
                    'name' => 'e_note',
                    'table' => 'telemetry_errors',
                    'fields' => [
                        0 => 'e_note',
                    ],
                    'is_full_text' => true,
                ],
                'telemetry_errors_ignore__#resolve_message' => [
                    'addon' => 'cms_homesite',
                    'name' => 'resolve_message',
                    'table' => 'telemetry_errors_ignore',
                    'fields' => [
                        0 => 'resolve_message',
                    ],
                    'is_full_text' => true,
                ],
            ],
            'foreign_keys' => [
                'telemetry_errors__e_site||telemetry_sites__id' => [
                    'addon' => 'cms_homesite',
                    'from_table' => 'telemetry_errors',
                    'from_field' => 'e_site',
                    'to_table' => 'telemetry_sites',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'telemetry_stats__s_site||telemetry_sites__id' => [
                    'addon' => 'cms_homesite',
                    'from_table' => 'telemetry_stats',
                    'from_field' => 's_site',
                    'to_table' => 'telemetry_sites',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
