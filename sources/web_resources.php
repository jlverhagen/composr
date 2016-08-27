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
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__web_resources()
{
    global $EARLY_SCRIPT_ORDER;
    $EARLY_SCRIPT_ORDER = array('underscore', 'jquery', 'backbone', 'modernizr', 'composr');
}

/**
 * Make sure that the given javascript file is loaded up.
 *
 * @sets_output_state
 *
 * @param  ID_TEXT $javascript The javascript file required
 */
function require_javascript($javascript)
{
    global $JAVASCRIPTS, $SMART_CACHE, $JS_OUTPUT_STARTED_LIST;

    if (empty($javascript)) {
        return;
    }

    $JAVASCRIPTS[$javascript] = true;

    if (array_key_exists($javascript, $JS_OUTPUT_STARTED_LIST)) {
        return;
    }

    $JS_OUTPUT_STARTED_LIST[$javascript] = true;

    if (strpos($javascript, 'merged__') === false) {
        $SMART_CACHE->append('JAVASCRIPTS', $javascript);
    }
}

/**
 * Force a JavaScript file to be cached (ordinarily we can rely on this to be automated by require_javascript/javascript_tempcode).
 *
 * @param  string $j The javascript file required
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @return string The path to the javascript file in the cache (blank: no file)
 */
function javascript_enforce($j, $theme = null)
{
    if (get_param_integer('keep_textonly', 0) == 1) {
        return '';
    }

    list($minify, $https, $mobile) = _get_web_resources_env();

    global $SITE_INFO;

    // Make sure the JavaScript exists
    if ($theme === null) {
        $theme = @method_exists($GLOBALS['FORUM_DRIVER'], 'get_theme') ? $GLOBALS['FORUM_DRIVER']->get_theme() : 'default';
    }
    $dir = get_custom_file_base() . '/themes/' . $theme . '/templates_cached/' . filter_naughty(user_lang());
    if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] != '1')) {
        if (!is_dir($dir)) {
            require_code('files2');
            make_missing_directory($dir);
        }
    }
    $js_cache_path = $dir . '/' . filter_naughty($j);
    if (!$minify) {
        $js_cache_path .= '_non_minified';
    }
    if ($https) {
        $js_cache_path .= '_ssl';
    }
    if ($mobile) {
        $js_cache_path .= '_mobile';
    }
    $js_cache_path .= '.js';

    global $CACHE_TEMPLATES;
    $support_smart_decaching = support_smart_decaching();
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(true);
    }
    $is_cached = (is_file($js_cache_path)) && ($CACHE_TEMPLATES || !running_script('index')/*must cache for non-index to stop getting blanked out in depended sub-script output generation and hence causing concurrency issues*/) && (!is_browser_decaching()) && ((!in_safe_mode()) || (isset($GLOBALS['SITE_INFO']['safe_mode'])));
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(false);
    }

    if (($support_smart_decaching) || (!$is_cached)) {
        $found = find_template_place($j, '', $theme, '.js', 'javascript');
        if ($found === null) {
            return '';
        }
        $theme = $found[0];
        $full_path = get_custom_file_base() . '/themes/' . $theme . $found[1] . $j . $found[2];
        if (!is_file($full_path)) {
            $full_path = get_file_base() . '/themes/' . $theme . $found[1] . $j . $found[2];
        }
    }

    if ((($support_smart_decaching) && ((@(filemtime($js_cache_path) < filemtime($full_path)) && (@filemtime($full_path) < time())) || ((!empty($SITE_INFO['dependency__' . $full_path])) && (!dependencies_are_good(explode(',', $SITE_INFO['dependency__' . $full_path]), filemtime($js_cache_path)))) || (@filemtime(get_file_base() . '/_config.php') > @filemtime($js_cache_path)))) || (!$is_cached)) {
        if (@filesize($full_path) == 0) {
            return '';
        }

        require_code('css_and_js');
        js_compile($j, $js_cache_path, $minify);
    }

    if (@intval(filesize($js_cache_path)) == 0/*@ for race condition*/) {
        return '';
    }

    return $js_cache_path;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) all the JavaScript files that have been required.
 *
 * @param  ?string $position Position to get JavaScript for (null: all positions)
 * @set null header footer
 * @return Tempcode The Tempcode to tie in the JavaScript files
 */
