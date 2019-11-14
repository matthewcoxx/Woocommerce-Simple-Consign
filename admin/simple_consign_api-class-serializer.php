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
        if ( null !== wp_unslash( $_POST['acme-checkout'] ) ) {
            $value = sanitize_text_field( $_POST['acme-checkout'] );

            update_option( 'simple_consign_checkout', $value );
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

        if ( null !== wp_unslash( $_POST['triggerapi'] ) ) {
            $value2 = sanitize_text_field( $_POST['triggerapi'] );
        {
            if ($_POST['triggerapi'] == 1)
            {
                $timeoptions = esc_attr( get_option( 'simple_consign_limitapi' ));
                $includeInactiveItems = esc_attr( get_option( 'simple_consign_inactiveitems' ) );
                $includeOnlyEcommerceItems = esc_attr( get_option( 'simple_consign_ecom' ) );
                $includeItemsWithQuantityZero = esc_attr( get_option( 'simple_consign_zero' ) );
                $includeItemsWithStatus = esc_attr( get_option( 'simple_consign_status' ) );
                $lastupdated = get_option( 'simple_consign_triggerapialt');
                $apicaller = new Simple_Consign_Class_Functionality();
                $apicaller->run($lastupdated, $timeoptions, $includeInactiveItems, $includeOnlyEcommerceItems, $includeItemsWithQuantityZero, $includeItemsWithStatus);

            }
        }
    }
        if ( null !== wp_unslash( $_POST['cancel'] ) ) {
            $value2 = sanitize_text_field( $_POST['cancel'] );
        {

                $lock_file = fopen(plugin_dir_path(dirname(__DIR__, 1)).'log/lock.pid', 'c');
                $flc = file_get_contents(plugin_dir_path(dirname(__DIR__, 1)).'log/lock.pid');
                posix_kill ( $flc , 'SIGKILL' );
                ftruncate($lock_file, 0);
                flock($lock_file, LOCK_UN);

                
        }
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