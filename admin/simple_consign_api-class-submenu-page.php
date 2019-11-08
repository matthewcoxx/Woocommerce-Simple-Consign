<?php
/**
 * Creates the submenu
 *
 * @package SimpleConsign
 */

/**
 * Creates the submenu
 * @package SimpleConsign
 */
class Simple_Consign_Class_Submenu_Page {

	private $deserializer;

	public function __construct( $deserializer ) {
		$this->deserializer = $deserializer;
	}

	/**
	 * Renders the contents
	 */
	public function render() {
		include_once( 'views/simple_consign_api_admin.php' );
		//include_once( 'views/manual_trigger_simple_consign_api_admin.php' );
	}
}