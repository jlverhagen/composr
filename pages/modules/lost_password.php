<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_cns
 */

/**
 * Module page class.
 */
class Module_lost_password
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $GLOBALS['OUTPUT_STREAMING'] = false; // Due to meta refresh that may happen

        $type = get_param_string('type', 'browse');

        require_lang('cns');
        require_css('cns');

        if ($type == 'browse') {
            breadcrumb_set_self(do_lang_tempcode('LOST_PASSWORD'));

            $this->title = get_screen_title('LOST_PASSWORD');
        }

        if ($type == 'step2') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('LOST_PASSWORD'))));
            breadcrumb_set_self(do_lang_tempcode('LOST_PASSWORD'));

            $this->title = get_screen_title('LOST_PASSWORD');
        }

        if ($type == 'step3') {
            $this->title = get_screen_title('LOST_PASSWORD');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }

        require_code('cns_lost_password');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->step1();
        }
        if ($type == 'step2') {
            return $this->step2();
        }
        if ($type == 'step3') {
            return $this->step3();
        }

        return new Tempcode();
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        if ($check_perms && is_guest($member_id)) {
            return array(
                'browse' => array('LOST_PASSWORD', 'menu/site_meta/user_actions/lost_password'),
            );
        }
        return array();
    }

    /**
     * The UI to ask for the username to get the lost password for.
     *
     * @return Tempcode The UI
     */
    public function step1()
    {
        $fields = new Tempcode();
        require_code('form_templates');

        $set_name = 'account';
        $required = true;
        $set_title = do_lang_tempcode('ACCOUNT');
        $field_set = alternate_fields_set__start($set_name);

        $field_set->attach(form_input_line(do_lang_tempcode('USERNAME'), '', 'username', trim(get_param_string('username', '')), false));
        // form_input_username not used, so as to stop someone accidentally autocompleting to someone else's similar name - very possible for a person already known to be forgetful

        $field_set->attach(form_input_email(do_lang_tempcode('EMAIL_ADDRESS'), '', 'email_address', trim(get_param_string('email_address', '', INPUT_FILTER_GET_COMPLEX)), false));

        $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required));

        $password_reset_process = get_password_reset_process();

        $temporary_passwords = ($password_reset_process != 'emailed');
        $text = do_lang_tempcode('_PASSWORD_RESET_TEXT_' . $password_reset_process);
        $submit_name = do_lang_tempcode('PASSWORD_RESET_BUTTON');
        $post_url = build_url(array('page' => '_SELF', 'type' => 'step2'), '_SELF');

        return do_template('FORM_SCREEN', array(
            '_GUID' => '080e516fef7c928dbb9fb85beb6e435a',
            'SKIP_WEBSTANDARDS' => true,
            'TITLE' => $this->title,
            'HIDDEN' => '',
            'FIELDS' => $fields,
            'TEXT' => $text,
            'SUBMIT_ICON' => 'menu__site_meta__user_actions__lost_password',
            'SUBMIT_NAME' => $submit_name,
            'URL' => $post_url,
        ));
    }

    /**
     * The UI and actualisation for sending out the confirm email.
     *
     * @return Tempcode The UI
     */
    public function step2()
    {
        $username = trim(post_param_string('username', ''));
        $email_address = trim(post_param_string('email_address', ''));

        list($email, $email_address_masked, $member_id) = lost_password_emailer_step($username, $email_address);

        $password_reset_process = get_password_reset_process();

        if ($password_reset_process == 'ultra') {
            // Input UI (as code will be typed immediately, there's no link in the e-mail for 'ultra' mode)
            $zone = get_module_zone('lost_password');
            $_url = build_url(array('page' => 'lost_password', 'type' => 'step3', 'member' => $member_id), $zone);
            require_code('form_templates');
            $fields = new Tempcode();
            $fields->attach(form_input_line(do_lang_tempcode('CODE'), '', 'code', null, true));
            $submit_name = do_lang_tempcode('PROCEED');
            return do_template('FORM_SCREEN', array(
                '_GUID' => '9f03d4abe0140559ec6eba2fa34fe3d6',
                'TITLE' => $this->title,
                'GET' => true,
                'SKIP_WEBSTANDARDS' => true,
                'HIDDEN' => '',
                'URL' => $_url,
                'FIELDS' => $fields,
                'TEXT' => do_lang_tempcode('ENTER_CODE_FROM_EMAIL'),
                'SUBMIT_ICON' => 'menu__site_meta__user_actions__lost_password',
                'SUBMIT_NAME' => $submit_name,
            ));
        }

        return inform_screen($this->title, do_lang_tempcode('RESET_CODE_MAILED', escape_html($email_address_masked), escape_html($email)));
    }

    /**
     * The UI and actualisation for: accepting code if it is correct (and not ''), and setting password to something random, emailing it
     *
     * @return Tempcode The UI
     */
    public function step3()
    {
        $password_reset_process = get_password_reset_process();

        $code = trim(get_param_string('code', ''));
        if ($code == '') {
            require_code('form_templates');
            $fields = new Tempcode();
            $fields->attach(form_input_username(do_lang_tempcode('USERNAME'), '', 'username', null, true));
            $fields->attach(form_input_line(do_lang_tempcode('CODE'), '', 'code', null, true));
            $submit_name = do_lang_tempcode('PROCEED');
            return do_template('FORM_SCREEN', array(
                '_GUID' => '6e4db5c6f3c75faa999251339533d22a',
                'TITLE' => $this->title,
                'GET' => true,
                'SKIP_WEBSTANDARDS' => true,
                'HIDDEN' => '',
                'URL' => get_self_url(false, false, array(), false, true),
                'FIELDS' => $fields,
                'TEXT' => do_lang_tempcode('MISSING_CONFIRM_CODE'),
                'SUBMIT_ICON' => 'buttons_menu__site_meta__user_actions__lost_password_proceed',
                'SUBMIT_NAME' => $submit_name,
            ));
        }
        $username = get_param_string('username', null);
        if ($username !== null) {
            $username = trim($username);
            $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
            if ($member_id === null) {
                warn_exit(do_lang_tempcode('PASSWORD_RESET_ERROR_2'));
            }
        } else {
            $member_id = get_param_integer('member');
        }
        $correct_code = $GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_password_change_code');
        if ($correct_code == '') {
            if (get_member() == $member_id) { // Already reset and already logged in
                $redirect_url = build_url(array('page' => 'members', 'type' => 'view', 'id' => $member_id), get_module_zone('members'), array(), false, false, false, 'tab__edit__settings');
                return redirect_screen($this->title, $redirect_url);
            }

            $_reset_url = build_url(array('page' => '_SELF', 'username' => $GLOBALS['FORUM_DRIVER']->get_username($member_id, false, USERNAME_DEFAULT_BLANK)), '_SELF');
            $reset_url = $_reset_url->evaluate();
            warn_exit(do_lang_tempcode('PASSWORD_ALREADY_RESET', escape_html($reset_url), get_site_name()));
        }
        if ($password_reset_process == 'ultra') {
            list($correct_code, $correct_session) = explode('__', $correct_code);
            if ($correct_session != get_session_id()) {
                warn_exit(do_lang_tempcode('WRONG_RESET_SESSION', escape_html(display_time_period(60 * 60 * intval(get_option('session_expiry_time'))))));
            }
        }
        if ($code != $correct_code) {
            $test = $GLOBALS['SITE_DB']->query_select_value_if_there('actionlogs', 'date_and_time', array('the_type' => 'LOST_PASSWORD', 'param_a' => strval($member_id), 'param_b' => $code));
            if ($test !== null) {
                warn_exit(do_lang_tempcode('INCORRECT_PASSWORD_RESET_CODE')); // Just an old code that has expired
            }
            log_hack_attack_and_exit('HACK_ATTACK_PASSWORD_CHANGE'); // Incorrect code, hack attack
        }

        $email = $GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_email_address');
        $join_time = $GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_join_time');
        $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);

        require_code('crypt');
        $new_password = get_rand_password();

        $temporary_passwords = ($password_reset_process != 'emailed');

        if (!$temporary_passwords) {
            // Send password in mail
            $_login_url = build_url(array('page' => 'login', 'type' => 'browse', 'username' => $GLOBALS['FORUM_DRIVER']->get_username($member_id)), get_module_zone('login'), array(), false, false, true);
            $login_url = $_login_url->evaluate();
            $account_edit_url = build_url(array('page' => 'members', 'type' => 'view'), get_module_zone('members'), array(), false, false, true, 'tab__edit');
            if (get_option('one_per_email_address') != '0') {
                $lang_string = 'MAIL_NEW_PASSWORD_EMAIL_LOGIN';
            } else {
                $lang_string = 'MAIL_NEW_PASSWORD';
            }
            $message = do_lang($lang_string, comcode_escape($new_password), $login_url, array(comcode_escape(get_site_name()), comcode_escape($username), $account_edit_url->evaluate(), comcode_escape($email)));
            require_code('mail');
            dispatch_mail(do_lang('LOST_PASSWORD_FINAL'), $message, array($email), $GLOBALS['FORUM_DRIVER']->get_username($member_id, true), '', '', array('require_recipient_valid_since' => $join_time));
        }

        if ((get_value('no_password_hashing') === '1') && (!$temporary_passwords)) {
            $password_compatibility_scheme = 'plain';
            $new = $new_password;
        } else {
            require_code('crypt');
            $password_compatibility_scheme = ($temporary_passwords ? 'temporary' : '');
            $salt = $GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_pass_salt');
            $new = ratchet_hash($new_password, $salt);
        }

        unset($_GET['code']);
        $GLOBALS['FORUM_DB']->query_update('f_members', array('m_validated_email_confirm_code' => '', 'm_password_compat_scheme' => $password_compatibility_scheme, 'm_password_change_code' => '', 'm_pass_hash_salted' => $new), array('id' => $member_id), '', 1);

        $password_change_days = get_option('password_change_days');
        if (intval($password_change_days) > 0) {
            if ($password_compatibility_scheme == '') {
                require_code('password_rules');
                bump_password_change_date($member_id, $new_password, $new, $salt, true);
            }
        }

        if ($temporary_passwords) { // Log them in, then invite them to change their password
            require_code('users_inactive_occasionals');
            create_session($member_id, 1);

            $redirect_url = build_url(array('page' => 'members', 'type' => 'view', 'id' => $member_id), get_module_zone('members'), array(), false, false, false, 'tab__edit__settings');
            $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
            $GLOBALS['FORCE_META_REFRESH'] = true; // Some browsers can't set cookies and redirect at the same time
            return redirect_screen($this->title, $redirect_url, do_lang_tempcode('YOU_HAVE_TEMPORARY_PASSWORD', escape_html($username)));
        }

        // Email new password
        return inform_screen($this->title, do_lang_tempcode('NEW_PASSWORD_MAILED', escape_html($email), escape_html($new_password)));
    }
}
