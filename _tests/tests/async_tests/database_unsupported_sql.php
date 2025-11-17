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
class database_unsupported_sql_test_set extends cms_test_case
{
    public function testSQL()
    {
        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING | IGNORE_NONBUNDLED, true, true, ['php']);
        foreach ($files as $path) {
            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            $matches = [];
            $num_matches = preg_match_all('#(query|sql).*\'[^\'\n]*(SUM|COUNT|AVG|MIN|MAX)\([\s*.\w]*[^\'\s*.\w][\s*.\w]*\)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $this->assertTrue(false, 'Unsupported SQL aggregate syntax (expression within aggregate function) in ' . $path . ' (' . $matches[0][$i] . ')');
            }

            $matches = [];
            $num_matches = preg_match_all('#(query|sql)[^\'\n]*\'.*(SUM|COUNT|AVG|MIN|MAX)\([^\'()]*.\)\s*[+\-*/]\s*(SUM|COUNT|AVG|MIN|MAX)\([^\'()]*.\)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $this->assertTrue(false, 'Unsupported SQL aggregate syntax (expression between aggregate functions) in ' . $path . ' (' . $matches[0][$i] . ')');
            }

            // Exceptions...
            if (in_array($path, [
                'rootkit_detection.php',
                'sources/database_repair.php',
                'sources/database/oracle.php',
                'sources/database/shared/sqlserver.php',
                'sources/database/shared/mysql.php',
                'sources/database/postgresql.php',
            ])) {
                continue;
            }

            $matches = [];
            $num_matches = preg_match_all('#WHERE.*\w+(<>|=)\\\\\'#i', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $this->assertTrue(false, 'SQL equal/not-equal operations have to use db_string_equal_to/db_string_not_equal_to for Oracle (even in query_parameterised), in ' . $path . ' (' . $matches[0][$i] . ')');
            }
        }
    }
}
