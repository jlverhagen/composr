<?php

namespace Box\Spout\Writer;

use Box\Spout\Common\Type;
use Box\Spout\TestUsingResource;

/**
 * Class XLSXTest
 *
 * @package Box\Spout\Writer
 */
class XLSXTest extends \PHPUnit_Framework_TestCase
{
    use TestUsingResource;

    /**
     * @expectedException \Box\Spout\Common\Exception\IOException
     */
    public function testAddRowShouldThrowExceptionIfCannotOpenAFileForWriting()
    {
        $fileName = 'file_that_wont_be_written.xlsx';
        $this->createUnwritableFolderIfNeeded($fileName);
        $filePath = $this->getGeneratedUnwritableResourcePath($fileName);

        $writer = WriterFactory::create(Type::XLSX);
        @$writer->openToFile($filePath);
        $writer->addRow(['xlsx--11', 'xlsx--12']);
        $writer->close();
    }

    /**
     * @expectedException \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function testAddRowShouldThrowExceptionIfCallAddRowBeforeOpeningWriter()
    {
        $writer = WriterFactory::create(Type::XLSX);
        $writer->addRow(['xlsx--11', 'xlsx--12']);
        $writer->close();
    }

    /**
     * @expectedException \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function testAddRowShouldThrowExceptionIfCallAddRowsBeforeOpeningWriter()
    {
        $writer = WriterFactory::create(Type::XLSX);
        $writer->addRows([['xlsx--11', 'xlsx--12']]);
        $writer->close();
    }

    /**
     * @return void
     */
    public function testAddNewSheetAndMakeItCurrent()
    {
        $fileName = 'test_add_new_sheet_and_make_it_current.xlsx';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($resourcePath);
        $writer->addNewSheetAndMakeItCurrent();
        $writer->close();

        $sheets = $writer->getSheets();
        $this->assertEquals(2, count($sheets), 'There should be 2 sheets');
        $this->assertEquals($sheets[1], $writer->getCurrentSheet(), 'The current sheet should be the second one.');
    }

    /**
     * @return void
     */
    public function testSetCurrentSheet()
    {
        $fileName = 'test_set_current_sheet.xlsx';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($resourcePath);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->addNewSheetAndMakeItCurrent();

        $firstSheet = $writer->getSheets()[0];
        $writer->setCurrentSheet($firstSheet);

        $writer->close();

        $this->assertEquals($firstSheet, $writer->getCurrentSheet(), 'The current sheet should be the first one.');
    }

    /**
     * @return void
     */
    public function testAddRowShouldWriteGivenDataToSheetUsingInlineStrings()
    {
        $fileName = 'test_add_row_should_write_given_data_to_sheet.xlsx';
        $dataRows = [
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ];

        $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = true);

