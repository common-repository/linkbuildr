<?php
/**
 * Linkbuildr: Linkbuildr_API class
 *
 * The Linkbuildr API class uses the Module_Abstract class format and
 * handles the implementation of Linkbuildr's additions to WordPress REST API
 *
 * @package Linkbuildr
 * @subpackage Linkbuildr_API
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_API' ) ) {

	/**
	 * Linkbuildr_API class, handles implementing Linkbuildr's WordPress REST API endpoints.
	 *
	 * Registers, and implements callbacks for a custom WordPress REST API endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @see Module_Abstract
	 */
	class Linkbuildr_API extends Module_Abstract {
		/**
		 * Version of the API.
		 *
		 * @since 1.0.0
		 * @var String $version Stores the API version currently '1'
		 */
		protected $version;

		/**
		 * Holds the namespace/url for the begining of any API endpoints.
		 *
		 * @since 1.0.0
		 * @var String $namespace Holds the namespace/url for the begining of any API endpoints.
		 */
		protected $namespace;

		/**
		 * Defines which class properties are readable.
		 *
		 * @since 1.0.0
		 * @var Array $readable_properties Required as part of the Module_Abstract architecture,
		 *                                 contains String values of the names of class properties that are readable
		 */
		protected static $readable_properties = array( 'version', 'namespace' );

		/**
		 * Defines which class properties are writable.
		 *
		 * @since 1.0.0
		 * @var Array $writeable_properties Required as part of the Module_Abstract architecture,
		 *                                  contains String values of the names of class properties that are writable
		 */
		protected static $writeable_properties = array();

		/**
		 * Defines the level of access required to access the endpoints.
		 *
		 * @since 1.0.0
		 * @var String $REQUIRED_CAPABILITY Defines the level of access a user needs to have to be able to
		 *                                  access the API endpoints defined in this class.
		 */
		const REQUIRED_CAPABILITY = 'administrator';


		/*
		 * General methods
		 */

		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		protected function __construct() {

			$this->version   = '1';
			$this->namespace = 'linkbuildr/v' . $this->version;

			$this->register_hook_callbacks();
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'rest_api_init', array( $this, 'register_guten_api' ) );
		}

		/**
		 * Registers a custom WP REST API endpoint.
		 *
		 * Registers a custom WP REST API endpoint at /postNotificationData/{post_id}
		 * which returns data used by Linkbuildr to determine if a Notice should be
		 * displayed in the Block Editor
		 *
		 * @since 1.0.0
		 *
		 * @global function  register_rest_route
		 */
		public function register_guten_api() {
			register_rest_route(
				$this->namespace,
				'/postNotificationData/(?P<id>\d+)',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'linkbuildr_notification_data' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function( $param, $request, $key ) {
								return is_numeric( $param );
							},
						),
					),
					'permission_callback' => function () {
						return current_user_can( 'edit_others_posts' );
					},
				)
			);
		}

		/**
		 * Gets data used in determining Admin Notifications
		 *
		 * Function called by the Registered WP REST API route to get linkbuildr post specific data
		 * and filter it down to only relevent info and return it.
		 *
		 * @since 1.0.0
		 *
		 * @param array $request {
		 *     An array of the request data coming in from the WP REST API call.
		 *     @type int $id The id of the post currently in context when the request is made.
		 *
		 * }.
		 *
		 * @global Object $wpdb WordPress global db reference.
		 * @return Array $data {
		 *     @type int $showCount         The number of postSiteContact objects to be shown to the user in notifications.
		 *     @type array $postSiteContact An Array of a single instance of the data object PostSiteContacts, which
		 *                                  is the first Site Contact which the user should be notified of needing data.
		 * }
		 */
		public function linkbuildr_notification_data( $request ) {
			global $wpdb;

			$post_id_local = $request['id'];

			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];

			$posts_site_contacts                 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%d", $post_id_local ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			$posts_site_contacts_invalid_entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%d AND is_valid='false'", $post_id_local ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			$posts_site_contacts_unsent_entries  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%d AND is_sent='false'", $post_id_local ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			$show_count                          = count( $posts_site_contacts_invalid_entries );

			$first_posts_site_contacts = null;

			if ( $show_count > 0 ) {
				$first_posts_site_contacts = $posts_site_contacts_invalid_entries[0];
			}

			$display_notifications = get_option( 'linkbuildr_show_notifications' );
			if ( ! $display_notifications ) {
				$show_count = 0;
			}

			$data = array(
				'displayNotifications' => $display_notifications,
				'showCount'            => $show_count,
				'postSiteContact'      => $posts_site_contacts,
			);

			return $data;
		}

		/**
		 * Prepares site to use the plugin during activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide Flag to indicate if the activation is Multisite/Network wide.
		 */
		public function activate( $network_wide ) {
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {

		}

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary.
		 *
		 * @since 1.0.0
		 *
		 * @param string $db_version the version of the db to compare with the stored version value and know if we need to upgrade.
		 */
		public function upgrade( $db_version = 0 ) {

		}

		/**
		 * Checks that the object is in a correct state
		 *
		 * @mvc Model
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them.
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			// Note: __set() calls validate_settings(), so settings are never invalid.

			return true;
		}

	} // end linkbuildr_API
}
