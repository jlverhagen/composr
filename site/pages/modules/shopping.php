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
 * @package    shopping
 */

/**
 * Module page class.
 */
class Module_shopping
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Manuprathap';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 8;
        $info['update_require_upgrade'] = true;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('shopping_cart');
        $GLOBALS['SITE_DB']->drop_table_if_exists('shopping_order_details');
        $GLOBALS['SITE_DB']->drop_table_if_exists('shopping_order');
        $GLOBALS['SITE_DB']->drop_table_if_exists('shopping_logging');
        $GLOBALS['SITE_DB']->drop_table_if_exists('shopping_order_addresses');

        $GLOBALS['SITE_DB']->query_delete('group_category_access', array('module_the_name' => 'shopping'));
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if (is_null($upgrade_from)) {
            $GLOBALS['SITE_DB']->create_table('shopping_cart', array(
                'id' => '*AUTO',
                'session_id' => 'ID_TEXT',
                'ordered_by' => 'MEMBER',
                'product_id' => 'AUTO_LINK',
                'product_name' => 'SHORT_TEXT',
                'product_code' => 'SHORT_TEXT',
                'quantity' => 'INTEGER',
                'price_pre_tax' => 'REAL',
                'price' => 'REAL',
                'product_description' => 'LONG_TEXT',
                'product_type' => 'SHORT_TEXT',
                'product_weight' => 'REAL',
                'is_deleted' => 'BINARY', // Indicates an item is no longer in the cart because the user deleted it
            ));
            $GLOBALS['SITE_DB']->create_index('shopping_cart', 'ordered_by', array('ordered_by'));
            $GLOBALS['SITE_DB']->create_index('shopping_cart', 'session_id', array('session_id'));
            $GLOBALS['SITE_DB']->create_index('shopping_cart', 'product_id', array('product_id'));

            // Cart contents turns into order + details...

            $GLOBALS['SITE_DB']->create_table('shopping_order', array(
                'id' => '*AUTO',
                'c_member' => 'INTEGER',
                'session_id' => 'ID_TEXT',
                'add_date' => 'TIME',
                'tot_price' => 'REAL',
                'order_status' => 'ID_TEXT', // ORDER_STATUS_[awaiting_payment|payment_received|onhold|dispatched|cancelled|returned]
                'notes' => 'LONG_TEXT',
                'transaction_id' => 'SHORT_TEXT',
                'purchase_through' => 'SHORT_TEXT',
                'tax_opted_out' => 'BINARY',
            ));
            $GLOBALS['SITE_DB']->create_index('shopping_order', 'finddispatchable', array('order_status'));
            $GLOBALS['SITE_DB']->create_index('shopping_order', 'soc_member', array('c_member'));
            $GLOBALS['SITE_DB']->create_index('shopping_order', 'sosession_id', array('session_id'));
            $GLOBALS['SITE_DB']->create_index('shopping_order', 'soadd_date', array('add_date'));

            $GLOBALS['SITE_DB']->create_table('shopping_order_details', array(
                'id' => '*AUTO',
                'order_id' => '?AUTO_LINK',
                'p_id' => '?AUTO_LINK',
                'p_name' => 'SHORT_TEXT',
                'p_code' => 'SHORT_TEXT',
                'p_type' => 'SHORT_TEXT',
                'p_quantity' => 'INTEGER',
                'p_price' => 'REAL',
                'included_tax' => 'REAL',
                'dispatch_status' => 'SHORT_TEXT'
            ));
            $GLOBALS['SITE_DB']->create_index('shopping_order_details', 'p_id', array('p_id'));
            $GLOBALS['SITE_DB']->create_index('shopping_order_details', 'order_id', array('order_id'));

            $GLOBALS['SITE_DB']->create_table('shopping_logging', array(
                'id' => '*AUTO',
                'e_member_id' => '*MEMBER',
                'session_id' => 'ID_TEXT',
                'ip' => 'IP',
                'last_action' => 'SHORT_TEXT',
                'date_and_time' => 'TIME'
            ));
            $GLOBALS['SITE_DB']->create_index('shopping_logging', 'calculate_bandwidth', array('date_and_time'));

            $GLOBALS['SITE_DB']->create_table('shopping_order_addresses', array(
                // These are filled after an order is made (maybe via what comes back from IPN, maybe from what is set for a local payment), and presented in the admin orders UI
                'id' => '*AUTO',
                'order_id' => '?AUTO_LINK',
                'firstname' => 'SHORT_TEXT', // NB: May be full-name, or include company name
                'lastname' => 'SHORT_TEXT',
                'street_address' => 'LONG_TEXT',
                'city' => 'SHORT_TEXT',
                'county' => 'SHORT_TEXT',
                'state' => 'SHORT_TEXT',
                'post_code' => 'SHORT_TEXT',
                'country' => 'SHORT_TEXT',
                'email' => 'SHORT_TEXT',
                'phone' => 'SHORT_TEXT',
            ));
            $GLOBALS['SITE_DB']->create_index('shopping_order_addresses', 'order_id', array('order_id'));
        }

        if ((!is_null($upgrade_from)) && ($upgrade_from < 7)) {
            $GLOBALS['SITE_DB']->add_table_field('shopping_order_addresses', 'contact_phone', 'SHORT_TEXT');
            $GLOBALS['SITE_DB']->add_table_field('shopping_order_addresses', 'address_state', 'SHORT_TEXT');
            $GLOBALS['SITE_DB']->add_table_field('shopping_order_addresses', 'first_name', 'SHORT_TEXT');
            $GLOBALS['SITE_DB']->add_table_field('shopping_order_addresses', 'last_name', 'SHORT_TEXT');

            $GLOBALS['SITE_DB']->alter_table_field('shopping_order', 'session_id', 'ID_TEXT');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_cart', 'session_id', 'ID_TEXT');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_logging', 'session_id', 'ID_TEXT');

            $GLOBALS['SITE_DB']->change_primary_key('shopping_cart', array('id'));

            $GLOBALS['SITE_DB']->delete_index_if_exists('shopping_order', 'recent_shopped');
        }

        if (($upgrade_from !== null) && ($upgrade_from < 8)) {
            $GLOBALS['SITE_DB']->add_table_field('shopping_order_addresses', 'address_county', 'SHORT_TEXT');

            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'first_name', 'SHORT_TEXT', 'firstname');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'last_name', 'SHORT_TEXT', 'lastname');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_street', 'LONG_TEXT', 'street_address');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_city', 'SHORT_TEXT', 'city');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_county', 'SHORT_TEXT', 'county');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_state', 'SHORT_TEXT', 'state');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_zip', 'SHORT_TEXT', 'post_code');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'address_country', 'SHORT_TEXT', 'country');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'receiver_email', 'SHORT_TEXT', 'email');
            $GLOBALS['SITE_DB']->alter_table_field('shopping_order_addresses', 'contact_phone', 'SHORT_TEXT', 'phone');
        }
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

        $ret = array(
            'browse' => array('SHOPPING', 'menu/rich_content/ecommerce/shopping_cart'),
        );
        if (!$check_perms || !is_guest($member_id)) {
            $ret += array(
                'my_orders' => array('MY_ORDERS', 'menu/rich_content/ecommerce/orders'),
            );
        }
        return $ret;
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('shopping');
        require_lang('catalogues');

        $ecom_catalogue_count = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'COUNT(*)', array('c_ecommerce' => 1));
        $ecom_catalogue = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_name', array('c_ecommerce' => 1));
        $ecom_catalogue_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_categories', 'MIN(id)', array('c_name' => $ecom_catalogue));

        if ($type == 'browse') {
            if ($ecom_catalogue_count == 1) {
                breadcrumb_set_parents(array(array('_SELF:catalogues:category:=' . $ecom_catalogue_id, do_lang_tempcode('DEFAULT_CATALOGUE_PRODUCTS_TITLE'))));
            } else {
                breadcrumb_set_parents(array(array('_SELF:catalogues:browse:ecommerce=1', do_lang_tempcode('CATALOGUES'))));
            }

            $this->title = get_screen_title('SHOPPING');
        }

        if ($type == 'add_item') {
            $this->title = get_screen_title('SHOPPING');
        }

        if ($type == 'update_cart') {
            $this->title = get_screen_title('SHOPPING');
        }

        if ($type == 'empty_cart') {
            $this->title = get_screen_title('SHOPPING');
        }

        if ($type == 'finish') {
            if ($ecom_catalogue_count == 1) {
                breadcrumb_set_parents(array(array('_SELF:catalogues:category:=' . $ecom_catalogue_id, do_lang_tempcode('DEFAULT_CATALOGUE_PRODUCTS_TITLE')), array('_SELF:_SELF:browse', do_lang_tempcode('SHOPPING'))));
            } else {
                breadcrumb_set_parents(array(array('_SELF:catalogues:browse:ecommerce=1', do_lang_tempcode('CATALOGUES')), array('_SELF:_SELF:browse', do_lang_tempcode('SHOPPING'))));
            }

            $this->title = get_screen_title('_PURCHASE_FINISHED');
        }

        if ($type == 'my_orders') {
            $this->title = get_screen_title('MY_ORDERS');
        }

        if ($type == 'order_det') {
            breadcrumb_set_parents(array(array('_SELF:orders:browse', do_lang_tempcode('MY_ORDERS'))));

            $id = get_param_integer('id');
            $this->title = get_screen_title('_MY_ORDER_DETAILS', true, array(escape_html($id)));
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
        @ignore_user_abort(true); // Must keep going till completion

        require_code('shopping');
        require_code('feedback');
        require_lang('ecommerce');
        require_code('ecommerce');

        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        }

        // Kill switch
        if ((ecommerce_test_mode()) && (!$GLOBALS['IS_ACTUALLY_ADMIN']) && (!has_privilege(get_member(), 'access_ecommerce_in_test_mode'))) {
            warn_exit(do_lang_tempcode('PURCHASE_DISABLED'));
        }

        $GLOBALS['NO_QUERY_LIMIT'] = true;

        $type = get_param_string('type', 'browse');

        delete_incomplete_orders();

        if ($type == 'browse') {
            return $this->view_shopping_cart();
        }
        if ($type == 'add_item') {
            return $this->add_item_to_cart();
        }
        if ($type == 'update_cart') {
            return $this->update_cart();
        }
        if ($type == 'empty_cart') {
            return $this->empty_cart();
        }
        if ($type == 'finish') {
            return $this->finish();
        }
        if ($type == 'my_orders') {
            return $this->my_orders();
        }
        if ($type == 'order_det') {
            return $this->order_det();
        }

        return new Tempcode();
    }

    /**
     * The UI to show shopping cart
     *
     * @return Tempcode The UI
     */
    public function view_shopping_cart()
    {
        $pro_ids = array();

        $pro_ids_val = null;

        require_code('templates_results_table');
        require_code('form_templates');
        require_css('shopping');
        require_javascript('shopping');

        log_cart_actions('View cart');

        $where = array('is_deleted' => 0);
        if (is_guest()) {
            $where['session_id'] = get_session_id();
        } else {
            $where['ordered_by'] = get_member();
        }
        $result = $GLOBALS['SITE_DB']->query_select('shopping_cart', array('*'), $where);

        $max_rows = count($result);

        if ($max_rows > 0) {
            $shopping_cart = new Tempcode();

            $fields_title = results_field_title(
                array(
                    '',
                    do_lang_tempcode('PRODUCT'),
                    do_lang_tempcode('UNIT_PRICE'),
                    do_lang_tempcode('QUANTITY'),
                    do_lang_tempcode('ORDER_PRICE_AMT'),
                    do_lang_tempcode('TAX'),
                    do_lang_tempcode('SHIPPING_PRICE'),
                    do_lang_tempcode('TOTAL_PRICE'),
                    do_lang_tempcode('REMOVE')
                ), null
            );

            $i = 1;
            $sub_tot = 0.0;
            $shipping_cost = 0.0;

            foreach ($result as $value) {
                $pro_ids[] = $value['product_id'];

                $_hook = $value['product_type'];

                $value['sl_no'] = $i;

                require_code('hooks/systems/ecommerce/' . filter_naughty_harsh($_hook));

                $object = object_factory('Hook_ecommerce_' . filter_naughty_harsh($_hook));

                if (method_exists($object, 'show_cart_entry')) {
                    $object->show_cart_entry($shopping_cart, $value);
                }

                // Tax
                $tax = 0;
                if (method_exists($object, 'calculate_tax')) {
                    $tax = $object->calculate_tax($value['price'], $value['price_pre_tax']);
                }

                // Shipping
                if (method_exists($object, 'calculate_shipping_cost')) {
                    $shipping_cost = $object->calculate_shipping_cost($value['product_weight']);
                } else {
                    $shipping_cost = 0;
                }

                $sub_tot += round($value['price'] + $tax + $shipping_cost, 2) * $value['quantity'];

                $i++;
            }

            $width = null;//array('50','100%','85','85','85','85','85','85','85');

            $results_table = results_table(do_lang_tempcode('SHOPPING'), 0, 'cart_start', $max_rows, 'cart_max', $max_rows, $fields_title, $shopping_cart, null, null, null, 'sort', null, $width, 'cart');

            $update_cart_url = build_url(array('page' => '_SELF', 'type' => 'update_cart'), '_SELF');
            $empty_cart_url = build_url(array('page' => '_SELF', 'type' => 'empty_cart'), '_SELF');

            $payment_form = render_cart_payment_form();

            $proceed_box = do_template('ECOM_SHOPPING_CART_PROCEED', array(
                '_GUID' => '02c90b68ca06620d39a42727766ce8b0',
                'SUB_TOTAL' => float_format($sub_tot),
                'SHIPPING_COST' => float_format($shipping_cost),
                'GRAND_TOTAL' => float_format($sub_tot),
                'PROCEED' => do_lang_tempcode('PROCEED'),
                'CURRENCY' => ecommerce_get_currency_symbol(),
                'PAYMENT_FORM' => $payment_form,
            ));
        } else {
            $update_cart_url = new Tempcode();
            $empty_cart_url = new Tempcode();

            $results_table = do_lang_tempcode('CART_EMPTY');
            $proceed_box = new Tempcode();
        }

        $ecom_catalogue_count = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'COUNT(*)', array('c_ecommerce' => 1));
        $ecom_catalogue = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_name', array('c_ecommerce' => 1));
        $ecom_catalogue_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_categories', 'MIN(id)', array('c_name' => $ecom_catalogue));
        if ($ecom_catalogue_count == 1) {
            $cont_shopping_url = build_url(array('page' => 'catalogues', 'type' => 'category', 'id' => $ecom_catalogue_id), get_module_zone('catalogues'));
        } else {
            $cont_shopping_url = build_url(array('page' => 'catalogues', 'type' => 'browse', 'ecommerce' => 1), get_module_zone('catalogues'));
        }

        // Product ID string for hidden field in Shopping cart
        $pro_ids_val = is_array($pro_ids) ? implode(',', $pro_ids) : '';

        $allow_opt_out_tax = get_option('allow_opting_out_of_tax');

        $allow_opt_out_tax_value = get_order_tax_opt_out_status();

        $tpl = do_template('ECOM_SHOPPING_CART_SCREEN', array(
            '_GUID' => 'badff09daf52ee1c84b472c44be1bfae',
            'TITLE' => $this->title,
            'RESULTS_TABLE' => $results_table,
            'FORM_URL' => $update_cart_url,
            'CONT_SHOPPING_URL' => $cont_shopping_url,
            'MESSAGE' => '',
            'PRO_IDS' => $pro_ids_val,
            'EMPTY_CART_URL' => $empty_cart_url,
            'PROCEED_BOX' => $proceed_box,
            'ALLOW_OPTOUT_TAX' => $allow_opt_out_tax,
            'ALLOW_OPTOUT_TAX_VALUE' => strval($allow_opt_out_tax_value),
        ));

        require_code('templates_internalise_screen');
        return internalise_own_screen($tpl);
    }

    /**
     * Add an item to the cart.
     *
     * @return Tempcode The UI
     */
    public function add_item_to_cart()
    {
        if (is_guest()) {
            require_code('users_inactive_occasionals');
            set_session_id(get_session_id(), true); // Persist guest sessions longer
        }

        $product_details = get_product_details();

        add_to_cart($product_details);

        log_cart_actions('Added to cart');

        $cart_view = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');

        return redirect_screen($this->title, $cart_view, do_lang_tempcode('ADDED_TO_CART'));
    }

    /**
     * Update the cart.
     *
     * @return Tempcode The UI
     */
    public function update_cart()
    {
        $p_ids = post_param_string('product_ids');

        $pids = explode(",", $p_ids);

        $product_to_remove = array();

        $product_details = array();

        if (count($pids) > 0) {
            foreach ($pids as $pid) {
                $qty = post_param_integer('quantity_' . $pid);

                $object = find_product($pid);

                $remove = post_param_integer('remove_' . $pid, 0);

                if ($remove == 0) {
                    if (method_exists($object, 'get_available_quantity')) {
                        $available_qty = $object->get_available_quantity($pid, false);

                        if ((!is_null($available_qty)) && ($available_qty <= $qty)) {
                            $qty = $available_qty;

                            attach_message(do_lang_tempcode('PRODUCT_QUANTITY_CHANGED', strval($pid)), 'warn');
                        }
                    }
                }

                $product_details[] = array('product_id' => $pid, 'quantity' => $qty);

                if ($remove == 1) {
                    $product_to_remove[] = $pid;
                }
            }
        }

        update_cart($product_details);

        log_cart_actions('Updated cart');

        if (count($product_to_remove) > 0) {
            remove_from_cart($product_to_remove);
        }

        $cart_view = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');

        return redirect_screen($this->title, $cart_view, do_lang_tempcode('CART_UPDATED'));
    }

    /**
     * Empty the shopping cart.
     *
     * @return Tempcode The UI
     */
    public function empty_cart()
    {
        log_cart_actions('Cart emptied');

        empty_cart(true);

        $cart_view = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');

        return redirect_screen($this->title, $cart_view, do_lang_tempcode('CART_EMPTIED'));
    }

    /**
     * Wrap-up so as to remove redundancy in templates.
     *
     * @param  Tempcode $content To wrap.
     * @param  Tempcode $title The title to use.
     * @param  ?mixed $url URL (null: no next URL).
     * @param  boolean $get Whether it is a GET form
     * @return Tempcode Wrapped.
     */
    public function wrap($content, $title, $url, $get = false)
    {
        if (is_null($url)) {
            $url = '';
        }
        require_javascript('checking');

        return do_template('PURCHASE_WIZARD_SCREEN', array('_GUID' => '02fd80e2b4d4fc2348736a72e504a208', 'GET' => $get ? true : null, 'TITLE' => $title, 'CONTENT' => $content, 'URL' => $url));
    }

    /**
     * Finish step.
     *
     * @return Tempcode The result of execution.
     */
    public function finish()
    {
        $via = get_option('payment_gateway');
        require_code('hooks/systems/ecommerce_via/' . filter_naughty_harsh($via));
        $object = object_factory('Hook_' . $via);

        $message = mixed();
        if (method_exists($object, 'get_callback_url_message')) {
            $message = $object->get_callback_url_message();
        }

        require_code('shopping');

        if (get_param_integer('cancel', 0) == 0) {
            empty_cart();

            log_cart_actions('Completed payment');

            // Take payment
            if (perform_local_payment()) {
                list($success, $message, $message_raw) = handle_local_payment($via, $object);
            }

            // Process transaction
            if (has_interesting_post_fields()) {
                $order_id = handle_ipn_transaction_script();

                $product_object = find_product(do_lang('CART_ORDER', $order_id));

                if (method_exists($product_object, 'get_finish_url')) {
                    return redirect_screen($this->title, $product_object->get_finish_url(), $message);
                }
            }

            return $this->wrap(do_template('PURCHASE_WIZARD_STAGE_FINISH', array('_GUID' => '3857e761ab75f314f4960805bc76b936', 'TITLE' => $this->title, 'MESSAGE' => $message)), $this->title, null);
        }

        delete_pending_orders_for_current_user(); // Don't lock the stock unless they go back to the cart again

        if (!is_null($message)) {
            return $this->wrap(do_template('PURCHASE_WIZARD_STAGE_FINISH', array('_GUID' => '6eafce1925e5069ceb438ec24754b47d', 'TITLE' => $this->title, 'MESSAGE' => $message)), $this->title, null);
        }

        return inform_screen(get_screen_title('PURCHASING'), do_lang_tempcode('PRODUCT_PURCHASE_CANCEL'), true);
    }

    /**
     * Show all my orders
     *
     * @return Tempcode The interface.
     */
    public function my_orders()
    {
        $member_id = get_member();

        if (has_privilege(get_member(), 'assume_any_member')) {
            $member_id = get_param_integer('id', $member_id);
        }

        $orders = array();

        $rows = $GLOBALS['SITE_DB']->query_select('shopping_order', array('*'), array('c_member' => $member_id), 'ORDER BY add_date');

        foreach ($rows as $row) {
            if ($row['purchase_through'] == 'cart') {
                $order_det_url = build_url(array('page' => '_SELF', 'type' => 'order_det', 'id' => $row['id']), '_SELF');

                $order_title = do_lang('CART_ORDER', $row['id']);
            } else {
                $res = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('p_id', 'p_name'), array('order_id' => $row['id']));

                if (!array_key_exists(0, $res)) {
                    continue; // DB corruption
                }
                $product_det = $res[0];

                $order_title = $product_det['p_name'];

                $order_det_url = build_url(array('page' => 'catalogues', 'type' => 'entry', 'id' => $product_det['p_id']), get_module_zone('catalogues'));
            }

            $orders[] = array('ORDER_TITLE' => $order_title, 'ID' => strval($row['id']), 'AMOUNT' => strval($row['tot_price']), 'TIME' => get_timezoned_date($row['add_date'], true, false, true, true), 'STATE' => do_lang_tempcode($row['order_status']), 'NOTE' => '', 'ORDER_DET_URL' => $order_det_url, 'DELIVERABLE' => '');
        }

        if (count($orders) == 0) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        return do_template('ECOM_ORDERS_SCREEN', array('_GUID' => '79eb5f17cf4bc2dc4f0cccf438261c73', 'TITLE' => $this->title, 'CURRENCY' => get_option('currency'), 'ORDERS' => $orders));
    }

    /**
     * Show an order details
     *
     * @return Tempcode The interface.
     */
    public function order_det()
    {
        $id = get_param_integer('id');

        $products = array();

        $rows = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('order_id' => $id), 'ORDER BY p_name');
        foreach ($rows as $row) {
            $product_det_url = build_url(array('page' => 'catalogues', 'type' => 'entry', 'id' => $row['p_id']), get_module_zone('catalogues'));

            $products[] = array('PRODUCT_NAME' => $row['p_name'], 'ID' => strval($row['p_id']), 'AMOUNT' => strval($row['p_price']), 'QUANTITY' => strval($row['p_quantity']), 'DISPATCH_STATUS' => do_lang_tempcode($row['dispatch_status']), 'PRODUCT_DET_URL' => $product_det_url, 'DELIVERABLE' => '');
        }

        if (count($products) == 0) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        return do_template('ECOM_ORDERS_DETAILS_SCREEN', array('_GUID' => '8122a53dc0ccf27648af460759a2b6f6', 'TITLE' => $this->title, 'CURRENCY' => get_option('currency'), 'PRODUCTS' => $products));
    }
}
