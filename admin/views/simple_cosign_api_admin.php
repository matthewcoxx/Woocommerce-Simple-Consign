<?php

$currentDate =  time() - 86400 .'000';

?>
<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
 
        <div id="universal-message-container">
            <h2>SimpleCosign Settings</h2>
 
            <div class="options">
            <p>
                <label>API Key</label>
                <br />
                <input type="text" name="acme-message"
                value="<?php echo esc_attr( $this->deserializer->get_value( 'simple_cosign_apikey' ) ); ?>"
                />
            </p>
        </div><!-- #universal-message-container -->

        <?php
            wp_nonce_field( 'acme-settings-save', 'acme-custom-message' );
            submit_button('Save');
        ?>

    </form>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
    <div id="universal-message-container">

    <label>Trigger API Manually</label>
                <br />
                <input type="hidden" name="acme-triggerapi"
                value="<?php echo $currentDate; ?>"
                />
        <?php
            wp_nonce_field( 'acme-settings-save', 'acme-custom-message' );
            submit_button('Trigger API');
        ?>
    
        <?php
    if (!empty(esc_attr($this->deserializer->get_value( 'simple_cosign_triggerapi'))))
    {
    ?>
    <label>Manually Triggering the API may take some time. Do not refresh.</label>
    <div style="overflow-y: scroll; height:400px;">
    <h3>API Call Log</h3>
    <?php
        $lastupdated = $this->deserializer->get_value( 'simple_cosign_triggerapialt');
        $apicaller = new Simple_Cosign_Class_Functionality();
        $apicaller->run($lastupdated);
    ?>
    </div>
    <?php
    }
        ?>
    </div><!-- #universal-message-container -->

</form>

</div><!-- .wrap -->