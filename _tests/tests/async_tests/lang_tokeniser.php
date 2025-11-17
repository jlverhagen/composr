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
class lang_tokeniser_test_set extends cms_test_case
{
    public function testLangStemmer()
    {
        require_code('lang_stemmer_' . fallback_lang());
        $stemmer = object_factory('Stemmer_' . fallback_lang());

        $this->assertTrue($stemmer->stem('cakes') == 'cake'); // Simple stemming case
        $this->assertTrue($stemmer->stem('CaKes') == 'CaKe'); // Preserve mid-word case
        $this->assertTrue($stemmer->stem('CaKeS') == 'CaKeS'); // Won't stem upper case
        $this->assertTrue($stemmer->stem('CAKES') == 'CAKES'); // Won't stem upper case

        $this->assertTrue($stemmer->stem('fish') == 'fish'); // Simple stemming non-case
        $this->assertTrue($stemmer->stem('FisH') == 'FisH'); // Preserve mid-word case
    }

    public function testLangTokeniser()
    {
        require_code('lang_tokeniser_' . fallback_lang());
        $tokeniser = object_factory('Source_lang_tokeniser_' . fallback_lang());

        // Querying...

        $got = $tokeniser->query_to_search_tokens('One Two Three Four Five Six', true, 5);
        $this->assertTrue(array_keys($got[0]) == ['one', 'two', 'three', 'four', 'five']);

        $got = $tokeniser->query_to_search_tokens("it's 'its'");
        $this->assertTrue(array_keys($got[0]) == ["it's", 'its']);

        $got = $tokeniser->query_to_search_tokens('a "test search"');
        $this->assertTrue(array_keys($got[0]) == ['a', 'test search']);

        $got = $tokeniser->query_to_search_tokens('"test search"');
        $this->assertTrue(array_keys($got[0]) == ['test search']);

        $got = $tokeniser->query_to_search_tokens('test" search');
        $this->assertTrue(array_keys($got[0]) == ['test', 'search']);

        $search = 'This, is; an    example- SEARCH +YES -No - + Blah-blah "Go Go Go" +"And this"';
        $expected = [
            ['this' => true, 'is' => true, 'an' => true, 'example' => true, 'search' => true, 'blah' => true, 'go go go' => false],
            ['yes' => true, 'and this' => false],
            ['no' => true],
        ];
        $got = $tokeniser->query_to_search_tokens($search);
        $this->assertTrue($got == $expected);

        // Indexing...

        $got = $tokeniser->text_to_ngrams('Composr/software FAQ');
        $this->assertTrue(array_keys($got) == ['composr', 'software', 'faq']);

        $got = $tokeniser->text_to_ngrams("it's 'its'");
        $this->assertTrue(array_keys($got) == ["it's", 'its']);

        $total_singular_ngram_tokens = 0;
        $expected = [
            'this' => true,
            'this is' => false,
            'this is a' => false,
            'is' => true,
            'is a' => false,
            'is a test' => false,
            'a' => true,
            'a test' => false,
            'a test sentence' => false,
            'test' => true,
            'test sentence' => false,
            'sentence' => true,
            'another' => true,
            'another test' => false,
            'another test sentence' => false,
        ];
        $got = $tokeniser->text_to_ngrams('This is a test sentence. Another test sentence', 3, $total_singular_ngram_tokens);
        $this->assertTrue($got == $expected);
        $this->assertTrue($total_singular_ngram_tokens == 8);
    }
}
