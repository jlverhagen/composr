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
 * @package    enhanced_spreadsheets
 */

/**
 * Find supported spreadsheet file types for reading.
 *
 * @return string A comma-separated list of supported file types
 */
function spreadsheet_read_file_types() : string
{
    if (!addon_installed('enhanced_spreadsheets')) {
        return non_overridden__spreadsheet_read_file_types();
    }

    // Required PHP extensions
    if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
        return non_overridden__spreadsheet_read_file_types();
    }

    // Required PHP version
    if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
        return non_overridden__spreadsheet_read_file_types();
    }

    return 'csv,txt,ods,xlsx';
}

/**
 * Open spreadsheet for reading.
 *
 * @param  PATH $path File path
 * @param  ?string $filename Filename (null: derive from $path)
 * @param  integer $algorithm An ALGORITHM_* constant
 * @param  boolean $trim Whether to trim each cell
 * @param  ?string $default_charset The default character set to assume if none is specified in the file (null: website character set) (blank: smart detection)
 * @return object A subclass of CMS_Spreadsheet_Reader
 */
function spreadsheet_open_read(string $path, ?string $filename = null, int $algorithm = 3, bool $trim = true, ?string $default_charset = '') : object
{
    if (!addon_installed('enhanced_spreadsheets')) {
        return non_overridden__spreadsheet_open_read($path, $filename, $algorithm, $trim, $default_charset);
    }

    // Required PHP extensions
    if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
        return non_overridden__spreadsheet_open_read($path, $filename, $algorithm, $trim, $default_charset);
    }

    // Required PHP version
    if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
        return non_overridden__spreadsheet_open_read($path, $filename, $algorithm, $trim, $default_charset);
    }

    if ($filename === null) {
        $filename = basename($path);
    }

    $ext = get_file_extension($filename);
    switch ($ext) {
        case 'csv':
        case 'txt':
            return new CMS_CSV_Reader($path, $filename, $algorithm, $trim, $default_charset);

        case 'xlsx':
        case 'ods':
            require_code('files_spreadsheets_read__spout');
            return new CMS_OpenSpout_Reader($path, $filename, $algorithm, $trim, $default_charset);
    }

    warn_exit(do_lang_tempcode('UNKNOWN_FORMAT', escape_html($ext)));
}
