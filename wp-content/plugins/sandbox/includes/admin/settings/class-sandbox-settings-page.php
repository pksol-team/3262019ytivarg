<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Sandbox_Settings_Page', false ) ) :

/**
 * Sandbox_Settings_Page.
 */
abstract class Sandbox_Settings_Page {

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var array|mixed|void
     */
    protected $sandbox_settings = array();

    /**
     * @var
     */
    public $sandbox_migration;

    /**
     * Error messages
     * @var WP_Error
     */
    protected $errors;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'sandbox_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'sandbox_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'sandbox_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'sandbox_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'sandbox_sections_upgrade_notice_' . $this->id, array( $this, 'get_upgrade_text' ));
        $this->errors = new WP_Error();
        $this->sandbox_settings = get_option('sandbox_settings', array());
	}

	/**
	 * Get settings page ID.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

    /**
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages
	 *
	 * @return mixed
	 */
	public function add_settings_page( $pages ) {
	    if ($this->is_visible()){
            $pages[ $this->id ] = $this->label;
        }
		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'sandbox_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'sandbox_get_sections_' . $this->id, array() );
	}

    /**
     *  Determines is user has permissions to view this page
     */
    public function is_visible(){
        return isset(Sandbox::getInstance()->server_data['permissions']['parent'][$this->id . '_tab']) ? intval(Sandbox::getInstance()->server_data['permissions']['parent'][$this->id . '_tab']) : 1;
    }

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=sandbox&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings.
	 */
	public function output() {

        if ( ! $this->is_visible() ) return FALSE;

        $this->sandbox_settings = get_option('sandbox_settings', array());

        $expiration = Sandbox_API::getInstance()->get_expiration_date();

        $this->setData(array(
            'expiration' => $expiration,
            'is_wizard'  => empty($this->sandbox_settings),
            'settings'   => $this->sandbox_settings,
            'web_host'   => Sandbox_API::getInstance()->get_host(),
            'domain'     => Sandbox_API::getInstance()->get_domain(),
            'options'    => get_option('sandbox_options', false)
        ));

        $view = Sandbox_Admin_Settings::get_page_view( $this->get_id() );

        include( $view );
	}

	/**
	 * Save settings.
	 */
	public function save(){}

}

endif;
