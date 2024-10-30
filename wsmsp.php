<?php
/**
* Plugin Name: Minimum Signup Period For WooCommerce Subscriptions
* Plugin URI: https://www.elliotvs.co.uk/plugins/minimum-signup-period-for-woocommerce-subscriptions
* Description: Allows you to create a minimum signup period for WooCommerce subscriptions. The customer will pay for the full initial minimum period upfront, then after that period ends, the subscription will renew as normal each month. Doesn't use "trial" period and works with product addons etc.
* Version: 1.1.0
* Author: RelyWP
* Author URI: https://www.relywp.com
* License: GPLv3
*
* WC requires at least: 3.4
* WC tested up to: 6.5.1
**/

include plugin_dir_path( __FILE__ ) . 'admin-options.php';

// Redirect to settings on activate
register_activation_hook( __FILE__, 'wsmsp_my_plugin_activate' );
add_action( 'admin_init', 'wsmsp_my_plugin_redirect' );
function wsmsp_my_plugin_activate()
{
	add_option( 'wsmsp_my_plugin_do_activation_redirect', true );
}

function wsmsp_my_plugin_redirect()
{
	if ( get_option( 'wsmsp_my_plugin_do_activation_redirect', false ) ) {
		delete_option( 'wsmsp_my_plugin_do_activation_redirect' );
		wp_redirect( "/wp-admin/admin.php?page=wsmsp" );
	}
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wsmsp_settings_link' );
function wsmsp_settings_link( array $links ) {
    $url = get_admin_url() . "options-general.php?page=wsmsp";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'textdomain') . '</a>';
    $links[] = $settings_link;
    return $links;
}

// CHECK IF MONTHLY
function wsmsp_is_cart_monthly() {
	
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        if(!empty($product)){
			$user_id = wp_get_current_user();
			$productitle = $product->get_name();
			if (strpos($productitle, 'Monthly') !== false) {
				return true;
			}
        }
    }
	
	return false;
	
}

// CHECK IF SUBSCRIPTION IN CART
function wsmsp_check_cart_subscription() {
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        if(!empty($product)){
			if ( class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
				return true;
			}
        }
    }
	return false;
}

