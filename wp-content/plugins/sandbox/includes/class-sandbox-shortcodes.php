<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Sandbox_Shortcodes', false ) ) :

    /**
     * Sandbox_Shortcodes.
     */
    class Sandbox_Shortcodes {

        /**
         * Constructor.
         */
        public function __construct() {
            add_shortcode( 'tdr_username', array($this, 'sandbox_get_username') );
            add_shortcode( 'tdr_password', array($this, 'sandbox_get_password') );
            add_shortcode( 'tdr_url', array($this, 'sandbox_get_url') );
            add_shortcode( 'tdr_email_input', array($this, 'sandbox_get_email_input') );
            add_shortcode( 'tdr_email_submit', array($this, 'sandbox_get_email_submit') );
            add_shortcode( 'tdr_subscribe', array($this, 'sandbox_get_subscribe') );
            add_shortcode( 'tdr_expiration_notice', array($this, 'sandbox_expiration_notice') );
            add_shortcode( 'tdr_extension_text_chrome', array($this, 'sandbox_extension_text_chrome') );
            add_shortcode( 'tdr_extension_button_chrome', array($this, 'sandbox_extension_button_chrome') );
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_username($atts ) {
            $sandbox_options = get_option('sandbox_options');
            return "<span id='tdr_username'>" . $sandbox_options['username'] . "</span>";
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_password($atts ) {
            $sandbox_options = get_option('sandbox_options');
            return "<span id='tdr_password'>" . $sandbox_options['password'] . "</span>";
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_url($atts ) {
            $sandbox_options = get_option('sandbox_options');
            return "<span id='tdr_url'>" . $sandbox_options['url'] . "</span>";
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_email_input($atts ) {
            return "<input type='text' name='tdr_email_input' id='tdr_email_input' placeholder='Email'/>";
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_email_submit($atts ) {
            return "<input type='submit' id='tdr_email_submit' value='". $atts['text'] ."'/>";
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_get_subscribe($atts ) {
            ob_start();
            ?>
            <div class="tdr_input_wrapper">
                <label for="tdr_subscribe"><?php echo $atts['text'];?></label>
                <input type='checkbox' id='tdr_subscribe' checked="checked"/>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_expiration_notice($atts) {
            $sandbox_options = get_option('sandbox_options');
            $expiration = Sandbox_API::getInstance()->get_expiration_date();
            $html = "<span id='tdr_expiration_notice'>";
                if (empty($expiration)){
                    $html .= __('This install has no expiration date.', 'sandbox');
                }
                else{
                    $diff = strtotime($expiration) - time();
                    if ($diff > 3600 * 24 * 6 && $diff < 3600 * 24 * 8){
                        $html .= __('This install will be deleted approximately 7 days after your last login.', 'sandbox');
                    }
                    else{
                        $html .= sprintf(__('This install is currently scheduled to be deleted on %s.', 'sandbox'), date("F jS, Y", strtotime($expiration)));
                    }
                }
            $html .= "</span>";
            return $html;
        }

        /**
         * @param $atts
         * @param null $content
         *
         * @return string
         */
        public function sandbox_extension_text_chrome($atts, $content = null ) {
            return "<div id='install-chrome' style='display: none;'>" . do_shortcode($content) . '</div>';
        }

        /**
         * @param $atts
         * @return string
         */
        public function sandbox_extension_button_chrome($atts ) {
            return "<a target='_blank' class='button-primary' href='https://chrome.google.com/webstore/detail/wp-sandbox/nhjkfekhgaccgjpoaebhgjhhbcbjhnco'>". $atts['text'] ."</a>";
        }
    }

endif;

return new Sandbox_Shortcodes();
