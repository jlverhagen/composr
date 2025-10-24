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
 * @package    hybridauth
 */

/**
 * Hook class.
 */
class Hook_symbol_HYBRIDAUTH_BUTTONS_CSS
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('hybridauth')) {
            return null;
        }

        return [
            'compile' => SYMBOL_COMPILE_STATIC_NONE,
            'public' => false,
        ];
    }

    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @param  string $lang The language to evaluate this symbol in (some symbols refer to language elements)
     * @param  array $escaped Array of escaping operations
     * @return string Result
     */
    public function run(array $param, string $lang, array $escaped) : string
    {
        require_code('hybridauth');
        $providers = enumerate_hybridauth_providers();

        $css = '';
        foreach ($providers as $provider => $info) {
            if (!$info['enabled']) {
                continue;
            }

            $_css = do_template('_hybridauth_button', [
                '_GUID' => '9e32c801bc53a57f37b77a4ac8f95428',
                'CODENAME' => $provider,
                'BACKGROUND_COLOUR' => $info['background_colour'],
                'TEXT_COLOUR' => $info['text_colour'],
                'ICON' => $info['icon'],
            ], null, false, null, '.css', 'css');
            $css .= $_css->evaluate();
        }

        return $css;
    }
}
