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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class cms_merge_test_set extends cms_test_case
{
    public function testFullTableCoverage()
    {
        $c = cms_file_get_contents_safe(get_file_base() . '/sources/hooks/modules/admin_import/cms_merge.php', FILE_READ_LOCK);

        // Check all tables are referenced...

        require_code('database_relations');

        $skip_flags = TABLE_PURPOSE__FLUSHABLE | TABLE_PURPOSE__NON_BUNDLED | TABLE_PURPOSE__AUTOGEN_STATIC | TABLE_PURPOSE__MISC_NO_MERGE | TABLE_PURPOSE__NOT_KNOWN;

        $tables = $GLOBALS['SITE_DB']->query_select('db_meta', ['DISTINCT m_table']);
        foreach ($tables as $table) {
            // Exceptions
            if (strpos($table['m_table'], 'catalogue_efv_') !== false) {
                continue; // These are imported, but the test can't detect it
            }
            if (table_has_purpose_flag($table['m_table'], $skip_flags)) {
                continue;
            }

            $table_is_referenced = (strpos($c, $table['m_table']) !== false);
            $this->assertTrue($table_is_referenced, 'No cms_merge import defined for ' . $table['m_table']);
        }

        // Check every function is usable...

        $lang_array = [];
        $hooks = find_all_hook_obs('modules', 'admin_import_types', 'Hook_admin_import_types_');
        foreach ($hooks as $_hook) {
            $lang_array += $_hook->run();
        }

        $matches = [];
        $num_matches = preg_match_all('#^\s+public function import_(\w+)\(#m', $c, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $import_type = $matches[1][$i];
            $this->assertTrue(isset($lang_array[$import_type]), 'Unrecognised import type in cms_merge importer: ' . $import_type);
        }
    }
}
