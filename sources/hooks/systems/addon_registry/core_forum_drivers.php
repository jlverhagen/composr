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
 * @package    core_forum_drivers
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_forum_drivers
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
        return 'The code layer that binds the software to one of various different forum/member systems.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_nuances',
            'codebook_1b',
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
            'sources/hooks/systems/addon_registry/core_forum_drivers.php',
            'sources/forum/.htaccess',
            'sources/forum/forums.ini',
            'sources/forum/index.html',
            'sources/forum/none.php',
            'sources/forum/ipb1.php',
            'sources/forum/ipb2.php',
            'sources/forum/ipb3.php',
            'sources/forum/phpbb2.php',
            'sources/forum/phpbb3.php',
            'sources/forum/shared/.htaccess',
            'sources/forum/shared/index.html',
            'sources/forum/shared/ipb.php',
            'sources/forum/shared/vb.php',
            'sources/forum/shared/wbb.php',
            'sources/forum/smf.php',
            'sources/forum/smf2.php',
            'sources/forum/vb22.php',
            'sources/forum/vb3.php',
            'sources/forum/wbb2.php',
            'sources/forum/wbb22.php',
            'sources/forum/wowbb.php',
            'sources/forum/aef.php',
            'sources/forum/mybb.php',
            'sources_custom/forum/.htaccess',
            'sources_custom/forum/index.html',
        );
    }
}
