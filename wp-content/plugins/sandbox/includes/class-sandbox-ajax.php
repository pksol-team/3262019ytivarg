<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Sandbox_Ajax', false ) ) :

    /**
     * Sandbox_Ajax.
     */
    class Sandbox_Ajax {

        /**
         * Constructor.
         */
        public function __construct() {
            add_action( 'wp_ajax_sandbox_notice_dismiss', array($this, 'sandbox_notice_dismiss') );
            add_action( 'wp_ajax_sandbox_change_php_version', array($this, 'sandbox_change_php_version') );
            add_action( 'wp_ajax_sandbox_send_confirmation_email', array($this, 'sandbox_send_confirmation_email') );
            add_action( 'wp_ajax_nopriv_sandbox_send_confirmation_email', array($this, 'sandbox_send_confirmation_email') );
            add_action( 'wp_ajax_sandbox_expiration_time', array($this, 'sandbox_expiration_time') );
            add_action( 'wp_ajax_nopriv_sandbox_expiration_time', array($this, 'sandbox_expiration_time') );
        }

        /**
         *
         */
        public function sandbox_notice_dismiss(){
            update_option('is_show_sandbox_notice', 0);
            exit(json_encode(array('result' => true)));
        }

        /**
         *
         */
        public function sandbox_send_confirmation_email(){

            $email_to_address = $_POST['email'];
            $subscribe = $_POST['subscribe'];
            $sandbox_options = get_option('sandbox_options');
            $install = get_option('sandbox_domain', false);
            $api_url = Sandbox_API::getInstance()->get_manage_url() . 'send_confirmation';

            $response = wp_remote_post( $api_url, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'install' => $install,
                        'domain' => Sandbox_API::SETTINGS_DOMAIN,
                        'email_to_address' => $email_to_address,
                        'test_drive_options' => $sandbox_options,
                        'subscribe' => $subscribe,
                        'is_poopy_site' => Sandbox_API::getInstance()->is_poopy_site()
                    ),
                )
            );
            exit(wp_remote_retrieve_body($response));
        }

        public function sandbox_change_php_version(){

            $version = $_POST['version'];

            $result = Sandbox_API::getInstance()->sandbox_change_php_version($version);

            if (empty($result['error'])){
                Sandbox::getInstance()->server_data['php_version'] = $version;
                set_transient(Sandbox_API::SERVER_DATA_TRANSIENT, Sandbox::getInstance()->server_data);
            }

            wp_send_json($result);
        }

        public function sandbox_expiration_time(){

            $expiration = get_transient( Sandbox_API::EXPIRATION_DATE_TRANSIENT );
            if ($expiration){
                if (current_time( 'timestamp' ) < (int) $expiration){
                    if (Sandbox_API::getInstance()->is_poopy_site()){
                        $timer_title = __('Remaining Time: ', 'sandbox') . Sandbox_API::get_date_diff( current_time( 'timestamp' ), (int) $expiration, 3);
                    }
                    else {
                        $timer_title = Sandbox_API::get_date_diff( current_time( 'timestamp' ), (int) $expiration, 3);
                    }
                }
                else{
                    $timer_title = __('Expired', 'sandbox');
                }
            }
            else{
                $timer_title = __('Sandbox', 'sandbox');
            }

            wp_send_json(array(
                'timer' => $timer_title
            ));
        }
    }

endif;

return new Sandbox_Ajax();
