<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    classified_ads
 */

/**
 * Hook class.
 */
class Hook_addon_registry_classified_ads
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
        return 'New Features';
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
        return array(
            'Icon by Andrey Kem',
        );
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
        return 'Set up price scales for placing entries (\'classified adverts\') in catalogues.

The scales allow you to define discounts to customers buying longer listing periods. You can set up a free period if you wish to.

Customers are given a control panel (the classifieds module) that shows their listings and allows renewal.

Notifications are sent the day before an advert expires.

Fully integrated with catalogues, eCommerce, and Conversr member accounts.

The basic process for setting up this addon is:
1) Create a catalogue for the adverts (Content Management > Catalogues > Add Catalogue)
2) Set the fields for the catalogue as appropriate
3) Set the permissions to "Add/Post/Submit entries" (i.e. not unvetted, not bypassing validation)
4) Add whatever categories you want to your catalogue
5) Go to Admin Zone > Setup > Classifieds pricing
6) Set prices up against your catalogue
7) Make sure your catalogue is on your menus

Users will find add links when browsing the catalogue. Their entries will be added as non-validated. From their classifieds module they may then pay for their classified advert to go live (this uses the eCommerce module, validating the entry upon payment).
';
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
                'Conversr',
                'catalogues',
                'ecommerce',
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
        return 'themes/default/images_custom/icons/48x48/menu/classifieds.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images_custom/icons/24x24/menu/classifieds.png',
            'themes/default/images_custom/icons/48x48/menu/classifieds.png',
            'sources_custom/hooks/systems/addon_registry/classified_ads.php',
            'adminzone/pages/minimodules_custom/admin_classifieds.php',
            'lang_custom/EN/classifieds.ini',
            'site/pages/modules_custom/classifieds.php',
            'sources_custom/classifieds.php',
            'sources_custom/hooks/modules/members/classifieds.php',
            'sources_custom/hooks/systems/cron/classifieds.php',
            'sources_custom/hooks/systems/page_groupings/classifieds.php',
            'sources_custom/hooks/systems/ecommerce/classifieds.php',
            'sources_custom/hooks/systems/notifications/classifieds.php',
            'sources_custom/miniblocks/main_classifieds_prices.php',
            'themes/default/templates_custom/CLASSIFIED_ADVERTS_SCREEN.tpl',
            'themes/default/templates_custom/CLASSIFIEDS_PRICING_SCREEN.tpl',
            'themes/default/templates_custom/CLASSIFIEDS.tpl',
            'sources_custom/hooks/systems/config/max_classified_listings_per_page.php',
        );
    }
}
