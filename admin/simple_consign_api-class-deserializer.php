<?php
/**
 * Retrieves from the database.
 *
 * @package Simple_Consign_Class
 */

/**
 * @package Simple_Consign_Class
 */
class Simple_Consign_Class_Deserializer {

	/**
	 * Retrieves the value for specified key or empty string.
	 *
	 * @param  string $option_key The key
	 * @return string             The value or an empty string.
	 */
	public function get_value( $option_key ) {
		return get_option( $option_key, '' );
	}

}