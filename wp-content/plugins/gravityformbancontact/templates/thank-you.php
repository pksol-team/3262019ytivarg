<?php get_header(); ?>
<main>

    <h1>Thank You</h1>
    <p>Thank you for your payment we will contact you soon</p>
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
                window.location.href = redirect;
            }
        }
    }
    countDown();
</script>


<?php get_footer(); ?> 