function javascript_tempcode()
{
    global $JAVASCRIPTS, $JAVASCRIPT, $JS_OUTPUT_STARTED, $EARLY_SCRIPT_ORDER;

    $JS_OUTPUT_STARTED = true;

    $js = new Tempcode();

    list($minify, $https, $mobile) = _get_web_resources_env();

    // Fix order, so our main JavaScript, and jQuery, runs first
    if (isset($JAVASCRIPTS['global'])) {
        $arr_backup = $JAVASCRIPTS;
        $JAVASCRIPTS = array();

        foreach ($EARLY_SCRIPT_ORDER as $important_script) {
            if (isset($arr_backup[$important_script])) {
                $JAVASCRIPTS[$important_script] = true;
            }
        }

        $JAVASCRIPTS['global'] = true;
        $JAVASCRIPTS += $arr_backup;
    }

    $javascripts_to_do = $JAVASCRIPTS;
    foreach ($javascripts_to_do as $j => $do_enforce) {
        if ($do_enforce === null) {
            continue; // Has already been included in a merger
        }

        _javascript_tempcode($j, $js, $minify, $https, $mobile, $do_enforce);
    }

    if ($JAVASCRIPT !== null) {
        $js->attach($JAVASCRIPT);
    }

    return $js;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) for an individual CSS file.
 *
 * @param  ID_TEXT $j The javascript file required
 * @param  Tempcode $js Tempcode object (will be written into if appropriate)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @param  ?boolean $do_enforce Whether to generate the cached file if not already cached (null: from what is cached)
 * @ignore
 */
function _javascript_tempcode($j, &$js, $_minify = null, $_https = null, $_mobile = null, $do_enforce = true)
{
    list($minify, $https, $mobile) = _get_web_resources_env(null, $_minify, $_https, $_mobile);

    $temp = $do_enforce ? javascript_enforce($j) : '';
    if (($temp != '') || (!$do_enforce)) {
        if (!$minify) {
            $j .= '_non_minified';
        }
        if ($https) {
            $j .= '_ssl';
        }
        if ($mobile) {
            $j .= '_mobile';
        }

        $support_smart_decaching = support_smart_decaching();
        $sup = ($support_smart_decaching && $temp != '' && !$GLOBALS['RECORD_TEMPLATES_USED']) ? strval(filemtime($temp)) : null; // Tweaks caching so that upgrades work without needing emptying browser cache; only runs if smart decaching is on because otherwise we won't have the mtime and don't want to introduce an extra filesystem hit

        $js->attach(do_template('JAVASCRIPT_NEED', array('_GUID' => 'b5886d9dfc4d528b7e1b0cd6f0eb1670', 'CODE' => $j, 'SUP' => $sup)));
    }
}

/**
 * Make sure that the given CSS file is loaded up.
 *
 * @sets_output_state
 *
 * @param  ID_TEXT $css The CSS file required
 */
function require_css($css)
{
    global $CSSS, $SMART_CACHE, $CSS_OUTPUT_STARTED_LIST, $CSS_OUTPUT_STARTED;

    if (empty($css)) {
        return;
    }

    $CSSS[$css] = true;

    if (array_key_exists($css, $CSS_OUTPUT_STARTED_LIST)) {
        return;
    }

    $CSS_OUTPUT_STARTED_LIST[$css] = true;

    if (strpos($css, 'merged__') === false) {
        $SMART_CACHE->append('CSSS', $css);
    }

    // Has to move into footer
    if ($CSS_OUTPUT_STARTED) {
        $value = new Tempcode();
        _css_tempcode($css, $value, $value);
        attach_to_screen_footer($value);
    }
}

/**
 * Force a CSS file to be cached.
 *
 * @param  string $c The CSS file required
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @return string The path to the CSS file in the cache (blank: no file)
 */
