<?php

/*
Plugin Name: Gravity Forms Bancontact Add-On
Plugin URI: http://www.gravityforms.com
Description: Bancontact add-on to demonstrate the use of the Stripe Gateway
Version: 2.1
Author: PK SOL
Author URI: https://www.pksol.com

------------------------------------------------------------------------
Copyright 2009-2019 PK SOL Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_BANCONTACT_VERSION', '2.1' );

add_action( 'gform_loaded', array( 'GF_Bancontact_Bootstrap', 'load' ), 5 );

class GF_Bancontact_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfbancontact.php' );

        GFAddOn::register( 'GFBanContact' );
    }

}

function gf_simple_addon() {
    return GFBanContact::get_instance();
}


add_action( 'wp_footer', 'gravity_script', 100 );

function gravity_script() {

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

            if(isset($total_price)) {

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
                
                

                window.onload = function() {

                    var if_submit = jQuery('.gform_confirmation_message');

                    if(if_submit.length > 0) {
                        
                        jQuery('.gform_confirmation_message').html('Redirecting...');


                        var stripe = Stripe('".$stripe_publishable."');
        
                        stripe.createSource({

                            type: 'bancontact',
                            amount: ".$total_price.",
                            currency: 'eur',
                            statement_descriptor: 'ORDER AT11990',
                            
                            owner: {
                                name: 'Jenny Rosen',
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

                };

                </script>";
            }

        }
        
    }

}





