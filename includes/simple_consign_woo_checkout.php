<?php
/**
 * SimpleConsign WooCheckout Functionality
 *
 * @category  Class
 * @package   WordPress
 * @author    Matthew Cox
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://wedo-products.com
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}

add_action('woocommerce_thankyou', 'simpleconsign_aftercheckout', 10, 1);
function simpleconsign_aftercheckout( $order_id ) {
    if ( ! $order_id )
        return;

    // Allow code execution only once 
    if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

        if($order->is_paid())
            $paid = __('yes');
        else
            $paid = __('no');

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();
            // Get the product Id
            $product_id = $product->get_id();
            // Get the product name
            $product_id = $item->get_name();
            $quantity = $item->get_quantity();
            $line_total = $item->get_total();

            $items_array[] = array(
                'sku' => $product->sku,
                'price' => $line_total,
                'quantity' => $quantity,
            );
        }

        $order_id = $order->get_order_number();
        $order_total = $order->get_total();
        $order_tax = $order->get_total_tax();
        $order_subtotal = $order->get_subtotal();
        // Output some data
        echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

        $data_array = array('key' => $apikey,
                            'orderNumber' => $order_id,
                            'type' => 'SALE',
                            'nonTaxableSaleTotal' => $order_subtotal,
                            'taxableSaleTotal' => $order_total,
                            'tax' => $order_tax,
                            'items' => $items_array);


		$data = $data_array;
		$data_string = json_encode($data);
		$context = stream_context_create(array(
			'http' => array(
				'method' => "POST",
				'header' => "Accept: application/json\r\n".
							"Content-Type: application/json\r\n",
				'content' => $data_string
			)
		));
		  
		$result = file_get_contents('https://user.traxia.com/app/api/transaction', false, $context);

/*
		https://user.traxia.com/app/api/transaction
{
   "key":"Your API key here",
   "orderNumber":"1234",
   "type":"SALE",
   "nonTaxableSaleTotal": 7998,
   "taxableSaleTotal": 0,
   "tax": 0,
   "items": [
      {
         "sku": "GFFY72",
         "price": 5400,
         "quantity": 1
      },
      {
         "sku": "AD4J56",
         "price": 1299,
         "quantity": 2
      }
   ]
}
*/
		// Flag the action as done (to avoid repetitions on reload for example)
		/*
        $order->update_meta_data( '_thankyou_action_done', true );
        $order->save();
    }
    */

}
}
?>