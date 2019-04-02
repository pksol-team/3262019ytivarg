<?php get_header(); ?>
<?php 

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
            

            global $wpdb;
            $prefix = $wpdb->prefix;

            $entry_row = $wpdb->get_row(
                "SELECT * FROM $prefix"."gf_entry_meta
                WHERE meta_key = 'source_id' AND meta_value = '$source' ", OBJECT
            );

            $entry_id = $entry_row->entry_id;
        
            $price_row = $wpdb->get_row(
                "SELECT * FROM $prefix"."gf_entry_meta
                WHERE entry_id = $entry_id AND meta_key = 'price'", OBJECT
            );    

            $price_amount = $entry_row->meta_value;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/sources/'.$source.'?client_secret='.$_GET['client_secret']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            curl_setopt($ch, CURLOPT_USERPWD, $stripe_publishable . ':' . '');

            $result = json_decode(curl_exec($ch));
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close ($ch);

        
            if($result->status == 'failed') {
             
                $msg = '<h1>Payment Failed</h1>
                <p>You didn\'t authorize the payment</p>';
            
            } else {

                $msg = '<h1>Thank You</h1>
                <p>Thank you for your payment we will contact you soon</p>';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=".$price_amount."&currency=eur&source=".$source);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERPWD, $stripe_publishable . ':' . '');

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                var_dump($result);
                
                if (curl_errno($ch)) {
                    
                    echo 'Error:' . curl_error($ch);
                    echo "Please Contact admin: ".get_option('admin_email');

                } else {

                    $wpdb->query(
                        "UPDATE $prefix"."gf_entry
                        SET status = 'active'
                        WHERE id = $entry_id"
                    );

                }

                curl_close ($ch);

            }

        }
    
    }

    



?>

<main>
    <?= $msg ?>
    <p id="timer"></p>
</main>

<script type="text/javascript">



    var count = 8;
    var redirect = "<?= get_site_url() ?>";

    function countDown() {
        var timer = document.getElementById("timer");

        if (timer != null) {
            if (count > 0) {
                count--;
                timer.innerHTML = "Redirecting you to the Home in " + count + " seconds.";
                setTimeout("countDown()", 1000);
            } else {
                // window.location.href = redirect;
            }
        }
    }
    countDown();
</script>


<?php get_footer(); ?> 