function css_enforce($c, $theme = null)
{
    $text_only = (get_param_integer('keep_textonly', 0) == 1);
    if ($text_only) {
        $c .= '_textonly';
    }

    list($minify, $https, $mobile) = _get_web_resources_env();

    global $SITE_INFO;

    // Make sure the CSS file exists
    if ($theme === null) {
        $theme = @method_exists($GLOBALS['FORUM_DRIVER'], 'get_theme') ? $GLOBALS['FORUM_DRIVER']->get_theme() : 'default';
    }
    $active_theme = $theme;
    $dir = get_custom_file_base() . '/themes/' . $theme . '/templates_cached/' . filter_naughty(user_lang());
    if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] != '1')) {
        if (!is_dir($dir)) {
            require_code('files2');
            make_missing_directory($dir);
        }
    }
    $css_cache_path = $dir . '/' . filter_naughty($c);
    if (!$minify) {
        $css_cache_path .= '_non_minified';
    }
    if ($https) {
        $css_cache_path .= '_ssl';
    }
    if ($mobile) {
        $css_cache_path .= '_mobile';
    }
    $css_cache_path .= '.css';

    global $CACHE_TEMPLATES;
    $support_smart_decaching = support_smart_decaching();
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(true);
    }
    $is_cached = (is_file($css_cache_path)) && ($CACHE_TEMPLATES || !running_script('index')/*must cache for non-index to stop getting blanked out in depended sub-script output generation and hence causing concurrency issues*/) && (!is_browser_decaching()) && ((!in_safe_mode()) || (isset($GLOBALS['SITE_INFO']['safe_mode'])));
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(false);
    }

    if (($support_smart_decaching) || (!$is_cached) || ($text_only)) {
        $found = find_template_place($c, '', $theme, '.css', 'css');
        if ($found === null) {
            return '';
        }
        $theme = $found[0];
        $full_path = get_custom_file_base() . '/themes/' . $theme . $found[1] . $c . $found[2];
        if (!is_file($full_path)) {
            $full_path = get_file_base() . '/themes/' . $theme . $found[1] . $c . $found[2];
        }
        if (($text_only) && (!is_file($full_path))) {
            return '';
        }
    }

    if (((!$is_cached) || (($support_smart_decaching) && ((@(filemtime($css_cache_path) < filemtime($full_path)) && (@filemtime($full_path) < time()) || ((!empty($SITE_INFO['dependency__' . $full_path])) && (!dependencies_are_good(explode(',', $SITE_INFO['dependency__' . $full_path]), filemtime($css_cache_path))))))))) {
        if (@filesize($full_path) == 0) {
            return '';
        }

        require_code('css_and_js');
        css_compile($active_theme, $theme, $c, $full_path, $css_cache_path, $minify);
    }

    if (@intval(filesize($css_cache_path)) == 0/*@ for race condition*/) {
        return '';
    }

    return $css_cache_path;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) all the CSS files that have been required.
 *
 * @param  boolean $inline Force inline CSS
 * @param  boolean $only_global Only do global CSS
 * @param  ?string $context HTML context for which we filter (minimise) any CSS we spit out as inline (null: none)
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @return Tempcode The Tempcode to tie in the CSS files
 */
function css_tempcode($inline = false, $only_global = false, $context = null, $theme = null)
{
    global $CSSS, $CSS_OUTPUT_STARTED;

    $CSS_OUTPUT_STARTED = true;

    list($minify, $https, $mobile, $seed) = _get_web_resources_env();

    if (!$only_global) {
        _handle_web_resource_merging('.css', $CSSS, $minify, $https, $mobile);
    }

    $css = new Tempcode();
    $css_need_inline = new Tempcode();
    if ($only_global) {
        $css_to_do = array('global' => true, 'no_cache' => true);
        if (isset($CSSS['email'])) {
            $css_to_do['email'] = true;
        }
    } else {
        $css_to_do = $CSSS;
    }
    foreach ($css_to_do as $c => $do_enforce) {
        if ($do_enforce === null) {
            continue; // Has already been included in a merger
        }

        if (is_integer($c)) {
            $c = strval($c);
        }

        _css_tempcode($c, $css, $css_need_inline, $inline, $context, $theme, $seed, null, null, null, null, $do_enforce);
    }
    $css_need_inline->attach($css);
    return $css_need_inline;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) for an individual CSS file.
 *
 * @param  ID_TEXT $c The CSS file required
 * @param  Tempcode $css Main Tempcode object (will be written into if appropriate)
 * @param  Tempcode $css_need_inline Inline Tempcode object (will be written into if appropriate)
 * @param  boolean $inline Only do global CSS
 * @param  ?string $context HTML context for which we filter (minimise) any CSS we spit out as inline (null: none)
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme) (null: from what is cached)
 * @param  ?ID_TEXT $_seed The seed colour (null: previous cached) (blank: none) (null: from what is cached)
 * @param  ?boolean $_text_only Whether operating in text-only mode (null: from what is cached)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @param  boolean $do_enforce Whether to generate the cached file if not already cached
 *
 * @ignore
 */
