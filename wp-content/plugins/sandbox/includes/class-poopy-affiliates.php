<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'poopy_affiliates', false ) ) :

    /**
     * Sandbox_Shortcodes.
     */
    class poopy_affiliates {

        public static function extension_notice() {
            ob_start();
            ?>
            <div class="chrome-extension-ad">
                <h4>try the new</h4>
                <h3>Poopy.life Chrome Extension</h3>
                <p>One-click instant WordPress installs right inside Chrome</p>
                <img src="<?php echo SANDBOX_ROOT_URL;?>/static/img/Extension-Preview.png" border="0" width="340px" />
                <a target='_blank' class='install-now' href='https://chrome.google.com/webstore/detail/wp-sandbox/nhjkfekhgaccgjpoaebhgjhhbcbjhnco'>Install Now</a>
                <p class="detailed-description">Keyboard shortcuts, templates, completely free gets rid of this obnoxious popup</p>
            </div>
            <?php
            echo ob_get_clean();
        }
    }

endif;

return new poopy_affiliates();
