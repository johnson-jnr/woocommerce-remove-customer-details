<?php
/*
** 
 * Plugin Name:            Woocommerce Remove Customer Details
 * Description:            Remove Customer Email and/or Phone From Woocommerce New Order Email
 * Version:                1.0 
 * Author:                 Johnson Towoju (Figarts)
 * Author URI:             www.figarts.co
 * License:                GPL-2.0+
 * License URI:            http://www.gnu.org/licenses/gpl-2.0.txt
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Check if WooCommerce is active
 **/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	function wrcds_filter_email_recipients($recipients) {
		$recipients = array_map( 'trim', explode( ',', $recipients ) );
		$recipients = array_filter( $recipients, 'is_email' );
		return implode( ', ', $recipients );
	}

	add_action( 'woocommerce_thankyou', 'wrcds_remove_customer_details_from_email', 10, 1);
	function wrcds_remove_customer_details_from_email($order_id) {
    $order = new WC_Order($order_id);

		$new_order_email = WC()->mailer()->get_emails()['WC_Email_New_Order'];
		if ($new_order_email->get_option('enabled') == 'yes') {
			$keys = array('email', 'phone');

			foreach($keys as $key) {
				$val = $new_order_email->get_option($key);
				if ($val == 'yes') {
						$func = 'set_billing_' . $key;
						$order->$func('');
				}
			}

			$email_recipients = $new_order_email->get_option('email_to');
			$recipients = wrcds_filter_email_recipients($email_recipients);
			$new_order_email->recipient = $recipients;
			$new_order_email->trigger($order->id, $order);
		}		
	}	
	
	add_action( 'woocommerce_settings_api_form_fields_new_order', 'wrcds_add_custom_email_setting', 10, 1);
	function wrcds_add_custom_email_setting($form_fields){
	
		$form_fields['remove_customer_details'] = [
			'title'	=>	'Remove Customer Details',
			'type'	=>	'title'];

		$form_fields['email_to'] = [
			'title'				=>	'Recipient(s)',
			'type'				=>	'text',
			'description' =>	'Enter recipients (comma separated) to remove customer details from sent emails',
			'desc_tip' 		=> 	 true];
			
		$form_fields['email'] = [
			'title'			=>	'Customer Email',
			'type'			=>	'checkbox',
			'default'		=>	'no',
		];
				
		$form_fields['phone'] = [
		'title'		=>	'Customer Phone',
		'type'		=>	'checkbox',
		'default'	=>	'no'];

		return $form_fields;
	}
}
