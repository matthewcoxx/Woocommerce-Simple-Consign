<?php
/**
 * @package SimpleConsign
 */
 
class Simple_Consign_Class_Serializer {
 
    /**
    * Initializes at admin_post hook
    */
    public function init() {
        add_action( 'admin_post', array( $this, 'save' ) );
    }
 
    /**
     * Validates nonce value, verifies user role
     */
    public function save() {
 
        if ( ! ( $this->has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
            echo '<p>User has no Permissions</p>';
        }
 
        if ( null !== wp_unslash( $_POST['acme-message'] ) ) {
            $value = sanitize_text_field( $_POST['acme-message'] );

            update_option( 'simple_consign_apikey', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-ecom'] ) ) {
            $value = sanitize_text_field( $_POST['acme-ecom'] );
            if (!empty(esc_attr($_POST['acme-ecom']))) 
            {
                update_option( 'simple_consign_ecom', 1 );
            }
            else
            {
                update_option( 'simple_consign_ecom', '' );
            }
        }
        if ( null !== wp_unslash( $_POST['acme-inactiveitems'] ) ) {
            $value = sanitize_text_field( $_POST['acme-inactiveitems'] );

            update_option( 'simple_consign_inactiveitems', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-status'] ) ) {
            $value = sanitize_text_field( $_POST['acme-status'] );

            update_option( 'simple_consign_status', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-zero'] ) ) {
            $value = sanitize_text_field( $_POST['acme-zero'] );

            update_option( 'simple_consign_zero', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-limitapi'] ) ) {
            $value = sanitize_text_field( $_POST['acme-limitapi'] );

            update_option( 'simple_consign_limitapi', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-cronjob'] ) ) {
            $value = sanitize_text_field( $_POST['acme-cronjob'] );

            update_option( 'simple_consign_cronjob', $value );
        }
        if ( null !== wp_unslash( $_POST['acme-triggerapi'] ) ) {
            $value2 = sanitize_text_field( $_POST['acme-triggerapi'] );

            update_option( 'simple_consign_triggerapi', $value2 );
            update_option( 'simple_consign_triggerapialt', $value2 );
        }

        $this->redirect();
    }
 
    /**
     * Checks Nonce
     *
     * @access private
     *
     * @return boolean False field isn't set, or if nonce is invalid
     */
    private function has_valid_nonce() {
 
        if ( ! isset( $_POST['acme-custom-message'] ) ) {
            return false;
        }
 
        $field  = wp_unslash( $_POST['acme-custom-message'] );
        $action = 'acme-settings-save';
 
        return wp_verify_nonce( $field, $action );
 
    }
 
    /**
     * Redirect to previous page if referall is set.
     * Otherwise the login page.
     *
     * @access private
     */
    private function redirect() {
 
        if ( ! isset( $_POST['_wp_http_referer'] ) ) {
            $_POST['_wp_http_referer'] = wp_login_url();
        }
 
        $url = sanitize_text_field(
                wp_unslash( $_POST['_wp_http_referer'] )
            );
 
        wp_safe_redirect( urldecode( $url ) );
        exit;
    }

}