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

$x_axis_label = @cms_empty_safe($map['x_axis_label']) ? '' : $map['x_axis_label'];
$y_axis_label = @cms_empty_safe($map['y_axis_label']) ? '' : $map['y_axis_label'];

$begin_at_zero = !isset($map['begin_at_zero']) ? true : ($map['begin_at_zero'] == '1');
$clamp_y_axis = !isset($map['clamp_y_axis']) ? false : intval($map['clamp_y_axis']);
$logarithmic = !isset($map['logarithmic']) ? false : ($map['logarithmic'] == '1');

$wordwrap_tooltip_at = !isset($map['wordwrap_tooltip_at']) ? null : intval($map['wordwrap_tooltip_at']);

$color = empty($map['color']) ? null : $map['color'];

$file = empty($map['file']) ? 'uploads/website_specific/graph_test/scatter_diagram.csv' : $map['file'];

$datapoints = [];
require_code('files_spreadsheets_read');
$sheet_reader = spreadsheet_open_read(get_custom_file_base() . '/' . $file, null, CMS_Spreadsheet_Reader::ALGORITHM_RAW);
while (($line = $sheet_reader->read_row()) !== false) {
    if (substr($line[0], 0, 1) == '#') {
        continue; // Comment line
    }

    if (count($line) < 2) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('e91bb0b95adf514395a2e772f133f7c2')));
    }

    $tooltip = implode("\n", array_slice($line, 4));
    if ($wordwrap_tooltip_at !== null) {
        $tooltip = wordwrap($tooltip, $wordwrap_tooltip_at);
    }

    $datapoints[] = [
        'x' => $line[0],
        'y' => $line[1],
        'r' => empty($line[2]) ? null : $line[2],
        'category' => empty($line[3]) ? '' : $line[3],
        'tooltip' => $tooltip,
    ];
}
$sheet_reader->close();

$options = ['begin_at_zero' => $begin_at_zero, 'clamp_y_axis' => $clamp_y_axis, 'logarithmic' => $logarithmic, 'wordwrap_tooltip_at' => $wordwrap_tooltip_at];
if (!empty($map['id'])) {
    $options['id'] = $map['id'];
}

$tpl = graph_scatter_diagram($datapoints, $x_axis_label, $y_axis_label, $options, $color, $width, $height);
$tpl->evaluate_echo();
