<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_symbol_SYMBOL_DATA_AS_JSON
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        require_code('global2');
        require_code('symbols');
        require_code('symbols2');

        $lang = user_lang();
        $value = array(
            'PAGE'                => ecv_PAGE($lang, [], []),
            'PAGE_TITLE'          => ecv_PAGE_TITLE($lang, [], []),
            'ZONE'                => ecv_ZONE($lang, [], []),
            'MEMBER'              => ecv_MEMBER($lang, [], []),
            'IS_GUEST'            => ecv_IS_GUEST($lang, [], []),
            'USERNAME'            => ecv_USERNAME($lang, [], []),
            'AVATAR'              => ecv_AVATAR($lang, [], []),
            'MEMBER_EMAIL'        => ecv_MEMBER_EMAIL($lang, [], []),
            'PHOTO'               => ecv_PHOTO($lang, [], []),
            'MEMBER_PROFILE_URL'  => ecv_MEMBER_PROFILE_URL($lang, [], []),
            'DATE_AND_TIME'       => ecv_DATE_TIME($lang, [], []),
            'DATE'                => ecv_DATE($lang, [], []),
            'TIME'                => ecv_TIME($lang, [], []),
            'FROM_TIMESTAMP'      => ecv_FROM_TIMESTAMP($lang, [], []),
            'HIDE_HELP_PANEL'     => ecv_HIDE_HELP_PANEL($lang, [], []),
            'MOBILE'              => ecv2_MOBILE($lang, [], []),
            'THEME'               => ecv2_THEME($lang, [], []),
            'JS_ON'               => ecv_JS_ON($lang, [], []),
            'LANG'                => ecv2_LANG($lang, [], []),
            'BROWSER_UA'          => ecv2_BROWSER_UA($lang, [], []),
            'OS'                  => ecv2_OS($lang, [], []),
            'DEV_MODE'            => ecv_DEV_MODE($lang, [], []),
            'USER_AGENT'          => ecv2_USER_AGENT($lang, [], []),
            'IP_ADDRESS'          => ecv2_IP_ADDRESS($lang, [], []),
            'TIMEZONE'            => ecv2_TIMEZONE($lang, [], []),
            'HTTP_STATUS_CODE'    => ecv2_HTTP_STATUS_CODE($lang, [], []),
            'CHARSET'             => ecv2_CHARSET($lang, [], []),
            'KEEP'                => ecv_KEEP($lang, [], []),
            'FORCE_PREVIEWS'      => ecv_FORCE_PREVIEWS($lang, [], []),
            'PREVIEW_URL'         => ecv_PREVIEW_URL($lang, [], []),
            'SITE_NAME'           => ecv2_SITE_NAME($lang, [], []),
            'COPYRIGHT'           => ecv2_COPYRIGHT($lang, [], []),
            'DOMAIN'              => ecv2_DOMAIN($lang, [], []),
            'FORUM_BASE_URL'      => ecv2_FORUM_BASE_URL($lang, [], []),
            'BASE_URL'            => ecv2_BASE_URL($lang, [], []),
            'CUSTOM_BASE_URL'     => ecv2_CUSTOM_BASE_URL($lang, [], []),
            'BASE_URL_NOHTTP'     => ecv2_BASE_URL_NOHTTP($lang, [], []),
            'CUSTOM_BASE_URL_NOHTTP' => ecv2_CUSTOM_BASE_URL_NOHTTP($lang, [], []),
            'BRAND_NAME'          => ecv2_BRAND_NAME($lang, [], []),
            'IS_STAFF'            => ecv_IS_STAFF($lang, [], []),
            'IS_ADMIN'            => ecv_IS_ADMIN($lang, [], []),
            'VERSION'             => ecv2_VERSION($lang, [], []),
            'COOKIE_PATH'         => ecv2_COOKIE_PATH($lang, [], []),
            'COOKIE_DOMAIN'       => ecv2_COOKIE_DOMAIN($lang, [], []),
            'IS_HTTPAUTH_LOGIN'   => ecv_IS_HTTPAUTH_LOGIN($lang, [], []),
            'IS_A_COOKIE_LOGIN'   => ecv2_IS_A_COOKIE_LOGIN($lang, [], []),
            'SESSION_COOKIE_NAME' => ecv2_SESSION_COOKIE_NAME($lang, [], []),
            'GROUP_ID'            => ecv2_GROUP_ID($lang, [], []),
            'INLINE_STATS'        => ecv2_INLINE_STATS($lang, [], []),
            'RUNNING_SCRIPT'      => current_script(),
            'CSP_NONCE'           => ecv2_CSP_NONCE($lang, [], []),
        );

        require_code('config');
        $value['CONFIG_OPTION'] = [
            'thumb_width'        => get_option('thumb_width'),
            'js_overlays'        => get_option('js_overlays'),
            'js_captcha'         => get_option('js_captcha'),
            'google_analytics'   => get_option('google_analytics'),
            'long_google_cookies' => get_option('long_google_cookies'),
            'editarea'          => get_option('editarea'),
            'enable_animations'  => get_option('enable_animations'),
            'detect_javascript'  => get_option('detect_javascript'),
            'is_on_timezone_detection' => get_option('is_on_timezone_detection'),
            'fixed_width'        => get_option('fixed_width'),
            'infinite_scrolling' => get_option('infinite_scrolling'),
            'wysiwyg'           => get_option('wysiwyg'),
            'eager_wysiwyg'      => get_option('eager_wysiwyg'),
            'simplified_attachments_ui'   => get_option('simplified_attachments_ui'),
            'show_inline_stats'           => get_option('show_inline_stats'),
            'notification_desktop_alerts' => get_option('notification_desktop_alerts'),
            'enable_theme_img_buttons' => get_option('enable_theme_img_buttons'),
            'enable_previews' => get_option('enable_previews'),
            'background_template_compilation' => get_option('background_template_compilation'),
            'complex_uploader' => get_option('complex_uploader'),
            'collapse_user_zones' => get_option('collapse_user_zones'),
            'sitewide_im' => get_option('sitewide_im'),
            'topic_pin_max_days' => get_option('topic_pin_max_days'),
            'cookie_notice' => get_option('cookie_notice'),
        ];

        $value['VALUE_OPTION'] = [
            'js_keep_params' => get_value('js_keep_params'),
            'commercial_spellchecker' => get_value('commercial_spellchecker'),
        ];

        $value['HAS_PRIVILEGE'] = [
            'sees_javascript_error_alerts' =>  has_privilege(get_member(), 'sees_javascript_error_alerts'),
        ];

        require_code('urls');
        $value['can_try_url_schemes'] = can_try_url_schemes();
        $value['staff_tooltips_url_patterns'] = $this->staff_tooltips_url_patterns($value['IS_STAFF'] === '1');

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * @param boolean $is_staff If the current user is a staff member
     * @return array
     */
    private function staff_tooltips_url_patterns($is_staff) {
        $url_patterns = [];
        if (!$is_staff) {
            return $url_patterns;
        }

        require_code('content');
        $cma_hooks = find_all_hooks('systems', 'content_meta_aware');
        foreach (array_keys($cma_hooks) as $content_type) {
            $content_type_ob = get_content_object($content_type);

            if (!isset($content_type_ob)) {
                continue;
            }

            $info = $content_type_ob->info();
            if (isset($info['view_page_link_pattern'])) {
                list($zone, $attributes,) = page_link_decode($info['view_page_link_pattern']);
                $url = build_url($attributes, $zone, null, false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
            if (isset($info['edit_page_link_pattern'])) {
                list($zone, $attributes,) = page_link_decode($info['edit_page_link_pattern']);
                $url = build_url($attributes, $zone, null, false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
        }

        return $url_patterns;
    }

    /**
     * @param string $pattern Pattern
     * @return string
     */
    public function _escape_url_pattern_for_js_regex($pattern) {
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = str_replace('?', '\\?', $pattern);
        $pattern = str_replace('_WILD\\/', '([^&]*)\\/?', $pattern);
        $pattern = str_replace('_WILD', '([^&]*)', $pattern);

        return '^' . $pattern;
    }
}
