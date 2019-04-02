<?php

/*
Plugin Name: Gravity Forms Bancontact Add-On
Plugin URI: http://www.gravityforms.com
Description: Bancontact add-on to demonstrate the use of the Stripe Gateway
Version: 2.1
Author: PK SOL
Author URI: https://www.pksol.com
*/



if(get_option( 'gravityformsaddon_stripe-gateways_settings' )) {
    
    register_activation_hook( __FILE__, 'plugin_activation_bancontact' );

    function plugin_activation_bancontact() {
        
        $gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );
        $gatway_settings['enabled_bancontact'] = '1';
        update_option('gravityformsaddon_stripe-gateways_settings', $gatway_settings);
    }
    
    $gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );
    
    if($gatway_settings['enabled_bancontact'] != NULL && $gatway_settings['enabled_bancontact'] != '0') {
        
        add_action( 'wp_footer', 'gravity_script_bancontact', 100 );
    
        function gravity_script_bancontact() {
    
            $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
            $stripe_publishable = '';
    
            if($settings['webhooks_enabled'] == '1') {
    
                if( $settings['api_mode'] == 'test' && $settings['test_publishable_key_is_valid'] == '1' && $settings['test_secret_key_is_valid'] == '1' ) {
                    $stripe_publishable = $settings['test_publishable_key'];
                } elseif( $settings['api_mode'] == 'live' && $settings['live_publishable_key_is_valid'] == '1' && $settings['live_secret_key_is_valid'] == '1' ) {        
                    $stripe_publishable = $settings['live_publishable_key'];
                }
    
                if($stripe_publishable != '') {
    
                    $total_price = $_POST['total-input-gravity'];
                    $owner_name = $_POST['owner_name'];
    
                    if(isset($total_price)) {

                        $gateway_name = $_POST['method_name'];
                        if($gateway_name == 'bancontact') {

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
    
                                            redirect: {
                                                return_url: '".$home_url."',
                                            },
    
                                        }).then(function(result) {
                                            
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
    
                                        });
    
                                    }
    
                                });
    
                                </script>
                            ";
                            
                        }
                    }
                }   
            }
        }
    }    
}