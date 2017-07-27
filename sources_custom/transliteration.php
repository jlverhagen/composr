<?php /*

 Composr
 Copyright (c) ocProducts/Tapatalk, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    transliteration
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__transliteration()
{
    if (!function_exists('transliterator_transliterate')) {
        require_code('Transliterator/Transliterator');

        function transliterator_transliterate($whatever, $text)
        {
            $transliterator = new BehatTransliterator();
            return $transliterator->transliterate($text);
        }
    }
}
