<?php
/**
 * SimpleCosign Functionality
 *
 * @category  Class
 * @package   WordPress
 * @author    Matthew Cox
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://wedo-products.com
 */
/*
API Callback Quick information:
	 - All avilable values 

    $SCitem->name;
    $SCitem->category;  
    $SCitem->sku;
    $SCitem->description;
    $SCitem->size;
    $SCitem->familyGroup;
    $SCitem->brand;
    $SCitem->state;
    $SCitem->dateCreated;
    $SCitem->lastUpdated;
    $SCitem->status;
    $SCitem->quantity;
    $SCitem->cost;
    $SCitem->retail;
    $SCitem->discount; 
    $SCitem->images
    $SCitem->consignmentItem;
    $SCitem->doNotDiscount;
    $SCitem->ecommerceItem;
    $SCitem->used;
    $SCitem->dropShip;
    $SCitem->height;
    $SCitem->width;
    $SCitem->depth;
    $SCitem->weight;
    $SCitem->lengthUnit;
    $SCitem->weightUnit;
	$SCitem->currentPrice;
	
  */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}
/*
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
    }*/

/**
 * Class to manage breadcrumbs and vendor's custom fields.
 */
class Simple_Cosign_Class_Functionality {

	/**
	 * Constructor
	 *
	 * @return void
	 */

	private $wpdb;
	public $lastupdated;

