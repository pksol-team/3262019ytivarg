<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>


</div><!-- .col-full -->
</div><!-- #content -->


<?php


	// if(isset($_GET['source'])) {
	// 	$source = $_GET['source'];
		
	// 	$ch = curl_init();
	
	// 	curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=50&currency=eur&source=".$source);
	// 	curl_setopt($ch, CURLOPT_POST, 1);
	// 	curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_Ho1YZOJMnpnAS9xsW4TBwYef' . ':' . '');
	
	// 	$headers = array();
	// 	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	// 	$result = curl_exec($ch);
	// 	if (curl_errno($ch)) {
	// 		echo 'Error:' . curl_error($ch);
	// 	}
	// 	curl_close ($ch);
	
	// 	var_dump($result);

		
	// }



?>

<?php do_action('storefront_before_footer'); ?>

<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="col-full">

        <?php
		/**
			 * Functions hooked in to storefront_footer action
			 *
			 * @hooked storefront_footer_widgets - 10
			 * @hooked storefront_credit         - 20
			 */
		do_action('storefront_footer');
		?>

    </div><!-- .col-full -->
</footer><!-- #colophon -->

<?php do_action('storefront_after_footer'); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

<!-- <script src="https://js.stripe.com/v3/"></script> -->

<script>
	
	// var stripe = Stripe('pk_test_u6MyZSb3klmtwdkVWSWcvmJV');

	// Create an instance of Elements.
	// var elements = stripe.elements();
	// console.log(elements);

</script>


<script>
    // stripe.createSource({
	// 	type: 'bancontact',
    //     amount: 50,
    //     currency: 'eur',
	// 	statement_descriptor: 'ORDER AT11990',
    //     owner: {
    //         name: 'Jenny Rosen',
    //     },
    //     redirect: {
    //         return_url: 'https://stripe.pakistancouncilofyouth.com/',
	// 	},
    // }).then(function(result) {
    //     console.log(result);
	// });
	
	// stripe.handleCardPayment("src_client_secret_EligkT2UbUF9e4NRQYSvRRvN").then(function(response) {
	// 	if (response.error) {

	// 		// Handle error here
	// 	} else if (response.paymentIntent && response.paymentIntent.status === 'succeeded') {
	// 		// Handle successful payment here
	// 	}
	// });

</script> 



</body>

</html> 