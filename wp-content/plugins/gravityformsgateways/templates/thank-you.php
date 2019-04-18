<?php 

    $settings = get_option('gravityformsaddon_gravityformsstripe_settings');
    $stripe_publishable = 'xyz';
    $stripe_secret = 'xyz';

    if($settings['webhooks_enabled'] == '1') {


        if( $settings['api_mode'] == 'test') {
            $stripe_publishable = $settings['test_publishable_key'];
            $stripe_secret = $settings['test_secret_key'];
        } elseif( $settings['api_mode'] == 'live') {
            $stripe_publishable = $settings['live_publishable_key'];
            $stripe_secret = $settings['live_secret_key'];
        }


        $source = $_GET['source'];
        
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

        $price_amount = $price_row->meta_value;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/sources/'.$source.'?client_secret='.$_GET['client_secret']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_USERPWD, $stripe_publishable . ':' . '');

        $result = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            echo "Please Contact admin: ".get_option('admin_email');
        }
        curl_close ($ch);

        if($result->status == 'failed') {
            
            $msg = '<h1>Payment Failed</h1>
            <p>You didn\'t authorize the payment</p>';
        
        } else {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=".$price_amount."&currency=eur&source=".$source);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret . ':' . '');

            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result2 = curl_exec($ch);

            if (curl_errno($ch)) {
                
                echo 'Error:' . curl_error($ch);
                echo "Please Contact admin: ".get_option('admin_email');
            }

            if(strlen($result2) > 150) {
                $msg = '<h1>Thank You</h1>
                <p>Thank you for your payment.</p>';

                $charge_date = date('Y-m-d H:i:s');
                $price_updated = $price_amount/100;

                $transaction = json_decode($result2);

                $method_row = $wpdb->get_row(
                    "SELECT * FROM $prefix"."gf_entry_meta
                    WHERE entry_id = $entry_id AND meta_key = 'method'", OBJECT
                );  
                $method = ucfirst($method_row->meta_value);


                $wpdb->query(
                    "UPDATE $prefix"."gf_entry
                    SET status = 'active', 
                     payment_status = 'Paid',
                     payment_date = '$charge_date', 
                     payment_amount = '$price_updated', 
                     payment_method = '$method',
                     transaction_id = '$transaction->id',
                     is_fulfilled = '1'
                    WHERE id = $entry_id"   
                );

                $wpdb->query(
                    "INSERT INTO $prefix"."gf_addon_payment_transaction
                    (lead_id, transaction_type, transaction_id, is_recurring, amount, date_created)
                    VALUES ('$entry_id', 'payment', '$transaction->id', '0', '$price_updated', '$charge_date' )"
                );


            } else {
                $msg = '<h1>Payment Failed</h1>
                <p>Something Went Wrong </p>'."Please Contact admin: ".get_option('admin_email');
            }

        
            curl_close ($ch);

        }

        
    
    }


?>

<main style="width: 34%; margin-top: 30vh; font-family: sans-serif; margin: 0 auto; border: 2px solid #eee; padding: 10px;">
    <?= $msg ?>
    <p id="timer"></p>
</main>


<script type="text/javascript">



    var count = 10;
    var redirect = "<?= get_site_url() ?>";

    function countDown() {
        var timer = document.getElementById("timer");

        if (timer != null) {
            if (count > 0) {
                count--;
                timer.innerHTML = "Redirecting you to the Home in " + count + " seconds.";
                setTimeout("countDown()", 1000);
            } else {
                window.location.href = redirect;
            }
        }
    }
    countDown();
</script>