function _css_tempcode($c, &$css, &$css_need_inline, $inline = false, $context = null, $theme = null, $_seed = null, $_text_only = null, $_minify = null, $_https = null, $_mobile = null, $do_enforce = true)
{
    static $text_only = null;
    if ($_text_only !== null) {
        $text_only = $_text_only;
    } elseif ($text_only === null) {
        $text_only = (get_param_integer('keep_textonly', 0) == 1);
    }

    list($minify, $https, $mobile, $seed) = _get_web_resources_env($_seed, $_minify, $_https, $_mobile);

    if ($seed != '') {
        $keep = symbol_tempcode('KEEP');
        $css->attach(do_template('CSS_NEED_FULL', array('_GUID' => 'f2d7f0303a08b9aa9e92f8b0208ee9a7', 'URL' => find_script('themewizard') . '?type=css&show=' . urlencode($c) . '.css' . $keep->evaluate()), user_lang(), false, null, '.tpl', 'templates', $theme));
    } elseif (($c == 'no_cache') || ($inline)) {
        if (!$text_only) {
            if ($context !== null) {
                require_code('mail');
                $__css = filter_css($c, $theme, $context);
            } else {
                $_css = do_template($c, null, user_lang(), false, null, '.css', 'css', $theme);
                $__css = $_css->evaluate();
                $__css = str_replace('} ', '}' . "\n", preg_replace('#\s+#', ' ', $__css));
            }

            if (trim($__css) != '') {
                $css_need_inline->attach(do_template('CSS_NEED_INLINE', array('_GUID' => 'f5b225e080c633ffa033ec5af5aec866', 'CODE' => $__css), user_lang(), false, null, '.tpl', 'templates', $theme));
            }
        }
    } else {
        $temp = $do_enforce ? css_enforce($c, $theme) : '';

        if (!$minify) {
            $c .= '_non_minified';
        }
        if ($https) {
            $c .= '_ssl';
        }
        if ($mobile) {
            $c .= '_mobile';
        }
        if (($temp != '') || (!$do_enforce)) {
            $support_smart_decaching = support_smart_decaching();
            $sup = ($support_smart_decaching && $temp != '') ? strval(filemtime($temp)) : null; // Tweaks caching so that upgrades work without needing emptying browser cache; only runs if smart decaching is on because otherwise we won't have the mtime and don't want to introduce an extra filesystem hit
            $css->attach(do_template('CSS_NEED', array('_GUID' => 'ed35fac857214000f69a1551cd483096', 'CODE' => $c, 'SUP' => $sup), user_lang(), false, null, '.tpl', 'templates', $theme));
        }
    }
}

/**
 * Get the environment needed for web resources.
 *
 * @param  ?ID_TEXT $_seed The seed colour (blank: none) (null: from what is cached)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @return array A tuple: whether we are minify, if HTTPS is on, if mobile mode is on, seed
 *
 * @ignore
 */
function _get_web_resources_env($_seed = null, $_minify = null, $_https = null, $_mobile = null)
{
    static $seed_cached = null;
    if ($_seed !== null) {
        $seed = $_seed;
    } elseif ($seed_cached === null || running_script('preview'/*may change seed in script code*/)) {
        if (function_exists('has_privilege') && has_privilege(get_member(), 'view_profiling_modes')) {
            $seed = get_param_string('keep_theme_seed', '');
        } else {
            $seed = '';
        }
        $seed_cached = $seed;
    } else {
        $seed = $seed_cached;
    }

    static $minify_cached = null;
    if ($_minify !== null) {
        $minify = $_minify;
    } elseif ($minify_cached === null || $seed != '') {
        if ($seed == '') {
            $minify = (get_param_integer('keep_no_minify', 0) == 0);
            $minify_cached = $minify;
        } else {
            $minify = false;
        }
    } else {
        $minify = $minify_cached;
    }

    static $https_cached = null;
    if ($_https !== null) {
        $https = $_https;
    } elseif ($https_cached === null) {
        $https = ((addon_installed('ssl')) && function_exists('is_page_https') && function_exists('get_zone_name') && ((tacit_https()) || is_page_https(get_zone_name(), get_page_name())));
        $https_cached = $https;
    } else {
        $https = $https_cached;
    }

    static $mobile_cached = null;
    if ($_mobile !== null) {
        $mobile = $_mobile;
    } elseif ($mobile_cached === null) {
        $mobile = is_mobile();
        $mobile_cached = $mobile;
    } else {
        $mobile = $mobile_cached;
    }

	return array($minify, $https, $mobile, $seed);
}

