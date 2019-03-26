<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Sandbox_API', false ) ) :

    /**
     * Sandbox_API.
     */
    class Sandbox_API {

        /**
         * Singletone instance
         * @var Sandbox_API
         */
        protected static $instance;

        /**
         * @var string
         */
        public $host = '';

        /**
         * free web domain
         */
        const POOPY_DOMAIN = 'poopy.life';

        /**
         * pro web domain
         */
        const PRO_DOMAIN = 'wpsandbox.pro';

        /**
         * manage app API path
         */
        const MANAGE_URI = 'api/request/';

        /**
         * prefix to get default website settings
         */
        const SETTINGS_DOMAIN = 'oxygen';

        /**
         *  Expiration date can be extended transient key
         */
        const IS_EXTEND_EXPIRATION_TRANSIENT = 'is_extend_expiration_date';

        /**
         *  Expiration date transient key
         */
        const EXPIRATION_DATE_TRANSIENT = 'expiration_date';

        /**
         *  Expiration date last_update
         */
        const EXPIRATION_DATE_LAST_UPDATE = 'expiration_date_last_update';

        /**
         *  Save server data
         */
        const SERVER_DATA_TRANSIENT = 'server_data_transient';

        /**
         *  Save server settings
         */
        const SERVER_SETTINGS_TRANSIENT = 'server_settings_transient';

        /**
         *  Known domain
         */
        const KNOWN_DOMAINS_TRANSIENT = 'known_domains_transient';

        /**
         *  License check
         */
        const LICENSE_CHECK_TRANSIENT = 'license_check_transient';

        /**
         * @var mixed|void
         */
        public $sandbox_domain;

        /**
         * @var array|mixed|void
         */
        protected $sandbox_settings = array();

        /**
         *
         * Available PHP versions
         *
         * @var array
         */
        public static $php_version = array('5.2', '5.3', '5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3');

        /**
         * Return singletone instance
         * @return Sandbox_API
         */
        static public function getInstance() {
            if (self::$instance == NULL) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct() {
            $this->init_host();
            $this->sandbox_domain = get_option('sandbox_domain', false);
            $this->sandbox_settings = get_option('sandbox_settings', array());
        }

        /**
         * Init web host poopy.life or wpsandbox.pro
         */
        private function init_host(){
            $url = site_url();
            $is_dev_environment = false;
            $web_domain_parts = explode('.', $url);
            array_shift($web_domain_parts);
            if (count($web_domain_parts) == 3){
                $web_dev = array_shift($web_domain_parts);
                if ($web_dev == 'web-dev'){
                    $is_dev_environment = true;
                }
            }

            $web_domain = strpos($url, self::POOPY_DOMAIN) !== false ? self::POOPY_DOMAIN : self::PRO_DOMAIN;
            if ($is_dev_environment){
                $web_domain = 'dev.' . $web_domain;
            }
            $this->host = 'http://' . $web_domain . '/';
        }

        /**
         * @return string
         */
        public function get_host(){
            return $this->host;
        }

        /**
         * @return string
         */
        public function get_domain(){
            return trim(preg_replace('%http:\/\/%', '', $this->get_host()), '/');
        }

        /**
         * @return string
         */
        public function get_manage_url(){
            return $this->get_host() . self::MANAGE_URI;
        }

        /**
         * @return bool
         */
        public function is_poopy_site(){
            $url = site_url();
            return strpos($url, self::POOPY_DOMAIN) !== false;
        }

        /**
         * @return bool|false|mixed|string
         */
        public function get_expiration_date(){

            $expiration_timestamp = get_transient( self::EXPIRATION_DATE_TRANSIENT );

            if (false === $expiration_timestamp){

                $server = $this->get_sandbox_data();

                $expiration = date('Y-m-d H:i:s', get_option('sandbox_expiration', strtotime('+1 hour')));

                if (isset($server['server_expiration_date'])){
                    if ( $server['server_keep_alive'] ){
                        $expiration = false;
                    }
                    else{
                        set_transient(self::EXPIRATION_DATE_TRANSIENT, strtotime($server['server_expiration_date']), HOUR_IN_SECONDS );
                        update_option(self::EXPIRATION_DATE_LAST_UPDATE, time());
                        $expiration = $server['server_expiration_date'];
                    }
                }
            }
            else{
                // calculate expiration date according to last update
                $last_update = get_option(self::EXPIRATION_DATE_LAST_UPDATE, false);
                if ($last_update){
                    $expiration_timestamp = $expiration_timestamp - (time() - $last_update);
                }
                $expiration = date('Y-m-d H:i:s', $expiration_timestamp);
            }

            return $expiration;
        }

        /**
         * @return array|bool|mixed|object
         */
        public function get_sandbox_data(){

            $response = get_transient( self::SERVER_DATA_TRANSIENT );

            if ( false === $response){

                $api_url = $this->get_manage_url() . 'sandbox';

                $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

                $response = self::remote_request( $api_url,  array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key ));

                $response = json_decode(wp_remote_retrieve_body($response), true);

                set_transient(self::SERVER_DATA_TRANSIENT, $response, HOUR_IN_SECONDS );

            }

            return $response;
        }

        /**
         * @return array|mixed|object|\WP_Error
         */
        public function get_sandbox_settings(){

            $response = get_transient( self::SERVER_SETTINGS_TRANSIENT );

            if ( false === $response){

                $api_url = $this->get_manage_url() . 'sandbox_get_server_settings';

                $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

                $server_data = $this->get_sandbox_data();

                if (!empty($server_data)) {

                    $response = self::remote_request($api_url, array(
                        'sid' => $server_data['id'],
                        'install' => $this->sandbox_domain,
                        'auth_key' => $auth_key
                    ));

                    $response = json_decode(wp_remote_retrieve_body($response), TRUE);

                    if (empty($response['error'])){
                        set_transient(self::SERVER_SETTINGS_TRANSIENT, $response, HOUR_IN_SECONDS);
                    }
                }
            }
            return $response;
        }

        /**
         *  Extend sandbox expiration date
         */
        public function sandbox_extend_expiration_date(){

            $is_expired = get_transient( self::IS_EXTEND_EXPIRATION_TRANSIENT );

            if ( false === $is_expired ){

                $api_url = $this->get_manage_url() . 'sandbox_expiration_date';

                $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

                $response = self::remote_request($api_url, array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key ));

                $response = json_decode(wp_remote_retrieve_body($response), true);

                if ( !empty($response['status']) && $response['status'] == true ){

                    set_transient(self::IS_EXTEND_EXPIRATION_TRANSIENT, true, HOUR_IN_SECONDS );
                }

                return $response;
            }

            return false;
        }

        /**
         *
         * Create / Update sandbox template
         *
         * @param bool $is_update
         * @return array|bool|mixed|object|\WP_Error
         */
        public function sandbox_create_template($is_update = false ){

            $settings = get_option('sandbox_settings', array());

            $template_domain = $is_update ? $settings['template_name'] : $this->sandbox_domain;

            if ( empty($template_domain) ) return false;

            $api_url = $this->get_manage_url() . 'sandbox_create_template';

            $auth_key = empty($settings['auth_key']) ? '' : $settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $template_domain, 'auth_key' => $auth_key ));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            // check if database dump was successfully created
            if ( ! empty($response['status']) && $response['status'] == 'success' ){
                // check if template achieve was successfully generated
                update_option('sandbox_settings', array(
                    'template_name' => $template_domain,
                    'template_date' => time(),
                    'auth_key'      => $response['new_auth_key'],
                    'creation_uri'  => 'create?src=' . $template_domain . '&key=' . $response['new_auth_key']
                ));
            }

            return $response;
        }

        /**
         *  Deleting sandbox template
         */
        public function sandbox_delete_template(){

            $api_url = $this->get_manage_url() . 'sandbox_delete_template';

            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

            self::remote_request($api_url, array( 'install' => $this->sandbox_settings['template_name'], 'auth_key' => $auth_key ));

            delete_option('sandbox_settings');
        }

        /**
         *  Migrate sandbox
         */
        public function sandbox_migrate($license){

            $sandbox_settings = get_option('sandbox_settings', array());

            $api_url = $this->get_manage_url() . 'sandbox_migrate';

            $auth_key = empty($sandbox_settings['auth_key']) ? '' : $sandbox_settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $sandbox_settings['template_name'], 'auth_key' => $auth_key, 'license' => $license ));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            return $response;
        }

        /**
         *
         * Extending sandbox expiration for one week
         *
         * @param $expiration_date
         * @return array|mixed|object|\WP_Error
         */
        public function sandbox_set_expiration_date($expiration_date){

            $api_url = $this->get_manage_url() . 'sandbox_expiration_date';

            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key, 'timestamp' => $expiration_date ));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            return $response;
        }

        /**
         *
         * Update sandbox permissions
         *
         * @param $permissions
         * @return array|mixed|object|\WP_Error
         */
        public function sandbox_update_permissions($permissions){

            $api_url = $this->get_manage_url() . 'sandbox_permissions';

            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key, 'permissions' => $permissions ));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            return $response;
        }

        /**
         *
         * Update sandbox welcome notices for child installs.
         *
         * @param $settings
         *
         * @return array|bool|mixed|object|\WP_Error
         */
        public function sandbox_update_welcome_notice($settings) {
            $api_url = $this->get_manage_url() . 'sandbox_server_settings';
            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];
            $server_data = $this->get_sandbox_data();
            $response = false;
            if (!empty($server_data)) {
                $response = self::remote_request( $api_url, array(
                    'sid' => $server_data['id'],
                    'install' => $this->sandbox_domain,
                    'auth_key' => $auth_key,
                    'settings' => json_encode($settings),
                ));
                $response = json_decode(wp_remote_retrieve_body($response), true);
            }
            return $response;
        }

        /**
         *  Remove sandbox expiration date
         */
        public function sandbox_keep_alive(){

            $api_url = $this->get_manage_url() . 'sandbox_keep_alive';

            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            return $response;
        }

        /**
         *
         * Change sandbox PHP version
         *
         * @param $version
         * @return array|mixed|object|\WP_Error
         */
        public function sandbox_change_php_version($version){

            $api_url = $this->get_manage_url() . 'sandbox_change_php_version';

            $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

            $response = self::remote_request($api_url, array( 'install' => $this->sandbox_domain, 'auth_key' => $auth_key, 'version' => $version));

            $response = json_decode(wp_remote_retrieve_body($response), true);

            return $response;
        }

        /**
         *
         * Get list of known domains
         *
         * @return array|mixed|object|\WP_Error
         */
        public function sandbox_known_domains(){

            $domains = get_transient( self::KNOWN_DOMAINS_TRANSIENT );

            if ( false === $domains ){

                $api_url = $this->get_manage_url() . 'sandbox_known_domains';

                $response = self::remote_request($api_url, array(), 'GET');

                if (!is_wp_error($response)){
                    $response = json_decode(wp_remote_retrieve_body($response), true);
                    if ( !empty($response) ){
                        set_transient(self::KNOWN_DOMAINS_TRANSIENT, $response, HOUR_IN_SECONDS );
                    }
                    return $response;
                }
                return array();
            }

            return $domains;
        }

        /**
         * @return bool
         */
        public function check_license(){

            $license_check = get_transient( self::LICENSE_CHECK_TRANSIENT );

            if ( false === $license_check ) {

                $api_url = $this->get_manage_url() . 'sandbox_check_is_license_valid';

                $auth_key = empty($this->sandbox_settings['auth_key']) ? '' : $this->sandbox_settings['auth_key'];

                $response = self::remote_request($api_url, array(
                    'install' => $this->sandbox_domain,
                    'auth_key' => $auth_key
                ));

                $response = json_decode(wp_remote_retrieve_body($response), TRUE);

                $license_check = ( ! empty($response['status']) && $response['status'] == TRUE ) ? 'valid' : 'not_valid';

                set_transient(self::LICENSE_CHECK_TRANSIENT, $license_check, HOUR_IN_SECONDS );

            }

            return $license_check == 'valid' ? TRUE : FALSE;
        }

        /**
         * @param $api_url
         * @param $data
         * @param string $method
         * @return array|\WP_Error
         */
        public static function remote_request($api_url, $data, $method = 'POST'){

            return wp_remote_post( $api_url, array(
                    'method' => $method,
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => $data
                )
            );
        }

        /**
         * Get human readable time difference between 2 dates
         *
         * Return difference between 2 dates in year, month, hour, minute or second
         * The $precision caps the number of time units used: for instance if
         * $time1 - $time2 = 3 days, 4 hours, 12 minutes, 5 seconds
         * - with precision = 1 : 3 days
         * - with precision = 2 : 3 days, 4 hours
         * - with precision = 3 : 3 days, 4 hours, 12 minutes
         *
         * From: http://www.if-not-true-then-false.com/2010/php-calculate-real-differences-between-two-dates-or-timestamps/
         *
         * @param mixed $time1 a time (string or timestamp)
         * @param mixed $time2 a time (string or timestamp)
         * @param integer $precision Optional precision
         * @return string time difference
         */
        public static function get_date_diff( $time1, $time2, $precision = 2 ) {
            // If not numeric then convert timestamps
            if( !is_int( $time1 ) ) {
                $time1 = strtotime( $time1 );
            }
            if( !is_int( $time2 ) ) {
                $time2 = strtotime( $time2 );
            }
            // If time1 > time2 then swap the 2 values
            if( $time1 > $time2 ) {
                list( $time1, $time2 ) = array( $time2, $time1 );
            }
            // Set up intervals and diffs arrays
            if (Sandbox_API::getInstance()->is_poopy_site()) {
                $intervals = array('hour', 'minute', 'second');
            }
            else{
                $intervals = array( 'year', 'month', 'day', 'hour', 'minute', 'second' );
            }
            $diffs = array();
            foreach( $intervals as $interval ) {
                // Create temp time from time1 and interval
                $ttime = strtotime( '+1 ' . $interval, $time1 );
                // Set initial values
                $add = 1;
                $looped = 0;
                // Loop until temp time is smaller than time2
                while ( $time2 >= $ttime ) {
                    // Create new temp time from time1 and interval
                    $add++;
                    $ttime = strtotime( "+" . $add . " " . $interval, $time1 );
                    $looped++;
                }
                $time1 = strtotime( "+" . $looped . " " . $interval, $time1 );
                $diffs[ $interval ] = $looped;
            }
            $count = 0;
            $times = array();
            foreach( $diffs as $interval => $value ) {
                // Break if we have needed precission
                if( $count >= $precision ) {
                    break;
                }
                // Add value and interval if value is bigger than 0
                if( $value > 0 ) {
                    if( $value != 1 ){
                        $interval .= "s";
                    }
                    // Add value and interval to times array
                    if (Sandbox_API::getInstance()->is_poopy_site()){
                        $times[] = $value < 10 ? '0' . $value : $value;
                    }
                    else{
                        $times[] = $value . " " . $interval;
                    }
                    $count++;
                }
                else{
                    if (Sandbox_API::getInstance()->is_poopy_site()) $times[] = '00';
                }
            }
            // Return string with times
            return Sandbox_API::getInstance()->is_poopy_site() ? implode( ":", $times ) : implode( ", ", $times );
        }
    }

endif;

Sandbox_API::getInstance();
