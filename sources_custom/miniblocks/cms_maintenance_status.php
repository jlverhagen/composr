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
 * @package    cms_homesite
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('cms_homesite')) {
    return do_template('RED_ALERT', ['_GUID' => '4129a34799505dd6943a32b343ab48f1', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('cms_homesite'))]);
}

$nonbundled_addons = isset($map['include_non_bundled']) ? cms_strtolower_ascii($map['include_non_bundled']) : 'exclude';

require_code('files_spreadsheets_read');
$sheet_reader = Source_spreadsheet_reader::spreadsheet_open_read(get_file_base() . '/data/maintenance_status.csv', null, Source_spreadsheet_reader::ALGORITHM_RAW);

$header_row = $sheet_reader->read_row(); // Header row
unset($header_row[0]);

$rows = [];
while (($row = $sheet_reader->read_row()) !== false) {
    $codename = $row[0];
    unset($row[0]);
    $data = array_values($row);

    // Remove non-bundled if include_non_bundled is not 'include' or 'only'
    if (($nonbundled_addons != 'include') && ($nonbundled_addons != 'only') && (cms_strtolower_ascii($data[4]) == 'yes')) {
        continue;
    }

    // Remove bundled if include_non_bundled is 'only'
    if (($nonbundled_addons == 'only') && (cms_strtolower_ascii($data[4]) != 'yes')) {
        continue;
    }

    $rows[$codename] = ['DATA' => $data, 'CODENAME' => $codename];
}

$sheet_reader->close();

cms_mb_ksort($rows, SORT_NATURAL | SORT_FLAG_CASE);

return do_template('BLOCK_CMS_MAINTENANCE_STATUS', [
    '_GUID' => '8c7ba3e7a2c667e7eebf36b9fe067868',
    'HEADER_ROW' => array_values($header_row),
    'ROWS' => $rows,
]);
