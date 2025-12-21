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
 * @package    composr_tutorials
 */

/**
 * Hook class.
 */
class Hook_database_manifest_composr_tutorials
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
            'api_classes' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__FLUSHABLE,
            'api_function_params' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__FLUSHABLE | TABLE_PURPOSE__SUBDATA/*under api_functions*/,
            'api_functions' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__FLUSHABLE | TABLE_PURPOSE__SUBDATA/*under api_classes*/,
            'api_functions_fulltext_index' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__FLUSHABLE,
            'tutorials_external' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED,
            'tutorials_external_tags' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__SUBDATA/*under tutorials_external*/,
            'tutorials_internal' => TABLE_PURPOSE__NORMAL | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__FLUSHABLE | TABLE_PURPOSE__AUTOGEN_STATIC,
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
                'api_classes' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        'id' => '*AUTO',
                        'c_comment' => 'BINARY',
                        'c_is_abstract' => 'BINARY',
                        'c_extends' => 'ID_TEXT',
                        'c_name' => 'ID_TEXT',
                        'c_package' => 'ID_TEXT',
                        'c_implements' => 'LONG_TEXT',
                        'c_traits' => 'LONG_TEXT',
                        'c_type' => 'MINIID_TEXT',
                        'c_edit_date' => 'TIME',
                        'c_source_url' => 'URLPATH',
                    ],
                ],
                'api_functions' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        'id' => '*AUTO',
                        'class_id' => 'AUTO_LINK',
                        'f_is_abstract' => 'BINARY',
                        'f_is_final' => 'BINARY',
                        'f_is_static' => 'BINARY',
                        'f_php_return_type_nullable' => 'BINARY',
                        'class_name' => 'ID_TEXT',
                        'f_name' => 'ID_TEXT',
                        'f_php_return_type' => 'ID_TEXT',
                        'f_return_type' => 'ID_TEXT',
                        'f_description' => 'LONG_TEXT',
                        'f_flags' => 'LONG_TEXT',
                        'f_return_description' => 'LONG_TEXT',
                        'f_visibility' => 'MINIID_TEXT',
                        'f_return_range' => 'SHORT_TEXT',
                        'f_return_set' => 'SHORT_TEXT',
                        'f_edit_date' => 'TIME',
                    ],
                ],
                'api_function_params' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        'id' => '*AUTO',
                        'function_id' => 'AUTO_LINK',
                        'p_is_variadic' => 'BINARY',
                        'p_php_type_nullable' => 'BINARY',
                        'p_ref' => 'BINARY',
                        'p_name' => 'ID_TEXT',
                        'p_php_type' => 'ID_TEXT',
                        'p_type' => 'ID_TEXT',
                        'p_description' => 'LONG_TEXT',
                        'p_default' => 'SERIAL',
                        'p_range' => 'SHORT_TEXT',
                        'p_set' => 'SHORT_TEXT',
                    ],
                ],
                'tutorials_external' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        'id' => '*AUTO',
                        't_pinned' => 'BINARY',
                        't_author' => 'ID_TEXT',
                        't_difficulty_level' => 'ID_TEXT',
                        't_icon' => 'ID_TEXT',
                        't_media_type' => 'ID_TEXT',
                        't_views' => 'INTEGER',
                        't_summary' => 'LONG_TEXT',
                        't_submitter' => 'MEMBER',
                        't_title' => 'SHORT_TEXT',
                        't_add_date' => 'TIME',
                        't_edit_date' => 'TIME',
                        't_url' => 'URLPATH',
                    ],
                ],
                'api_functions_fulltext_index' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        'i_f_id' => '*AUTO_LINK',
                        'i_ac' => '*INTEGER',
                        'i_ngram' => '*INTEGER',
                        'i_lang' => '*LANGUAGE_NAME',
                        'i_c_id' => 'AUTO_LINK',
                        'i_f_name' => 'ID_TEXT',
                        'i_submitter' => 'MEMBER',
                        'i_occurrence_rate' => 'REAL',
                        'i_add_time' => 'TIME',
                    ],
                ],
                'tutorials_external_tags' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        't_id' => '*AUTO_LINK',
                        't_tag' => '*ID_TEXT',
                    ],
                ],
                'tutorials_internal' => [
                    'addon' => 'composr_tutorials',
                    'fields' => [
                        't_page_name' => '*ID_TEXT',
                        't_views' => 'INTEGER',
                    ],
                ],
            ],
            'indices' => [
                'api_classes__by_package' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'by_package',
                    'table' => 'api_classes',
                    'fields' => [
                        0 => 'c_package',
                        1 => 'c_name',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions__by_class_id' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'by_class_id',
                    'table' => 'api_functions',
                    'fields' => [
                        0 => 'class_id',
                        1 => 'f_name',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions__by_class_name' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'by_class_name',
                    'table' => 'api_functions',
                    'fields' => [
                        0 => 'class_name',
                        1 => 'f_name',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__content_id' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'content_id',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_f_id',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_10' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_10',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_submitter',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_11' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_11',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_f_name',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_12' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_12',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_c_id',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_13' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_13',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_submitter',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_14' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_14',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_f_name',
                        3 => 'i_c_id',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_15' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_15',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_f_name',
                        3 => 'i_submitter',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_16' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_16',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_c_id',
                        3 => 'i_submitter',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_17' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_17',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_f_name',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_18' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_18',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_c_id',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_19' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_19',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_2' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_2',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_20' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_20',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_f_name',
                        4 => 'i_c_id',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_21' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_21',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_f_name',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_22' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_22',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_c_id',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_23' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_23',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_f_name',
                        4 => 'i_c_id',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_24' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_24',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_f_name',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_25' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_25',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_c_id',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_26' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_26',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_f_name',
                        3 => 'i_c_id',
                        4 => 'i_submitter',
                        5 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_27' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_27',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_f_name',
                        5 => 'i_c_id',
                        6 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_28' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_28',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_f_name',
                        5 => 'i_submitter',
                        6 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_29' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_29',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_c_id',
                        5 => 'i_submitter',
                        6 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_3' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_3',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_30' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_30',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_f_name',
                        4 => 'i_c_id',
                        5 => 'i_submitter',
                        6 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_31' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_31',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_add_time',
                        3 => 'i_f_name',
                        4 => 'i_c_id',
                        5 => 'i_submitter',
                        6 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_32' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_32',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_4' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_4',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_f_name',
                        3 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_5' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_5',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_c_id',
                        3 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_6' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_6',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_submitter',
                        3 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_7' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_7',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_add_time',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_8' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_8',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_f_name',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_functions_fulltext_index__main_9' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'main_9',
                    'table' => 'api_functions_fulltext_index',
                    'fields' => [
                        0 => 'i_lang',
                        1 => 'i_ngram',
                        2 => 'i_ac',
                        3 => 'i_c_id',
                        4 => 'i_occurrence_rate',
                    ],
                    'is_full_text' => false,
                ],
                'api_function_params__by_function_id' => [
                    'addon' => 'composr_tutorials',
                    'name' => 'by_function_id',
                    'table' => 'api_function_params',
                    'fields' => [
                        0 => 'function_id',
                        1 => 'p_name',
                    ],
                    'is_full_text' => false,
                ],
                'tutorials_external__#t_summary' => [
                    'addon' => 'composr_tutorials',
                    'name' => 't_summary',
                    'table' => 'tutorials_external',
                    'fields' => [
                        0 => 't_summary',
                    ],
                    'is_full_text' => true,
                ],
                'tutorials_external__#t_title' => [
                    'addon' => 'composr_tutorials',
                    'name' => 't_title',
                    'table' => 'tutorials_external',
                    'fields' => [
                        0 => 't_title',
                    ],
                    'is_full_text' => true,
                ],
            ],
            'foreign_keys' => [
                'api_functions__class_id||api_classes__id' => [
                    'addon' => 'composr_tutorials',
                    'from_table' => 'api_functions',
                    'from_field' => 'class_id',
                    'to_table' => 'api_classes',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'api_functions_fulltext_index__i_c_id||api_classes__id' => [
                    'addon' => 'composr_tutorials',
                    'from_table' => 'api_functions_fulltext_index',
                    'from_field' => 'i_c_id',
                    'to_table' => 'api_classes',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'api_functions_fulltext_index__i_f_id||api_functions__id' => [
                    'addon' => 'composr_tutorials',
                    'from_table' => 'api_functions_fulltext_index',
                    'from_field' => 'i_f_id',
                    'to_table' => 'api_functions',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'api_function_params__function_id||api_functions__id' => [
                    'addon' => 'composr_tutorials',
                    'from_table' => 'api_function_params',
                    'from_field' => 'function_id',
                    'to_table' => 'api_functions',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
                'tutorials_external_tags__t_id||tutorials_external__id' => [
                    'addon' => 'composr_tutorials',
                    'from_table' => 'tutorials_external_tags',
                    'from_field' => 't_id',
                    'to_table' => 'tutorials_external',
                    'to_field' => 'id',
                    'special_values' => [],
                ],
            ],
            'privileges' => [],
        ];
    }
}
