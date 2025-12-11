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
 * Hook class.
 */
class Hook_spreadsheet_reader_enhanced_spreadsheets
{
    /**
     * Get a list of file types supported by this hook.
     *
     * @return ?array A list of supported file types without dots (null: hook disabled)
     */
    public function spreadsheet_read_file_types() : ?array
    {
        if (!addon_installed('enhanced_spreadsheets')) {
            return null;
        }

        // Required PHP extensions
        if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
            return null;
        }

        // Required PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
            return null;
        }

        return ['ods', 'xlsx'];
    }

    /**
     * Open spreadsheet for reading.
     *
     * @param  PATH $path File path
     * @param  ?string $filename Filename (null: derive from $path)
     * @param  integer $algorithm An ALGORITHM_* constant
     * @param  boolean $trim Whether to trim each cell
     * @param  ?string $default_charset The default character set to assume if none is specified in the file (null: website character set) (blank: smart detection)
     * @return ?object A subclass of Source_spreadsheet_reader (null: hook disabled)
     */
    public function spreadsheet_open_read(string $path, ?string $filename = null, int $algorithm = 3, bool $trim = true, ?string $default_charset = '') : ?object
    {
        if (!addon_installed('enhanced_spreadsheets')) {
            return null;
        }

        // Required PHP extensions
        if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
            return null;
        }

        // Required PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
            return null;
        }

        return object_factory('Source_spreadsheet_reader_openspout', false, [$path, $filename, $algorithm, $trim, $default_charset], true);
    }
}

/**
 * OpenSpout spreadsheet reader.
 *
 * @package enhanced_spreadsheets
 */
class Source_spreadsheet_reader_openspout extends Source_spreadsheet_reader
{
    protected $reader = null;
    protected $row_iterator = null;

    /**
     * Constructor. Opens spreadsheet for reading.
     *
     * @param  PATH $path File path
     * @param  string $filename Filename
     * @param  integer $algorithm An ALGORITHM_* constant
     * @param  boolean $trim Whether to trim each cell
     * @param  ?string $default_charset The default character set to assume if none is specified in the file (null: website character set) (blank: smart detection)
     */
    public function __construct(string $path, string $filename, int $algorithm = 3, bool $trim = true, ?string $default_charset = '')
    {
        require_code('openspout/vendor/autoload');

        $before = ini_get('ocproducts.type_strictness');
        cms_ini_set('ocproducts.type_strictness', '0');

        $ext = get_file_extension($filename);
        switch ($ext) {
            case 'ods':
                $this->reader = new \OpenSpout\Reader\ODS\Reader();
                break;

            case 'xlsx':
                $this->reader = new \OpenSpout\Reader\XLSX\Reader();
                break;

            default:
                fatal_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('a407077f74ab56ee87e5100a778000e1')));
        }

        if (function_exists('libxml_disable_entity_loader')) {
            @libxml_disable_entity_loader(false);
        }

        $this->reader->open($path);

        $sheet_iterator = $this->reader->getSheetIterator(); // We will only look at the first sheet
        $sheet_iterator->rewind();
        $this->row_iterator = $sheet_iterator->current()->getRowIterator();
        $this->row_iterator->rewind();

        if ($before !== false) {
            cms_ini_set('ocproducts.type_strictness', $before);
        }

        parent::__construct($path, $filename, $algorithm, $trim, $default_charset);
    }

    /**
     * Rewind to return first record again.
     */
    public function rewind()
    {
        $before = ini_get('ocproducts.type_strictness');
        cms_ini_set('ocproducts.type_strictness', '0');
        $this->row_iterator->rewind();
        if ($before !== false) {
            cms_ini_set('ocproducts.type_strictness', $before);
        }
    }

    /**
     * Read spreadsheet row.
     *
     * @return ~array Row (false: error)
     */
    protected function _read_row()
    {
        if ($this->reader === null) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('94edd48cc8a155ffb052e79bc086fb5c')));
        }

        $before = ini_get('ocproducts.type_strictness');
        cms_ini_set('ocproducts.type_strictness', '0');

        if (!$this->row_iterator->valid()) {
            if ($before !== false) {
                cms_ini_set('ocproducts.type_strictness', $before);
            }
            return false;
        }

        $_row = $this->row_iterator->current();
        $this->row_iterator->next();
        $_cells = $_row->getCells();

        $cells = [];
        foreach ($_cells as $cell) {
            $cells[] = @strval($cell->getValue());
        }

        if ($before !== false) {
            cms_ini_set('ocproducts.type_strictness', $before);
        }

        return $cells;
    }

    /**
     * Standard destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close down the spreadsheet file handle, for when we're done.
     */
    public function close()
    {
        if ($this->reader !== null) {
            $before = ini_get('ocproducts.type_strictness');
            cms_ini_set('ocproducts.type_strictness', '0');
            $this->reader->close();
            if ($before !== false) {
                cms_ini_set('ocproducts.type_strictness', $before);
            }
            $this->reader = null;
        }
    }
}
