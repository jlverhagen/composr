<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

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
