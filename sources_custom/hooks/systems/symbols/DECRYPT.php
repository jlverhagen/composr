<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    password_censor
 */

/**
 * Hook class.
 */
class Hook_symbol_DECRYPT
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('password_censor')) {
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
        $value = '';

        if (!@cms_empty_safe($param[1])) {
            require_code('encryption');
            if ((is_encryption_enabled()) && (is_data_encrypted($param[0]))) {
                $value = decrypt_data($param[0], $param[1]);
            } else {
                $value = $param[0];
            }
        }

        return $value;
    }
}
