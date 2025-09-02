<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite_tracker
 */

/**
 * Hook class.
 */
class Hook_symbol_MANTIS_WEEK_ACTIVITY
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        if (get_forum_type() != 'cns') {
            return null;
        }

        if (strpos(get_db_type(), 'mysql') === false) {
            return null;
        }

        return [
            'compile' => SYMBOL_COMPILE_STATIC_IF_AGGRESSIVE,
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
        $cnt_in_last_week = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM mantis_bug_table WHERE last_updated>' . strval(time() - 60 * 60 * 24 * 7));
        return strval($cnt_in_last_week);
    }
}
