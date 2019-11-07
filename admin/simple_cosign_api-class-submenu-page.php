<?php
/**
 * Creates the submenu
 *
 * @package SimpleCosign
 */

/**
 * Creates the submenu
 * @package SimpleCosign
 */
class Simple_Cosign_Class_Submenu_Page {

	private $deserializer;

	public function __construct( $deserializer ) {
		$this->deserializer = $deserializer;
	}

	/**
	 * Renders the contents
	 */
	public function render() {
		include_once( 'views/simple_cosign_api_admin.php' );
		//include_once( 'views/manual_trigger_simple_cosign_api_admin.php' );
	}
}