<?php
/**
 * SimpleCosign WooCheckout Functionality
 *
 * @category  Class
 * @package   WordPress
 * @author    Matthew Cox
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://wedo-products.com
 */


add_action('woocommerce_thankyou', 'simplecosign_aftercheckout', 10, 1);
function simplecosign_aftercheckout( $order_id ) {
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
        }

        // Output some data
        echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

		/*https://user.traxia.com/app/api/transaction
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
?>