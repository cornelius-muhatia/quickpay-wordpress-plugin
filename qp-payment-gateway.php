<?php

/**
 * Plugin Name: Qp Payment Gateway.
 * Version: 1.0.0
 * Plugin URI: https://www.quickpay.co.ke
 * Description: Quickpay Gateway is a fast and convenient way to process card payments
 * Author: Binary Limited
 * Author URI: http://www.binary.co.ke
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qp-payment-gateway
 */
defined('ABSPATH') or die('No script kiddies please!');
//load actions
add_action('admin_menu', 'qpg_load_menu');
add_action('wp_ajax_process_transactions', 'qpg_process_transactions');
add_action('wp_ajax_quickpay_checkout', 'qpg_quickpay_checkout');
add_action('wp_ajax_nopriv_quickpay_checkout', 'qpg_quickpay_checkout');
add_action('init', 'qpg_quickpay_shortcodes_init');
register_activation_hook(__FILE__, 'qpg_install');

//include global files
include("helper/CustomArrayAccess.php");

/**
 * Intialize the application with the default settings
 */
function qpg_install()
{
    include("Model.php");
    $model = new Qpg_Model();
    //create default tables that will be used by the plugin
    $model->create_schema();
    //add default settings
    $vars['qp_merchant'] = get_bloginfo('name');
    $vars['qp_merchant_desc'] = get_bloginfo('description');
    $vars['qp_button'] = "Make Payment";
    update_option('quickpay_checkout_vars', maybe_serialize($vars));
}

/**
 * Load admin menu 
 */
function qpg_load_menu()
{
    add_menu_page(__('Quickpay Checkout', 'qp-payment-gateway'), __('Quickpay', 'qp-payment-gateway'), 'manage_options', 'quickpay_checkout_options', 'qpg_plugin_options_page', plugins_url("/resources/img/qp_logo.svg", __FILE__), '4');
    add_submenu_page('quickpay_checkout_options', __('Transactions', 'qp-payment-gateway'), __('Transaction', 'qp-payment-gateway'), 'manage_options', 'quickpay_checkout_transactions', 'qpg_transaction_page');
}

/**
 * Used to load transaction view
 */
function qpg_transaction_page()
{
    if (!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    //Add resources
    wp_enqueue_style('qp-admin-css', plugins_url("/resources/css/admin.min.css", __FILE__));
    wp_enqueue_style('qp-datatables-css', plugins_url("/resources/css/datatables.min.css", __FILE__));
    wp_enqueue_script('qp-datatables-script', plugins_url('/resources/js/datatables.min.js', __FILE__), array('jquery'));
    wp_enqueue_script('qp-transactions-script', plugins_url('/resources/js/transactions.min.js', __FILE__), array('jquery', 'qp-datatables-script'));
    wp_localize_script('qp-transactions-script', 'qp_ajax', array('transactions_url' => admin_url('admin-ajax.php')));
    include("views/transaction.php");
}

/**
 * Used to load plugins options page
 */
function qpg_plugin_options_page()
{

    if (!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    wp_enqueue_style('qp-admin-css', plugins_url("/resources/css/admin.min.css", __FILE__));
    wp_enqueue_script('qp-transactions-script', plugins_url('/resources/js/admin.min.js', __FILE__), array('jquery'));

    include("views/settings.php");
}

/**
 * Create application shortcode
 */
function qpg_quickpay_shortcodes_init()
{

    function qpg_quickpay_shortcode($atts = [], $content = 'content')
    {
        // Read in existing option value from database
        $options = new Qpg_CustomArrayAccess(maybe_unserialize(get_option('quickpay_checkout_vars')), '');
        //create amount option
        $options['amt_option'] = (($options['qp_custom_amount'] == 1) || (!is_numeric($content))) ? 'req-amount-type="custom"' : 'req-amount="' . $content . '"';
        //create multiplier option
        $options['amt_multiplier'] = ($options['qp_multiplier'] == 1) ? 'true' : 'false';
        //select processing environment (test or live)
        $options['url'] = "https://checkout" . (($options['qp_environment'] == 0) ? '-test' : '') . ".quickpay.co.ke/js";
        //get action url
        $options['action_url'] = plugins_url("/quickpay-checkout/quickpay");
        //get image url
        $icon = wp_get_attachment_image_src($options['qp_icon'], null, true); //get_post($options['qp_icon']);        
        $options['img_url'] = ($icon != false) ? $icon[0] : '';

        //enque style sheets
        wp_enqueue_style('qp-frontend-css', plugins_url("/resources/css/frontend.min.css", __FILE__));
        wp_enqueue_script('qp-ajax-script', plugins_url('/resources/js/main.min.js', __FILE__), array('jquery'));
        wp_localize_script('qp-ajax-script', 'qp_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
        //intialize and create form
        include("views/checkout-form.php");
        //$form = new Form($options);
        return createForm($options);
    }

    add_shortcode('quickpay_checkout', 'qpg_quickpay_shortcode');
}

/**
 * Process quickpay checkout server side request
 */
function qpg_quickpay_checkout()
{
    include('quickpay/Checkout.php');
    include('Model.php');

    $options = new Qpg_CustomArrayAccess(maybe_unserialize(get_option('quickpay_checkout_vars')), '');
    $checkout = new Qpg_Checkout($options['qp_private_key'], new Qpg_Model());
    if ($options['qp_environment'] == 0)
    {
        $checkout->activate_test();
    }

    wp_send_json(array("message" => $checkout->process()));
    wp_die();
}

/**
 * used to process transactions datatables server side request
 */
function qpg_process_transactions()
{
    include('Model.php');
    $model = new Qpg_Model();
    wp_send_json($model->get_transactions());
    wp_die();
}

