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

require_code('graphs');

$width = empty($map['width']) ? null : $map['width'];
$height = empty($map['height']) ? null : $map['height'];

$show_data_labels = !isset($map['show_data_labels']) ? true : ($map['show_data_labels'] == '1');
$doughnut = !isset($map['doughnut']) ? false : ($map['doughnut'] == '1');

$wordwrap_tooltip_at = !isset($map['wordwrap_tooltip_at']) ? null : intval($map['wordwrap_tooltip_at']);

$color_pool = @cms_empty_safe($map['color_pool']) ? [] : _parse_color_pool_string($map['color_pool']);

$file = empty($map['file']) ? 'uploads/website_specific/graph_test/pie_chart.csv' : $map['file'];

$datapoints = [];
require_code('files_spreadsheets_read');
$sheet_reader = spreadsheet_open_read(get_custom_file_base() . '/' . $file, null, CMS_Spreadsheet_Reader::ALGORITHM_RAW);
while (($line = $sheet_reader->read_row()) !== false) {
    if (substr($line[0], 0, 1) == '#') {
        continue; // Comment line
    }

    if (count($line) < 2) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('2302dbf13d345a178c53eb11fa0e381a')));
    }

    $tooltip = implode("\n", array_slice($line, 2));
    if ($wordwrap_tooltip_at !== null) {
        $tooltip = wordwrap($tooltip, $wordwrap_tooltip_at);
    }

    $datapoints[] = [
        'label' => $line[0],
        'value' => $line[1],
        'tooltip' => $tooltip,
    ];
}
$sheet_reader->close();

$options = ['show_data_labels' => $show_data_labels, 'doughnut' => $doughnut, 'wordwrap_tooltip_at' => $wordwrap_tooltip_at];
if (!empty($map['id'])) {
    $options['id'] = $map['id'];
}

$tpl = graph_pie_chart($datapoints, $options, $color_pool, $width, $height);
$tpl->evaluate_echo();
