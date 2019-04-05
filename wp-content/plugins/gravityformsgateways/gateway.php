<?php

/*
Plugin Name: Gravity Forms Stripe Payment Gateways Add-On
Plugin URI: http://www.gravityforms.com
Description: Gravity Forms Stripe Payment Gateways Add-On
Version: 2.1
Author: PK SOL
Author URI: https://www.pksol.com
*/







define( 'GF_STRIPE_VERSION', '2.5' );

// If Gravity Forms is loaded, bootstrap the Stripe Add-On.
add_action( 'gform_loaded', array( 'GF_Stripe_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Stripe_Bootstrap
 *
 * Handles the loading of the Stripe Add-On and registers with the Add-On framework.
 *
 * @since 1.0.0
 */
class GF_Stripe_Bootstrap {

	/**
	 * If the Payment Add-On Framework exists, Stripe Add-On is loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @uses GFAddOn::register()
	 *
	 * @return void
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-stripe.php' );

		GFAddOn::register( 'GFStripe' );

	}

}

/**
 * Obtains and returns an instance of the GFStripe class
 *
 * @since  1.0.0
 * @access public
 *
 * @uses GFStripe::get_instance()
 *
 * @return object GFStripe
 */
function gf_stripe() {
	return GFStripe::get_instance();
}












































define( 'GF_GATEWAY_VERSION', '2.1' );

add_action( 'gform_loaded', array( 'GF_Gateway_Bootstrap', 'load' ), 5 );

class GF_Gateway_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gateway.php' );

        GFAddOn::register( 'GFGateways' );
    }

}



function gf_simple_addon() {
    return GFGateways::get_instance();
}


if(get_option( 'gravityformsaddon_stripe-gateways_settings' )) {
    
    register_activation_hook( __FILE__, 'plugin_activation_all' );

    function plugin_activation_all() {
        
        $gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );
        
        $gatway_settings['enabled_sofort'] = '1';
        $gatway_settings['enabled_bancontact'] = '1';
        $gatway_settings['enabled_eps'] = '1';
        $gatway_settings['enabled_ideal'] = '1';

        update_option('gravityformsaddon_stripe-gateways_settings', $gatway_settings);
    }

}



add_action( 'wp_footer', 'gravity_scriptGateway', 100 );

