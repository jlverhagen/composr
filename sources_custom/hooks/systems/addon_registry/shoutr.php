<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    shoutr
 */

/**
 * Hook class.
 */
class Hook_addon_registry_shoutr
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
        return 'Fun and Games';
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
        return 'Two new features to your shout box. Shake makes all the current visitors to your website\'s browser screen shake. The second makes any new messages within the shout box appear like an apparition and fly towards the current users of the site.

After installing this addon your shout box will essentially be treated as an embedded copy of the chatroom linked to it. Enter/leave room messages will be saved into the room when a member goes somewhere it is shown. For that reason you may want to associate it to a chatroom that isn\'t used independently to avoid that room being cluttered with these messages (the messages don\'t show in the shout box itself, as they are filtered out there). Alternatively you can blank out the language strings that provide the enter/leave room messages - Composr will recognise this and turn the feature off.';
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
            'requires' => array(
                'JavaScript enabled',
                'chat',
            ),
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
            'sources_custom/hooks/systems/addon_registry/shoutr.php',
            'themes/default/javascript_custom/shake.js',
            'themes/default/javascript_custom/shoutbox.js',
            'themes/default/javascript_custom/text_ghosts.js',
            'sources_custom/blocks/side_shoutbox.php',
            'themes/default/templates_custom/BLOCK_SIDE_SHOUTBOX.tpl',
            'themes/default/css_custom/shoutbox.css',
        );
    }
}
