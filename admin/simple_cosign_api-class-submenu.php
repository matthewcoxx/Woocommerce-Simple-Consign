<?php
/**
 * Creates the submenu item for the plugin.
 *
 * @package SimpleCosign
 */

/**
 * Creates the submenu item for the plugin.
 *
 * Registers a new menuunder 'Tools'
 * @package SimpleCosign
 */
class Simple_Cosign_Class_Submenu {

	/**
	 * A reference the class responsible for rendering the submenu page.
	 *
	 * @var    Simple_Cosign_Class_Submenu_Page
	 * @access private
	 */
	private $submenu_page;

	/**
	 * Initializes the partial classes.
	 *
	 * @param Simple_Cosign_Class_Submenu_Page $submenu_page A reference that renders the page
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
			'SimpleCosign Settings Page',
			'SimpleCosign Settings',
			'manage_options',
			'simple_cosign_api',
			array( $this->submenu_page, 'render' )
		);
	}
}