	public function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;
		$this->apikey = get_option('simple_cosign_apikey');

	}
	/**
	* Run before construct
	*
	* @return void
	*/
	public function run($lastupdated) {

	$this->lastupdated = $lastupdated;

	update_option( 'simple_cosign_triggerapi', '' );
	
	$apikey = $this->apikey;
	$apidata = json_decode($this->apipull($apikey));
	$apiloop = $apidata->results;
	$outof_total = count($apiloop);
	$outof = 1;

	foreach ($apiloop as $SCitem)
	{
		ob_end_flush();
		echo '('.$outof.'/'.$outof_total.')';
		$this->create_new_product($SCitem);
		ob_start();
		$outof++;
	}

	}
	/**
	* Pull API Data
	*
	* @param mixed $apikey
	*
	* @return string|bool
	*/
	public function apipull($apikey) {
		
		//last updated time for updating.
		//'modifiedSince' => $this->lastupdated, 'includeInactiveItems' => true,
		$data = array('key' => $apikey, 'includeInactiveItems' => true, 'includeItemsWithQuantityZero' => true);
		$data_string = json_encode($data);
		$context = stream_context_create(array(
			'http' => array(
				'method' => "POST",
				'header' => "Accept: application/json\r\n".
							"Content-Type: application/json\r\n",
				'content' => $data_string
			)
		));
		  
		$result = file_get_contents('http://user.traxia.com/app/api/inventory', false, $context);

		return $result;
	}
	/**
	* Create New Woo Product
	*
	* @param mixed $SCitem
	*
	* @return void
	*/
	public function create_new_product($SCitem) {

		//If SKU does not exist then create a new product in Woocommerce.
		if ($SCitem->status == 'ACTIVE' && $SCitem->quantity > 0)
		{
			//Check if Category is already created.
			$lower_cat = strtolower($SCitem->category);
			$catcheck = ucwords($lower_cat);
			if ( !has_term( $catcheck, 'product_cat' ) )
			{
				wp_insert_term( $catcheck, 'product_cat', array(
					'description' => '',
					'parent' => 0,
				) );
				//echo $catcheck;
			}
			if (!empty(wc_get_product_id_by_sku($SCitem->sku)))
			{
				echo 'UPDATED - '.$SCitem->sku.' - '.$catcheck.'<br>';
				/*echo wp_filter_nohtml_kses($SCitem->name).'<br>
					-- SKU:'.$SCitem->sku.'<br>
					-- DESC: '.$SCitem->description.'<br>
					-- CAT: '.ucwords($lower_cat).'<br>
					-- NOT IN DB<br>
					-- WCID: '. wc_get_product_id_by_sku($SCitem->sku). '<br>
					-- IMAGES: '.print_r($SCitem->images).'<br>
					-- STATUS: '.$SCitem->status.'<br>';*/
				$post_id = wc_get_product_id_by_sku($SCitem->sku);
				$post_array = array(
					'ID'           => $post_id,
					'post_category' => $catcheck,
					'post_title'   => wp_filter_nohtml_kses($SCitem->name),
					'post_content' => wp_filter_kses($SCitem->description),
					'post_excerpt' => mb_strimwidth(wp_filter_kses($SCitem->description), 0, 255, "...")
				);
				
				wp_update_post( $post_array );

				$term = get_term_by('name', $catcheck, 'product_cat');

				wp_set_object_terms($post_id , $term->term_id, 'product_cat');

					
			}
			else
			{
				echo 'INSERTED - '.$SCitem->sku.' - '.$catcheck.'<br>';
				/*echo wp_filter_nohtml_kses($SCitem->name).'<br>
					-- SKU:'.$SCitem->sku.'<br>
					-- DESC: '.$SCitem->description.'<br>
					-- CAT: '.ucwords($lower_cat).'<br>
					-- NOT IN DB<br>
					-- WCID: '. wc_get_product_id_by_sku($SCitem->sku). '<br>
					-- IMAGES: '.print_r($SCitem->images).'<br>
					-- STATUS: '.$SCitem->status.'<br>';
				*/
				$post_id = wp_insert_post(array(
					'post_title' => wp_filter_nohtml_kses($SCitem->name),
					'post_category' => $catcheck,
					'post_type' => 'product',
					'post_status' => 'publish',
					'post_content' => wp_filter_kses($SCitem->description),
					'post_excerpt' => mb_strimwidth(wp_filter_kses($SCitem->description), 0, 255, "...")
				));

				$term = get_term_by('name', $catcheck, 'product_cat');

				wp_set_object_terms($post_id , $term->term_id, 'product_cat');
			}

		

			$this->update_product($post_id, $SCitem);
			if (!has_post_thumbnail( $post_id ) && !empty($SCitem->images))
			{
				echo 'i';
			$SCimages = $SCitem->images;
			$imagecount = 0;

			foreach ($SCimages as $SCimage)
			{
				if ($imagecount == 0)
				{
					$flags = 0;
				}
				else
				{
					$flags = 1;
				}

				$url = $SCimage;
				$this->attach_product_thumbnail($post_id,$url,$flags);
				$imagecount++;
			}
		}
		}
		else
		{
			if (!empty(wc_get_product_id_by_sku($SCitem->sku)))
			{
				$post_id = wc_get_product_id_by_sku($SCitem->sku);

				// gets ID of post being trashed
 				$post_type = get_post_type( $post_id );
 
 				// does not run on other post types { $post }
 				if ( $post_type != 'product' ) {
 				return true;
 				}
 
 				// get ID of featured image
 				$post_thumbnail_id = get_post_thumbnail_id( $post_id );

				 // gets array from custom field { $gallery } 
				// $attach_id_array = get_post_meta($post_id,'_product_image_gallery', true);
		   		//$attach_id_array .= ','.$attach_id;
 				$gallery_images = get_field('gallery', $post_id);

 				// loop through { $gallery } 
 				foreach ($gallery_images as $gallery_image) {
 
 				// get each attachment ID
 				$gallery_id = $gallery_image['id'];

 				// delete attachments
 				wp_delete_attachment( $gallery_id, true );
 				}
 
 				// delete featured image
				wp_delete_attachment( $post_thumbnail_id, true );

				// delete the post
				wp_delete_post( $post_id, false );
				 
			}
		}
	}
	
	/**
	* Update products
	*
	* @param mixed $post_id
	*
	* @param mixed $SCitem
	* 
	* @return void
	*/
	private function update_product($post_id, $SCitem)
	{
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', sprintf('%.2f', $SCitem->retail / 100) );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', $SCitem->weight );
		update_post_meta( $post_id, '_length', $SCitem->depth );
		update_post_meta( $post_id, '_width', $SCitem->width );
		update_post_meta( $post_id, '_height', $SCitem->height );
		update_post_meta( $post_id, '_sku', $SCitem->sku );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', sprintf('%.2f', $SCitem->currentPrice/ 100) );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'yes' );
		wc_update_product_stock($post_id, $SCitem->quantity, 'set');
		update_post_meta( $post_id, '_backorders', 'no' );
		update_post_meta( $post_id, '_stock', $SCitem->quantity );
	}
	/**
	* Attach images to product (feature/ gallery)
	*
	* @param mixed $post_id
	*
	* @param mixed $url
	*
	* @param mixed $flag
	*
	* @return void
	*/
    public function attach_product_thumbnail($post_id, $url, $flag){
   
	   /*
		* If allow_url_fopen is enable in php.ini then use this
		*/
	   $image_url = $url;
	   $url_array = explode('/',$url);
	   $image_name = $url_array[count($url_array)-1];
	   $image_data = file_get_contents($image_url); // Get image data
   
	 /*
	  * If allow_url_fopen is not enable in php.ini then use this
	  */
   
   
	 // $image_url = $url;
	 // $url_array = explode('/',$url);
	 // $image_name = $url_array[count($url_array)-1];
   
	 // $ch = curl_init();
	 // curl_setopt ($ch, CURLOPT_URL, $image_url);
   
	  // Getting binary data
	 // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 // curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
   
	  //$image_data = curl_exec($ch);
	  //curl_close($ch);
   
   
   
	   $upload_dir = wp_upload_dir(); // Set upload folder
	   $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); //    Generate unique name
	   $filename = basename( $unique_file_name ); // Create image file name
   
	   // Check folder permission and define file location
	   if( wp_mkdir_p( $upload_dir['path'] ) ) {
		   $file = $upload_dir['path'] . '/' . $filename;
	   } else {
		   $file = $upload_dir['basedir'] . '/' . $filename;
	   }
   
   
   
   
   
   
   
	   // Create the image file on the server
	   file_put_contents( $file, $image_data );
   
	   // Check image file type
	   $wp_filetype = wp_check_filetype( $filename, null );
   
	   // Set attachment data
	   $attachment = array(
		   'post_mime_type' => $wp_filetype['type'],
		   'post_title' => sanitize_file_name( $filename ),
		   'post_content' => '',
		   'post_status' => 'inherit'
	   );
   
	   // Create the attachment
	   $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
   
	   // Include image.php
	   require_once(ABSPATH . 'wp-admin/includes/image.php');
   
	   // Define attachment metadata
	   $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
   
	   // Assign metadata to attachment
	   wp_update_attachment_metadata( $attach_id, $attach_data );
   
	   // asign to feature image
	   if( $flag == 0){
		   // And finally assign featured image to post
		   set_post_thumbnail( $post_id, $attach_id );
	   }
   
	   // assign to the product gallery
	   if( $flag == 1 ){
		   // Add gallery image to product
		   $attach_id_array = get_post_meta($post_id,'_product_image_gallery', true);
		   $attach_id_array .= ','.$attach_id;
		   update_post_meta($post_id,'_product_image_gallery',$attach_id_array);
	   }
   
   }


}

return new Simple_Cosign_Class_Functionality();
