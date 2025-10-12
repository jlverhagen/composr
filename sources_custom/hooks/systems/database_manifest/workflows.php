<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    workflows
 */

/**
 * Hook class.
 */
class Hook_database_manifest_workflows
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
                'workflows' => [
                    'addon' => 'workflows',
                    'fields' => [
                        'id' => '*AUTO',
                        'is_default' => 'BINARY',
                        'workflow_name' => 'SHORT_TRANS',
                    ],
                ],
                'workflow_approval_points' => [
                    'addon' => 'workflows',
                    'fields' => [
                        'id' => '*AUTO',
                        'workflow_id' => 'AUTO_LINK',
                        'the_position' => 'INTEGER',
                        'workflow_approval_name' => 'SHORT_TRANS',
                    ],
                ],
                'workflow_content' => [
                    'addon' => 'workflows',
                    'fields' => [
                        'id' => '*AUTO',
                        'workflow_id' => 'AUTO_LINK',
                        'content_id' => 'ID_TEXT',
                        'content_type' => 'ID_TEXT',
                        'notes' => 'LONG_TEXT',
                        'original_submitter' => 'MEMBER',
                    ],
                ],
                'workflow_content_status' => [
                    'addon' => 'workflows',
                    'fields' => [
                        'id' => '*AUTO',
                        'workflow_approval_point_id' => 'AUTO_LINK',
                        'workflow_content_id' => 'AUTO_LINK',
                        'approved_by_member' => 'MEMBER',
                        'status_code' => 'SHORT_INTEGER',
                    ],
                ],
                'workflow_permissions' => [
                    'addon' => 'workflows',
                    'fields' => [
                        'id' => '*AUTO',
                        'workflow_approval_point_id' => 'AUTO_LINK',
                        'usergroup' => 'GROUP',
                    ],
                ],
            ],
            'indices' => [
                'workflows__#workflow_name' => [
                    'addon' => 'workflows',
                    'name' => 'workflow_name',
                    'table' => 'workflows',
                    'fields' => [
                        0 => 'workflow_name',
                    ],
                    'is_full_text' => true,
                ],
                'workflow_approval_points__#workflow_approval_name' => [
                    'addon' => 'workflows',
                    'name' => 'workflow_approval_name',
                    'table' => 'workflow_approval_points',
                    'fields' => [
                        0 => 'workflow_approval_name',
                    ],
                    'is_full_text' => true,
                ],
            ],
            'foreign_keys' => [
                'workflow_approval_points__workflow_id||workflows__id' => [
                    'addon' => 'workflows',
                    'from_table' => 'workflow_approval_points',
                    'from_field' => 'workflow_id',
                    'to_table' => 'workflows',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'workflow_content__workflow_id||workflows__id' => [
                    'addon' => 'workflows',
                    'from_table' => 'workflow_content',
                    'from_field' => 'workflow_id',
                    'to_table' => 'workflows',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'workflow_content_status__workflow_approval_point_id||workflow_approval_points__id' => [
                    'addon' => 'workflows',
                    'from_table' => 'workflow_content_status',
                    'from_field' => 'workflow_approval_point_id',
                    'to_table' => 'workflow_approval_points',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'workflow_content_status__workflow_content_id||workflow_content__id' => [
                    'addon' => 'workflows',
                    'from_table' => 'workflow_content_status',
                    'from_field' => 'workflow_content_id',
                    'to_table' => 'workflow_content',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'workflow_permissions__workflow_approval_point_id||workflow_approval_points__id' => [
                    'addon' => 'workflows',
                    'from_table' => 'workflow_permissions',
                    'from_field' => 'workflow_approval_point_id',
                    'to_table' => 'workflow_approval_points',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
