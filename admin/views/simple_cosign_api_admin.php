<?php

$currentDate =  time() .'000';

?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 
    <form method="post" id="form" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
 
        <div id="universal-message-container">
            <h2>SimpleCosign Settings</h2>
 
            <div class="options">
            <p>
                <label>API Key</label>
                <br />
                <input type="text" name="acme-message"
                value="<?php echo esc_attr( $this->deserializer->get_value( 'simple_cosign_apikey' ) ); ?>"
                /><br /><br />
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_cosign_ecom' )))) 
                {
                ?>
                    <input type="checkbox" name="acme-ecom" value="1" checked/>
                <?php
                }
                else
                {
                ?>
                    <input type="checkbox" name="acme-ecom" value="1"/>
                <?php
                }
                ?>
                <label>Include only eCommerce items</label><br />
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_cosign_inactiveitems' )))) 
                {
                ?>
                    <input type="checkbox" name="acme-inactiveitems" value="1" checked/>
                <?php
                }
                else
                {
                ?>
                    <input type="checkbox" name="acme-inactiveitems" value="1"/>
                <?php
                }
                ?>
                <label>Include inactive items</label><br />
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_cosign_status' )))) 
                {
                ?>
                    <input type="checkbox" name="acme-status" value="1" checked/>
                <?php
                }
                else
                {
                ?>
                    <input type="checkbox" name="acme-status" value="1"/>
                <?php
                }
                ?>
                <label>Include items with status's other than "ACTIVE" </label><br />
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_cosign_zero' )))) 
                {
                ?>
                    <input type="checkbox" name="acme-zero" value="1" checked/>
                <?php
                }
                else
                {
                ?>
                    <input type="checkbox" name="acme-zero" value="1"/>
                <?php
                }
                ?>
                <label>Include items with quantity of zero</label><br />
                <fieldset>
                    <legend>Limit API Results to a limited time frame:</legend>
                    <sub>This helps if you have a large amount of items in your store to only load the newest items. See <a href="https://wiki.traxia.com/display/guide/List+and+Search+Inventory">here</a> for more information.
                        <p>
                        <label>Time</label>
                            <select name="acme-limitapi">
                            <?php if (!empty(esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' ))))
                            {
                                ?>
                                <option value = "">Disabled</option>
                                <?php
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' )) == 30)
                                {
                                ?>
                                <option value = "30" selected>30 Seconds</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "30">30 Seconds</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' )) == 60)
                                {
                                ?>
                                <option value = "60" selected>1 Minute</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "60">1 Minute</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' )) == 300)
                                {
                                ?>
                                <option value = "300" selected>5 Minutes</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "300">5 Minutes</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' )) == 3600)
                                {
                                ?>
                                <option value = "3600" selected>1 Hour</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "3600">1 Hour</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' )) == 86400)
                                {
                                ?>
                                <option value = "86400" selected>24 Hours</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "86400">24 Hours</option>
                                <?php
                                }
                                ?>
                                <?php
                            }
                            else
                            {
                            ?>
                                <option value = "" selected>Disabled</option>
                                <option value = "30">30 Seconds</option>
                                <option value = "60">1 Minute</option>
                                <option value = "300">5 minutes</option>
                                <option value = "3600">1 Hour</option>
                                <option value = "86400">24 Hours</option>
                            <?php
                            } ?>
                            </select>
                        </p>
                </fieldset><br />
                <fieldset>
                    <legend>Automatic Cronjob:</legend>
                    <sub>Set the time frame in which the API will check automatically for new products.</sub>
                        <p>
                        <label>Time</label>
                        <select name="acme-cronjob">
                            <?php if (!empty(esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' ))))
                            {
                                ?>
                                <option value = "">Disabled</option>
                                <?php
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )) == 60)
                                {
                                ?>
                                <option value = "60" selected>30 Seconds</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "60">1 Minute</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )) == 300)
                                {
                                ?>
                                <option value = "300" selected>5 minutes</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "300">5 minutes</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )) == 600)
                                {
                                ?>
                                <option value = "600" selected>10 minutes</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "600">10 minutes</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )) == 3600)
                                {
                                ?>
                                <option value = "3600" selected>1 Hour</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "3600">1 Hour</option>
                                <?php
                                }
                                if (esc_attr( $this->deserializer->get_value( 'simple_cosign_cronjob' )) == 86400)
                                {
                                ?>
                                <option value = "86400" selected>24 Hours</option>
                                <?php   
                                }
                                else
                                {
                                ?>
                                <option value = "86400">24 Hours</option>
                                <?php
                                }
                                ?>
                                <?php
                            }
                            else
                            {
                            ?>
                                <option value = "" selected>Disabled</option>
                                <option value = "60">1 Minute</option>
                                <option value = "300">5 minutes</option>
                                <option value = "600">10 minutes</option>
                                <option value = "3600">1 Hour</option>
                                <option value = "86400">24 Hours</option>
                            <?php
                            } ?>
                            </select>
                        </p>
                </fieldset><br />
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
    <h3>API Call Log</h3>
    <label>Manually Triggering the API may take some time. Do not refresh.</label>
    <div style="overflow-y: scroll; height:400px;">
    <?php
        $timeoptions = esc_attr( $this->deserializer->get_value( 'simple_cosign_limitapi' ));
        $includeInactiveItems = esc_attr( $this->deserializer->get_value( 'simple_cosign_inactiveitems' ) );
        $includeOnlyEcommerceItems = esc_attr( $this->deserializer->get_value( 'simple_cosign_ecom' ) );
        $includeItemsWithQuantityZero = esc_attr( $this->deserializer->get_value( 'simple_cosign_zero' ) );
        $includeItemsWithStatus = esc_attr( $this->deserializer->get_value( 'simple_cosign_status' ) );
        $lastupdated = $this->deserializer->get_value( 'simple_cosign_triggerapialt');
        $apicaller = new Simple_Cosign_Class_Functionality();
        $apicaller->run($lastupdated, $timeoptions, $includeInactiveItems, $includeOnlyEcommerceItems, $includeItemsWithQuantityZero, $includeItemsWithStatus);
    ?>
    </div>
    <?php
    }
        ?>
    </div><!-- #universal-message-container -->

</form>

</div><!-- .wrap -->
<script type="text/javascript">
    // when page is ready
    jQuery(document).ready(function() {
         // on form submit
         jQuery("#form").on('submit', function() {
            // to each unchecked checkbox
            console.log(this + 'input[type=checkbox]');

          jQuery(this).find('input[type=checkbox]').each(function () {
              if (jQuery(this).is(':not(:checked)'))
              {
                jQuery(this).prop('checked', true).val(0);
              }
              else
              {
                console.log('checked');
              }
          })
        })
    })
</script>