// CHECK IF BOTH SUBSCRIPTION AND NORMAL ITEMS IN CART
add_action( 'woocommerce_before_checkout_form', 'wsmsp_check_cart', 10, 0 );
add_action( 'woocommerce_before_cart', 'wsmsp_check_cart', 10, 0 );
add_action( 'woocommerce_applied_coupon', 'wsmsp_check_cart', 10, 0 );
add_action( 'woocommerce_update_cart_action_cart_updated', 'wsmsp_check_cart', 10, 0 );
function wsmsp_check_cart() {
	
	$options = get_option( 'wsmsp_options' );
	
	$disable_normal = $options['wsmsp_disable_normal'];
	if($disable_normal == "") { $disable_normal = 1; }
	
	if($disable_normal) {
		if(wsmsp_check_cart_subscription()) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product = $cart_item['data'];
				if(!empty($product)){
					if ( class_exists( 'WC_Subscriptions_Product' ) && !WC_Subscriptions_Product::is_subscription( $product ) ) {
						wc_add_notice( sprintf( __( "Sorry, you can't add '%s' to the same cart as a subscription.", "woo-coupon-usage"), $product->name), "error" );
					   WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
		}
	}
	
}

// CHANGE CHECKOUT PRICE
add_action( 'woocommerce_before_calculate_totals', 'wsmsp_modify_checkout_price', 20, 1);
function wsmsp_modify_checkout_price( $cart_obj ) {

	if ( is_admin() && !defined( 'DOING_AJAX' ) )
		return;

	$options = get_option( 'wsmsp_options' );
	$numbermonths = $options['wsmsp_field_number_months'];

	if(!$numbermonths) { $numbermonths = 1; }

	if(wsmsp_is_cart_monthly() && is_checkout()) {

		foreach ( $cart_obj->get_cart() as $cart_item ) {
			$cart_item['data']->set_price( $cart_item['data']->get_price() * $numbermonths );
		}

	}
	
}

// CHANGE CART PRICE
add_action( 'woocommerce_before_cart_totals', 'wsmsp_modify_cart_price' );
function wsmsp_modify_cart_price() {
	
    if ( is_admin() && !defined( 'DOING_AJAX' ) )
        return;
	
	$options = get_option( 'wsmsp_options' );
	$numbermonths = $options['wsmsp_field_number_months'];
	if(!$numbermonths) { $numbermonths = 1; }
	if(wsmsp_is_cart_monthly()) {
		
		WC()->cart->subtotal = ( WC()->cart->subtotal - WC()->cart->get_subtotal_tax() ) * $numbermonths;

		WC()->cart->total = WC()->cart->total * $numbermonths;
		
		$subtotal_tax = WC()->cart->get_total_tax();
		$subtotal_tax = $subtotal_tax * $numbermonths;
		?>

		<style>
		.includes_tax:not(.recurring-total) .woocommerce-Price-amount.amount, .cart-subtotal-tax:not(.recurring-total) .woocommerce-Price-amount.amount,
			.tax-rate:not(.recurring-total) .woocommerce-Price-amount.amount {
			visibility: hidden;
			font-size: 0;
			display: flex;
		}
		.includes_tax:not(.recurring-total) .woocommerce-Price-amount.amount::after, .cart-subtotal-tax:not(.recurring-total) .woocommerce-Price-amount.amount::after,
			.tax-rate:not(.recurring-total) .woocommerce-Price-amount.amount::after {
			content: " <?php echo html_entity_decode(get_woocommerce_currency_symbol()) . number_format( $subtotal_tax, 2, ".", ""); ?>";
			visibility: visible;
			font-size: initial !important;
		}
		</style>

		<?php
		
	}
	
}

// Adds Custom Text
add_action( 'woocommerce_review_order_before_payment', 'wsmsp_modify_cart_text_after');
add_action( 'woocommerce_proceed_to_checkout', 'wsmsp_modify_cart_text_after');
function wsmsp_modify_cart_text_after( $cart_obj ) {
	
	global $woocommerce;

	if( wsmsp_is_cart_monthly() ) {

		$options = get_option( 'wsmsp_options' );
		$currency = get_woocommerce_currency_symbol();

		if($options['wsmsp_field_text']) {
		$option_text = $options['wsmsp_field_text'];
		} else {
		$option_text = "The first {number} months will be billed upfront today.
		<br/><br/>
		You will then be billed the 'recurring total' every month starting on the date: {date}";
		}

		if($options['wsmsp_field_number_months']) { $numbermonths = $options['wsmsp_field_number_months']; } else { $numbermonths = 1; }

		$date = date("jS \of F Y", strtotime(" +".$numbermonths." months"));
		$option_text = str_replace("{number}", $numbermonths, $option_text);
		$option_text = str_replace("{date}", $date, $option_text);

		if(wsmsp_is_cart_monthly()) {
		?>
			<style>
			.subscription-price {
				visibility: hidden;
				font-size: 0;
				display: flex;
			}
			.subscription-price::after {
				content: '<?php echo html_entity_decode(get_woocommerce_currency_symbol()) . number_format( WC()->cart->total / $numbermonths, 2, ".", ""); ?>';
				visibility: visible;
				font-size: initial !important;
			}
			</style>
		<?php
		}
		?>
		<style>.first-payment-date, .recurring-total, .recurring-totals { display: none; }</style>

		<table class="shop_table">
			<tr>
				<th>Recurring Total</th>
				<td>
					<strong>
						<span class="woocommerce-Price-amount amount">
							<bdi>
								<span class="woocommerce-Price-currencySymbol"><?php echo $currency ?></span><?php echo number_format( WC()->cart->total / $numbermonths, 2, ".", ""); ?> (incl. VAT)
							</bdi>
						</span>
					</strong>
				</td>
			</tr>
		</table>

		<?php
		echo "<div style='margin-top: 10px; border: 4px solid #f3f3f3;padding: 10px;'><strong style='color: green;'>" . $option_text . "</strong></div><br/><br/>";
		
	}
	
}

// Adds Custom Text
add_action( 'woocommerce_review_order_after_cart_contents', 'wsmsp_modify_cart_text_total');

function wsmsp_modify_cart_text_total( $cart_obj ) {
	
	$options = get_option( 'wsmsp_options' );

	if($options['wsmsp_field_text2']) {
		$option_text2 = $options['wsmsp_field_text2'];
	} else {
		$option_text2 = "for the first {number} months.";
	}

	if($options['wsmsp_field_number_months']) { $numbermonths = $options['wsmsp_field_number_months']; } else { $numbermonths = 1; }

	$date = date("jS \of F Y", strtotime(" +".$numbermonths." months"));
	$option_text2 = str_replace("{number}", $numbermonths, $option_text2);
	$option_text2 = str_replace("{date}", $date, $option_text2);

	if(wsmsp_is_cart_monthly()) {
	?>
		<style>
		.cart-subtotal:not(.recurring-total) span.amount::after {
			content: " <?php echo $option_text2; ?>";
		}
		</style>
	<?php
	}
	
}

// Update Renewal Date & Renewal Total
add_action('woocommerce_checkout_subscription_created', 'a_wsmsp_next_payment_date_change', 10, 3); 
function a_wsmsp_next_payment_date_change( $subscription, $order, $recurring_cart ){
	
	$options = get_option( 'wsmsp_options' );
	$numbermonths = $options['wsmsp_field_number_months'];
	$numbermonthsdate = $options['wsmsp_field_number_months'] - 1;
	
	$subid = $subscription->get_id();

	$items = $order->get_items();
	
	foreach ( $items as $item ) {
		
		$productitle = $item['name'];
		
		if(strpos($productitle, 'Monthly') !== false) {
				
			// Update Date
			$nextdate = get_post_meta( $subid, '_schedule_next_payment', true );
			$newdate = date( 'Y-m-d H:i:s', strtotime( '+'.$numbermonthsdate.' month', strtotime( $nextdate )) );
			update_post_meta( $subid , '_schedule_next_payment', $newdate);

			// Update Price

			$getid = $item->get_product_id();
			$gettype = $item->get_type();
			$getname = $item->get_name();
			$getsubtotal = $item->get_subtotal();
			$gettotal = $item->get_total();
			$getmeta = $item->get_meta_data();

			foreach ( $subscription->get_items() as $item_id => $item ) {
				wc_delete_order_item( $item_id );
			}

			$newsubtotal = $getsubtotal / $numbermonths;
			$newtotal = $gettotal / $numbermonths;

			$subscription->add_product( wc_get_product($getid), 1, [
				'subtotal'     => $newsubtotal,
				'total'        => $newtotal,
			] );

			foreach ( $subscription->get_items() as $item_id => $item ) {
				$item->set_total($newtotal);
			}

		}
		
	}
	
	$subscription = wc_get_order( $subid );
	$subscription->calculate_taxes();
	$subscription->calculate_totals( true );
	$subscription->save();
	
}