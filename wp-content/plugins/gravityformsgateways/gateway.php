<?php

/*
Plugin Name: Gravity Forms Stripe Payment Gateways Add-On
Plugin URI: http://www.gravityforms.com
Description: Gravity Forms Stripe Payment Gateways Add-On
Version: 2.1
Author: PK SOL
Author URI: https://www.pksol.com
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


add_action( 'wp_footer', 'gravity_scriptGateway', 100 );

function gravity_scriptGateway() {

    $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
    $stripe_publishable = '';

    if($settings['webhooks_enabled'] == '1') {

        $process_the_gateway = false;

        if( $settings['api_mode'] == 'test' && $settings['test_publishable_key_is_valid'] == '1' && $settings['test_secret_key_is_valid'] == '1' ) {
            $stripe_publishable = $settings['test_publishable_key'];
        } elseif( $settings['api_mode'] == 'live' && $settings['live_publishable_key_is_valid'] == '1' && $settings['live_secret_key_is_valid'] == '1' ) {        
            $stripe_publishable = $settings['live_publishable_key'];
        }

        if($stripe_publishable != '') {

            $total_price = $_POST['total-input-gravity'];

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

            $wpdb->query(
                "UPDATE $prefix"."gf_entry
                SET status = 'deactive'
                WHERE id = $entry_id"
            );

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




add_action("gform_after_update_entry", "update_entry", 10, 2);

function update_entry($form, $entry_id){
    
    update_option('sofort_update', uniqid());

    


	// $lead = RGFormsModel::get_lead($entry_id);
    // $value = RGFormsModel::get_field_value_long($lead, 1, $form, false);
    

}

