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

/**
 * Find a match for a problem in the database.
 *
 * @param  string $error_message The error that occurred
 * @param  boolean $evaluate_comcode Whether to evaluate the Comcode (false: return as a string of Comcode rather than HTML)
 * @return ?string The full Comcode (null: not found)
 */
function get_problem_match_nearest(string $error_message, bool $evaluate_comcode = true) : ?string
{
    if (!defined('DEFAULT_BRAND_NAME')) {
        define('DEFAULT_BRAND_NAME', 'Composr'); // TODO: This is a fudge
    }

    require_code('files_spreadsheets_read');

    // Find matches. Stored in a spreadsheet file.
    $matches = [];
    $sheet_reader = spreadsheet_open_read(get_custom_file_base() . '/uploads/website_specific/cms_homesite/errorservice.csv');
    while (($row = $sheet_reader->read_row()) !== false) {
        $message = $row['Message'];
        $summary = $row['Summary'];
        $how = $row['How did this happen?'];
        $solution = $row['How do I fix it?'];

        $assembled = $summary . "\n\n[title=\"2\"]How did this happen?[/title]\n\n" . $how . "\n\n[title=\"2\"]How do I fix it?[/title]\n\n" . $solution;

        // Possible rebranding
        $brand = get_param_string('product', DEFAULT_BRAND_NAME);
        if (($brand != DEFAULT_BRAND_NAME) && ($brand != '')) {
            $brand_base_url = get_param_string('product_site', '');
            if ($brand_base_url != '') {
                $assembled = str_replace(DEFAULT_BRAND_NAME, $brand, $assembled);
                $assembled = str_replace('ocProducts', 'Core Development Team', $assembled); // LEGACY
                $assembled = str_replace(get_brand_base_url(), $brand_base_url, $assembled);
            }
        }

        $regexp = str_replace('xxx', '.*', preg_quote($message, '#'));
        if (preg_match('#' . $regexp . '#', $error_message) != 0) {
            $matches[$message] = $assembled;
        }
    }
    $sheet_reader->close();

    // No matches
    if (empty($matches)) {
        return null;
    }

    // Sort by how good the match is (string length)
    uksort($matches, '_strlen_sort');

    // Return best-match result
    $_output = array_pop($matches);
    if ($evaluate_comcode === false) {
        return $_output;
    }

    $output = comcode_to_tempcode($_output, $GLOBALS['FORUM_DRIVER']->get_guest_id());
    return $output->evaluate();
}
