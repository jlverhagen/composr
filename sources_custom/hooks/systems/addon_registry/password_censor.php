<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    password_censor
 */

/**
 * Hook class.
 */
class Hook_addon_registry_password_censor
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
     * Get the addon category
     *
     * @return string The category
     */
    public function get_category()
    {
        return 'Development';
    }

    /**
     * Get the addon author
     *
     * @return string The author
     */
    public function get_author()
    {
        return 'Chris Graham';
    }

    /**
     * Find other authors
     *
     * @return array A list of co-authors that should be attributed
     */
    public function get_copyright_attribution()
    {
        return array();
    }

    /**
     * Get the addon licence (one-line summary only)
     *
     * @return string The licence
     */
    public function get_licence()
    {
        return 'Licensed on the same terms as Composr';
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Regularly wipe old passwords posted in private on your website, in case it is ever hacked.

In detail:
This is a scheduler script that regularly censors anything looking like a password stored in support ticket posts over 60 days old to limit the potential fallout if your ticket system ever got compromised.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array();
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
            'conflicts_with' => array()
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources_custom/hooks/systems/addon_registry/password_censor.php',
            'data_custom/password_censor.php',
            'sources_custom/password_censor.php',
            'sources_custom/hooks/systems/cron/password_censor.php',
            'sources_custom/hooks/systems/comcode/encrypt.php',
            'sources_custom/hooks/systems/comcode/self_destruct.php',
            'sources_custom/notifications.php',
            'sources_custom/hooks/systems/startup/password_censor.php',
            'sources_custom/hooks/systems/symbols/DECRYPT.php',
            'themes/default/templates_custom/COMCODE_ENCRYPT.tpl',
            'themes/default/templates_custom/COMCODE_SELF_DESTRUCT.tpl',
        );
    }
}