function gravity_scriptGateway() {

    $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
    $stripe_publishable = '';

    if($settings['webhooks_enabled'] == '1') {

        if( $settings['api_mode'] == 'test' && $settings['test_publishable_key_is_valid'] == '1' && $settings['test_secret_key_is_valid'] == '1' ) {
            $stripe_publishable = $settings['test_publishable_key'];
        } elseif( $settings['api_mode'] == 'live' && $settings['live_publishable_key_is_valid'] == '1' && $settings['live_secret_key_is_valid'] == '1' ) {        
            $stripe_publishable = $settings['live_publishable_key'];
        }

        if($stripe_publishable != '') {

            echo "
            
            <script>    

                window.onload = function() {

                        var form = jQuery('.gform_wrapper');

                        if(form.length > 0) {

                            var gravity_total = form.find('[name=total-input-gravity]');
                            var field_total = jQuery('.ginput_total');
    
                            if(gravity_total.length == 1 && field_total.length == 1) {
                                
                                writeTotal(field_total.next().val());
   
                                jQuery('.payment_calss').change(function() {
                                    var thiss = jQuery(this);
                                    var methodInput = thiss.next().next();
                                    methodInput.val(thiss.val());
                                    if(thiss.val() == 'sofort') {
                                        jQuery('.labe_country').show();
                                        jQuery('[name=country_name]').show();
                                    } else {
                                        jQuery('.labe_country').hide();
                                        jQuery('[name=country_name]').hide();
                                    }
                                });

                                

                                field_total.next().change(function(e) {
                                    
                                    var price_input = jQuery(this);
                                    writeTotal(price_input.val());
    
                                });
    
                            }
                            
                        }

                        function writeTotal(price) {
                            jQuery('[name=total-input-gravity]').val(price);
                        }



                    };

                </script>
            
            ";
            
            if( isset($_POST['method_name']) &&  isset($_POST['total-input-gravity']) ) {

                $gateway_name = $_POST['method_name'];
                $owner_name = $_POST['owner_name'];
                $total_price = $_POST['total-input-gravity'];
                $country_name = $_POST['country_name'];
                $uniqid = $_POST['uniqid'];

                global $wpdb;
                $prefix = $wpdb->prefix;
    
                $entry_row = $wpdb->get_row(
                    "SELECT * FROM $prefix"."gf_entry_meta
                    WHERE meta_value = '$uniqid'", OBJECT
                );

                $entry_id = $entry_row->entry_id;

                $home_url = get_site_url();

                echo "
                    <script src='https://js.stripe.com/v3/'></script>
                    <script>
                    
                    window.addEventListener('load', function(evt) {

                        var if_submit = jQuery('.gform_confirmation_message');

                        if(if_submit.length > 0) {
                            
                            jQuery('.gform_confirmation_message').html('Redirecting...');

                            var stripe = Stripe('".$stripe_publishable."');
            
                            stripe.createSource({

                                type: '".$gateway_name."',
                                amount: ".$total_price.",
                                currency: 'eur',
                                
                                owner: {
                                    name: '".$owner_name."',
                                },

                                sofort: {
                                    country: '".$country_name."',
                                },

                                redirect: {
                                    return_url: '".$home_url."',
                                },

                            }).then(function(result) {
                                
                                if(result.hasOwnProperty('error')) {
                                    
                                    jQuery('.gform_confirmation_message').html(result.error.message);
                                    
                                    jQuery('.lds-ring').remove();

                                    var referrer =  document.referrer;

                                    jQuery('body').css('background', '#423a4a');

                                    var error_div = `<div class='error-stripe' style='
                                        display: block;
                                        margin: 0 auto;
                                        width: 400px;
                                        margin-top: 30vh;
                                        background: #e64e4e;
                                        box-shadow: 0px 5px 11px #00000085;
                                    '>
                                        <h2 style='
                                        color: #fff;
                                        border-bottom: 1px solid #cb5154;
                                        padding: 10px;
                                        margin: 0;
                                    '>Error</h2>
                                        <p style='
                                        color: #fff;
                                        padding: 10px;
                                        border-top: 1px solid #e85e62;
                                    '>Amount must be at least 50</p>
                                        <div class='button-div' style='
                                        text-align: right;
                                        display: block;
                                        padding: 15px;
                                        background: #fff;
                                    '>
                                            <a href='`+referrer+`' style='
                                        background: #d45659;
                                        padding: 5px 40px;
                                        color: #fff;
                                    '>Back</a>
                                        </div>
                                    </div>`;

                                    jQuery(error_div).appendTo('body');

                                } else {

                                    var ajaxurl = '".get_site_url()."/wp-admin/admin-ajax.php';

                                    var data = {
                                        result: result,
                                        entry_id: ".$entry_id.",
                                        price: ".$total_price.",
                                        action: 'redirection_at_stripe'
                                    }

                                    jQuery.post(ajaxurl, data, function(response) {
                                        window.location.href = response.trim();
                                    });

                                }

                            });

                        }

                    });

                    </script>
                ";


            }

        }
        
    }

}


add_action( 'gform_after_submission', 'update_status_gravity', 10, 2);

function update_status_gravity( $entry, $form ) {

    gform_update_meta( $entry['id'], 'uniqid', $_POST['uniqid'] );

    $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
    $stripe_publishable = '';

    if($settings['webhooks_enabled'] == '1') {

        if( $settings['api_mode'] == 'test' && $settings['test_publishable_key_is_valid'] == '1' && $settings['test_secret_key_is_valid'] == '1' ) {
            $stripe_publishable = $settings['test_publishable_key'];
        } elseif( $settings['api_mode'] == 'live' && $settings['live_publishable_key_is_valid'] == '1' && $settings['live_secret_key_is_valid'] == '1' ) {        
            $stripe_publishable = $settings['live_publishable_key'];
        }

        if($stripe_publishable != '') {
            
            global $wpdb;
            $entry_id = $entry['id'];
            $prefix = $wpdb->prefix;

            if(isset($_POST['method_name'])) {
                $wpdb->query(
                    "UPDATE $prefix"."gf_entry
                    SET status = 'deactive'
                    WHERE id = $entry_id"
                );
            }


        }

    }

}


