<?php
/**
 * SimpleConsign Functionality
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
//TODOS:
// -- Log all for error reporting
// -- Documentation
// -- Test Cronjob and Checkout.
//IF custom set to 1 it will not update.
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}
/**
 * Class to manage breadcrumbs and vendor's custom fields.
 */
class Simple_Consign_Class_Functionality {

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
		$this->apikey = get_option('simple_consign_apikey');

	}
	/**
	* Run before construct
	*
	* @return void
	*/
	public function run($lastupdated, $timeoptions, $includeInactiveItems, $includeOnlyEcommerceItems, $includeItemsWithQuantityZero, $includeItemsWithStatus) {
		//Lock file
		$lock_file = fopen(plugin_dir_path( __DIR__ ).'/log/lock.pid', 'c');
		$got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
		if ($lock_file === false || (!$got_lock && !$wouldblock)) {
    		throw new Exception(
        		"Unexpected error opening or locking lock file. Perhaps you " .
        		"don't  have permission to write to the lock file or its " .
        		"containing directory?"
    			);
		}
		else if (!$got_lock && $wouldblock) {
			$this->logAction('Another instance is already running.');
    		exit();
		}

		// Lock acquired; let's write our PID to the lock file for the convenience
		// of humans who may wish to terminate the script.
		ftruncate($lock_file, 0);
		fwrite($lock_file, getmypid() . "\n");

		//Update database once triggered.
		update_option( 'simple_consign_triggerapi', '' );
		$this->logAction('DEL');
		$this->timeoption = $timeoptions;
		$this->lastupdated = $lastupdated;
		$this->includeInactiveItems = $includeInactiveItems;
		$this->includeOnlyEcommerceItems = $includeOnlyEcommerceItems;
		$this->includeItemsWithQuantityZero = $includeItemsWithQuantityZero;
		$this->includeItemsWithStatus = $includeItemsWithStatus;
		$apikey = $this->apikey;
		$this->logAction('Connecting to API.');
		sleep(1);
		$api_data_decoded = json_decode($this->apiPull($apikey));
		$this->logAction('Connected.');
		$api_data = $api_data_decoded->results;
		$outof_total = count($api_data);
		$outof = 1;

		//Pull Data from API & Woo then Merge.
		$merged_data = $this->mergeData($this->wooPull(), $api_data);
		$merged_data_unique = $merged_data;
		$deleteable_skus = $merged_data_unique[1];

		foreach ($merged_data_unique[0] as $SCitem)
		{
		$logOpt = '';
		$logOpt = '('.$outof.' / '.$outof_total.') - ';
		$this->logOpt_loopcheck = '('.$outof.' / '.$outof_total.') - ';

			if ($this->includeItemsWithStatus)
			{
				if (!empty(wc_get_product_id_by_sku($SCitem->sku)))
				{
					//Update Products and Images
					$this->updateProduct($SCitem, $logOpt);
				}
				else
				{
					//Create Products and Images
					$this->createNewProduct($SCitem, $logOpt);
				}
			}
			elseif ($SCitem->status == 'ACTIVE')
			{
				if (!empty(wc_get_product_id_by_sku($SCitem->sku)))
				{
					//Update Products and Images					
					$this->updateProduct($SCitem, $logOpt);
				}
				else
				{
					//Create Products and Images
					$this->createNewProduct($SCitem, $logOpt);
				}
			}

			$outof++;

		}

		//Delete Products and Images that are no longer needed
		$this->deleteProduct($deleteable_skus);
		$this->logAction('COMPLETE');

		ftruncate($lock_file, 0);
		flock($lock_file, LOCK_UN);
		update_option( 'simple_consign_triggerapi_lastrun', time() );
	}

	public function logAction($action)
	{
		$write_path = plugin_dir_path( __DIR__ ).'/log/output.txt';

		if ($action == 'COMPLETE')
		{

		}
		else
		{
		//	$fp = fopen(plugin_dir_path( __FILE__ ) . '/log/output.txt', 'w');
		//	fwrite($fp, '');
		//	fclose($fp);	
		}

		if ($this->logOpt_loopcheck == $this->readAction())
		{
			$fp = fopen($write_path, 'w');
			fwrite($fp, '');
			fclose($fp);
		}

		if ($action == 'DEL')
		{
			$fp = fopen($write_path, 'w');
			fwrite($fp, '');
			fclose($fp);
		}
		elseif ($action == 'COMPLETE')
		{
			$fp = fopen($write_path, 'w');
			fwrite($fp, 'COMPLETE');
			fclose($fp);
		}
		else
		{
			$fp = fopen($write_path, 'w');
			fwrite($fp, $action);
			fclose($fp);
		}

	}

	public function readAction()
	{

		echo file_get_contents(plugin_dir_path( __DIR__ ).'/log/output.txt');
		//Clean
		//$fp = fopen(plugin_dir_path( __FILE__ ) . '/log/output.txt', 'w');
		//fwrite($fp, '');
		//fclose($fp);
	}

	/**
	* Pull API Data
	*
	* @param mixed $apikey
	*
	* @return string|bool
	*/
	public function apiPull($apikey) {
		
		$data_array = array('key' => $apikey);

		if (!empty($this->timeoption))
		{
			$modifiedSince =  time() - $this->timeoption .'000';
			$data_array['modifiedSince'] = $modifiedSince;
		}
		if ($this->includeInactiveItems)
		{
			$data_array['includeInactiveItems'] = true;
		}
		if ($this->includeOnlyEcommerceItems)
		{
			$data_array['includeOnlyEcommerceItems'] = true;
		}
		if ($this->includeItemsWithQuantityZero)
		{
			$data_array['includeItemsWithQuantityZero'] = true;
		}

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
		  
		$result = file_get_contents('http://user.traxia.com/app/api/inventory', false, $context);

		return $result;
	}
	/**
	* Pull Woo Data
	*
	* @param mixed $apikey
	*
	* @return string|bool
	*/	
	public function wooPull()
	{
		$all_product_data = $this->wpdb->get_results("SELECT ID,post_title,post_content,post_author,post_date_gmt FROM `" . $this->wpdb->prefix . "posts` WHERE post_type='product' AND post_status = 'publish'");
		return $all_product_data;
	}
	/**
	 * Merge Data from Woo & API
	 * 
	 * @param mixed $woo_data
	 * 	
     * @param mixed $api_data
	 *
	 * @return (array|array)[$data_unique, $delete_data_sku, $woo_products_array, $woo_products_array_sku, $api_data_array_sku]
	 */
	public function mergeData($woo_data, $api_data)
	{
			// Loop to compare woo commerce to api.
			foreach ($woo_data as $woo_data_single)
			{
				$woo_product = wc_get_product($woo_data_single->ID);
				$woo_products_array[] = $woo_product;
				$woo_products_array_sku[] = $woo_product->sku;	
			}
			// Get products from API and output SKU
			foreach ($api_data as $api_data_single)
			{
				$api_data_array_sku[] = $api_data_single->sku;
			}
			// diff fore deletion
			$delete_data_sku = array_diff($woo_products_array_sku, $api_data_array_sku);

			// merge data
			foreach ($api_data as $api_data_single)
			{
				$api_data_array_sku[] = $api_data_single->sku;
			}

			$merge_data = array_merge($woo_products_array, $api_data);
			$data_unique = $api_data;
			//array_unique($merge_data, SORT_REGULAR);

			return array($data_unique, $delete_data_sku, $woo_products_array, $woo_products_array_sku, $api_data_array_sku);
	}
	/**
	 * Check for category , add if not available.
	 * 
	 * @param mixed $SCitem
	 *
	 * @return void
	 */
	public function categoryCheck($SCitem)
	{
		$lower_cat = strtolower($SCitem->category);
			$catcheck = ucwords($lower_cat);
			if ( !has_term( $catcheck, 'product_cat' ) )
			{
				wp_insert_term( $catcheck, 'product_cat', array(
					'description' => '',
					'parent' => 0,
				) );
			
			}
		return $catcheck;
	}
	/**
	 * Check for custom attribute , add if not available.
	 * 
	 * @param mixed $post_id
	 *
	 * @return void
	 */
	public function addAttributes($post_id)
	{
		$attr = 'custom';
		
		wp_set_object_terms($post_id, '0', $attr);

        $thedata[sanitize_title($attr)] = array(
            	'name' => wc_clean($attr),
                'value' => '0',
                'postion' => '0',
                'is_visible' => '0',
                'is_variation' => '1',
                'is_taxonomy' => '1'
        );
                            
		update_post_meta($post_id, '_product_attributes', $thedata);
						
	}
	/**
	* Create New Woo Product
	*
	* @param mixed $SCitem
	*
	* @return void
	*/
	public function createNewProduct($SCitem, $logOpt) {

		//If SKU does not exist then create a new product in Woocommerce.

		//echo 'INSERTED - '.$SCitem->sku.' - '.$catcheck.'<br>';
		/*echo wp_filter_nohtml_kses($SCitem->name).'<br>
			-- SKU:'.$SCitem->sku.'<br>
			-- DESC: '.$SCitem->description.'<br>
			-- CAT: '.ucwords($lower_cat).'<br>
			-- NOT IN DB<br>
			-- WCID: '. wc_get_product_id_by_sku($SCitem->sku). '<br>
			-- IMAGES: '.print_r($SCitem->images).'<br>
			-- STATUS: '.$SCitem->status.'<br>';
		*/

		$catcheck = $this->categoryCheck($SCitem);

		$post_id = wp_insert_post(array(
			'post_title' => wp_filter_nohtml_kses($SCitem->name),
			'post_category' => $catcheck,
			'post_type' => 'product',
			'post_status' => 'publish',
			'post_content' => wp_filter_kses($SCitem->description),
			'post_excerpt' => mb_strimwidth(wp_filter_kses($SCitem->description), 0, 255, "...")
		));

		$logOpt .= wp_filter_nohtml_kses($SCitem->name).' - '.$post_id.' - ';

		$this->updateProductMeta($post_id, $SCitem);
		$this->addAttributes($post_id);

		$term = get_term_by('name', $catcheck, 'product_cat');
		wp_set_object_terms($post_id , $term->term_id, 'product_cat');

		$images = $this->attachProductImages($post_id, $SCitem, $logOpt);

		$logOpt .= $images;

		$this->logAction('[ADDED] '.$logOpt);

	}
	
	/**
	* Update Product
	*
	* @param mixed $post_id
	*
	* @param mixed $SCitem
	* 
	* @return void
	*/
	public function updateProduct($SCitem, $logOpt)
	{

		//echo 'UPDATED - '.$SCitem->sku.' - '.$catcheck.'<br>';
		/*echo wp_filter_nohtml_kses($SCitem->name).'<br>
			-- SKU:'.$SCitem->sku.'<br>
			-- DESC: '.$SCitem->description.'<br>
			-- CAT: '.ucwords($lower_cat).'<br>
			-- NOT IN DB<br>
			-- WCID: '. wc_get_product_id_by_sku($SCitem->sku). '<br>
			-- IMAGES: '.print_r($SCitem->images).'<br>
			-- STATUS: '.$SCitem->status.'<br>';
			*/
		
		$catcheck = $this->categoryCheck($SCitem);

		$post_id = wc_get_product_id_by_sku($SCitem->sku);
		$post_array = array(
			'ID'           => $post_id,
			'post_category' => $catcheck,
			'post_title'   => wp_filter_nohtml_kses($SCitem->name),
			'post_content' => wp_filter_kses($SCitem->description),
			'post_excerpt' => mb_strimwidth(wp_filter_kses($SCitem->description), 0, 255, "...")
		);

		$logOpt .= wp_filter_nohtml_kses($SCitem->name).' - '.$post_id.' - ';

		wp_update_post( $post_array );

		$term = get_term_by('name', $catcheck, 'product_cat');
		wp_set_object_terms($post_id , $term->term_id, 'product_cat');

		$this->updateProductMeta($post_id, $SCitem);

		$images = $this->updateProductImages($post_id, $SCitem, $logOpt);

		$logOpt .= $images;

		$this->logAction('[UPDATED] '.$logOpt);

	}
	/**
	 * Update Product Meta Data
	 * 
	 * 
	 * 
	 */
	public function updateProductMeta($post_id, $SCitem)
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
	 * Delete products and images
	 * 
	 * @param array $deleteable_skus
	 */
	public function deleteProduct($deleteable_skus)
	{

		foreach ($deleteable_skus as $delete_sku)
		{

			if (!empty(wc_get_product_id_by_sku($delete_sku)))
			{
				$post_id = wc_get_product_id_by_sku($delete_sku);
				$product = new WC_product($post_id);
				$dont_delete_check = $product->get_attribute( 'custom' );

				if ($dont_delete_check == 1)
				{
					$this->logAction('[DELETED] SKU:'.$delete_sku.' CUSTOM SET. SKIPPED.');
				}
				else
				{
					$post_thumbnail_id = get_post_thumbnail_id( $post_id );
					$product = new WC_product($post_id);
					$attachment_ids = $product->get_gallery_attachment_ids();
				
					foreach( $attachment_ids as $attachment_id ) 
					{
						wp_delete_attachment( $attachment_id, true );
					}

					wp_delete_attachment( $post_thumbnail_id, true );
					wp_delete_post( $post_id, false );
					
					$this->logAction('[DELETED] SKU:'.$delete_sku.' DELETED.');
				}

			}

		}

		//return $output;
	}
	/**
	 * Delete product images
	 * 
	 * @param int $post_id
	 */
	public function deleteImage($post_id, $attachment_id)
	{

		$product = new WC_product($post_id);
		$dont_delete_check = $product->get_attribute( 'custom' );

		if ($dont_delete_check == 1)
		{
			//Do nothing...
		}
		else
		{
			$attach_id_array = get_post_meta($post_id,'_product_image_gallery', true);
			$temp_array = explode(",", $attach_id_array);
			$removed_array = array_search($attachment_id, $temp_array);
			unset($temp_array[$removed_array]);
			$attach_id_array_new = implode(",", $temp_array);
			update_post_meta($post_id,'_product_image_gallery',$attach_id_array_new);
			wp_delete_attachment( $attachment_id , true);
		}

	}
	/**
	* Attach images to product (feature/ gallery)
	*
	* @param mixed $post_id
	*
	* @param mixed $SCitem
	*/
    public function attachProductImages($post_id, $SCitem, $logOpt){
   

		if (!empty($SCitem->images))
		{
			$attach_id_array = array();
			$SCimages = $SCitem->images;
			$imagecount = 0;

			foreach ($SCimages as $SCimage)
			{
			
	   			/*
				* If allow_url_fopen is enable in php.ini then use this
				*/
	   			$image_url = $SCimage;
	   			$url_array = explode('/',$SCimage);
	   			$image_name = $url_array[count($url_array)-1];
	   			$image_data = file_get_contents($SCimage); // Get image data


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
				if(wp_mkdir_p( $upload_dir['path']))
				{
		   			$file = $upload_dir['path'] . '/' . $filename;
				} 
				else 
				{
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
				if( $imagecount === 0)
				{
		   			// And finally assign featured image to post
		   			set_post_thumbnail( $post_id, $attach_id );
	   			}
   
	   			// assign to the product gallery
			   if( $imagecount > 0 )
			   {
					// Add gallery image to product
					$attach_id_array = get_post_meta($post_id,'_product_image_gallery', true);
		   			$attach_id_array .= ','.$attach_id;
		   			update_post_meta($post_id,'_product_image_gallery',$attach_id_array);
				}

				$image_hash[] = array($attach_id =>
								array(
									'hash' => wp_hash( $image_data, $scheme = 'auth' ),
									'url' => $image_url,
									'id' => $attach_id
								));

	   		$imagecount++;
			}

			$hashed_images = json_encode($image_hash);
			update_post_meta($post_id,'_product_image_gallery_hashes',$hashed_images);
			return $imagecount.' Images Added';
		}
   }

	/**
	* Update images to product (feature/ gallery)
	*
	* @param mixed $post_id
	*
	* @param mixed $SCitem
	*/
    public function updateProductImages($post_id, $SCitem, $logOpt){
   
		//Let's compare hashes of each image to make sure there was even a change. If so then we will delete the old and add the new.
		if (!empty($SCitem->images))
		{

			//$attach_id_array = array();
			$SCimages = $SCitem->images;
			$imagecount = 0;
			//First let's hash every image for this product and put it in an array. If there is any mismatch we will just delete all of them and start anew.
			$hashed_images_array = json_decode(get_post_meta($post_id,'_product_image_gallery_hashes', true));
			$WOOimages = get_post_meta($post_id,'_product_image_gallery', true);
			$WOOimages_1 = explode(",", $WOOimages);
			$WOOimages_alt = count($WOOimages_1);
			$SCimages_alt = count($SCimages);

			//Delete images that are singles and update the image hashes. This way we only replace the single updated image.
			//TO DO
			$deleted_images_hash_count = 1;
			$deleted_images_count = 1;

			if ($WOOimages_alt > $SCimages_alt)
			{
				$delete_ids = array_diff($WOOimages_1, $SCimages);

				foreach ($delete_ids as $delete_ids_single)
				{
					foreach ($hashed_images_array as $hashed_image)
					{
						if ($delete_ids_single == $hashed_image->id)
						{
							unset($hashed_images_array[$delete_ids_single]);
							$hashed_images_array_fixed = implode(",", $hashed_images_array);
							$hashed_images_final = json_encode($hashed_images_array_fixed);
							update_post_meta($post_id,'_product_image_gallery_hashes', $hashed_images_final);

							$hashed_match_id = $hashed_image->id;
							$this->deleteImage($post_id, $hashed_match_id);
							$deleted_images_hash_count++;
						}
					}
				}
			}
			if ($deleted_images_count > 1)
			{
			$logOpt_alt = $deleted_images_hash_count.' - Hash(s) Updated';
			}
			foreach ($SCimages as $SCimage)
			{
		
	   			/*
				* If allow_url_fopen is enable in php.ini then use this
				*/
	   			$image_url = $SCimage;
	   			$url_array = explode('/',$SCimage);
	   			$image_name = $url_array[count($url_array)-1];
				$image_data = file_get_contents($SCimage); // Get image data
				$image_hashed = wp_hash( $image_data, $scheme = 'auth' );

				$upload_dir = wp_upload_dir(); // Set upload folder
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); //    Generate unique name
				$filename = basename( $unique_file_name ); // Create image file name

				// Check folder permission and define file location
			 	if(wp_mkdir_p( $upload_dir['path'])) 
			 	{
					$file = $upload_dir['path'] . '/' . $filename;
			 	} 
			 	else 
			 	{
					$file = $upload_dir['basedir'] . '/' . $filename;
				}

				foreach ($hashed_images_array as $hashed_image)
				{
					if ($hashed_image->url != $image_url || $hashed_image->hash != $image_hashed)
					{

						$hashed_match_id = $hashed_image->id;
						$this->deleteImage($post_id, $hashed_match_id);
						$deleted_images_count++;
						break;
					}


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
   
				// Create the attachment
	   			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
   
	   			// Include image.php
	   			require_once(ABSPATH . 'wp-admin/includes/image.php');
   
	   			// Define attachment metadata
	   			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
   
				// Assign metadata to attachment
				//update_attached_file( $hashed_match_id, $file );

	   			wp_update_attachment_metadata( $attach_id, $attach_data );
   
	   			// asign to feature image
				if( $imagecount === 0)
				{
		   			// And finally assign featured image to post
		   			set_post_thumbnail( $post_id, $attach_id );
	   			}
   
	   			// assign to the product gallery
			   if( $imagecount > 0 )
			   {
		   			// Add gallery image to product
					$attach_id_array = get_post_meta($post_id,'_product_image_gallery', true);
					var_dump($attach_id_array);
		   			$attach_id_array .= ','.$attach_id;
		   			update_post_meta($post_id,'_product_image_gallery',$attach_id_array);
				}
				//echo '<pre>';
				//var_dump($SCitem->sku);
				//var_dump($SCitem->images);
				//var_dump($attach_id_array);
				//var_dump($imagecount);
				//echo '</pre>';
				$imagecount++;
			} 
			if ($deleted_images_count > 0)
			{
				$logOpt_alt = $deleted_images_count.' - Image(s) Deleted';
			}
			if ($imagecount > 1)
			{
				$img_c = $imagecount - 1;
				$logOpt_alt = $img_c.' - Image(s) Updated';
			}

			return $logOpt_alt;
		}

	}
}

return new Simple_Consign_Class_Functionality();
