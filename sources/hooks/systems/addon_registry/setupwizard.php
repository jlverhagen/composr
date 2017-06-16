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
 * @package    setupwizard
 */

/**
 * Hook class.
 */
class Hook_addon_registry_setupwizard
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Quick-start setup wizard.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_configuration',
            'tut_drinking',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
            'previously_in_addon' => array('core_setupwizard'),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/setup/setupwizard.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/setup/setupwizard.png',
            'themes/default/images/icons/48x48/menu/adminzone/setup/setupwizard.png',
            'sources/hooks/modules/admin_setupwizard_installprofiles/.htaccess',
            'sources_custom/hooks/modules/admin_setupwizard_installprofiles/.htaccess',
            'sources/hooks/modules/admin_setupwizard_installprofiles/index.html',
            'sources_custom/hooks/modules/admin_setupwizard_installprofiles/index.html',
            'sources/hooks/modules/admin_setupwizard_installprofiles/community.php',
            'sources/hooks/modules/admin_setupwizard_installprofiles/infosite.php',
            'themes/default/templates/SETUPWIZARD_SCREEN.tpl',
            'themes/default/templates/SETUPWIZARD_2.tpl',
            'themes/default/templates/SETUPWIZARD_7.tpl',
            'themes/default/templates/SETUPWIZARD_BLOCK_PREVIEW.tpl',
            'sources/hooks/systems/addon_registry/setupwizard.php',
            'sources/setupwizard.php',
            'sources/hooks/systems/preview/setupwizard.php',
            'sources/hooks/systems/preview/setupwizard_blocks.php',
            'adminzone/pages/modules/admin_setupwizard.php',
            'sources/hooks/modules/admin_setupwizard/.htaccess',
            'sources_custom/hooks/modules/admin_setupwizard/.htaccess',
            'sources/hooks/modules/admin_setupwizard/index.html',
            'sources_custom/hooks/modules/admin_setupwizard/index.html',
            'sources/hooks/systems/page_groupings/setupwizard.php',
            'sources/hooks/modules/admin_setupwizard/core.php',
            'sources/hooks/modules/admin_setupwizard_installprofiles/minimalistic.php',
            'themes/default/css/setupwizard.css',
            'themes/default/javascript/setupwizard.js',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/SETUPWIZARD_SCREEN.tpl' => 'administrative__setupwizard_2_screen',
            'templates/SETUPWIZARD_2.tpl' => 'administrative__setupwizard_2_screen',
            'templates/SETUPWIZARD_7.tpl' => 'administrative__setupwizard_7_screen',
            'templates/SETUPWIZARD_BLOCK_PREVIEW.tpl' => 'administrative__setupwizard_block_preview',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__setupwizard_block_preview()
    {
        return array(
            lorem_globalise(do_lorem_template('SETUPWIZARD_BLOCK_PREVIEW', array(
                'LEFT' => lorem_paragraph(),
                'RIGHT' => lorem_paragraph(),
                'START' => lorem_paragraph(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__setupwizard_2_screen()
    {
        require_lang('config');

        $inner = do_lorem_template('SETUPWIZARD_2', array(
            'SKIP_WEBSTANDARDS' => true,
            'TITLE' => lorem_title(),
            'URL' => placeholder_url(),
            'SUBMIT_ICON' => 'buttons__proceed',
            'SUBMIT_NAME' => lorem_word(),
        ));

        return array(
            lorem_globalise(do_lorem_template('SETUPWIZARD_SCREEN', array(
                'TITLE' => lorem_title(),
                'INNER' => $inner,
                'STEP' => '7',
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__setupwizard_7_screen()
    {
        require_lang('config');

        $inner = do_lorem_template('SETUPWIZARD_7', array(
            'TITLE' => lorem_title(),
            'FORM' => placeholder_form(),
            'BALANCED' => lorem_phrase(),
            'LIBERAL' => lorem_phrase(),
            'CORPORATE' => lorem_phrase(),
        ));

        return array(
            lorem_globalise(do_lorem_template('SETUPWIZARD_SCREEN', array(
                'TITLE' => lorem_title(),
                'INNER' => $inner,
                'STEP' => '7',
            )), null, '', true)
        );
    }
}
