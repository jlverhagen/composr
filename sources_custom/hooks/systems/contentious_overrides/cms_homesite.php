<?php /*

Composr
Copyright (c) Christopher Graham, 2004-2024

See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite
 */

/**
 * Hook class.
 */
class Hook_contentious_overrides_cms_homesite
{
    public function compile_included_code($path, $codename, &$code)
    {
        if (!addon_installed('cms_homesite')) {
            return;
        }

        if (strpos($path, 'sources_custom/') !== false) {
            return;
        }

        switch ($codename) {
            // Homesite addon management
            case 'hooks/systems/ajax_tree/choose_download':
                require_code('override_api');
                if ($code === null) {
                    $code = clean_php_file_for_eval(file_get_contents($path), $path);
                }

                insert_code_after__by_command(
                    $code,
                    'run',
                    'require_code(\'global4\');',
                    "
                    if ((!is_numeric(\$id)) && (\$id != '')) {
                        \$_id = null;
                        if (substr(\$id, 0, 8) == 'Version ') {
                            \$id_float = floatval(substr(\$id, 8));
                            do {
                                \$str = 'Version ' . float_to_raw_string(\$id_float, 2, true);
                                \$parent = \$GLOBALS['SITE_DB']->query_select_value_if_there('download_categories', 'id', [\$GLOBALS['SITE_DB']->translate_field_ref('category') => 'Addons']);
                                if (\$parent === null) {
                                    break;
                                }
                                \$_id = \$GLOBALS['SITE_DB']->query_select_value_if_there('download_categories', 'id', ['parent_id' => \$parent, \$GLOBALS['SITE_DB']->translate_field_ref('category') => \$str]);
                                if (\$_id === null) {
                                    \$id_float -= 0.1;
                                }
                            } while ((\$_id === null) && (\$id_float > 0.0));
                        } else {
                            \$_id = \$GLOBALS['SITE_DB']->query_select_value_if_there('download_categories', 'id', [\$GLOBALS['SITE_DB']->translate_field_ref('category') => \$id]);
                        }
                        if (\$_id === null) {
                            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'download_category'));
                        }
                        \$id = strval(\$_id);
                    }"
                );
                break;
        }
    }

    public function require_lang_compile(array &$load_target, string $codename, string $lang)
    {
        if (get_base_url() != get_brand_base_url()) {
            return;
        }

        if (!addon_installed('cms_homesite')) {
            return;
        }

        if ($lang != 'EN') {
            return;
        }

        if ($codename == 'ecommerce') {
            $load_target['ECOM_PRODUCTS_INTRO_POINTS_ONLY'] = '<p>You can purchase products using your points (you currently have {2}).</p><p>You earn points by contributing to our community in some way. That might be through posting on our forums, receiving points from other members, or submitting something to the site. Explore enough and do enough, and you will notice your point count rising. Our aim is to encourage participation in the community by rewarding our active members.</p><p>On the homesite, you can also buy points by donating to any partners (Community > Partners) who are asking for funds in exchange for points (typically, you receive 100 points for every $1 USD, excluding transaction fees; these points do not count towards rank points or voting power).</p><p>The products that may be purchased are listed below&hellip;</p>';
            $load_target['ECOM_PRODUCTS_INTRO_BOTH'] = '<p>You can purchase products via money or from your points (you currently have {2}).</p><p>You earn points by contributing to our community in some way. That might be through posting on our forums, receiving points from other members, or submitting something to the site. Explore enough and do enough, and you will notice your point count rising. Our aim is to encourage participation in the community by rewarding our active members.</p><p>On the homesite, you can also buy points by donating to any partners (Community > Partners) who are asking for funds in exchange for points (typically, you receive 100 points for every $1 USD, excluding transaction fees; these points do not count towards rank points or voting power).</p><p>The products that may be purchased are listed below&hellip;</p>';
        }
    }
}