        foreach ($dataRows as $dataRow) {
            foreach ($dataRow as $cellValue) {
                $this->assertInlineStringWasWrittenToSheet($fileName, 1, $cellValue);
            }
        }
    }

    /**
     * @return void
     */
    public function testAddRowShouldWriteGivenDataToTwoSheetUsingInlineStrings()
    {
        $fileName = 'test_add_row_should_write_given_data_to_sheet.xlsx';
        $dataRows = [
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ];

        $numSheets = 2;
        $this->writeToMultipleSheetsInXLSXFile($dataRows, $numSheets, $fileName, $shouldUseInlineStrings = true);

        for ($i = 1; $i <= $numSheets; $i++) {
            foreach ($dataRows as $dataRow) {
                foreach ($dataRow as $cellValue) {
                    $this->assertInlineStringWasWrittenToSheet($fileName, $numSheets, $cellValue);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function testAddRowShouldWriteGivenDataToSheetUsingSharedStrings()
    {
        $fileName = 'test_add_row_should_write_given_data_to_sheet.xlsx';
        $dataRows = [
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ];

        $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = false);

        foreach ($dataRows as $dataRow) {
            foreach ($dataRow as $cellValue) {
                $this->assertSharedStringWasWritten($fileName, $cellValue);
            }
        }
    }

    /**
     * @return void
     */
    public function testAddRowShouldWriteGivenDataToTwoSheetUsingSharedStrings()
    {
        $fileName = 'test_add_row_should_write_given_data_to_two_sheet_using_shared_strings.xlsx';
        $dataRows = [
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ];

        $numSheets = 2;
        $this->writeToMultipleSheetsInXLSXFile($dataRows, $numSheets, $fileName, $shouldUseInlineStrings = false);

        for ($i = 1; $i <= $numSheets; $i++) {
            foreach ($dataRows as $dataRow) {
                foreach ($dataRow as $cellValue) {
                    $this->assertSharedStringWasWritten($fileName, $cellValue);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function testAddRowShouldWriteGivenDataToTheCorrectSheet()
    {
        $fileName = 'test_add_row_should_write_given_data_to_the_correct_sheet.xlsx';
        $dataRowsSheet1 = [
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
        ];
        $dataRowsSheet2 = [
            ['xlsx--sheet2--11', 'xlsx--sheet2--12'],
            ['xlsx--sheet2--21', 'xlsx--sheet2--22', 'xlsx--sheet2--23'],
        ];
        $dataRowsSheet1Again = [
            ['xlsx--sheet1--31', 'xlsx--sheet1--32'],
            ['xlsx--sheet1--41', 'xlsx--sheet1--42', 'xlsx--sheet1--43'],
        ];

        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        /** @var \Box\Spout\Writer\XLSX $writer */
        $writer = WriterFactory::create(Type::XLSX);
        $writer->setShouldUseInlineStrings(true);

        $writer->openToFile($resourcePath);

        $writer->addRows($dataRowsSheet1);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->addRows($dataRowsSheet2);

        $firstSheet = $writer->getSheets()[0];
        $writer->setCurrentSheet($firstSheet);

        $writer->addRows($dataRowsSheet1Again);

        $writer->close();

        foreach ($dataRowsSheet1 as $dataRow) {
            foreach ($dataRow as $cellValue) {
                $this->assertInlineStringWasWrittenToSheet($fileName, 1, $cellValue, 'Data should have been written in Sheet 1');
            }
        }
        foreach ($dataRowsSheet2 as $dataRow) {
            foreach ($dataRow as $cellValue) {
                $this->assertInlineStringWasWrittenToSheet($fileName, 2, $cellValue, 'Data should have been written in Sheet 2');
            }
        }
        foreach ($dataRowsSheet1Again as $dataRow) {
            foreach ($dataRow as $cellValue) {
                $this->assertInlineStringWasWrittenToSheet($fileName, 1, $cellValue, 'Data should have been written in Sheet 1');
            }
        }
    }

    /**
     * @return void
     */
    public function testAddRowShouldAutomaticallyCreateNewSheetsIfMaxRowsReachedAndOptionTurnedOn()
    {
        $fileName = 'test_add_row_should_automatically_create_new_sheets_if_max_rows_reached_and_option_turned_on.xlsx';
        $dataRows = [
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
            ['xlsx--sheet2--11', 'xlsx--sheet2--12'], // this should be written in a new sheet
        ];

        // set the maxRowsPerSheet limit to 2
        \ReflectionHelper::setStaticValue('\Box\Spout\Writer\Internal\XLSX\Workbook', 'maxRowsPerWorksheet', 2);

        $writer = $this->writeToXLSXFile($dataRows, $fileName, true, $shouldCreateSheetsAutomatically = true);
        $this->assertEquals(2, count($writer->getSheets()), '2 sheets should have been created.');

        $this->assertInlineStringWasNotWrittenToSheet($fileName, 1, 'xlsx--sheet2--11');
        $this->assertInlineStringWasWrittenToSheet($fileName, 2, 'xlsx--sheet2--11');

        \ReflectionHelper::reset();
    }

    /**
     * @return void
     */
    public function testAddRowShouldNotCreateNewSheetsIfMaxRowsReachedAndOptionTurnedOff()
    {
        $fileName = 'test_add_row_should_not_create_new_sheets_if_max_rows_reached_and_option_turned_off.xlsx';
        $dataRows = [
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
            ['xlsx--sheet1--31', 'xlsx--sheet1--32'], // this should NOT be written in a new sheet
        ];

        // set the maxRowsPerSheet limit to 2
        \ReflectionHelper::setStaticValue('\Box\Spout\Writer\Internal\XLSX\Workbook', 'maxRowsPerWorksheet', 2);

        $writer = $this->writeToXLSXFile($dataRows, $fileName, true, $shouldCreateSheetsAutomatically = false);
        $this->assertEquals(1, count($writer->getSheets()), 'Only 1 sheet should have been created.');

        $this->assertInlineStringWasNotWrittenToSheet($fileName, 1, 'xlsx--sheet1--31');

        \ReflectionHelper::reset();
    }

    /**
     * @return void
     */
    public function testAddRowShouldEscapeHtmlSpecialCharacters()
    {
        $fileName = 'test_add_row_should_escape_html_special_characters.xlsx';
        $dataRows = [
            ['I\'m in "great" mood', 'This <must> be escaped & tested'],
        ];

        $this->writeToXLSXFile($dataRows, $fileName);

        $this->assertInlineStringWasWrittenToSheet($fileName, 1, 'I&#039;m in &quot;great&quot; mood', 'Quotes should be escaped');
        $this->assertInlineStringWasWrittenToSheet($fileName, 1, 'This &lt;must&gt; be escaped &amp; tested', '<, > and & should be escaped');
    }

    /**
     * @return void
     */
    public function testAddRowShouldEscapeControlCharacters()
    {
        $fileName = 'test_add_row_should_escape_html_special_characters.xlsx';
        $dataRows = [
            ['control\'s '.chr(21).' "character"'],
        ];

        $this->writeToXLSXFile($dataRows, $fileName);

        $this->assertInlineStringWasWrittenToSheet($fileName, 1, 'control&#039;s _x0015_ &quot;character&quot;');
    }


    /**
     * @param array $allRows
     * @param string $fileName
     * @param bool $shouldUseInlineStrings
     * @param bool $shouldCreateSheetsAutomatically
     * @return XLSX
     */
    private function writeToXLSXFile($allRows, $fileName, $shouldUseInlineStrings = true, $shouldCreateSheetsAutomatically = true)
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        /** @var \Box\Spout\Writer\XLSX $writer */
        $writer = WriterFactory::create(Type::XLSX);
        $writer->setShouldUseInlineStrings($shouldUseInlineStrings);
        $writer->setShouldCreateNewSheetsAutomatically($shouldCreateSheetsAutomatically);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);
        $writer->close();

        return $writer;
    }

    /**
     * @param array $allRows
     * @param int $numSheets
     * @param string $fileName
     * @param bool $shouldUseInlineStrings
     * @param bool $shouldCreateSheetsAutomatically
     * @return XLSX
     */
    private function writeToMultipleSheetsInXLSXFile($allRows, $numSheets, $fileName, $shouldUseInlineStrings = true, $shouldCreateSheetsAutomatically = true)
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        /** @var \Box\Spout\Writer\XLSX $writer */
        $writer = WriterFactory::create(Type::XLSX);
        $writer->setShouldUseInlineStrings($shouldUseInlineStrings);
        $writer->setShouldCreateNewSheetsAutomatically($shouldCreateSheetsAutomatically);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);

        for ($i = 1; $i < $numSheets; $i++) {
            $writer->addNewSheetAndMakeItCurrent();
            $writer->addRows($allRows);
        }

        $writer->close();

        return $writer;
    }

    /**
     * @param string $fileName
     * @param int $sheetNumber
     * @param string $inlineString
     * @param string $message
     * @return void
     */
    private function assertInlineStringWasWrittenToSheet($fileName, $sheetNumber, $inlineString, $message = '')
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSheetFile = $resourcePath . '#xl/worksheets/sheet' . $sheetNumber . '.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSheetFile);

        $this->assertContains($inlineString, $xmlContents, $message);
    }

    /**
     * @param string $fileName
     * @param int $sheetNumber
     * @param string $inlineString
     * @param string $message
     * @return void
     */
    private function assertInlineStringWasNotWrittenToSheet($fileName, $sheetNumber, $inlineString, $message = '')
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSheetFile = $resourcePath . '#xl/worksheets/sheet' . $sheetNumber . '.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSheetFile);

        $this->assertNotContains($inlineString, $xmlContents, $message);
    }

    /**
     * @param string $fileName
     * @param string $sharedString
     * @param string $message
     * @return void
     */
    private function assertSharedStringWasWritten($fileName, $sharedString, $message = '')
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSharedStringsFile = $resourcePath . '#xl/sharedStrings.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSharedStringsFile);

        $this->assertContains($sharedString, $xmlContents, $message);
    }
}
