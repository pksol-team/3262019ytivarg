<?php

/*
Plugin Name: Gravity Forms Stripe Payment Gateways Add-On
Plugin URI: http://www.gravityforms.com
Description: Gravity Forms Stripe Payment Gateways Add-On
Version: 2.1
Author: Ahsen Soft
Author URI: https://www.ahsensoft.nl
Text Domain: stripe-gateways
*/



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


add_action('plugins_loaded', 'plugin_init'); 

function plugin_init() {
    load_plugin_textdomain( 'stripe-gateways', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}


// Activation    
register_activation_hook( __FILE__, 'plugin_activation_all' );

function plugin_activation_all() {

    // if there is not already settings then update all methods to true
    if(!get_option( 'gravityformsaddon_stripe-gateways_settings' )) {

        $gatway_settings = array();

        $gatway_settings['enabled_sofort'] = '1';
        $gatway_settings['enabled_bancontact'] = '1';
        $gatway_settings['enabled_eps'] = '1';
        $gatway_settings['enabled_ideal'] = '1';

        update_option( 'gravityformsaddon_stripe-gateways_settings', $gatway_settings);

    }

}


add_action( 'wp_footer', 'gravity_scriptGateway', 100 );

function gravity_scriptGateway() {



    echo "
        <script>
        
            jQuery(document).ready(function() {
                
                var Credit_card = jQuery('.ginput_container_creditcard');

                var if_gateways = jQuery('[name=method_name]');

                if(Credit_card.length > 0 && if_gateways.length > 0 ) {
                    
                    Credit_card.parent().hide();
                    jQuery('.payment_calss').append('<option value=credit-card data-image=".plugins_url('gravityformsgateways/images/credit-card.svg').">". __('Credit Card','stripe-gateways') ."</option>');

                }

                jQuery('.payment_calss option').each(function(indexOp, ElOP) {

                    if(indexOp != 0) {
                        var options = jQuery(ElOP);
                        var html = `<label style='display: block; margin-bottom: 5px; '> <input type='radio' class='gateway-radio' value='`+options.attr('value')+`'> &nbsp; `+options.html()+` <img src=`+options.attr('data-image')+` style='height: 30px; position: relative; top: 8px;'> </label>`;
                        jQuery(html).insertBefore('.payment_calss');
                    }

                });

                jQuery('.payment_calss').hide();
                
                var first_gateway = jQuery('.gateway-radio').first();
                var first_gateway_val = first_gateway.val();

                first_gateway.trigger('click');
                jQuery('.method_name').val(first_gateway_val);
                jQuery('.payment_calss').val(first_gateway_val);

                jQuery(document).on('click', '.gateway-radio', function() {
                    jQuery('.gateway-radio').prop('checked', false);
                    jQuery(this).prop('checked', true);
                    jQuery('.payment_calss').val(jQuery(this).val());
                    jQuery('.payment_calss').trigger('change');
                });



            });

            window.onload = function() {

                var Credit_card = jQuery('.ginput_container_creditcard');
                
                function writeTotal(form, price) {
                    form.find('[name=total-input-gravity]').val(price);
                }

                // update payment method to our custom field
                jQuery('.payment_calss').change(function() {
                
                    var thiss = jQuery(this);
                    var methodInput = thiss.next().next();
                    methodInput.val(thiss.val());

                    if(Credit_card.length > 0) {

                        jQuery('.ginput_container_creditcard').parent().hide();

                        if(thiss.val() == 'credit-card') {
                            
                            Credit_card.parent().show();
                            jQuery('.payee').hide();
    
                        } else {
                            
                            Credit_card.parent().hide();
                            jQuery('.payee').show();
    
                        }

                    }


                    if(thiss.val() == 'sofort') {
                        jQuery('.labe_country').show();
                        jQuery('[name=country_name]').show();
                    } else {
                        jQuery('.labe_country').hide();
                        jQuery('[name=country_name]').hide();
                    }
                    
                });

                var form = jQuery('.gform_wrapper');

                if(form.length > 0) {
                    
                    var gravity_total = form.find('[name=total-input-gravity]');
                    var field_total = form.find('.ginput_total');

                    writeTotal(form, field_total.next().val());

                    field_total.next().change(function(e) {

                        var price_input = jQuery(this);
                        writeTotal(form, price_input.val());

                    });

                }
            }
        </script>
    ";
        

    if( isset($_POST['method_name']) && $_POST['method_name'] != 'credit-card') {
        
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

        $stripe_publishable = 'xyz';
        $settings = get_option('gravityformsaddon_gravityformsstripe_settings');

        if($settings['webhooks_enabled'] == '1') {

            if( $settings['api_mode'] == 'test') {
                $stripe_publishable = $settings['test_publishable_key'];
            } else {
                $stripe_publishable = $settings['live_publishable_key'];
            }

        }

        

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
                            amount: ". $total_price*100 .",
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
                                
                                jQuery('.lds-ring').remove();

                                var referrer =  document.referrer;

                                jQuery('body').attr('style', 'background-color: #423a4a !important; color: #423a4a !important; ');
                                jQuery('body div').hide();
                                jQuery('link').remove();

                                var error_div = `<div class='error-stripe' style='
                                    display: block;
                                    margin: 0 auto;
                                    width: 500px;
                                    margin-top: 30vh;
                                    background: #e64e4e;
                                    box-shadow: 0px 5px 11px #00000085;
                                '>
                                    <h2 style='
                                    color: #fff;
                                    border-bottom: 1px solid #cb5154;
                                    padding: 10px 20px;
                                    margin: 0;
                                    font-family: sans-serif;
                                    font-weight: normal;
                                    font-size: 37px;
                                '>Error</h2>
                                    <p class='gform_confirmation_message' style='
                                    font-size: 16px;
                                    color: #fff;
                                    margin: 0;
                                    border-top: 1px solid #e85e62;
                                    padding: 15px 20px;
                                    font-family: sans-serif;
                                '></p>
                                    <div class='button-div' style='
                                    text-align: right;
                                    display: block;
                                    padding: 15px;
                                    background: #fff;
                                '>
                                        <a href='`+referrer+`' style='
                                            background: #d45659;
                                            padding: 7px 40px;
                                            color: #fff;
                                            text-decoration: none;
                                            font-family: sans-serif;
                                        '>
                                            Back
                                        </a>
                                    </div>
                                </div>`;

                                jQuery(error_div).appendTo('body');

                                var message = result.error.message;

                                if(result.error.hasOwnProperty('param')) {
                                    message += ' ' + result.error.param;
                                }

                                jQuery('.gform_confirmation_message').html(message);


                            } else {

                                var ajaxurl = '".get_site_url()."/wp-admin/admin-ajax.php';

                                var data = {
                                    result: result,
                                    entry_id: ".$entry_id.",
                                    price: ". $total_price*100 .",
                                    method: '".$gateway_name."',
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


/* 
    after submit check if form has payment field then make status deactive

*/

add_action( 'gform_after_submission', 'update_status_gravity', 10, 2);

function update_status_gravity( $entry, $form ) {
    
    if(isset($_POST['method_name'])  && $_POST['method_name'] != 'credit-card' ) {

        gform_update_meta( $entry['id'], 'uniqid', $_POST['uniqid'] );
                
        global $wpdb;
        $entry_id = $entry['id'];
        $prefix = $wpdb->prefix;

        $wpdb->query(
            "UPDATE $prefix"."gf_entry
            SET status = 'deactive'
            WHERE id = $entry_id"
        );
    }

}


/* 
    Ajax action when response come from stripe 
*/
add_action( 'wp_ajax_nopriv_redirection_at_stripe', 'redirection_at_stripe' );
add_action( 'wp_ajax_redirection_at_stripe', 'redirection_at_stripe' );

function redirection_at_stripe() { 
    
    $data = $_POST;
    $entry_id = $data['entry_id'];
    $price = $data['price'];
    $method = $data['method'];
    
    gform_update_meta( $entry_id, 'method', $method );
    gform_update_meta( $entry_id, 'price', $price );
    gform_update_meta( $entry_id, 'source_id', $data['result']['source']['id'] );
    gform_update_meta( $entry_id, 'client_secret', $data['result']['source']['client_secret'] );

    echo $data['result']['source']['redirect']['url'];
    die();

}



/* 
    A virtual page with the result that stripe gives
*/

add_filter( 'init', function( $template ) {

    if ( isset( $_GET['client_secret'] ) && isset($_GET['source']) ) {
        
        $secret = $_GET['client_secret'];
        $source = $_GET['source'];
        
        include plugin_dir_path( __FILE__ ) . 'templates/thank-you.php';
        die;

    }

} );



/* 

    when user submits form check if he/she has filled 
    the payment method and payee name field then show loading
    otherwise show error

*/


if(isset($_POST['method_name']) && $_POST['method_name'] != 'credit-card' ) {
    
    $array = $_POST;
    
    $form_idd = $array['form_idd'];
    $payment_value = $_POST['method_name'];


    $key = '_'.$form_idd. str_replace('input', '', array_search($payment_value, $array));

    add_filter( 'gform_field_validation'.$key, 'custom_payment_validation', 10, 4 );
    
    function custom_payment_validation( $result, $value, $form, $field ) {

        $owner_name = $_POST['owner_name'];

        if($owner_name == "") {
            
            $result['is_valid'] = false;
            $result['message'] = __('Please Select Payment Method and enter payee name ','stripe-gateways');

        } elseif ( $result['is_valid'] && $value == 'Select Gateway') {
            
            $result['is_valid'] = false;
            $result['message'] = __('Please Select Payment Method and enter payee name ','stripe-gateways');

        } else {
            
            echo "

                <style id='loader-style'>
                    body {
                        color: #423a4a;
                    }
                    body div {
                        display: none;
                        color
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

        }

        return $result;

    }



    
}

/* 

    when user creates or updates the form check if there is no default gateways enabled then enable all gateways 
    otherwise leave as like that

*/
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