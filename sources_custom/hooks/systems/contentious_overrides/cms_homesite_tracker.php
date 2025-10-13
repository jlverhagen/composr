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
class Hook_contentious_overrides_cms_homesite_tracker
{
    public function compile_included_code($path, $codename, &$code)
    {
        if (!addon_installed('cms_homesite_tracker') || !addon_installed('cms_homesite')) {
            return;
        }

        if (strpos($path, 'pages/modules/admin_email_log.php') !== false) {
            require_code('override_api');
            if ($code === null) {
                $code = clean_php_file_for_eval(file_get_contents($path), $path);
            }

            insert_code_after__by_command(
                $code,
                "show",
                "'RESULTS_TABLE' => \$results_table,",
                "
                'TRACKER_RESULTS_TABLE' => tracker_email_log(),
                ",
                1,
            );
        }
    }
}

/**
 * Get the Tempcode for the Mantis tracker e-mail log.
 *
 * @return Tempcode The table
 */
function tracker_email_log() : object
{
    i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

    if (!addon_installed('cms_homesite_tracker') || !addon_installed('cms_homesite')) {
        return new Tempcode();
    }

    // Add Mantis error tracker e-mail log

    require_lang('cms_homesite');
    require_code('temporal');
    require_code('templates_results_table');

    $tracker_start = get_param_integer('tracker_start', 0);
    $tracker_max = get_param_integer('tracker_max', 50);

    $header_row = results_header_row([
        do_lang_tempcode('DATE_TIME'),
        do_lang_tempcode('TO'),
        do_lang_tempcode('SUBJECT')
    ], [], 'tracker_sort', '');

    $result_entries = new Tempcode();
    $rows = $GLOBALS['SITE_DB']->_query('SELECT `email_id`,`email`,`subject`,`submitted` FROM mantis_email_table ORDER BY `submitted` DESC', $tracker_max, $tracker_start);
    foreach ($rows as $row) {
        $result_entries->attach(results_entry([
            get_timezoned_date($row['submitted']),
            escape_html($row['email']),
            escape_html($row['subject']),
        ], false));
    }
    $max_rows = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM mantis_email_table', false, true);
    $results_table = results_table(do_lang_tempcode('TRACKER_EMAIL_LOG'), $tracker_start, 'tracker_start', $tracker_max, 'tracker_max', $max_rows, $header_row, $result_entries, [], null, null, 'tracker_sort', new Tempcode());

    return $results_table;
}
