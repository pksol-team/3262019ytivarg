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

            // echo "
            // <script src='https://js.stripe.com/v3/'></script>
            // <script>
                
            //     var stripe = Stripe('".$stripe_publishable."');
                
            //     // stripe.createSource({

            //     //     type: 'bancontact',
            //     //     amount: 50,
            //     //     currency: 'eur',
            //     //     statement_descriptor: 'ORDER AT11990',
            //     //     owner: {
            //     //         name: 'Jenny Rosen',
            //     //     },
            //     //     redirect: {
            //     //         return_url: 'https://stripe.pakistancouncilofyouth.com/',
            //     //     },
            //     // }).then(function(result) {
            //     //     console.log(result);
            //     // });

            //     jQuery(document).ready(function($) {

            //         $('.gform_wrapper').each(function(index, form) {

            //             var form = $(form);

            //             var is_redirect_url = form.find('[value=redirect_url]');
            //             var is_secret_stripe = form.find('[value=client_secret]');
            //             var is_source_stripe = form.find('[value=source_id]');

            //             if(is_redirect_url.length == 1 && is_secret_stripe.length == 1 && is_source_stripe.length == 1) {
                            
            //                 // console.log('apply stripe key');

            //             }

            //         });

            //     });

            // </script>";

        }
        
    }

}



// add_filter( 'gform_entry_meta', 'custom_entry_metas', 10, 2);


// function custom_entry_metas($entry_meta, $form_id) {
    

//     $entry_meta['score'] = array(
//         'label' => 'Score',
//         'is_numeric' => true,
//         'update_entry_meta_callback' => 'update_entry_meta',
//         'is_default_column' => true
//     );

// }




add_action( 'gform_after_submission', 'update_status_gravity', 10, 2);

function update_status_gravity( $entry, $form ) {

    $field_names = [];
    foreach ( $form['fields'] as $field ) {
        
        $field_names[] = ['name' => $field->label, 'meta_name' => $field->id ];

    }

    var_dump($field_names);


    // var_dump($form);

    // $summary = RGFormsModel::get_form_counts($entry['id']);

    // var_dump($summary);



    // foreach ( $form['fields'] as $field ) {
    //     $inputs = $field->get_entry_inputs();
    //     if ( is_array( $inputs ) ) {
    //         foreach ( $inputs as $input ) {
                
    //             $value = rgar( $entry, (string) $input['id'] );
                
    //             var_dump($input['id'], $value);

    //             // do something with the value
    //         }
    //     } else {

    //         $value = rgar( $entry, (string) $field->id );

    //         var_dump($input['id'], $value);
    //         // do something with the value
    //     }
    // }





    // gform_update_meta( $entry['id'], 'source_id', 'test' );
    
    // $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
    // $stripe_publishable = '';

    // if($settings['webhooks_enabled'] == '1') {

    //     $process_the_gateway = false;

    //     if( $settings['api_mode'] == 'test' && $settings['test_publishable_key_is_valid'] == '1' && $settings['test_secret_key_is_valid'] == '1' ) {
    //         $stripe_publishable = $settings['test_publishable_key'];
    //     } elseif( $settings['api_mode'] == 'live' && $settings['live_publishable_key_is_valid'] == '1' && $settings['live_secret_key_is_valid'] == '1' ) {        
    //         $stripe_publishable = $settings['live_publishable_key'];
    //     }

    //     if($stripe_publishable != '') {



            // global $wpdb;
            // $entry_id = $entry['id'];
            // $prefix = $wpdb->prefix;

            // $wpdb->query(
            //     "UPDATE $prefix"."gf_entry
            //     SET status = 'deactive'
            //     WHERE id = $entry_id"
            // );

            // echo "
            // <script src='https://js.stripe.com/v3/'></script>            
            // <script>

            //     window.onload = function() {


            //         var ajaxurl = '".get_site_url()."/wp-admin/admin-ajax.php';

            //         // jQuery.post(ajaxurl, data, function(response) {
            //         //     console.log(response);
            //         // });
                    
            //         jQuery('.gform_confirmation_message').html('Redirecting...');

            //         var stripe = Stripe('".$stripe_publishable."');

            //         stripe.createSource({
            //             type: 'bancontact',
            //             amount: 50,
            //             currency: 'eur',
            //             statement_descriptor: 'ORDER AT11990',
            //             owner: {
            //                 name: 'Jenny Rosen',
            //             },

            //             redirect: {
            //                 return_url: 'https://stripe.pakistancouncilofyouth.com/2019/03/27/thank-you/',
            //             },

            //         }).then(function(result) {

            //             var data = {
            //                 result: result,
            //                 action: 'redirection_at_stripe'
            //             }

            //             jQuery.post(ajaxurl, data, function(response) {
            //                 console.log(response);
            //                 // window.location.href = result.source.redirect.url;
            //             });

            //         });

            //     }

            // </script>";

        // }
        
    // }

}



// add_action( 'wp_ajax_nopriv_redirection_at_stripe', 'redirection_at_stripe' );
// add_action( 'wp_ajax_redirection_at_stripe', 'redirection_at_stripe' );

// function redirection_at_stripe() { 
//     echo 'working';
//     die();
// }


// if(isset($_GET['client_secret'])) {
    
//     $source = $_GET['source'];
    
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=50&currency=eur&source=".$source);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_Ho1YZOJMnpnAS9xsW4TBwYef' . ':' . '');

//     $headers = array();
//     $headers[] = 'Content-Type: application/x-www-form-urlencoded';
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

//     $result = curl_exec($ch);
//     if (curl_errno($ch)) {
//         echo 'Error:' . curl_error($ch);
//     }
//     curl_close ($ch);

// }



