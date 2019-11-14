<?php

$currentDate =  time() .'000';

?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 
    <form method="post" id="form" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
 
        <div id="universal-message-container">
 
            <div class="options">
            <p>
                <label>API Key</label>
                <br />
                <input type="text" name="acme-message"
                value="<?php echo esc_attr( $this->deserializer->get_value( 'simple_consign_apikey' ) ); ?>"
                /><br /><br />
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_ecom' )))) 
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
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_inactiveitems' )))) 
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
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_status' )))) 
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
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_zero' )))) 
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
                <?php if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_checkout' )))) 
                {
                ?>
                    <input type="checkbox" name="acme-checkout" value="1" checked/>
                <?php
                }
                else
                {
                ?>
                    <input type="checkbox" name="acme-checkout" value="1"/>
                <?php
                }
                ?>
                <label>Enable checkout syncronization</label><br />
                <sub>Sync purchases in Woocommerce with SimpleConsign. More information <a href="https://wiki.traxia.com/display/guide/Submit+Online+Transactions+and+Online+Returns">here</a>.</sub><br />
                <fieldset>
                    <legend>Limit API Results to a limited time frame:</legend>
                    <sub>This helps if you have a large amount of items in your store to only load the newest items. See <a href="https://wiki.traxia.com/display/guide/List+and+Search+Inventory">here</a> for more information.
                        <p>
                        <label>Time</label>
                            <select name="acme-limitapi">
                            <?php if (!empty(esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' ))))
                            {
                                ?>
                                <option value = "">Disabled</option>
                                <?php
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' )) == 30)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' )) == 60)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' )) == 300)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' )) == 3600)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_limitapi' )) == 86400)
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
                            <?php if (!empty(esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' ))))
                            {
                                ?>
                                <option value = "">Disabled</option>
                                <?php
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' )) == 60)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' )) == 300)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' )) == 600)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' )) == 3600)
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
                                if (esc_attr( $this->deserializer->get_value( 'simple_consign_cronjob' )) == 86400)
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
                        <?php
                        $tz = 'America/Chicago';
                        $timestamp = get_option( 'simple_consign_triggerapi_lastrun');
                        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
                        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
                        ?>
                        <sub>Last Run: <?php echo $dt->format('F j, Y, g:i a'); ?></sub>
                        <?php
                        $lock_file = fopen(plugin_dir_path(dirname(__DIR__, 1)).'log/lock.pid', 'c');
                        $got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
                        if (!$got_lock && $wouldblock) {
                        $locked = true;
                        ?>

                            <p id="statuson">Status: <span style="color:green;">Running</span></p>
                            <p id="statusoff" style="display:none;">Status: <span style="color:red;">Stopped</span></p>

                        <?php
                        }
                        else
                        {
                        ?>
                            <p id="statusoff" >Status: <span style="color:red;">Stopped</span></p>
                            <p id="statuson" style="display:none;">Status: <span style="color:green;">Running</span></p>

                        <?php
                        }
                        ?>

                </fieldset><br />
            </p>
        </div><!-- #universal-message-container -->

        <?php
            wp_nonce_field( 'acme-settings-save', 'acme-custom-message' );
            submit_button('Save');
        ?>

    </form>
    <form method="post" id="triggerbutton" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
    <div id="universal-message-container">

    <label>Trigger API Manually</label>
                <br />
                <input type="hidden" name="acme-triggerapi"
                value="<?php echo $currentDate; ?>"
                />
                <input type="hidden" name="triggerapi" value="1" />
        <?php
            wp_nonce_field( 'acme-settings-save', 'acme-custom-message' );
            submit_button('Trigger API');
        ?>
    <!--<p class="submit" id="cancelbutton" style="display:none;"><input style="background:red; border: 1px red; color: white;" type="submit" name="cancel" id="submit" class="button button-primary" value="Stop Cronjob"></p>-->
        <?php
 //   if (!empty(esc_attr($this->deserializer->get_value( 'simple_consign_triggerapi'))))
 //   {
    ?>
    <h3>API Call Log <img id="loadingimg" style="display:none;vertical-align:middle;width:25px;" src="<?php echo plugins_url().'/woocommerce-simple-consign/images/ajax-loader-circle.gif'; ?>"></h3>
    <label>Manually Triggering the API may take some time. Do not refresh.</label>
    <div class="apioutput" id="out" style="overflow-y: scroll; height:400px;overflow:auto;background:white;display:none;">
    <div id="loader3"></div>
    </div>
    <style>
        .apioutput div
        {
            border-bottom:1px solid gray;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 5px;
        }
    </style>
    <?php
//    }
        ?>
    </div><!-- #universal-message-container -->

</form>

</div><!-- .wrap -->

<script>
    jQuery(document).ready(function(){
        jQuery(function(){
<?php
if (!$locked)
{
?>
        jQuery('#triggerbutton').submit(function(e){
                e.preventDefault();
                var form = jQuery(this);
                var post_url = form.attr('action');
                var post_data = form.serialize();
                jQuery('#statusoff').fadeOut();
                jQuery('#triggerbutton > #universal-message-container > .submit').fadeOut();
                //jQuery('#triggerbutton > #universal-message-container > #cancelbutton').fadeIn();
                jQuery('#statuson').fadeIn();
                jQuery('#out').fadeIn();
                jQuery('#loadingimg').fadeIn();
                //jQuery('#triggerapibutton .cancel').fadeOut();
<?php
}
elseif ($locked)
{
?>
                jQuery('#out').fadeIn();
                jQuery('#loadingimg').fadeIn();
                jQuery('#triggerbutton > #universal-message-container > .submit').fadeOut();
                //jQuery('#triggerbutton > #universal-message-container > #cancelbutton').fadeIn();
<?php
}
?>

                jQuery('#loader3', form).html('Loading...');

                const out = document.getElementById("out")
                let c = 0
                let count = 1
var timer = setInterval(function() {
    // allow 1px inaccuracy by adding 1
    const isScrolledToBottom = out.scrollHeight - out.clientHeight <= out.scrollTop + 1

    const newElement = document.createElement("div")

    jQuery.get( "<?php echo plugins_url().'/woocommerce-simple-consign/log/output.txt'; ?>", function( data ) {
    

        if (data == 'COMPLETE')
        {
            clearInterval(timer);
            jQuery('#statuson').fadeOut();
            jQuery('#statusoff').fadeIn();
            jQuery('#loadingimg').fadeOut();
            jQuery('#triggerapibutton > #universal-message-container > .submit').fadeIn();
           // jQuery('#triggerapibutton > #universal-message-container > #cancelbutton').fadeOut();
        }

        var lastitemcheck = jQuery( ".apioutput div" ).last().text();

        if (data == lastitemcheck)
        {
            //console.log('not different!');
        }
        else
        {
                //console.log('different!');
            
                newElement.textContent = format(data)

                out.appendChild(newElement)

                if (isScrolledToBottom) {
                out.scrollTop = out.scrollHeight - out.clientHeight
                }
            }

        if (count > 1)
        {
            jQuery('#loader3').fadeOut();
        }
        count++;

    });


}, 500)


function format () {
  return Array.prototype.slice.call(arguments).join(' ')
}
<?php
if (!$locked)
{
?>
                jQuery.ajax({
                    type: 'POST',
                    url: post_url, 
                    data: post_data,
                    success: function(msg) {
                        //jQuery(form).fadeOut(800, function(){
                            form.html(msg).fadeIn().delay(2000);

                       // });
                    }
                });
            });
<?php
}
?>
        });
         });
</script>
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