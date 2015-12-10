<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    ecommerce
 */

/**
 * Hook class.
 */
class Hook_members_ecommerce
{
    /**
     * Find member-related links to inject to details section of the about tab of the member profile.
     *
     * @param  MEMBER $member_id The ID of the member we are getting links for
     * @return array List of pairs: title to value.
     */
    public function run($member_id)
    {
        if (!addon_installed('ecommerce')) {
            return array();
        }

        require_lang('ecommerce');

        $modules = array();

        /*  Now we provide this link under the embedded list of subscriptions
        if ($GLOBALS['SITE_DB']->query_select_value('subscriptions', 'COUNT(*)', array('s_member_id' => $member_id)) != 0) {
            $modules[] = array('views', do_lang_tempcode('MY_SUBSCRIPTIONS'), build_url(array('page' => 'subscriptions', 'type' => 'browse', 'id' => $member_id), get_module_zone('subscriptions')), 'menu/adminzone/audit/ecommerce/subscriptions');
        }
        */

        if ($GLOBALS['SITE_DB']->query_select_value('invoices', 'COUNT(*)', array('i_member_id' => $member_id)) != 0) {
            $modules[] = array('views', do_lang_tempcode('MY_INVOICES'), build_url(array('page' => 'invoices', 'type' => 'browse', 'id' => $member_id), get_module_zone('invoices')), 'menu/adminzone/audit/ecommerce/invoices');
        }

        if (has_actual_page_access(get_member(), 'admin_ecommerce', get_module_zone('admin_ecommerce'))) {
            $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
            $modules[] = array('views', do_lang_tempcode('CREATE_INVOICE'), build_url(array('page' => 'admin_invoices', 'type' => 'add', 'to' => $username), get_module_zone('admin_invoices')), 'menu/adminzone/audit/ecommerce/create_invoice');
        }

        return $modules;
    }

    /**
     * Get sections to inject to about tab of the member profile.
     *
     * @param  MEMBER $member_id The ID of the member we are getting sections for
     * @return array List of sections. Each tuple is Tempcode.
     */
    public function get_sections($member_id)
    {
        if (($member_id != get_member()) && (!has_privilege(get_member(), 'view_any_profile_field'))) {
            return array();
        }

        if (!addon_installed('ecommerce')) {
            return array();
        }

        require_code('ecommerce_subscriptions');
        $_subscriptions = find_member_subscriptions($member_id);

        // Note that this display is similar to the subscriptions module, but a bit more cut down, and showing only active subscriptions

        $subscriptions = array();
        foreach ($_subscriptions as $_subscription) {
            if (!$_subscription['is_active']) {
                continue; // We only show active subscriptions here
            }

            $subscriptions[] = prepare_templated_subscription($_subscription);
        }

        if (count($subscriptions) == 0) {
            return array();
        }

        require_lang('ecommerce');

        return array(do_template('MEMBER_SUBSCRIPTION_STATUS', array('SUBSCRIPTIONS' => $subscriptions, 'MEMBER_ID' => strval($member_id))));
    }
}
