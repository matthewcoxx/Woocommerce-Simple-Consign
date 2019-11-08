<?php
/**
 * Creates the submenu item for the plugin.
 *
 * @package SimpleConsign
 */

/**
 * Creates the submenu item for the plugin.
 *
 * Registers a new menuunder 'Tools'
 * @package SimpleConsign
 */
class Simple_Consign_Class_Submenu {

	/**
	 * A reference the class responsible for rendering the submenu page.
	 *
	 * @var    Simple_Consign_Class_Submenu_Page
	 * @access private
	 */
	private $submenu_page;

	/**
	 * Initializes the partial classes.
	 *
	 * @param Simple_Consign_Class_Submenu_Page $submenu_page A reference that renders the page
	 */
	public function __construct( $submenu_page ) {
		$this->submenu_page = $submenu_page;
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
	}

	/**
	 * Creates the submenu
	 */
	public function add_options_page() {

		add_menu_page(
			'SimpleConsign Settings Page',
			'SimpleConsign Settings',
			'manage_options',
			'simple_consign_api',
			array( $this->submenu_page, 'render' )
		);
	}
}