add_action( 'wp_ajax_nopriv_redirection_at_stripe', 'redirection_at_stripe' );
add_action( 'wp_ajax_redirection_at_stripe', 'redirection_at_stripe' );

function redirection_at_stripe() { 
    
    $data = $_POST;
    $entry_id = $data['entry_id'];
    $price = $data['price'];
    
    gform_update_meta( $entry_id, 'price', $price );
    gform_update_meta( $entry_id, 'source_id', $data['result']['source']['id'] );
    gform_update_meta( $entry_id, 'client_secret', $data['result']['source']['client_secret'] );

    echo $data['result']['source']['redirect']['url'];
    
    die();

}

add_filter( 'init', function( $template ) {

    if ( isset( $_GET['client_secret'] ) && isset($_GET['source']) ) {
        
        $secret = $_GET['client_secret'];
        $source = $_GET['source'];
        include plugin_dir_path( __FILE__ ) . 'templates/thank-you.php';
        die;

    }


} );

if(isset($_POST['method_name'])) {
    
    $array = $_POST;
    
    $form_idd = $array['form_idd'];
    $payment_value = $_POST['method_name'];
    $key = '_'.$form_idd. str_replace('input', '', array_search ($payment_value, $array));
    
    add_filter( 'gform_field_validation'.$key, 'custom_validation', 10, 4 );
    
    function custom_validation( $result, $value, $form, $field ) {
     
        if ( $result['is_valid'] && $value == 'Select Gateway' ) {
            $result['is_valid'] = false;
            $result['message'] = 'Please Select Payment Method.';
        } else {
            echo "

                <style id='loader-style'>
                    body div {
                        display: none;
                    }
                    .lds-ring {
                        display: block;
                        position: relative;
                        width: 128px;
                        height: 128px;
                        margin: 0 auto;
                        margin-top: 30vh;
                      }
                      .lds-ring div {
                        box-sizing: border-box;
                        display: block;
                        position: absolute;
                        width: 102px;
                        height: 102px;
                        margin: 6px;
                        border: 7px solid #5d74d8;
                        border-radius: 50%;
                        animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
                        border-color: #5d74d8 transparent transparent transparent;
                      }
                      .lds-ring div:nth-child(1) {
                        animation-delay: -0.45s;
                      }
                      .lds-ring div:nth-child(2) {
                        animation-delay: -0.3s;
                      }
                      .lds-ring div:nth-child(3) {
                        animation-delay: -0.15s;
                      }
                      @keyframes lds-ring {
                        0% {
                          transform: rotate(0deg);
                        }
                        100% {
                          transform: rotate(360deg);
                        }
                      }
                </style>

                <div class='lds-ring'><div></div><div></div><div></div><div></div></div>

            ";
            return $result;
        }
    }
    
}


add_action( 'gform_after_save_form', 'update_methods', 10, 2 );

function update_methods($form) {

    $form_id = $form['fields'][0]->formId;

    global $wpdb;
    $prefix = $wpdb->prefix;

    $thepost = $wpdb->get_row( "SELECT * FROM {$prefix}gf_form_meta WHERE form_id = $form_id", OBJECT );
    $data = json_decode($thepost->display_meta, true);

    if($data['stripe-gateways'] == NULL) {

        $gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );
        $to_update = false;
        
        if($gatway_settings['enabled_bancontact'] != NULL && $gatway_settings['enabled_bancontact'] != '0') {
            
            $to_update = true;
            $data['stripe-gateways']['enabled_bancontact'] = '1';

        }
        
        if($gatway_settings['enabled_eps'] != NULL && $gatway_settings['enabled_eps'] != '0') {
            
            $to_update = true;
            $data['stripe-gateways']['enabled_eps'] = '1';

        }
        
        if($gatway_settings['enabled_ideal'] != NULL && $gatway_settings['enabled_ideal'] != '0') {
            
            $to_update = true;
            $data['stripe-gateways']['enabled_ideal'] = '1';

        }
        
        if($gatway_settings['enabled_sofort'] != NULL && $gatway_settings['enabled_sofort'] != '0') {
            
            $to_update = true;
            $data['stripe-gateways']['enabled_sofort'] = '1';

        }

        $newData = json_encode($data);
        $wpdb->query(
            "UPDATE $prefix"."gf_form_meta
            SET display_meta = '".$newData."'
            WHERE form_id = $form_id"
        );

    }



}
