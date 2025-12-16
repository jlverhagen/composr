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
class comcode_pages_test_set extends cms_test_case
{
    public function setUp()
    {
        require_code('zones2');
    }

    public function testParsing()
    {
        require_code('files2');
        require_code('comcode_check');
        require_code('failure');

        push_throw_errors(true);

        if ($this->only === null) {
            $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN, true, true, ['txt']);
        } else {
            $files = ['docs/pages/comcode_custom/EN/' . $this->only . '.txt'];
        }

        foreach ($files as $path) {
            if ($path != 'themes/default/text/tempcode_test.txt') {
                continue;
            }

            if (preg_match('#^(adminzone/pages|buildr/pages|docs/pages|site/pages|pages|themes/default/text|themes/default/text_custom|data/modules/cms_comcode_pages)/#', $path) == 0) {
                continue;
            }

            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path, FILE_READ_LOCK | FILE_READ_BOM);

            try {
                check_comcode($c);
            } catch (Exception $e) {
                $this->assertTrue(false, 'Failed to parse ' . $path . ', ' . $e->getMessage());
            }
        }

        pop_throw_errors();
    }

    public function testPageTitleDetection()
    {
        if ($this->only !== null) {
            return;
        }

        $pages = [
            '[title]Test[/title]' => 'Test',
            '[title="1"]Test[/title]' => 'Test',
            '[title param="1"]Test[/title]' => 'Test',

            '[title x="y"]Test[/title]' => 'Test',
            '[title="1" x="y"]Test[/title]' => 'Test',
            '[title param="1" x="y"]Test[/title]' => 'Test',
            '[title x="y" param="1"]Test[/title]' => 'Test',

            '[title sub="Subtitle"]Test[/title]' => 'Test (Subtitle)',
            '[title="1" sub="Subtitle"]Test[/title]' => 'Test (Subtitle)',
            '[title param="1" sub="Subtitle"]Test[/title]' => 'Test (Subtitle)',
            '[title sub="Subtitle" param="1"]Test[/title]' => 'Test (Subtitle)',

            '[title]
                Test
            [/title]' => 'Test',

            '[title]Foo & Bar[/title]' => 'Foo & Bar',

            '[html][title]Foo &amp; Bar[/title][/html]' => 'Foo & Bar',
            '[semihtml][title]Foo &amp; Bar[/title][/semihtml]' => 'Foo & Bar',
            '[semihtml][title sub="\"\""]Foo &amp; Bar[/title][/semihtml]' => 'Foo & Bar',
            '[semihtml][title sub="[b]foo[/b]"]Foo &amp; Bar[/title][/semihtml]' => 'Foo & Bar',
        ];

        $path = cms_tempnam();

        foreach ($pages as $page_contents => $expected) {
            file_put_contents($path, $page_contents);
            $got = get_comcode_page_title_from_disk($path, true);
            $this->assertTrue($got == $expected, 'Failed on: ' . $page_contents . '; expected: ' . $expected . '; got: ' . $got);
        }

        foreach ($pages as $page_contents => $expected) {
            file_put_contents($path, $page_contents);
            $_got = get_comcode_page_title_from_disk($path, true, true);
            $got = $_got->evaluate();
            $this->assertTrue($got == escape_html($expected), 'Failed on: ' . $page_contents . '; expected: ' . $expected . '; got: ' . $got);
        }

        $page_contents = '[title sub="Foobar"]Test[/title]';
        file_put_contents($path, $page_contents);
        $got = get_comcode_page_title_from_disk($path, false);
        $this->assertTrue($got == 'Test', 'Failed on: ' . $page_contents);

        $page_contents = '[title="2"]Test[/title]';
        file_put_contents($path, $page_contents);
        $got = get_comcode_page_title_from_disk($path, true);
        $this->assertTrue(strpos($got, 'Test') === false, 'Failed on: ' . $page_contents);

        unlink($path);
    }
}
