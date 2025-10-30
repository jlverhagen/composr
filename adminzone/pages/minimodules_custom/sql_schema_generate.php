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
 * @package    meta_toolkit
 */

/*
Used to generate a database schema in the form of SQL code that can be imported into MySQL Workbench

First run this, then import it all into a new database (existing is problematic as it needs to be InnoDB), then run SQLEditor (http://www.malcolmhardie.com/sqleditor/) on that database -- or if you like try your luck importing, but that was crashing for me.
*/

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$error_msg = new Tempcode();
if (!addon_installed__messaged('meta_toolkit', $error_msg)) {
    return $error_msg;
}

if (post_param_integer('confirm', 0) == 0) {
    $preview = 'Generate database schema';
    $title = get_screen_title($preview, false);
    $url = get_self_url(false, false);
    return do_template('CONFIRM_SCREEN', ['_GUID' => '18339e23769bc683a20928549c8c8e11', 'TITLE' => $title, 'PREVIEW' => $preview, 'FIELDS' => form_input_hidden('confirm', '1'), 'URL' => $url]);
}

$filename = 'composr-erd.sql';

cms_ini_set('ocproducts.xss_detect', '0');

if (!isset($_GET['testing'])) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . escape_header($filename, true) . '"');
} else {
    header('Content-Type: text/plain; charset=' . get_charset());
}

require_code('database_relations');

$tables = get_all_innodb_tables();

echo get_innodb_table_sql($tables, $tables);

$GLOBALS['SCREEN_TEMPLATE_CALLED'] = '';
exit();
