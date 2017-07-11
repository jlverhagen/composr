<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

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
class Hook_payment_gateway_secpay
{
    // Requires:
    //  You have the SecPay Username set as the Composr "Gateway username" option
    //  You have the SecPay Username set as the Composr "Testing mode gateway username" option
    //  You have the SecPay "Remote Password" option set as the Composr "Gateway password" option
    //  You have the SecPay "VPN password" option set as the Composr "Gateway VPN password" option
    //  You have the SecPay "Hash key" option set as the Composr "Gateway digest code" option 

    /**
     * Get a standardised config map.
     *
     * @return array The config
     */
    public function get_config()
    {
        return array(
            'supports_remote_memo' => false,
        );
    }

    /**
     * Find a transaction fee from a transaction amount. Regular fees aren't taken into account.
     *
     * @param  float $amount A transaction amount
     * @return float The fee
     */
    public function get_transaction_fee($amount)
    {
        return 0.39;
    }

    /**
     * Get a list of card types.
     *
     * @param  ?string $it The card type to select by default (null: don't care)
     * @return Tempcode The list
     */
    public function create_selection_list_card_types($it = null)
    {
        $list = new Tempcode();
        $array = array('Visa', 'Master Card', 'Switch', 'UK Maestro', 'Maestro', 'Solo', 'Delta', 'American Express', 'Diners Card', 'JCB');
        foreach ($array as $x) {
            $list->attach(form_input_list_entry($x, $it == $x));
        }
        return $list;
    }

    /**
     * Get the gateway username.
     *
     * @return string The answer
     */
    protected function _get_username()
    {
        return ecommerce_test_mode() ? get_option('payment_gateway_test_username') : get_option('payment_gateway_username');
    }

    /**
     * Get the remote form URL.
     *
     * @return URLPATH The remote form URL
     */
    protected function _get_remote_form_url()
    {
        return 'https://www.secpay.com/java-bin/ValCard';
    }

    /**
     * Generate a transaction ID / trans-expecting ID.
     *
     * @return string A transaction ID
     */
    public function generate_trans_id()
    {
        require_code('crypt');
        return get_secure_random_string();
    }

    /**
     * Make a transaction (payment) button.
     *
     * @param  ID_TEXT $trans_expecting_id Our internal temporary transaction ID
     * @param  ID_TEXT $type_code The product codename
     * @param  SHORT_TEXT $item_name The human-readable product title
     * @param  ID_TEXT $purchase_id The purchase ID
     * @param  float $price Transaction price in money
     * @param  float $tax Transaction tax in money
     * @param  float $shipping_cost Shipping cost
     * @param  ID_TEXT $currency The currency to use
     * @return Tempcode The button
     */
    public function make_transaction_button($trans_expecting_id, $type_code, $item_name, $purchase_id, $price, $tax, $shipping_cost, $currency)
    {
        // https://www.secpay.com/sc_api.html

        $username = $this->_get_username();
        $form_url = $this->_get_remote_form_url();
        $digest = md5($trans_expecting_id . float_to_raw_string($price + $tax + $shipping_cost) . get_option('payment_gateway_password'));

        return do_template('ECOM_TRANSACTION_BUTTON_VIA_SECPAY', array(
            '_GUID' => 'e68e80cb637f8448ef62cd7d73927722',
            'TYPE_CODE' => $type_code,
            'ITEM_NAME' => $item_name,
            'PURCHASE_ID' => $purchase_id,
            'TRANS_EXPECTING_ID' => $trans_expecting_id,
            'DIGEST' => $digest,
            'TEST' => ecommerce_test_mode(),
            'PRICE' => float_to_raw_string($price),
            'TAX' => float_to_raw_string($tax),
            'SHIPPING_COST' => float_to_raw_string($shipping_cost),
            'AMOUNT' => float_to_raw_string($price + $tax + $shipping_cost),
            'CURRENCY' => $currency,
            'USERNAME' => $username,
            'FORM_URL' => $form_url,
            'MEMBER_ADDRESS' => $this->_build_member_address(),
        ));
    }