/**
 * Get web resource grouping codename for a particular context.
 *
 * @param  ?ID_TEXT $zone_name Current zone name (null: autodetect)
 * @param  ?boolean $is_admin Is for admin (null: autodetect)
 * @return ID_TEXT Grouping codename
 * @ignore
 */
function _get_web_resource_grouping_codename($zone_name = null, $is_admin = null)
{
    if ($is_admin === null) {
        $is_admin = $GLOBALS['FORUM_DRIVER']->is_super_admin(get_member());
    }
    if ($zone_name === null) {
        $zone_name = get_zone_name();
    }

    $grouping_codename = 'merged__';
    $grouping_codename .= $zone_name;
    if ($is_admin) {
        $grouping_codename .= '__admin';
    }
    return $grouping_codename;
}

/**
 * Handle web resource merging optimisation, for merging groups of CSS/JavaScript files that are used across the site, to reduce request quantity.
 *
 * @param  ID_TEXT $type Resource type
 * @set .css .js
 * @param  array $arr Resources (map of keys to true), passed by reference as we alter it
 * @param  boolean $minify If we are minifying
 * @param  boolean $https If we are using HTTPs
 * @param  boolean $mobile If we are using mobile
 * @return ?ID_TEXT Resource name for merged file, which we assume is compiled (as this function makes it) (null: we don't know what is required / race condition)
 * @ignore
 */
