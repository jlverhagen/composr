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
class seo_test_set extends cms_test_case
{
    public function testKeywordGeneration()
    {
        require_code('content2');

        // Test HTML stripping
        $txt = '[html]<h1>This is a title</h1><p>This is some text</p>[/html]';
        list($keywords, $description) = _seo_meta_find_data([$txt], $txt);
        $this->assertTrue($description == 'This is a title. This is some text', 'Got: ' . $description);

        // Test coverage for many cases:
        //  words differing only in case
        //  Proper Nouns
        //  apostrophes
        //  hyphenation
        //  no word repetition
        //  no stop words
        //  first word detected
        list($keywords) = _seo_meta_find_data(['hello Mr Tester this Is a world-renowned luxorious test. Epic epic testing, it shan\'t fail.'], '');
        $_keywords = explode(',', $keywords);
        sort($_keywords); // We need to re-sort, as the occurrence-based sort order isn't fully defined
        $keywords = implode(',', $_keywords);
        $this->assertTrue($keywords == 'Mr Tester,epic,fail,hello,luxorious,shan\'t,testing,world-renowned', 'Got: ' . $keywords);

        // Test last word detected
        list($keywords) = _seo_meta_find_data(['Epic'], '');
        $this->assertTrue($keywords == 'Epic', 'Got: ' . $keywords);

        // Test unicode too; also capitalised stop words still stripped
        $emoji = "\u{1F601}";
        list($keywords) = _seo_meta_find_data(['This is epic' . $emoji], '');
        $this->assertTrue($keywords == 'epic', 'Got: ' . $keywords);

        // Test filtering
        list($keywords) = _seo_meta_find_data(['Hello [attachment]new_1[/attachment] [media]uploads/downloads/example.png[/media] [b]World[/b] [Example]'], '');
        $this->assertTrue($keywords == 'Example,World,Hello', 'Got: ' . $keywords);
    }
}