    /**
     * Make a subscription (payment) button.
     *
     * @param  ID_TEXT $trans_expecting_id Our internal temporary transaction ID
     * @param  ID_TEXT $type_code The product codename
     * @param  SHORT_TEXT $item_name The human-readable product title
     * @param  ID_TEXT $purchase_id The purchase ID
     * @param  float $price Transaction price in money
     * @param  float $tax Transaction tax in money
     * @param  ID_TEXT $currency The currency to use
     * @param  integer $length The subscription length in the units
     * @param  ID_TEXT $length_units The length units
     * @set    d w m y
     * @return Tempcode The button
     */
    public function make_subscription_button($trans_expecting_id, $type_code, $item_name, $purchase_id, $price, $tax, $currency, $length, $length_units)
    {
        // https://www.secpay.com/sc_api.html

        $username = $this->_get_username();
        $form_url = $this->_get_remote_form_url();
        $digest = md5($trans_expecting_id . float_to_raw_string($price + $tax) . get_option('payment_gateway_password'));
        list($length_units_2, $first_repeat) = $this->_translate_subscription_details($length, $length_units);

        return do_template('ECOM_SUBSCRIPTION_BUTTON_VIA_SECPAY', array(
            '_GUID' => 'e5e6d6835ee6da1a6cf02ff8c2476aa6',
            'TYPE_CODE' => $type_code,
            'ITEM_NAME' => $item_name,
            'PURCHASE_ID' => $purchase_id,
            'TRANS_EXPECTING_ID' => $trans_expecting_id,
            'DIGEST' => $digest,
            'TEST' => ecommerce_test_mode(),
            'FIRST_REPEAT' => $first_repeat,
            'LENGTH' => strval($length),
            'LENGTH_UNITS_2' => $length_units_2,
            'PRICE' => float_to_raw_string($price),
            'TAX' => float_to_raw_string($tax),
            'AMOUNT' => float_to_raw_string($price + $tax),
            'CURRENCY' => $currency,
            'USERNAME' => $username,
            'FORM_URL' => $form_url,
            'MEMBER_ADDRESS' => $this->_build_member_address(),
        ));
    }

    /**
     * Find details for a subscription in secpay format.
     *
     * @param  integer $length The subscription length in the units
     * @param  ID_TEXT $length_units The length units
     * @set    d w m y
     * @return array A tuple: the period in secpay units, the date of the first repeat
     */
    protected function _translate_subscription_details($length, $length_units)
    {
        switch ($length_units) {
            case 'd':
                $length_units_2 = 'daily';
                $single_length = 60 * 60 * 24;
                break;
            case 'w':
                $length_units_2 = 'weekly';
                $single_length = 60 * 60 * 24 * 7;
                break;
            case 'm':
                $length_units_2 = 'monthly';
                $single_length = 60 * 60 * 24 * 31;
                break;
            case 'y':
                $length_units_2 = 'yearly';
                $single_length = 60 * 60 * 24 * 365;
                break;
        }
        if (($length_units == 'm') && ($length == 3)) {
            $length_units_2 = 'quarterly';
            $single_length = 60 * 60 * 24 * 92;
        }
        $first_repeat = date('Ymd', time() + $single_length);

        return array($length_units_2, $first_repeat);
    }