function _handle_web_resource_merging($type, &$arr, $minify, $https, $mobile)
{
    return null; // We find all kinds of complex conditions happen, leading to difficult bugs. Better to just not have this, and HTTP/2 improves things anyway.
    /*
    if (!$minify || !running_script('index')) {
        return null; // Optimisation disabled if no minification. Turn off minificiation when debugging JavaScript/CSS, as smart caching won't work with the merge system.
    }

    if ($type == '.js') {
        // Fix order, so our main JavaScript, and jQuery, goes first in the merge order
        $_arr = $arr;
        $arr = array('global' => true);
        global $EARLY_SCRIPT_ORDER;
        foreach ($EARLY_SCRIPT_ORDER as $important_script) {
            if (isset($_arr[$important_script])) {
                $arr[$important_script] = true;
            }
        }
        $arr += $_arr;
    }

    $is_admin = $GLOBALS['FORUM_DRIVER']->is_super_admin(get_member());
    $zone_name = get_zone_name();

    $grouping_codename_welcome = _get_web_resource_grouping_codename('', $is_admin);

    $grouping_codename = _get_web_resource_grouping_codename($zone_name, $is_admin);

    $value = get_value_newer_than($grouping_codename . $type, time() - 60 * 60 * 24);

    if ($zone_name != '') {
        $welcome_value = get_value_newer_than($grouping_codename_welcome . $type, time() - 60 * 60 * 24);
        if ($welcome_value === null) {
            return null; // Don't do this if we haven't got for welcome zone yet (we try and make all same as welcome zone if possible - so we need it to compare against)
        }
    } else {
        $welcome_value = $value;
    }

    // If not set yet, work out what merge situation would be and save it
    if (($value === null) || (strpos($value, '::') === false)) {
        $value = mixed();

        $is_guest = is_guest();

        // If is zone front page
        if (get_zone_default_page($zone_name) == get_page_name()) {
            // If in guest group or admin group
            if (($is_guest) || ($is_admin)) {
                $resources = array_keys($arr);
                $value = implode(',', $resources) . '::???';
            }
        }
    }

    // If set, ensure merged resources file exists, and apply it
    if ($value !== null) {
        if ($welcome_value == $value) { // Optimisation, if same as welcome zone, use that -- so user does not need to download multiple identical merged resources
            $grouping_codename = $grouping_codename_welcome;
        }

        $_value = explode('::', $value);
        $resources = ($_value[0] == '') ? array() : explode(',', $_value[0]);
        $hash = $_value[1];

        // Regenerate hash if we support smart decaching, it might have changed and hence we need to do recompiling with a new hash OR this may be the first time ("???" is placeholder)
        $support_smart_decaching = support_smart_decaching();
        if (($support_smart_decaching) || ($hash == '???')) {
            // Work out a hash (checksum) for cache busting on this merged file. Does it using an mtime has chain for performance (better than reading and hashing all the file contents)
            $old_hash = $hash;
            $hash = '';
            foreach ($resources as $resource) {
                if ($resource == 'no_cache') {
                    continue;
                }

                if ($type == '.js') {
                    $merge_from = javascript_enforce($resource);
                } else { // .css
                    $merge_from = css_enforce($resource);
                }
                if ($merge_from != '') {
                    $hash = substr(md5($hash . @strval(filemtime($merge_from))), 0, 5);
                }
            }
            if ($hash != $old_hash) {
                $value = implode(',', $resources) . '::' . $hash;
                set_value($grouping_codename . $type, $value);
            }
        }

        // Find merged file path
        $theme = filter_naughty($GLOBALS['FORUM_DRIVER']->get_theme());
        $dir = get_custom_file_base() . '/themes/' . $theme . '/templates_cached/' . filter_naughty(user_lang());
        $grouping_codename .= '_' . $hash; // Add cache buster component
        $file = $grouping_codename;
        if (!$minify) {
            $file .= '_non_minified';
        }
        if ($https) {
            $file .= '_ssl';
        }
        if ($mobile) {
            $file .= '_mobile';
        }
        $write_path = $dir . '/' . filter_naughty($file);
        $write_path .= $type;

        if (GOOGLE_APPENGINE) {
            gae_optimistic_cache(true);
        }
        $already_exists = is_file($write_path);
        if (GOOGLE_APPENGINE) {
            gae_optimistic_cache(false);
        }
        if (!$already_exists) {
            require_code('global4');
            $good_to_go = _save_web_resource_merging($resources, $type, $write_path);
        } else {
            $good_to_go = true;
        }

        if ($good_to_go) {
            $arr_cnt = count($arr);

            foreach ($resources as $resource) {
                if ($resource == 'no_cache') {
                    continue;
                }

                // Know we don't load up if unit already individually requested
                $arr_cnt--;
                $arr[$resource] = null;
            }

            if (($arr_cnt == 0) && (running_script('snippet'))) {
                return null; // No need to load up merged, as we already have the merged one loaded; but we did successfully also skip loading was that were included in that merge
            }

            if ($resources !== array()) { // Some stuff was merged
                $tmp = $arr;
                $arr = array();
                $arr[$grouping_codename] = false; // Add in merge one to load instead (first)
                $arr += $tmp;
            }

            return $grouping_codename;
        }
    }

    return null;*/
}

/**
 * Add some Comcode that does resource-inclusion for CSS and Javascript files that are currently loaded.
 *
 * @param  string $message_raw Comcode
 */
function inject_web_resources_context_to_comcode(&$message_raw)
{
    global $CSSS, $JAVASCRIPTS;

    $_css_comcode = '';
    foreach (array_keys($CSSS) as $i => $css) {
        if ($css == 'global' || $css == 'no_cache') {
            continue;
        }

        if ($_css_comcode != '') {
            $_css_comcode .= ',';
        }
        $_css_comcode .= $css;
    }
    if ($_css_comcode == '') {
        $css_comcode = '';
    } else {
        $css_comcode = '[require_css]' . $_css_comcode . '[/require_css]';
    }

    $_javascript_comcode = '';
    foreach (array_keys($JAVASCRIPTS) as $i => $javascript) {
        if ($javascript == 'global' || $javascript == 'custom_globals') {
            continue;
        }

        if ($_javascript_comcode != '') {
            $_javascript_comcode .= ',';
        }
        $_javascript_comcode .= $javascript;
    }
    if ($_javascript_comcode == '') {
        $javascript_comcode = '';
    } else {
        $javascript_comcode = '[require_javascript]' . $_javascript_comcode . '[/require_javascript]';
    }

    $message_raw = $css_comcode . $javascript_comcode . $message_raw;
}
