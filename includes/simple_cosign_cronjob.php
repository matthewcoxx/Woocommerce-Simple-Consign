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
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}

include_once(plugin_dir_path( __FILE__ ) . '/simple_cosign_api.php');

// SimpleCosign Cron Recurrences
// The activation hook
function isa_activation(){
    if( !wp_next_scheduled( 'isa_add_every_x_minutes_event' ) ){
        wp_schedule_event( time(), 'every_x_minutes', 'isa_add_every_x_minutes_event' );
    }
}

register_activation_hook(   __FILE__, 'isa_activation' );

// The deactivation hook
function isa_deactivation(){
    if( wp_next_scheduled( 'isa_add_x_minutes_event' ) ){
        wp_clear_scheduled_hook( 'isa_add_x_minutes_event' );
    }
}

register_deactivation_hook( __FILE__, 'isa_deactivation' );


// The schedule filter hook
function isa_add_x_minutes( $schedules ) {
    $schedules['x_minutes'] = array(
            'interval'  => esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )),
            'display'   => __( 'Every X Minutes', 'textdomain' )
    );
    return $schedules;
}

add_filter( 'cron_schedules', 'isa_add_x_minutes' );


// The WP Cron event callback function
function isa_x_minutes_event_func() {

    $timeoptions = esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' ));
    $includeInactiveItems = esc_attr( $this->deserializer->get_value( 'simple_cosign_inactiveitems' ) );
    $includeOnlyEcommerceItems = esc_attr( $this->deserializer->get_value( 'simple_cosign_ecom' ) );
    $includeItemsWithQuantityZero = esc_attr( $this->deserializer->get_value( 'simple_cosign_zero' ) );
    $includeItemsWithStatus = esc_attr( $this->deserializer->get_value( 'simple_cosign_status' ) );
    $lastupdated = $this->deserializer->get_value( 'simple_cosign_triggerapialt');
    $apicaller = new Simple_Cosign_Class_Functionality();
    $apicaller->run($lastupdated, $timeoptions, $includeInactiveItems, $includeOnlyEcommerceItems, $includeItemsWithQuantityZero, $includeItemsWithStatus);

}

add_action( 'isa_add_x_minutes_event', 'isa_every_x_event_func' );

if (!empty(esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' ))))
{
    wp_schedule_event( time(), 'every_x_minutes', 'isa_add_every_x_minutes_event' );
}
else
{
$timestamp = wp_next_scheduled( 'isa_add_x_minutes_event' );
wp_unschedule_event( $timestamp, 'isa_add_x_minutes_event' );
wp_clear_scheduled_hook( 'isa_add_x_minutes_event' );
}
?>