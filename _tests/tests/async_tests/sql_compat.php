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
class sql_compat_test_set extends cms_test_case
{
    public function testRead()
    {
        require_code('files2');

        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_ACCESS_CONTROLLERS | IGNORE_UNSHIPPED_VOLATILE | IGNORE_SHIPPED_VOLATILE | IGNORE_FLOATING | IGNORE_REBUILDABLE_OR_TEMP_FILES_FOR_BACKUP, true, true, ['php']);
        foreach ($files as $path) {
            if ($path == '_tests/tests/async_tests/xml_db.php') {
                continue;
            }

            $c = file_get_contents(get_file_base() . '/' . $path);

            $matches = [];

            $num_matches = preg_match_all('#(query(_select|_select_value|_select_value_if_there)\(\'[^\']*\', \[.*)ORDER BY ([\w, ]*)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $line = $matches[0][$i];
                $main = $matches[1][$i];
                $_order_bys = $matches[3][$i];
                $this->check_query_order_bys($c, $path, $line, $main, $_order_bys);
            }

            $num_matches = preg_match_all('#(query(_value|_value_if_there|)\(\'SELECT [^\w,]*.*)ORDER BY ([\w, ]*)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $line = $matches[0][$i];
                $main = $matches[1][$i];
                $_order_bys = $matches[3][$i];
                $this->check_query_order_bys($c, $path, $line, $main, $_order_bys);
            }
        }
    }

    protected function check_query_order_bys($c, $path, $line, $main, $_order_bys)
    {
        $order_bys = array_map('trim', explode(',', preg_replace('# (ASC|DESC)#', '', $_order_bys)));
        foreach ($order_bys as $order_by) {
            $ok = ($order_by == '') || (strpos($main, '*') !== false) || (strpos($main, $order_by) !== false);
            $line_num = substr_count(substr($c, 0, strpos($c, $line)), "\n") + 1;
            $this->assertTrue($ok, 'Query in ' . $path . ' on line ' . strval($line_num) . ' appears to not SELECT what is being sorted; this is an SQL violation even though it works on MySQL and some other databases');
            if ($ok) {
                //@print('geany ' . $path . ':' . strval($line_num) . "\n");
            }
        }
    }
}