    /**
     * Get a member address/etc for use in payment buttons.
     *
     * @return array A map of member address details (form field name => address value)
     */
    protected function _build_member_address()
    {
        $member_address = array();

        $shipping_email = '';
        $shipping_phone = '';
        $shipping_firstname = '';
        $shipping_lastname = '';
        $shipping_street_address = '';
        $shipping_city = '';
        $shipping_county = '';
        $shipping_state = '';
        $shipping_post_code = '';
        $shipping_country = '';
        $shipping_email = '';
        $shipping_phone = '';
        $cardholder_name = '';
        $card_type = '';
        $card_number = null;
        $card_start_date_year = null;
        $card_start_date_month = null;
        $card_expiry_date_year = null;
        $card_expiry_date_month = null;
        $card_issue_number = null;
        $card_cv2 = null;
        $billing_street_address = '';
        $billing_city = '';
        $billing_county = '';
        $billing_state = '';
        $billing_post_code = '';
        $billing_country = '';
        get_default_ecommerce_fields(null, $shipping_email, $shipping_phone, $shipping_firstname, $shipping_lastname, $shipping_street_address, $shipping_city, $shipping_county, $shipping_state, $shipping_post_code, $shipping_country, $cardholder_name, $card_type, $card_number, $card_start_date_year, $card_start_date_month, $card_expiry_date_year, $card_expiry_date_month, $card_issue_number, $card_cv2, $billing_street_address, $billing_city, $billing_county, $billing_state, $billing_post_code, $billing_country, false, false);

        if ($shipping_street_address == '') {
            $street_address = $billing_street_address;
            $city = $billing_city;
            $county = $billing_county;
            $state = $billing_state;
            $post_code = $billing_post_code;
            $country = $billing_country;
        } else {
            $street_address = $shipping_street_address;
            $city = $shipping_city;
            $county = $shipping_county;
            $state = $shipping_state;
            $post_code = $shipping_post_code;
            $country = $shipping_country;
        }

        $member_address = array();
        $member_address['ship_name'] = trim($shipping_firstname . ' ' . $shipping_lastname);
        list($street_address_1, $street_address_2) = split_street_address($street_address, 2);
        $member_address['ship_addr_1'] = $street_address_1;
        $member_address['ship_addr_2'] = $street_address_2;
        $member_address['ship_city'] = $city;
        $member_address['ship_state'] = $state;
        $member_address['ship_post_code'] = $post_code;
        $member_address['ship_country'] = $country;
        $member_address['ship_email'] = $shipping_email;
        $member_address['ship_tel'] = $shipping_phone;

        return $member_address;
    }

    /**
     * Make a subscription cancellation button.
     *
     * @param  ID_TEXT $purchase_id The purchase ID
     * @return Tempcode The button
     */
    public function make_cancel_button($purchase_id)
    {
        $cancel_url = build_url(array('page' => 'subscriptions', 'type' => 'cancel', 'id' => $purchase_id), get_module_zone('subscriptions'));
        return do_template('ECOM_SUBSCRIPTION_CANCEL_BUTTON_VIA_SECPAY', array('_GUID' => 'bd02018c985e2345be71eed537b2f841', 'CANCEL_URL' => $cancel_url, 'PURCHASE_ID' => $purchase_id));
    }

