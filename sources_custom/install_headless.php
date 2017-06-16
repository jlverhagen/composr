<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    meta_toolkit
 */

function do_install_to($database, $username, $password, $table_prefix, $safe_mode, $forum_driver = 'cns', $board_path = null, $forum_base_url = null, $database_forums = null, $username_forums = null, $password_forums = null, $extra_settings = null, $do_index_test = true, $db_type = null)
{
    rename(get_file_base() . '/_config.php', get_file_base() . '/_config.php.bak');

    $success = _do_install_to($database, $username, $password, $table_prefix, $safe_mode, $forum_driver, $board_path, $forum_base_url, $database_forums, $username_forums, $password_forums, $extra_settings, $db_type);

    if ($success && $do_index_test) {
        $url = get_base_url() . '/index.php?keep_no_query_limit=1';
        $http_result = cms_http_request($url, array('trigger_error' => false, 'timeout' => 20.0));
        $success = ($http_result->message == '200');

        if ((!$success) && (isset($_GET['debug']))) {
            @var_dump(escape_html($data));
            @var_dump($GLOBALS['HTTP_MESSAGE']);

            $error = $url . ' : ' . preg_replace('#^.*An error has occurred#s', 'An error has occurred', strip_tags($data));
            @print(escape_html($error));
            @ob_end_flush();
        }
    }

    @unlink(get_file_base() . '/_config.php');
    @rename(get_file_base() . '/_config.php.bak', get_file_base() . '/_config.php');

    return $success;
}

function _do_install_to($database, $username, $password, $table_prefix, $safe_mode, $forum_driver, $board_path, $forum_base_url, $database_forums, $username_forums, $password_forums, $extra_settings, $db_type)
{
    if ($db_type === null) {
        $db_type = get_db_type();
    }
    if ($board_path === null) {
        $board_path = get_file_base() . '/forums';
    }
    if ($database_forums === null) {
        $database_forums = $database;
    }
    if ($username_forums === null) {
        $username_forums = $username;
    }
    if ($password_forums === null) {
        $password_forums = $password;
    }
    if ($extra_settings === null) {
        $extra_settings = array(
        );
    }

    $settings = $extra_settings + array(
        'max' => '1000',
        'default_lang' => fallback_lang(),
        'email' => 'E-mail address',
        'advertise_on' => '0',
        'use_multi_db' => '0',
        'use_msn' => '0',
        'db_type' => $db_type,
        'forum_type' => $forum_driver,
        'board_path' => $board_path,
        'forum_base_url' => $forum_base_url,
        'domain' => get_domain(),
        'base_url' => get_base_url(),
        'table_prefix' => $table_prefix,
        'cns_table_prefix' => $table_prefix,
        'master_password' => '',
        'master_password_confirm' => '',
        'send_error_emails_ocproducts' => '1',
        'admin_username' => 'admin',
        'cns_admin_password' => '',
        'cns_admin_password_confirm' => '',
        'clear_existing_forums_on_install' => 'yes',
        'db_site' => $database,
        'db_site_host' => get_db_site_host(),
        'db_site_user' => $username,
        'db_site_password' => $password,
        'user_cookie' => 'cms_member_id',
        'pass_cookie' => 'cms_member_hash',
        'cookie_domain' => '',
        'cookie_path' => '/',
        'cookie_days' => '120',
        'db_forums' => $database_forums,
        'db_forums_host' => get_db_site_host(),
        'db_forums_user' => $username_forums,
        'db_forums_password' => $password_forums,
        'multi_lang_content' => '0',
        'self_learning_cache' => '0',
        'confirm' => '1',
    );

    $stages = array(
        array(
            array(),
            array(),
        ),

        array(
            array(
                'step' => '2',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '3',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '4',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '5',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '6',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '7',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '8',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '9',
            ),
            $settings,
        ),

        array(
            array(
                'step' => '10',
            ),
            $settings,
        ),
    );

    foreach ($stages as $stage) {
        list($get, $post) = $stage;
        $url = get_base_url() . '/install.php?keep_safe_mode=' . ($safe_mode ? '1' : '0');
        if (count($get) > 0) {
            $url .= '&' . http_build_query($get);
        }
        $http_result = cms_http_request($url, array('post_params' => $post));
        $data = $http_result->data;
        if (strpos(strip_tags($data), 'An error has occurred') !== false) {
            $http_result->message = '500';
        }
        $success = ($http_result->message == '200');

        if ((!$success) && (isset($_GET['debug']))) {
            @var_dump(escape_html($data));
            @var_dump($GLOBALS['HTTP_MESSAGE']);

            $error = $url . ' : ' . preg_replace('#^.*An error has occurred#s', 'An error has occurred', strip_tags($data));
            @print(escape_html($error));

            @ob_end_flush();
        }

        if (!$success) {
            return false; // Don't keep installing if there's an error
        }
    }

    return true;
}
