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
 * @package    visualisation
 */

$error_msg = new Tempcode();
if (!addon_installed__messaged('visualisation', $error_msg)) {
    return $error_msg;
}

require_code('maps');

$width = empty($map['width']) ? null : $map['width'];
$height = empty($map['height']) ? null : $map['height'];

$intensity_label = @cms_empty_safe($map['intensity_label']) ? 'Intensity' : $map['intensity_label'];

$color_pool = @cms_empty_safe($map['color_pool']) ? null : _parse_color_pool_string($map['color_pool']);

$show_labels = !empty($map['show_labels']);

$file = empty($map['file']) ? 'uploads/website_specific/graph_test/countries_on_map.csv' : $map['file'];

$data = [];
require_code('files_spreadsheets_read');
$sheet_reader = Source_spreadsheet_reader::spreadsheet_open_read(get_custom_file_base() . '/' . $file, null, Source_spreadsheet_reader::ALGORITHM_RAW);
while (($line = $sheet_reader->read_row()) !== false) {
    if (substr($line[0], 0, 1) == '#') {
        continue; // Comment line
    }

    if (count($line) < 2) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('cada4af2015259b4a496fcbad19913b0')));
    }

    $data[] = [
        'region' => $line[0],
        'intensity' => @cms_empty_safe($line[1]) ? '' : $line[1],
        'description' => implode(',', array_slice($line, 2)),
    ];
}
$sheet_reader->close();

$tpl = countries_on_map($data, $intensity_label, $color_pool, $show_labels, null, $width, $height);
$tpl->evaluate_echo();