    /**
     * Handle IPN's. The function may produce output, which would be returned to the Payment Gateway. The function may do transaction verification.
     *
     * @param  boolean $silent_fail Return null on failure rather than showing any error message. Used when not sure a valid & finalised transaction is in the POST environment, but you want to try just in case (e.g. on a redirect back from the gateway).
     * @return ?array A long tuple of collected data. Emulates some of the key variables of the PayPal IPN response (null: no transaction; will only return null when $silent_fail is set).
     */
    public function handle_ipn_transaction($silent_fail)
    {
        $txn_id = post_param_string('trans_id');

        if (substr($txn_id, 0, 7) == 'subscr_') { // "subscr_" was added in by us explicitly in ECOM_SUBSCRIPTION_BUTTON_VIA_SECPAY.tpl
            $is_subscription = true;
            $txn_id = substr($txn_id, 7);
        } else {
            $is_subscription = false;
        }

        $trans_expecting_id = $txn_id;

        $transaction_rows = $GLOBALS['SITE_DB']->query_select('ecom_trans_expecting', array('*'), array('id' => $trans_expecting_id), '', 1);
        if (!array_key_exists(0, $transaction_rows)) {
            if ($silent_fail) {
                return null;
            }
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $transaction_row = $transaction_rows[0];

        $member_id = $transaction_row['e_member_id'];
        $type_code = $transaction_row['e_type_code'];
        $item_name = $transaction_row['e_item_name'];
        $purchase_id = $transaction_row['e_purchase_id'];

        $code = post_param_string('code');
        $success = ($code == 'A');
        $message = post_param_string('message');
        if ($message == '') {
            switch ($code) {
                case 'P:A':
                    $message = do_lang('PGE_A');
                    break;
                case 'P:X':
                    $message = do_lang('PGE_X');
                    break;
                case 'P:P':
                    $message = do_lang('PGE_P');
                    break;
                case 'P:S':
                    $message = do_lang('PGE_S');
                    break;
                case 'P:E':
                    $message = do_lang('PGE_E');
                    break;
                case 'P:I':
                    $message = do_lang('PGE_I');
                    break;
                case 'P:C':
                    $message = do_lang('PGE_C');
                    break;
                case 'P:T':
                    $message = do_lang('PGE_T');
                    break;
                case 'P:N':
                    $message = do_lang('PGE_N');
                    break;
                case 'P:M':
                    $message = do_lang('PGE_M');
                    break;
                case 'P:B':
                    $message = do_lang('PGE_B');
                    break;
                case 'P:D':
                    $message = do_lang('PGE_D');
                    break;
                case 'P:V':
                    $message = do_lang('PGE_V');
                    break;
                case 'P:R':
                    $message = do_lang('PGE_R');
                    break;
                case 'P:#':
                    $message = do_lang('PGE_HASH');
                    break;
                case 'C':
                    $message = do_lang('PGE_COMM');
                    break;
                default:
                    $message = do_lang('UNKNOWN');
            }
        }

        $status = $success ? 'Completed' : 'Failed';
        $reason = '';
        $pending_reason = '';
        $memo = $transaction_row['e_memo'];
        $_amount = post_param_string('amount');
        $amount = ($_amount == '') ? null : floatval($_amount);
        $currency = post_param_string('currency', get_option('currency')); // May be blank for subscription
        $parent_txn_id = '';
        $period = '';

        // Subscription stuff
        if (get_param_integer('subc', 0) == 1) {
            if (!$success) {
                $status = 'SCancelled';
            }
        }

        // SECURITY
        $hash = post_param_string('hash');
        if ($is_subscription) {
            $my_hash = md5('trans_id=' . $txn_id . '&' . 'req_cv2=true' . '&' . get_option('payment_gateway_digest'));
        } else {
            $repeat = $this->_translate_subscription_details($transaction_row['e_length'], $transaction_row['e_length_units']);
            $my_hash = md5('trans_id=' . $txn_id . '&' . 'req_cv2=true' . '&' . 'repeat=' . $repeat . '&' . get_option('payment_gateway_digest'));
        }
        if ($hash != $my_hash) {
            if ($silent_fail) {
                return null;
            }
            fatal_ipn_exit(do_lang('IPN_UNVERIFIED'));
        }

        $this->store_shipping_address($trans_expecting_id, $txn_id);

        // We need to echo the output of our finish page to SecPay's IPN caller
        if ($success) {
            $_url = build_url(array('page' => 'purchase', 'type' => 'finish', 'type_code' => $transaction_row['e_type_code']), get_module_zone('purchase'));
        } else {
            $_url = build_url(array('page' => 'purchase', 'type' => 'finish', 'type_code' => $transaction_row['e_type_code'], 'cancel' => 1, 'message' => do_lang_tempcode('DECLINED_MESSAGE', $message)), get_module_zone('purchase'));
        }
        $url = $_url->evaluate();
        echo http_get_contents($url, array('trigger_error' => false));

        $tax = null;

        return array($trans_expecting_id, $txn_id, $type_code, $item_name, $purchase_id, $is_subscription, $status, $reason, $amount, $tax, $currency, $parent_txn_id, $pending_reason, $memo, $period, $member_id);
    }

    /**
     * Store shipping address for a transaction.
     *
     * @param  ID_TEXT $trans_expecting_id Expected transaction ID
     * @param  ID_TEXT $txn_id Transaction ID
     * @return AUTO_LINK Address ID
     */
    public function store_shipping_address($trans_expecting_id, $txn_id)
    {
        $_name = explode(' ', post_param_string('ship_name', ''));
        $name = array();
        if (count($_name) > 0) {
            $name[1] = $_name[count($_name) - 1];
            unset($_name[count($_name) - 1]);
        }
        $name[0] = implode(' ', $_name);

        $shipping_address = array(
            'a_firstname' => $name[0],
            'a_lastname' => trim($name[1] . ', ' . post_param_string('ship_company', ''), ' ,'),
            'a_street_address' => trim(post_param_string('ship_addr_1', '') . "\n" . post_param_string('ship_addr_2', '')),
            'a_city' => post_param_string('ship_city', ''),
            'a_county' => '',
            'a_state' => post_param_string('ship_state', ''),
            'a_post_code' => post_param_string('ship_post_code', ''),
            'a_country' => post_param_string('ship_country', ''),
            'a_email' => '',
            'a_phone' => post_param_string('ship_tel', ''),
        );
        return store_shipping_address($trans_expecting_id, $txn_id, $shipping_address);
    }

    /**
     * Find whether the hook auto-cancels (if it does, auto cancel the given subscription).
     *
     * @param  AUTO_LINK $subscription_id ID of the subscription to cancel
     * @return ?boolean True: yes. False: no. (null: cancels via a user-URL-directioning)
     */
    public function auto_cancel($subscription_id)
    {
        $username = $this->_get_username();
        $password = get_option('payment_gateway_password');
        $vpn_password = get_option('payment_gateway_vpn_password');

        $txn_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_transactions', 'id', array('t_purchase_id' => strval($subscription_id)));
        if ($txn_id === null) {
            return false;
        }
        $txn_id = 'subscr_' . $txn_id;

        require_code('xmlrpc');
        $result = xml_rpc('https://www.secpay.com:443/secxmlrpc/make_call', 'SECVPN.repeatCardFullAddr', array($username, $vpn_password, $txn_id, -1, $password, '', '', '', '', '', 'repeat_change=true,repeat=false'));
        $map = $this->_parse_result($result);
        $success = ((array_key_exists('code', $map)) && (($map['code'] == 'A') || ($map['code'] == 'P:P')));

        return $success;
    }

    /**
     * Perform a transaction (local not remote).
     *
     * @param  ID_TEXT $trans_expecting_id Our internal temporary transaction ID
     * @param  SHORT_TEXT $cardholder_name Cardholder name
     * @param  SHORT_TEXT $card_type Card Type
     * @set    "Visa" "Master Card" "Switch" "UK Maestro" "Maestro" "Solo" "Delta" "American Express" "Diners Card" "JCB"
     * @param  integer $card_number Card number
     * @param  SHORT_TEXT $card_start_date Card Start date (blank: none)
     * @param  SHORT_TEXT $card_expiry_date Card Expiry date (blank: none)
     * @param  ?integer $card_issue_number Card Issue number (null: none)
     * @param  integer $card_cv2 Card CV2 number (security number)
     * @param  float $amount Transaction amount
     * @param  ID_TEXT $currency The currency
     * @param  LONG_TEXT $billing_street_address Street address (billing, i.e. AVS)
     * @param  SHORT_TEXT $billing_city Town/City (billing, i.e. AVS)
     * @param  SHORT_TEXT $billing_county County (billing, i.e. AVS)
     * @param  SHORT_TEXT $billing_state State (billing, i.e. AVS)
     * @param  SHORT_TEXT $billing_post_code Postcode/Zip (billing, i.e. AVS)
     * @param  SHORT_TEXT $billing_country Country (billing, i.e. AVS)
     * @param  SHORT_TEXT $shipping_firstname First name (shipping)
     * @param  SHORT_TEXT $shipping_lastname Last name (shipping)
     * @param  LONG_TEXT $shipping_street_address Street address (shipping)
     * @param  SHORT_TEXT $shipping_city Town/City (shipping)
     * @param  SHORT_TEXT $shipping_county County (shipping)
     * @param  SHORT_TEXT $shipping_state State (shipping)
     * @param  SHORT_TEXT $shipping_post_code Postcode/Zip (shipping)
     * @param  SHORT_TEXT $shipping_country Country (shipping)
     * @param  SHORT_TEXT $shipping_email E-mail address (shipping)
     * @param  SHORT_TEXT $shipping_phone Phone number (shipping)
     * @param  ?integer $length The subscription length in the units. (null: not a subscription)
     * @param  ?ID_TEXT $length_units The length units. (null: not a subscription)
     * @set    d w m y
     * @return array A tuple: success (boolean), message (string), raw message (string), transaction ID (string)
     */
    public function do_local_transaction($trans_expecting_id, $cardholder_name, $card_type, $card_number, $card_start_date, $card_expiry_date, $card_issue_number, $card_cv2, $amount, $currency, $billing_street_address, $billing_city, $billing_county, $billing_state, $billing_post_code, $billing_country, $shipping_firstname = '', $shipping_lastname = '', $shipping_street_address = '', $shipping_city = '', $shipping_county = '', $shipping_state = '', $shipping_post_code = '', $shipping_country = '', $shipping_email = '', $shipping_phone = '', $length = null, $length_units = null)
    {
        // https://www.secpay.com/xmlrpc/

        $username = $this->_get_username();
        $vpn_password = get_option('payment_gateway_vpn_password');
        $digest = md5($trans_expecting_id . float_to_raw_string($amount) . get_option('payment_gateway_password'));
        $options = 'currency=' . $currency . ',card_type=' . str_replace(',', '', $card_type) . ',digest=' . $digest . ',cv2=' . strval($card_cv2) . ',mand_cv2=true';
        if (ecommerce_test_mode()) {
            $options .= ',test_status=true';
        }
        if ($length !== null) {
            list($length_units_2, $first_repeat) = $this->_translate_subscription_details($length, $length_units);
            $options .= ',repeat=' . $first_repeat . '/' . $length_units_2 . '/0/' . float_to_raw_string($amount);
        }

        $item_name = $GLOBALS['SITE_DB']->query_select_value('ecom_trans_expecting', 'e_item_name', array('id' => $trans_expecting_id));

        $shipping_street_address_lines = explode("\n", $shipping_street_address, 2);
        $shipping_address = 'ship_name=' . $shipping_firstname . ' ' . $shipping_lastname . ',';
        $shipping_address .= 'ship_addr_1=' . $shipping_street_address_lines[0] . ',';
        $shipping_address .= 'ship_addr_2=' . (isset($shipping_street_address_lines[1]) ? $shipping_street_address_lines[1] : '') . ',';
        $shipping_address .= 'ship_city=' . $shipping_city . ',';
        $shipping_address .= 'ship_state=' . $shipping_state . ',';
        $shipping_address .= 'ship_country=' . $shipping_country . ',';
        $shipping_address .= 'ship_post_code=' . $shipping_post_code . ',';
        $shipping_address .= 'ship_tel=' . $shipping_phone . ',';
        $shipping_address .= 'ship_email=' . $shipping_email;

        $billing_street_address_lines = explode("\n", $billing_street_address, 2);
        $billing_address = 'bill_addr_1=' . $billing_street_address_lines[0] . ',';
        $billing_address .= 'bill_addr_2=' . (isset($billing_street_address_lines[1]) ? $billing_street_address_lines[1] : '') . ',';
        $billing_address .= 'bill_city=' . $billing_city . ',';
        $billing_address .= 'bill_state=' . $billing_state . ',';
        $billing_address .= 'bill_country=' . $billing_country . ',';
        $billing_address .= 'bill_post_code=' . $billing_post_code;

        require_code('xmlrpc');
        if ($length !== null) {
            $trans_expecting_id = 'subscr_' . $trans_expecting_id;
        }
        $result = xml_rpc('https://www.secpay.com:443/secxmlrpc/make_call', 'SECVPN.validateCardFull', array($username, $vpn_password, $trans_expecting_id, get_ip_address(), $cardholder_name, $card_number, float_to_raw_string($amount), $card_expiry_date, ($card_issue_number === null) ? '' : strval($card_issue_number), $card_start_date, $currency, '', '', $options, $item_name, $shipping_address, $billing_address));
        $map = $this->_parse_result($result);

        $success = ((array_key_exists('code', $map)) && (($map['code'] == 'A') || ($map['code'] == 'P:P')));
        $message_raw = array_key_exists('message', $map) ? $map['message'] : do_lang('INTERNAL_ERROR');
        $message = $success ? do_lang_tempcode('ACCEPTED_MESSAGE', escape_html($message_raw)) : do_lang_tempcode('DECLINED_MESSAGE', escape_html($message_raw));

        return array($success, $message, $message_raw, $trans_expecting_id);
    }

    /**
     * Parse the result of the XMLRPC call.
     *
     * @param  string $result The result
     * @return array The map of result data
     */
    protected function _parse_result($result)
    {
        $pos_1 = strpos($result, '<value>');
        if ($pos_1 === false) {
            fatal_exit(do_lang('INTERNAL_ERROR'));
        }
        $pos_2 = strpos($result, '</value>');
        $value = @html_entity_decode(trim(substr($result, $pos_1 + 7, $pos_2 - $pos_1 - 7)), ENT_QUOTES);
        if (substr($value, 0, 1) == '?') {
            $value = substr($value, 1);
        }
        $_map = explode('&', $value);
        $map = array();
        foreach ($_map as $x) {
            $explode = explode('=', $x);
            if (count($explode) == 2) {
                $map[$explode[0]] = $explode[1];
            }
        }

        return $map;
    }
}
