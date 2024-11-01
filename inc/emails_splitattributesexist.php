<?php
$order_email_sent = get_post_meta($order_id, 'order_email_sent', true);
if($order_email_sent!='yes') {
		$order = new WC_Order($order_id);
		$billingemail= $order->get_billing_email();
		$getbillingfirstname= $order->get_billing_first_name();
		$getbillinglastname= $order->get_billing_last_name();
		$getbillingaddress1= $order->get_billing_address_1();
		$getbillingaddress2= $order->get_billing_address_2();
		$getbillingcity= $order->get_billing_city();
		$getbillingpostcode= $order->get_billing_postcode();
		$getbillingstate= $order->get_billing_state();
		$getbillingphone= $order->get_billing_phone();
		$to = $billingemail;
		$subject = 'Your Split order has been received!';
		$headers = array('Content-Type: text/html; charset=UTF-8');

        $woocommerceCurrency = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
		$paymentMethod = get_post_meta( $order_id, '_payment_method', true );


       $singlePost = $order_id;
        $cart_total = 0;
        if (!empty($parent_order)) {
            $cart_total = $order_id->get_total();
        }
        $wos_cart_notices = get_option('woo_sunarc_cart_notices', true);
        $co_total = $wos_cart_notices['co_total'];
        $posts_array = unserialize(get_post_meta($order_id, 'order_ids', true));
		if( count($posts_array) != 1){
			 $posts_array1 =array_pop($posts_array);
			}
		//echo '<pre>'; print_r($posts_array); echo '</pre>';
        if (!empty($posts_array)) {
            $first_order = current($posts_array);
            $order_total = 0;
            foreach ($posts_array as $post_data) {
                $this_order = wc_get_order($post_data);
                $total_amount = $this_order->get_total();
                $order_total += $total_amount;

                if (!get_option('woo_sunarc_shipping_cost', 0)) {
                    $child_order_shipping_items = $this_order->get_items('shipping');
                    if (!empty($child_order_shipping_items)) {
                        foreach ($child_order_shipping_items as $item_id => $item_data) {
                            wc_delete_order_item($item_id);
                        }
                    }
                }
                $this_order->calculate_totals();
            }
            $response = '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">';
            $response .= '<tbody><tr><h3 style="text-align: center;">Thank you for your order Number #'.implode(", #",$posts_array).'</h3></tr><tr>';
            $response .= '<td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:rgb(255,255,255);border-radius:3px">';
            $response .= '<tbody><tr><td valign="top" style="padding:20px 0;color:rgb(99,99,99);font-family:Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left"><h2 style="margin: 0 0 20px; color: rgb(150,88,138); font-size: 30px;">' . $wos_cart_notices['co_heading'] ? $wos_cart_notices['co_heading'] : 'Order' . (count($posts_array) > 1 ? 's' : '') . '</h2>';
            $response .= '<table cellspacing="0" cellpadding="6" border="1" style="color:rgb(99,99,99);border:1px solid rgb(229,229,229);vertical-align:middle;width:100%;font-family:Roboto,Arial,sans-serif">';
            $response .= '<tbody>';
            $cart_total += $order_total;
			
            foreach ($posts_array as $post_data) {
                $child_order = wc_get_order($post_data);
                $_payment_method = $child_order->get_payment_method_title();
                $child_order_data = $child_order->get_data();
				
                $response .= '<tr><th style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(255,255,255);vertical-align:middle;padding:12px;text-align:left; width: 40%;"><h2 style="margin: 0; color: rgb(150,88,138); font-size: 20px;">Order number #' . $post_data . '</h2></th></tr>';
                $response .= '<tr><th scope="row" style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left; width: 40%;"><h2 style="margin: 0; color: rgb(150,88,138); font-size: 16px;">Product</h2></th>';
                $response .= '<td style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left; width: 60%;font-weight:normal;"><h2 style="margin: 0; color: rgb(150,88,138); font-size: 16px;">Total</h2></td></tr>';

                foreach ($child_order->get_items() as $order_items) {
                    $response .= '<tr><th scope="row" style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left; width: 40%;"><a href="' . get_permalink($order_items->get_product_id()) . '">' . $order_items['name'] . '</a><strong class="product-quantity">*' . $order_items->get_quantity() . '</th>';
                    $response .= '<td style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left; width: 60%;font-weight:normal;">' . $woocommerceCurrency . number_format($order_items->get_total(), 2) . '</td></tr>';
                }
                $response .= '<tr><th scope="row" style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 40%;">Tax:</th>';
                $response .= '<td style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 60%;">' . $woocommerceCurrency . number_format($child_order_data['total_tax'], 2) . '</td></tr>';

                $response .= '<tr><th scope="row" style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 40%;">Payment method:</th>';
                $response .= '<td style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 60%;">' . $paymentMethod . '</td></tr>';

                $response .= '<tr><th scope="row" style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 40%;">Total:</th>';
                $response .= '<td style="color:rgb(99,99,99);border-width:1px 1px 1px;border-style:solid;border-color:rgb(229,229,229);vertical-align:middle;padding:12px;text-align:left;width: 60%;">' . $woocommerceCurrency . number_format($child_order->get_total(), 2) . '</td></tr>';
            }
            $response .= '</tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
        }
		$response .= '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="600px" style="margin: 0 auto;">
	<tbody><tr><td valign="top" width="50%" style="text-align:left;font-family:Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;border:0;padding:0">
			<h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Billing address</h2>
			<address style="padding:12px;color:#636363;border:1px solid #e5e5e5">
				'.$getbillingfirstname.' ' .$getbillinglastname.'<br>'.$getbillingaddress1.'<br>'.$getbillingaddress2.'<br>'.$getbillingcity. ' ' .$getbillingpostcode.'<br>'.$getbillingstate.'<br>'.$getbillingphone.'<br><a href="mailto:'.$billingemail.'" target="_blank">'.$billingemail.'</a></address>
		</td></tr></tbody></table>';
	// echo $response;

 
wp_mail( $to, $subject, $response, $headers );
	 }
 $posts_array = unserialize(get_post_meta($order_id, 'order_ids', true));
 if(!empty($posts_array)) {
  update_post_meta($order_id, 'order_email_sent', 'yes');
 }
	 ?>