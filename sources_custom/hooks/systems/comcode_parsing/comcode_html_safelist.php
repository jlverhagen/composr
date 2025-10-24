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
 * @package    comcode_html_safelist
 */

/**
 * Hook class.
 */
class Hook_comcode_parsing_comcode_html_safelist
{
    /**
     * Get a list of allowed HTML sequences.
     *
     * @return array List of allowed HTML sequences
     */
    public function get_allowed_html_seqs() : array
    {
        if (!addon_installed('comcode_html_safelist')) {
            return [];
        }

        $allowed_html_seqs = [];

        require_code('textfiles');
        $safelists = explode("\n", read_text_file('comcode_safelist', null, true));
        foreach ($safelists as $w) {
            if (trim($w) != '') {
                if ($w[0] != '/') {
                    $w = preg_quote($w, '#');
                } else {
                    $w = substr($w, 1, strlen($w) - 2);
                }
                $allowed_html_seqs[] = $w;
            }
        }
        return $allowed_html_seqs;
    }

    /**
     * Find if some Comcode tag sequence in the parsing stream is safelisted.
     *
     * @param  string $comcode_portion The chunk of Comcode
     * @return boolean Whether it is
     *
     * @ignore
     */
    public function comcode_safelisted(string $comcode_portion) : bool
    {
        if (!addon_installed('comcode_html_safelist')) {
            return false;
        }

        require_code('textfiles');
        static $safelists = null;
        if ($safelists === null) {
            $safelists = explode("\n", read_text_file('comcode_safelist'));
        }

        if (in_array($comcode_portion, $safelists)) {
            return true;
        }
        foreach ($safelists as $safelist) {
            if ((substr($safelist, 0, 1) == '/') && (substr($safelist, -1) == '/') && (preg_match($safelist, $comcode_portion) != 0)) {
                return true;
            }
        }

        return false;
    }
}
