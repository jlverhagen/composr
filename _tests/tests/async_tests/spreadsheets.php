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
class spreadsheets_test_set extends cms_test_case
{
    protected $files;
    protected $expected;

    public function setUp()
    {
        parent::setUp();

        require_code('files_spreadsheets_read');
        require_code('files_spreadsheets_write');

        $this->files = [
            'test.csv',
            'test-scsv.txt',
            'test-tsv.txt',
        ];
        if (addon_installed('enhanced_spreadsheets')) {
            $this->files = array_merge($this->files, [
                'test.ods',
                'test.xlsx',
            ]);
        }

        $this->expected = [];

        $this->expected[Source_spreadsheet_reader::ALGORITHM_RAW] = [
            ['A', 'B', 'C'],
            ['A1', 'B1', 'C,"1'],
            ['A2', 'B2', 'C"2'],
            ['', '', "C3\nC3"],
            ['A4', '', ''],
        ];

        $this->expected[Source_spreadsheet_reader::ALGORITHM_UNNAMED_FIELDS] = [
            ['A1', 'B1', 'C,"1'],
            ['A2', 'B2', 'C"2'],
            ['', '', "C3\nC3"],
            ['A4', '', ''],
        ];

        $this->expected[Source_spreadsheet_reader::ALGORITHM_NAMED_FIELDS] = [
            ['A' => 'A1', 'B' => 'B1', 'C' => 'C,"1'],
            ['A' => 'A2', 'B' => 'B2', 'C' => 'C"2'],
            ['A' => '', 'B' => '', 'C' => "C3\nC3"],
            ['A' => 'A4', 'B' => '', 'C' => ''],
        ];
    }

    public function testRead()
    {
        if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
            $this->assertTrue(false, 'test requires PHP 8.2 or higher');
            return;
        }

        if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
            $this->assertTrue(false, 'Zip and XML extensions needed for test');
            return;
        }

        $exts = [];
        foreach ($this->files as $file) {
            foreach ($this->expected as $algorithm => $expected) {
                $this->assertTrue(Source_spreadsheet_reader::is_spreadsheet_readable($file));

                $sheet_reader = Source_spreadsheet_reader::spreadsheet_open_read(get_file_base() . '/_tests/assets/spreadsheets/' . $file, $file, $algorithm);
                $rows = [];
                while (($row = $sheet_reader->read_row()) !== false) {
                    $rows[] = $row;
                }
                $sheet_reader->close();

                $this->assertTrue($rows == $expected, 'Failed on ' . $file . ' (algorithm '  . strval($algorithm) . ')');
                if ($this->debug) {
                    if ($rows != $expected) {
                        @var_dump($expected);
                        @var_dump($rows);
                        exit();
                    }
                }

                $exts[get_file_extension($file)] = true;
            }
        }

        $this->assertTrue(!Source_spreadsheet_reader::is_spreadsheet_readable('foo.bar'));

        $_exts = explode(',', Source_spreadsheet_reader::spreadsheet_read_file_types());
        sort($_exts);
        ksort($exts);
        $this->assertTrue($_exts == array_keys($exts), 'Not all file extensions covered');
    }

    public function testWrite()
    {
        if (version_compare(PHP_VERSION, '8.2.0', '<')) { // LEGACY
            $this->assertTrue(false, 'test requires PHP 8.2 or higher');
            return;
        }

        if (!class_exists('ZipArchive', false) || !function_exists('xml_parser_create')) {
            $this->assertTrue(false, 'Zip and XML extensions needed for test');
            return;
        }

        foreach ($this->expected as $algorithm => $expected) {
            if ($algorithm == Source_spreadsheet_reader::ALGORITHM_UNNAMED_FIELDS) {
                continue; // Not supported for write
            }

            foreach ($this->files as $file) {
                $this->assertTrue(Source_spreadsheet_writer::is_spreadsheet_writable($file));

                // Write out
                $path = null; // Will be written by reference
                $sheet_writer = Source_spreadsheet_writer::spreadsheet_open_write($path, $file, $algorithm);
                foreach ($expected as $row) {
                    $sheet_writer->write_row($row);
                }
                $sheet_writer->close();

                // Read back in and compare
                $sheet_reader = Source_spreadsheet_reader::spreadsheet_open_read($path, $file, $algorithm);
                $rows = [];
                while (($row = $sheet_reader->read_row()) !== false) {
                    $rows[] = $row;
                }
                $sheet_reader->close();
                $this->assertTrue($rows == $expected, 'Failed on ' . $file . ' (algorithm '  . strval($algorithm) . ')');
                if ($this->debug) {
                    if ($rows != $expected) {
                        @var_dump($expected);
                        @var_dump($rows);
                        exit();
                    }
                }
            }
        }

        $this->assertTrue(!Source_spreadsheet_writer::is_spreadsheet_writable('foo.bar'));

        if (addon_installed('enhanced_spreadsheets')) {
            $this->assertTrue(Source_spreadsheet_writer::spreadsheet_write_default() == 'ods');
        } else {
            $this->assertTrue(Source_spreadsheet_writer::spreadsheet_write_default() == 'csv');
        }
    }
}
