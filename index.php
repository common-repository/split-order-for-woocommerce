<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
	Plugin Name: Split Order for WooCommerce
	Plugin URI: 
	Description: This plugin split order multiple orders.
	Version: 1.0
	Author: SunArc
	Author URI: https://sunarctechnologies.com/
	Text Domain: woocommerce-split-order
	License: GPL2

*/

global $wpdb;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} else {

    clearstatcache();
}


require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
define('wos_sunarc_plugin_dir', dirname(__FILE__));


register_activation_hook(__FILE__, 'wos_plugin_activate');

function wos_plugin_activate() {
    $option_name = 'wos_splitorderpro';
    $new_value = 'default';
    update_option($option_name, $new_value);
	
}

// Deactivation Pluign 
function wos_deactivation() {
    $option_name = 'wos_auto_forced';
    $new_value = 'no';
    update_option($option_name, $new_value);
    $option_name = 'wos_splitorderpro';
    $new_value = '';
    update_option($option_name, $new_value);
}

register_deactivation_hook(__FILE__, 'wos_deactivation');

// Uninstall Pluign 
function wos_uninstall() {
    $option_name = 'wos_auto_forced';
    $new_value = 'no';
    update_option($option_name, $new_value);
    $option_name = 'wos_splitorderpro';
    $new_value = '';
    update_option($option_name, $new_value);
}


$SUNARC_all_plugins = get_plugins();

$SUNARC_activate_all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if (array_key_exists('woocommerce/woocommerce.php', $SUNARC_all_plugins) && in_array('woocommerce/woocommerce.php', $SUNARC_activate_all_plugins)) {
    $optionVal = get_option('wos_auto_forced');
    $splitDefault = get_option('wos_splitorderpro');
    if ($optionVal == 'yes' && $splitDefault == 'default') {
        require_once wos_sunarc_plugin_dir . '/inc/splitorder.php';
    } 
	else {
        
    }
}





add_action( 'woocommerce_email', 'wos_remove_hooks' );

function wos_remove_hooks( $email_class ) {
		remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
		remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
		remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
		
		// New order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		
		// Processing order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		
		// Completed order emails
		remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			
		// Note emails
		remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
}


	

add_action('woocommerce_checkout_create_order', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order( $order, $data ) {
 $order->update_meta_data( '_custom_meta_hide', 'yes' );
 

}

add_action( 'woocommerce_thankyou', 'Save_flag_for_order', 20, 1);
function Save_flag_for_order( $order_id ){
  $order = wc_get_order($order_id);
  $order->update_meta_data('Save_flag_for_order', 'yes');
  $order->save();
}


function action_woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
 $optionVal = get_option('wos_auto_forced');
	 $splitDefault = get_option('wos_splitorderpro');
	 $ordersids =  get_post_meta($order_id, 'order_ids',true);
	 	if($optionVal=='yes' ){
	if($splitDefault =='splitattributeexist' &&  $ordersids ==''){
	update_post_meta($order_id,'order_status_result','Main Order');
	
	}else
	{
	
		update_post_meta($order_id,'_order_total',0);  
		update_post_meta($order_id,'order_status_result','Main Order');  
		}
	}
}; 
add_action( 'woocommerce_checkout_order_processed', 'action_woocommerce_checkout_order_processed', 10, 3 ); 

function sun_wc_new_order_column( $columns ) {
    $columns['Sunarc_Status'] = 'Sunarc Status';
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'sun_wc_new_order_column' );

function sv_wc_cogs_add_order_profit_column_content( $column ) {
    global $post;

    if ( 'Sunarc_Status' === $column ) {
		
		echo get_post_meta($post->ID,'order_status_result',true);
    
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'sv_wc_cogs_add_order_profit_column_content' );


	 
add_filter( 'woocommerce_endpoint_order-received_title', 'sunarc_thank_you_title' );
 
function sunarc_thank_you_title( $old_title ){
	$optionVal = get_option('wos_auto_forced');
	 $splitDefault = get_option('wos_splitorderpro');
	 if ($optionVal == 'yes' && $splitDefault == 'splitaccordingattribute') {
  $order_id = wc_get_order_id_by_order_key( $_GET['key'] ); 
  update_post_meta($order_id,'_order_total',0);  
 	} ?>
	<script>
	jQuery(document).ready(function () {
    jQuery('.woocommerce-order-details__title').text('Main Order details');
});
</script>
	<?php
	 
}


if (!class_exists('wos_main_cls')) {

    class wos_main_cls {

        public function __construct() {
            add_action('init', array($this, 'init_sunarc'));
        }

        public function init_sunarc() {
			 define('wos_sunarc_version', '1.0.1');
            !defined('wos_sunarc_path') && define('wos_sunarc_path', plugin_dir_path(__FILE__));
            !defined('wos_sunarc_url') && define('wos_sunarc_url', plugins_url('/', __FILE__));

            require_once(wos_sunarc_path . 'classes/function-class.php' );

            WOS_Function_Class::instance();
        }

    }

}
new wos_main_cls();