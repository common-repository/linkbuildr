<?php
/**
 * Linkbuildr: Linkbuildr_Settings class
 *
 * The Linkbuildr Settings class uses the Module_Abstract class format and
 * implements the Linkbuildr's Settings handling.
 *
 * @package Linkbuildr
 * @subpackage Linkbuildr_Settings
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_Settings' ) ) {

	/**
	 * Linkbuildr_Settings class, implementing Linkbuildr's Settings handling.
	 *
	 * Store variables constant throughtout implementation and handle Admin pages
	 *
	 * @since 1.0.0
	 *
	 * @see Module_Abstract
	 */
	class Linkbuildr_Settings extends Module_Abstract {
		/**
		 * Defines settings used.
		 *
		 * @since 1.0.0
		 * @var Array settings contains local copy of stored settings.
		 */
		protected $settings;

		/**
		 * Stores the table_names used throughout Linkbuildr
		 *
		 * @since 1.0.0
		 * @var Array $table_names stores the names of custom tables used by Linkbuildr.
		 */
		public static $table_names;

		/**
		 * Stores the url of the Meta CSS file
		 *
		 * @since 1.0.0
		 * @var String $meta_css_url Stores the url for the meta_css file.
		 */
		public static $meta_css_url;

		/**
		 * Stores the url of the logo file
		 *
		 * @since 1.0.0
		 * @var Array $logo_url Stores the url of the logo file.
		 */
		public static $logo_url;

		/**
		 * Holds the default settings for the plugin
		 *
		 * @since 1.0.0
		 * @var Array $default_settings stores the default settings for the plugin.
		 */
		protected static $default_settings;

		/**
		 * Defines which class properties are readable.
		 *
		 * @since 1.0.0
		 * @var Array $readable_properties Required as part of the Module_Abstract architecture,
		 *                                 contains String values of the names of class properties that are readable.
		 */
		protected static $readable_properties = array( 'settings', 'table_names', 'logo_url' );

		/**
		 * Defines which class properties are writable.
		 *
		 * @since 1.0.0
		 * @var String $writeable_properties Required as part of the Module_Abstract architecture,
		 *                                   contains String values of the names of class properties that are writable.
		 */
		protected static $writeable_properties = array( 'settings' );

		/**
		 * Const storing the required capability needed for part of this class to work
		 *
		 * @since 1.0.0
		 * @var String contains the required level of capability to implement portions of this class
		 */
		const REQUIRED_CAPABILITY = 'administrator';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 */
		protected function __construct() {
			global $wpdb;

			self::$table_names = array(
				'site_contacts_table'       => $wpdb->base_prefix . 'linkbuildr_site_contacts',
				'email_templates_table'     => $wpdb->base_prefix . 'linkbuildr_email_templates',
				'posts_site_contacts_table' => $wpdb->base_prefix . 'linkbuildr_posts_site_contacts',
				'ignore_domains'            => $wpdb->base_prefix . 'linkbuildr_ignore_domains',
			);

			self::$meta_css_url = plugin_dir_url( dirname( __FILE__ ) ) . 'css/metastyles.css';
			self::$logo_url     = plugin_dir_url( dirname( __FILE__ ) ) . 'img/linkbuilder_logo.svg';

			$this->register_hook_callbacks();
		}

		/**
		 * Public setter for protected variables
		 *
		 * Updates settings outside of the Settings API or other subsystems
		 *
		 * @param string $variable the variable to update.
		 * @param array  $value This will be merged with Linkbuildr_Settings->settings, so it should mimic the structure of the Linkbuildr_Settings::$default_settings. It only needs the contain the values that will change, though. See Linkbuildr->upgrade() for an example.
		 */
		public function __set( $variable, $value ) {
			// Note: Module_Abstract::__set() is automatically called before this.

			if ( 'settings' !== $variable ) {
				return;
			}

			$this->settings = self::validate_settings( $value );
			update_option( 'linkbuildr_settings', $this->settings );
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since 1.0.0
		 */
		public function register_hook_callbacks() {
			add_action( 'admin_menu', __CLASS__ . '::register_admin_menu_items' );
			add_action( 'admin_init', __CLASS__ . '::linkbuildr_register_settings' );
			add_action( 'init', array( $this, 'init' ) );
			add_filter(
				'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) ) . '/linkbuildr.php',
				__CLASS__ . '::add_plugin_action_links'
			);
		}

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 *
		 * @since 1.0.0
		 *
		 * @param Bool $network_wide Flag to indicate if the activation is Multisite/Network wide.
		 */
		public function activate( $network_wide ) {
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @since 1.0.0
		 */
		public function deactivate() {
		}

		/**
		 * Initializes settings
		 *
		 * @since 1.0.0
		 */
		public function init() {
			self::$default_settings = self::get_default_settings();
			$this->settings         = self::get_settings();
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
		 * Checks that the object is in a correct state.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them.
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			// Note: __set() calls validate_settings(), so settings are never invalid.

			return true;
		}

		/**
		 * Establishes initial values for all settings
		 *
		 * @since 1.0.0
		 *
		 * @return Array containing the default settings
		 */
		protected static function get_default_settings() {
			return array(
				'db-version' => '1.0',
			);
		}

		/**
		 * Retrieves all of the settings from the database
		 *
		 * @since 1.0.0
		 *
		 * @return Array containing Settings from the db
		 */
		protected static function get_settings() {
			$settings = shortcode_atts(
				self::$default_settings,
				get_option( 'linkbuildr_settings', array() )
			);

			return $settings;
		}

		/**
		 * Adds links to the plugin's action link section on the Plugins page
		 *
		 * @since 1.0.0
		 *
		 * @param Array $links The links currently mapped to the plugin.
		 * @return array
		 */
		public static function add_plugin_action_links( $links ) {
			$url = esc_url(
				add_query_arg(
					'page',
					'linkbuildr-user-settings',
					get_admin_url() . 'admin.php'
				)
			);

			$settings_link = "<a href='$url'>" . __( 'Settings', 'linkbuildr' ) . '</a>';
			$help_link     = '<a href="http://wordpress.org/support/plugin/linkbuildr/" target="_blank">' . __( 'Help', 'linkbuildr' ) . '</a>';

			array_unshift( $links, $help_link );
			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 * Adds pages to the Admin Panel menu
		 *
		 * @since 1.0.0
		 */
		public static function register_admin_menu_items() {
			$unlinked_items = self::getEmptySites();
			if ( 0 === intval( $unlinked_items ) ) {
				add_menu_page( __( 'Dashboard', 'linkbuildr' ), __( 'Linkbuildr', 'linkbuildr' ), 'activate_plugins', 'linkbuildr-dashboard', __CLASS__ . '::markup_linkbuildr_dashboard_page', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjAuNiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAzNCAzNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMzQgMzQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7ZmlsbDojMUEyMzdFO30KPC9zdHlsZT4KPGcgaWQ9IkxpbmtidWlsZHJfMzR4MzQiPgoJPGc+CgkJPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSI3LjksMTcuNyAzMi43LDguNSAzMyw4LjEgMSwxNS42IDcuOSwxNy43IAkJIi8+CgkJPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSI4LjcsMjYuMSA4LjcsMjYuMSA4LjcsMjYuMSAJCSIvPgoJCTxwb2x5Z29uIGNsYXNzPSJzdDAiIHBvaW50cz0iMTIuNSwxOS4xIDkuNSwyNS45IDE2LjEsMjAuOCAyMiwyMy41IDMyLjcsOC42IAkJIi8+Cgk8L2c+CjwvZz4KPC9zdmc+Cg==' );
				add_submenu_page( 'linkbuildr-dashboard', __( 'Dashboard', 'linkbuildr' ), __( 'Dashboard', 'linkbuildr' ), 'activate_plugins', 'linkbuildr-dashboard', __CLASS__ . '::markup_linkbuildr_dashboard_page' );
				add_submenu_page( 'linkbuildr-dashboard', __( 'Contacts', 'linkbuildr' ), __( 'Contacts', 'linkbuildr' ), 'activate_plugins', 'site-contacts', __CLASS__ . '::markup_site_contacts_table_page' );
			} else {
				add_menu_page( __( 'Dashboard', 'linkbuildr' ), __( 'Linkbuildr', 'linkbuildr' ) . '<span class="lb-menu-red-dot awaiting-mod"><span class="pending-count">' . $unlinked_items . '</span></span>', 'activate_plugins', 'linkbuildr-dashboard', __CLASS__ . '::markup_linkbuildr_dashboard_page', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjAuNiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAzNCAzNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMzQgMzQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7ZmlsbDojMUEyMzdFO30KPC9zdHlsZT4KPGcgaWQ9IkxpbmtidWlsZHJfMzR4MzQiPgoJPGc+CgkJPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSI3LjksMTcuNyAzMi43LDguNSAzMyw4LjEgMSwxNS42IDcuOSwxNy43IAkJIi8+CgkJPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSI4LjcsMjYuMSA4LjcsMjYuMSA4LjcsMjYuMSAJCSIvPgoJCTxwb2x5Z29uIGNsYXNzPSJzdDAiIHBvaW50cz0iMTIuNSwxOS4xIDkuNSwyNS45IDE2LjEsMjAuOCAyMiwyMy41IDMyLjcsOC42IAkJIi8+Cgk8L2c+CjwvZz4KPC9zdmc+Cg==' );
				add_submenu_page( 'linkbuildr-dashboard', __( 'Dashboard', 'linkbuildr' ), __( 'Dashboard', 'linkbuildr' ), 'activate_plugins', 'linkbuildr-dashboard', __CLASS__ . '::markup_linkbuildr_dashboard_page' );
				add_submenu_page( 'linkbuildr-dashboard', __( 'Contacts', 'linkbuildr' ), __( 'Contacts', 'linkbuildr' ) . '<span class="lb-menu-red-dot awaiting-mod"><span class="pending-count">' . $unlinked_items . '</span></span>', 'activate_plugins', 'site-contacts', __CLASS__ . '::markup_site_contacts_table_page' );
			}
			add_submenu_page( 'linkbuildr-dashboard', __( 'New Contact', 'linkbuildr' ), __( 'New Contact', 'linkbuildr' ), 'activate_plugins', 'site-contact-form', __CLASS__ . '::markup_site_contacts_form_page' );
			add_submenu_page( 'linkbuildr-dashboard', __( 'Import Contacts', 'linkbuildr' ), __( 'Import Contacts', 'linkbuildr' ), 'activate_plugins', 'import-site-contact', __CLASS__ . '::markup_site_contacts_import' );
			add_submenu_page( 'linkbuildr-dashboard', __( 'Ignored Domains', 'linkbuildr' ), __( 'Ignored Domains', 'linkbuildr' ), 'activate_plugins', 'ignored-domains', __CLASS__ . '::markup_ignored_domains_page' );
			add_submenu_page( 'linkbuildr-dashboard', __( 'Email Templates', 'linkbuildr' ), __( 'Email Templates', 'linkbuildr' ), 'activate_plugins', 'email-templates', __CLASS__ . '::markup_email_templates_table_page' );
			add_submenu_page( 'linkbuildr-dashboard', __( 'New Email Template', 'linkbuildr' ), __( 'New Email Template', 'linkbuildr' ), 'activate_plugins', 'email-template-form', __CLASS__ . '::markup_email_template_form_page' );

			add_submenu_page( 'linkbuildr-dashboard', __( 'Settings', 'linkbuildr' ), __( 'Settings', 'linkbuildr' ), 'activate_plugins', 'linkbuildr-user-settings', __CLASS__ . '::markup_linkbuildr_settings_page' );
		}

		/**
		 * Registers settings for Linkbuildr
		 *
		 * @since 1.1.0
		 */
		public static function linkbuildr_register_settings() {
			register_setting(
				'linkbuildr-user-settings',
				'linkbuildr_show_notifications'
			);

			register_setting(
				'linkbuildr-user-settings',
				'linkbuildr_default_send_on_publish'
			);

			register_setting(
				'linkbuildr-user-settings',
				'linkbuildr_unsubsribe_landing'
			);

			register_setting(
				'linkbuildr-user-settings',
				'linkbuildr_unsubsribe_message'
			);

			register_setting(
				'linkbuildr-user-settings',
				'linkbuildr_unsubsribe_link_text'
			);

			// Notification Settings Section.
			add_settings_section(
				'linkbuildr-settings-notifications',
				__( 'General Settings', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_settings_section_general',
				'linkbuildr-user-settings'
			);

			// Notification Settings Section.
			add_settings_section(
				'linkbuildr-settings-notifications',
				__( 'Notification Settings', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_settings_section_notifications',
				'linkbuildr-user-settings'
			);

			// Notifications Checkbox.
			add_settings_field(
				'linkbuildr_notification_toggle',
				__( 'Display New Contact Notfications', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_checkbox_setting_notifications',
				'linkbuildr-user-settings',
				'linkbuildr-settings-notifications'
			);

			// Default Send On Publish Checkbox.
			add_settings_field(
				'linkbuildr_default_on_publish_toggle',
				__( 'Send Email On Publish by Default', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_checkbox_default_send_on_publish',
				'linkbuildr-user-settings',
				'linkbuildr-settings-notifications'
			);

			// Unsubscribe Settings Section.
			add_settings_section(
				'linkbuildr-settings-unsubscribe',
				__( 'Unsubscribe Settings', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_settings_section_unsubscribe',
				'linkbuildr-user-settings'
			);

			// Unsubscribe Link Text.
			add_settings_field(
				'linkbuildr_unsubsribe_link_text',
				__( 'Text for the unsubscribe link in emails.', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_input_unsubscribe_link_text',
				'linkbuildr-user-settings',
				'linkbuildr-settings-unsubscribe'
			);

			// Landing Page Selection.
			add_settings_field(
				'linkbuildr_unsubsribe_landing_post_id',
				__( 'Page for Unsubscribe Link to land on.', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_select_landing_page',
				'linkbuildr-user-settings',
				'linkbuildr-settings-unsubscribe'
			);

			// Landing Page 404 when not in use Checkbox.
			add_settings_field(
				'linkbuildr_unsubsribe_404_landing_not_in_use',
				__( '404 Unsubscribe Landing Page when not in use.', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_checkbox_404_landing',
				'linkbuildr-user-settings',
				'linkbuildr-settings-unsubscribe'
			);

			// Unsubscribing Message.
			add_settings_field(
				'linkbuildr_unsubsribe_message',
				__( 'Message to display on Unsubscribe Landing Page', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_text_area_unsubscribe_message',
				'linkbuildr-user-settings',
				'linkbuildr-settings-unsubscribe'
			);

			// Already Unsubscribed Message.
			add_settings_field(
				'linkbuildr_already_unsubsribed_message',
				__( 'Message to display on Unsubscribe Landing Page, if user is already Unsubscribed', 'linkbuildr' ),
				__CLASS__ . '::linkbuildr_render_text_area_already_unsubscribed_message',
				'linkbuildr-user-settings',
				'linkbuildr-settings-unsubscribe'
			);

		}

		/**
		 * Creates the markup for the Linkbuildr Settings Nofication Section
		 *
		 * @since 1.3.0
		 * @return String Markup of the Settings Notfication Section.
		 */
		public static function linkbuildr_render_settings_section_general() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                           = array();
				$variables['settings_section_title'] = __( 'General Settings', 'linkbuildr' );

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-general-section.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Nofication Section
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Notfication Section.
		 */
		public static function linkbuildr_render_settings_section_notifications() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                                 = array();
				$variables['settings_section_title']       = __( 'Notification Settings', 'linkbuildr' );
				$variables['settings_section_description'] = __( 'Settings for notifications displayed within the WordPress Admin', 'linkbuildr' );

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-notification-section.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Unsubscribe Section
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Unsubscribe Section.
		 */
		public static function linkbuildr_render_settings_section_unsubscribe() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                                 = array();
				$variables['settings_section_title']       = __( 'Unsubscribe Settings', 'linkbuildr' );
				$variables['settings_section_description'] = __( 'Settings for Unsubscribe Landing Page', 'linkbuildr' );

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-unsubscribe-section.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Notification Checkbox
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Notification Checkbox.
		 */
		public static function linkbuildr_render_checkbox_setting_notifications() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                         = array();
				$variables['setting_name']         = 'linkbuildr_show_notifications';
				$linkbuildr_show_notifications     = get_option( 'linkbuildr_show_notifications' );
				$notification_toggle_setting_value = 0;
				if ( isset( $linkbuildr_show_notifications ) ) {
					$notification_toggle_setting_value = $linkbuildr_show_notifications;
				}
				$variables['setting_value'] = $notification_toggle_setting_value;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-checkbox-field-notifications.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Default Send On Publish
		 *
		 * @since 1.3.0
		 * @return String Markup of the Settings Default Send On Publish Checkbox.
		 */
		public static function linkbuildr_render_checkbox_default_send_on_publish() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                          = array();
				$variables['setting_name']          = 'linkbuildr_default_send_on_publish';
				$linkbuildr_default_send_on_publish = get_option( 'linkbuildr_default_send_on_publish' );
				$notification_toggle_setting_value  = 0;
				if ( isset( $linkbuildr_default_send_on_publish ) ) {
					$notification_toggle_setting_value = $linkbuildr_default_send_on_publish;
				}
				$variables['setting_value'] = $notification_toggle_setting_value;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-checkbox-field-on-publish-default.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Unsubscribe Landing 404 Checkbox
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Unsubscribe Landing 404 Checkbox.
		 */
		public static function linkbuildr_render_checkbox_404_landing() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                             = array();
				$variables['setting_group']            = 'linkbuildr_unsubsribe_landing';
				$variables['setting_name']             = 'linkbuildr_unsubsribe_404_landing_not_in_use';
				$current_user_settings                 = get_option( 'linkbuildr_unsubsribe_landing' );
				$use_landing_page_toggle_setting_value = 0;
				if ( isset( $current_user_settings['linkbuildr_unsubsribe_404_landing_not_in_use'] ) ) {
					$use_landing_page_toggle_setting_value = $current_user_settings['linkbuildr_unsubsribe_404_landing_not_in_use'];
				}
				$variables['setting_value'] = $use_landing_page_toggle_setting_value;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-checkbox-field-404-landing.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Landing Page Select
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Landing Page Select.
		 */
		public static function linkbuildr_render_select_landing_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                  = array();
				$variables['setting_group'] = 'linkbuildr_unsubsribe_landing';
				$variables['setting_name']  = 'linkbuildr_unsubsribe_landing_post_id';
				$options_group              = get_option( 'linkbuildr_unsubsribe_landing' );
				$current_value              = 0;
				if ( isset( $options_group['linkbuildr_unsubsribe_landing_post_id'] ) ) {
					$current_value = $options_group['linkbuildr_unsubsribe_landing_post_id'];
				}
				$variables['current_value'] = $current_value;

				$get_post_args      = array(
					'post_type'   => 'page',
					'post_status' => 'publish',
				);
				$pages              = get_posts( $get_post_args );
				$variables['pages'] = $pages;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-select-landing-field.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Unsubscribe Link Text Input
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Unsubscribe Link Text Input.
		 */
		public static function linkbuildr_render_input_unsubscribe_link_text() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                 = array();
				$variables['setting_name'] = 'linkbuildr_unsubsribe_link_text';
				$variables['link_text']    = get_option( 'linkbuildr_unsubsribe_link_text' );

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-input-link-text.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Unsubscribe Message Text Area
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Unsubscribe Message Text Area.
		 */
		public static function linkbuildr_render_text_area_unsubscribe_message() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables     = array();
				$setting_group = 'linkbuildr_unsubsribe_message';
				$setting_name  = 'unsubsribe_message';
				$option_value  = get_option( 'linkbuildr_unsubsribe_message' );
				$current_value = '';

				if ( isset( $option_value[ $setting_name ] ) ) {
					$current_value = $option_value[ $setting_name ];
				}

				$variables['attr_name']           = $setting_group . '[' . $setting_name . ']';
				$variables['unsubscribe_message'] = $current_value;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-textarea-unsubscribe-field.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings Already Unsubscribed Message Text Area
		 *
		 * @since 1.1.0
		 * @return String Markup of the Settings Already Unsubscribed Message Text Area.
		 */
		public static function linkbuildr_render_text_area_already_unsubscribed_message() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables     = array();
				$setting_group = 'linkbuildr_unsubsribe_message';
				$setting_name  = 'already_unsubscribed_message';
				$option_value  = get_option( 'linkbuildr_unsubsribe_message' );
				$current_value = '';

				if ( isset( $option_value[ $setting_name ] ) ) {
					$current_value = $option_value[ $setting_name ];
				}

				$variables['attr_name']                   = $setting_group . '[' . $setting_name . ']';
				$variables['already_unsubscribe_message'] = $current_value;

				return self::render_template( 'linkbuildr-settings/linkbuildr-settings-textarea-already-unsubscribed-field.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Linkbuildr Settings page
		 *
		 * @since 1.0.0
		 */
		public static function markup_linkbuildr_settings_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables                   = array();
				$variables['settings_group'] = 'linkbuildr_user_settings';
				$variables['logo_url']       = self::$logo_url;

				$variables['general_section']                  = self::linkbuildr_render_settings_section_general();
				$variables['send_on_publish_default_checkbox'] = self::linkbuildr_render_checkbox_default_send_on_publish();

				$variables['notification_section']  = self::linkbuildr_render_settings_section_notifications();
				$variables['notification_checkbox'] = self::linkbuildr_render_checkbox_setting_notifications();

				$variables['unsubscribe_section']                  = self::linkbuildr_render_settings_section_unsubscribe();
				$variables['unsubscribe_link_text']                = self::linkbuildr_render_input_unsubscribe_link_text();
				$variables['unsubscribe_landing_select']           = self::linkbuildr_render_select_landing_page();
				$variables['unsubscribe_404_checkbox']             = self::linkbuildr_render_checkbox_404_landing();
				$variables['unsubscribe_message_textarea']         = self::linkbuildr_render_text_area_unsubscribe_message();
				$variables['unsubscribe_already_message_textarea'] = self::linkbuildr_render_text_area_already_unsubscribed_message();

				echo self::render_template( 'linkbuildr-settings/linkbuildr-settings-page.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Site Contacts Table page
		 *
		 * @since 1.0.0
		 */
		public static function markup_linkbuildr_dashboard_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();

				require_once __DIR__ . '/class-linkbuildr-site-contacts-table.php';
				require_once __DIR__ . '/class-linkbuildr-email-templates-table.php';

				$site_contact_table = new Linkbuildr_Site_Contacts_Table( array( 'screen' => 'linkbuildr-dashboard' ) );
				$site_contact_table->prepare_items();

				$email_template_table = new Linkbuildr_Email_Templates_Table( array( 'screen' => 'linkbuildr-dashboard' ) );
				$email_template_table->prepare_items();

				$message = array();
				if ( 'delete' === $site_contact_table->current_action() ) {
					if ( isset( $_REQUEST['id'], $_REQUEST['_wpnonce'], $_REQUEST['table'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $site_contact_table->__get( '_args' )['plural'] )
					) {
						$table = sanitize_text_field( wp_unslash( $_REQUEST['table'] ) );
						if ( $table === $site_contact_table->__get( '_args' )['singular'] ) {
							$delete_count = 0;
							$id_vals      = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
							if ( is_array( $id_vals ) ) {
								$delete_count = count( array_map( 'sanitize_text_field', $id_vals ) );
							}

							// translators: %d: count of Contacts Deleted.
							$message[] = sprintf( __( 'Contacts deleted: %d', 'linkbuildr' ), $delete_count );
						}
					}
				}

				if ( 'delete' === $email_template_table->current_action() ) {
					if ( isset( $_REQUEST['id'], $_REQUEST['_wpnonce'], $_REQUEST['table'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $email_template_table->__get( '_args' )['plural'] )
					) {
						$table = sanitize_text_field( wp_unslash( $_REQUEST['table'] ) );
						if ( $table === $email_template_table->__get( '_args' )['singular'] ) {
							$delete_count = 0;
							$id_vals      = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
							if ( is_array( $id_vals ) ) {
								$delete_count = count( array_map( 'sanitize_text_field', $id_vals ) );
							}

							// translators: %d: count of Email Templates Deleted.
							$message[] = sprintf( __( 'Email Templates deleted: %d', 'linkbuildr' ), $delete_count );
						}
					}
				}

				$page = 'linkbuildr-dashboard';
				if ( isset( $_REQUEST['page'] ) ) {
					$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
				}

				$site_contact_list_url   = get_admin_url( get_current_blog_id(), 'admin.php?page=site-contacts' );
				$email_template_list_url = get_admin_url( get_current_blog_id(), 'admin.php?page=email-templates' );

				$variables['site_contact_list_url']   = $site_contact_list_url;
				$variables['email_template_list_url'] = $email_template_list_url;
				$variables['site_contact_table']      = $site_contact_table;
				$variables['email_template_table']    = $email_template_table;
				$variables['message']                 = $message;
				$variables['logo_url']                = self::$logo_url;
				$variables['page']                    = $page;

				echo self::render_template( 'linkbuildr-dashboard.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Site Contacts Table page
		 *
		 * @since 1.0.0
		 */
		public static function markup_site_contacts_table_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();

				require_once __DIR__ . '/class-linkbuildr-site-contacts-table.php';

				$table = new Linkbuildr_Site_Contacts_Table();
				$table->prepare_items();

				$message = array();
				if ( 'delete' === $table->current_action() ) {
					$delete_count    = 1;
					$pluralalization = '';

					if ( isset( $_REQUEST['id'], $_REQUEST['_wpnonce'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $table->__get( '_args' )['plural'] )
					) {
						$id_vals = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( is_array( $id_vals ) ) {
							$delete_count = count( array_map( 'sanitize_text_field', $id_vals ) );
						}

						if ( 1 < $delete_count ) {
							$pluralalization = 's';
						}

						// translators: %d: count of Contacts Deleted, %s add 's' if plural.
						$message[] = sprintf( __( '%1$d Contact%2$s deleted.', 'linkbuildr' ), $delete_count, $pluralalization );
					}
				}

				$page = 'site-contact-form';
				if ( isset( $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$edit_form_url = get_admin_url( get_current_blog_id(), 'admin.php?page=site-contact-form' );

				$variables['edit_form_url'] = $edit_form_url;
				$variables['table']         = $table;
				$variables['message']       = $message;
				$variables['logo_url']      = self::$logo_url;
				$variables['page']          = $page;

				echo self::render_template( 'site-contacts-list.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Site Contacts Import page and also handles the submission of the import.
		 *
		 * @since 1.2.0
		 */
		public static function markup_site_contacts_import() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				global $wpdb;
				$email_templates_table_name = self::$table_names['email_templates_table'];

				$variables = array();
				$message   = array();
				$notice    = array();

				// this is default $item which will be used for new records.
				$default = array(
					'id'                => 0,
					'domain'            => '',
					'sitename'          => '',
					'firstname'         => '',
					'email'             => '',
					'templatechoice'    => '',
					'email_template_id' => null,
					'unsubscribed'      => 0,
				);

				// here we are verifying does this request is post back and have correct nonce.
				if ( array_key_exists( 'nonce', $_REQUEST ) && wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), basename( __FILE__ ) ) ) {

					if ( array_key_exists( 'lb-colMap', $_REQUEST ) && isset( $_REQUEST['lb-colMap'] ) && array_key_exists( 'lb-import-data', $_REQUEST ) && isset( $_REQUEST['lb-import-data'] ) ) {

						$site_contacts_table_name = self::$table_names['site_contacts_table'];
						$form_value_delimiter     = '#zlbz#';

						$import_errors     = array();
						$import_duplicates = array();

						$column_map       = explode( $form_value_delimiter, sanitize_text_field( wp_unslash( $_REQUEST['lb-colMap'] ) ) );
						$import_data      = wp_unslash( $_REQUEST['lb-import-data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						$import_data_sans = sanitize_text_field( $import_data );

						$column_map_count  = count( $column_map );
						$import_data_count = count( $import_data );

						$imported_count = 0;
						$skipped_count  = 0;

						if ( array_key_exists( 'skip-count', $_REQUEST ) && isset( $_REQUEST['skip-count'] ) ) {
							$skipped_count = intval( wp_unslash( $_REQUEST['skip-count'] ) );
						}

						$test_site_contact_list = array();

						for ( $i = 0; $i < $import_data_count; $i++ ) {

							$current_row = explode( $form_value_delimiter, sanitize_text_field( $import_data[ $i ] ) );

							$new_site_contact = array();

							for ( $j = 0; $j < $column_map_count; $j++ ) {
								if ( 'domain' === $column_map[ $j ] ) {
									$new_site_contact['domain'] = $current_row[ $j ];
								}
								if ( 'site' === $column_map[ $j ] ) {
									$new_site_contact['sitename'] = $current_row[ $j ];
								}
								if ( 'name' === $column_map[ $j ] ) {
									$new_site_contact['firstname'] = $current_row[ $j ];
								}
								if ( 'email' === $column_map[ $j ] ) {
									$new_site_contact['email'] = $current_row[ $j ];
								}
								if ( 'template' === $column_map[ $j ] ) {
									$new_site_contact['email_template_id'] = intval( $current_row[ $j ] );
								}
							}

							$final_new_site_contact                     = shortcode_atts( $default, $new_site_contact );
							$final_new_site_contact['unsubscribed_key'] = self::generate_unsubscribe_key( $new_site_contact['domain'] . time() );
							$dupe_results                               = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE domain = %s", $new_site_contact['domain'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
							if ( $dupe_results ) {
								$import_duplicates[] = $new_site_contact['domain'];
							} else {
								$insert_result  = $wpdb->insert( $site_contacts_table_name, $final_new_site_contact ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
								$new_contact_id = $wpdb->insert_id;
								if ( $insert_result ) {
									$imported_count++;
								} else {
									$import_errors[] = $new_site_contact['domain'];
								}
							}
						}

						if ( 0 < $imported_count ) {
							if ( 1 < $imported_count ) {
								// translators: %s: The number of Contacts that were imported.
								$message[] = sprintf( __( '%s Contacts successfully imported.', 'linkbuildr' ), $imported_count );
							} else {
								// translators: %s: The number of Contacts that were imported, when there is only 1.
								$message[] = sprintf( __( '%s Contact successfully imported.', 'linkbuildr' ), $imported_count );
							}
						}

						if ( 0 < $skipped_count ) {
							if ( 1 < $skipped_count ) {
								// translators: %s: The number of Contacts that were skipped due to invalid data.
								$notice[] = sprintf( __( '%s Contacts skipped due to invalid data.', 'linkbuildr' ), $skipped_count );
							} else {
								// translators: %s: The number of Contacts that were skipped due to invalid data, when there is only 1.
								$notice[] = sprintf( __( '%s Contact skipped due to invalid data.', 'linkbuildr' ), $skipped_count );
							}
							$notice[] = 'lb-line-break';
						}

						$error_count = count( $import_errors );
						if ( 0 < $error_count ) {
							if ( 1 < $error_count ) {
								// translators: %s: The number of Contacts that threw an error on import.
								$notice[] = sprintf( __( '%s Contacts encountered errors while importing.', 'linkbuildr' ), $error_count );
								$notice[] = __( 'The Contacts that encountered errors were associated with the following domains:', 'linkbuildr' );
							} else {
								// translators: %s: The number of Contacts that threw an error on import, when there is only 1.
								$notice[] = sprintf( __( '%s Contact encountered an error while importing.', 'linkbuildr' ), $error_count );
								$notice[] = __( 'The Contact that encountered an error was associated with the following domain:', 'linkbuildr' );
							}

							foreach ( $import_errors as $error_domain ) {
								$notice[] = ' - ' . $error_domain;
							}
							$notice[] = 'lb-line-break';
						}

						$dupe_count = count( $import_duplicates );
						if ( 0 < $dupe_count ) {
							if ( 1 < $dupe_count ) {
								// translators: %s: The number of Contacts that were duplicate.
								$notice[] = sprintf( __( '%s Contacts already had thier domain registered in the Contacts.', 'linkbuildr' ), $dupe_count );
								$notice[] = __( 'The duplicate Contacts were associated with the following domains:', 'linkbuildr' );
							} else {
								// translators: %s: The number of Contacts that were duplicate, when there is only 1.
								$notice[] = sprintf( __( '%s Contact already had its domain registered in a Contact.', 'linkbuildr' ), $dupe_count );
								$notice[] = __( 'The duplicate Contact was associated with the following domain:', 'linkbuildr' );
							}

							foreach ( $import_duplicates as $dupe_domain ) {
								$notice[] = ' - ' . $dupe_domain;
							}
						}
					}
				}

				$email_templates_table_name = self::$table_names['email_templates_table'];
				$email_templates            = $wpdb->get_results( "SELECT * FROM $email_templates_table_name", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

				$backlink_url = get_admin_url( get_current_blog_id(), 'admin.php?page=site-contacts' );
				$view_nonce   = wp_create_nonce( basename( __FILE__ ) );

				$variables['email_templates'] = $email_templates;
				$variables['notice']          = $notice;
				$variables['message']         = $message;
				$variables['logo_url']        = self::$logo_url;
				$variables['max_input_vars']  = ini_get( 'max_input_vars' );
				$variables['nonce']           = $view_nonce;

				echo self::render_template( 'site-contact-importer.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput

			} else {
				wp_die( 'Access denied.' );
			}

		}

		/**
		 * Creates the markup for the Site Contacts Table page
		 *
		 * @since 1.0.0
		 */
		public static function markup_ignored_domains_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();
				$message   = array();
				$notice    = array();

				$ignore_domains_table_name = self::$table_names['ignore_domains'];

				// $cache_key                   = 'ignore_domains_index_list';
				// Remove cache for actual filtering too
				$cache_key = 'ignored_domains_table_items';

				if ( isset( $_REQUEST['new-ignore-domain'], $_REQUEST['new-domain-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['new-domain-nonce'] ), basename( __FILE__ ) ) ) {
					$new_ignore_domain = sanitize_text_field( wp_unslash( $_REQUEST['new-ignore-domain'] ) );
					if ( '' !== $new_ignore_domain ) {
						global $wpdb;
						$result = $wpdb->insert( $ignore_domains_table_name, array( 'domain' => $new_ignore_domain ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

						if ( $result ) {
							// translators: %s: The Domain to be ignored.
							$message[] = sprintf( __( '%s added to Ignored Domains', 'linkbuildr' ), $new_ignore_domain );

							wp_cache_delete( $cache_key );
						} else {
							$notice[] = __( 'There was an error while adding domain to ignored domains', 'linkbuildr' );
						}
					}
				}

				require_once __DIR__ . '/class-linkbuildr-ignored-domains-table.php';

				$table = new Linkbuildr_Ignored_Domains_Table();
				$table->prepare_items();

				if ( 'delete' === $table->current_action() ) {
					$delete_count    = 1;
					$pluralalization = '';

					if ( isset( $_REQUEST['id'], $_REQUEST['_wpnonce'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $table->__get( '_args' )['plural'] )
					) {
						$id_vals = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( is_array( $id_vals ) ) {
							$delete_count = count( array_map( 'sanitize_text_field', $id_vals ) );
						}

						if ( 1 < $delete_count ) {
							$pluralalization = 's';
						}

						// translators: %d: count of Ignored Domains Deleted, %s add 's' if plural.
						$message[] = sprintf( __( '%1$d Ignored Domain%2$s deleted.', 'linkbuildr' ), $delete_count, $pluralalization );
						wp_cache_delete( $cache_key );
					}
				}

				$view_nonce = wp_create_nonce( basename( __FILE__ ) );

				$variables['table']          = $table;
				$variables['message']        = $message;
				$variables['logo_url']       = self::$logo_url;
				$variables['new_form_nonce'] = $view_nonce;

				echo self::render_template( 'ignored-domains-list.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Site Contacts Form page
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 * @global Object $post WordPress global current post reference.
		 */
		public static function markup_site_contacts_form_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();
				global $wpdb;
				global $post;

				$site_contacts_table_name       = self::$table_names['site_contacts_table'];
				$posts_site_contacts_table_name = self::$table_names['posts_site_contacts_table'];

				$message    = array();
				$notice     = array();
				$title_type = 'Edit';

				// $nb tells us if this is in an iFrame/modal.
				$nb = isset( $_GET['nb'] ) ? intval( wp_unslash( $_GET['nb'] ) ) : '';

				// this is default $item which will be used for new records.
				$default = array(
					'id'                => 0,
					'domain'            => '',
					'sitename'          => '',
					'firstname'         => '',
					'email'             => '',
					'templatechoice'    => '',
					'email_template_id' => null,
					'unsubscribed'      => 0,
				);

				// here we are verifying does this request is post back and have correct nonce.
				if ( array_key_exists( 'nonce', $_REQUEST ) && wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), basename( __FILE__ ) ) ) {
					$submitted_site_contact_values = $_REQUEST;

					if ( isset( $submitted_site_contact_values['unsubscribed'] ) ) {
						$submitted_site_contact_values['unsubscribed'] = 1;
					}

					// combine our default item with request params.
					$item = shortcode_atts( $default, $submitted_site_contact_values );

					// validate data, and if all ok save item to database.
					// if id is zero insert otherwise update.
					$item_valid = self::Site_Contact_Form_Validator( $item );
					if ( true === $item_valid ) {
						if ( 0 === intval( $item['id'] ) ) {
							// If this is a new Site Contact, generate an unsubscribe_key for it.
							$item['unsubscribed_key'] = self::generate_unsubscribe_key( $item['domain'] . time() );

							$result     = $wpdb->insert( $site_contacts_table_name, $item ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$item['id'] = $wpdb->insert_id;
							if ( $result ) {
								// translators: %s: The Domain (aka - Name/Url) of the Site Contact saved.
								$message[] = sprintf( __( '%s was successfully saved', 'linkbuildr' ), $item['domain'] );
							} else {
								$notice[] = __( 'There was an error while saving item', 'linkbuildr' );
							}
						} else {
							$result = $wpdb->update( $site_contacts_table_name, $item, array( 'id' => $item['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							if ( false !== $result ) {
								// translators: %s: The Domain (aka - Name/Url) of the Site Contact updated.
								$message[] = sprintf( __( '%s was successfully updated', 'linkbuildr' ), $item['domain'] );
							} else {
								$notice[] = __( 'There was an error while updating item', 'linkbuildr' );
							}
						}

						if ( '' !== $item['email'] && ( ( null !== $item['email_template_id'] ) || ( '' !== $item['templatechoice'] ) ) ) {
							$wpdb->update( $posts_site_contacts_table_name, array( 'is_valid' => 2 ), array( 'site_contact_id' => $item['id'] ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						}
						wp_cache_flush();
					} else {
						// if $item_valid not true it contains error message(s).
						array_push( $notice, $item_valid );
					}
				} else {
					if ( 1 === intval( $nb ) ) {
						if ( isset( $_GET['scid'] ) ) {
							$scid_val = intval( wp_unslash( $_GET['scid'] ) );

							$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE id = %d", $scid_val ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						}
					} else {
						// if this is not post back we load item to edit or give new one to create.
						if ( isset( $_GET['id'] ) ) {
							$id_val = intval( wp_unslash( $_GET['id'] ) );

							$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE id = %d", $id_val ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						} else {
							$item       = $default;
							$title_type = 'New';
						}
					}
				}

				// if after all of that we have an invalid item, then set as default and throw an error.
				if ( ! $item ) {
					$item     = $default;
					$notice[] = __( 'Item not found', 'linkbuildr' );
				}

				if ( 1 === intval( $nb ) ) {

					$current_post_site_contact_id = null;
					if ( isset( $_GET['id'] ) ) {
						$current_post_site_contact_id = intval( wp_unslash( $_GET['id'] ) );
					}

					$site_contact_id = null;
					if ( isset( $_GET['scid'] ) ) {
						$site_contact_id = intval( wp_unslash( $_GET['scid'] ) );
					}

					$blog_id_local = null;
					if ( isset( $_GET['bid'] ) ) {
						$blog_id_local = intval( wp_unslash( $_GET['bid'] ) );
					}

					if ( isset( $_GET['pid'] ) ) {
						$post_id_local = intval( wp_unslash( $_GET['pid'] ) );
					} elseif ( array_key_exists( 'post', $_GET ) ) {
						$post_id_local = get_query_var( 'post' );
					} else {
						$post_id_local = $post->ID;
					}

					$post_site_contacts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%s AND blog_id=%s  AND is_valid='false'", array( $post_id_local, $blog_id_local ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

					$site_contacts_to_update = array();

					foreach ( $post_site_contacts as $psc ) {
						$sc_temp = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE id=%s", $psc['site_contact_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						array_push( $site_contacts_to_update, $sc_temp );
					}

					$variables['site_contacts_to_update']      = $site_contacts_to_update;
					$variables['post_site_contacts']           = $post_site_contacts;
					$variables['current_post_site_contact_id'] = $current_post_site_contact_id;
					$variables['site_id']                      = $site_contact_id;
					$variables['post_id_local']                = $post_id_local;
					$variables['blog_id_local']                = $blog_id_local;
				}

				$email_templates_table_name = self::$table_names['email_templates_table'];
				$email_templates            = $wpdb->get_results( "SELECT * FROM $email_templates_table_name", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

				$backlink_url = get_admin_url( get_current_blog_id(), 'admin.php?page=site-contacts' );
				$view_nonce   = wp_create_nonce( basename( __FILE__ ) );

				$variables['title_type']      = $title_type;
				$variables['email_templates'] = $email_templates;
				$variables['nb']              = $nb;
				$variables['item']            = $item;
				$variables['notice']          = $notice;
				$variables['message']         = $message;
				$variables['logo_url']        = self::$logo_url;
				$variables['backlink_url']    = $backlink_url;
				$variables['nonce']           = $view_nonce;

				echo self::render_template( 'site-contact-form.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput

			} else {
				wp_die( 'Access denied.' );
			}

		}

		/**
		 * Simple function that validates data and retrieve bool on success
		 * and error message(s) on error.
		 *
		 * @since 1.0.0
		 *
		 * @param Object $item an instance of Site Contacts to be validated.
		 * @return bool|string boolean value true if successful, and string errors if failed
		 */
		protected static function Site_Contact_Form_Validator( $item ) {
			$messages = array();
			if ( empty( $item['domain'] ) ) {
				$messages[] = __( 'Domain is required', 'linkbuildr' );
			}
			if ( empty( $item['sitename'] ) ) {
				$messages[] = __( 'Site Name is required', 'linkbuildr' );
			}
			if ( empty( $item['firstname'] ) ) {
				$messages[] = __( 'First Name is required', 'linkbuildr' );
			}
			if ( empty( $item['email'] ) ) {
				$messages[] = __( 'Email is required', 'linkbuildr' );
			}
			if ( empty( $item['email_template_id'] ) ) {
				$messages[] = __( 'Message Template is required', 'linkbuildr' );
			}

			if ( empty( $messages ) ) {
				return true;
			}
			return $messages;
		}

		/**
		 * Creates the markup for the Email Templates Table page
		 *
		 * @since 1.0.0
		 */
		public static function markup_email_templates_table_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();

				require_once __DIR__ . '/class-linkbuildr-email-templates-table.php';

				$page = 'email-template-form';
				if ( isset( $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$table = new Linkbuildr_Email_Templates_Table();
				$table->prepare_items();

				$message = array();
				if ( 'delete' === $table->current_action() ) {
					$delete_count    = 1;
					$pluralalization = '';

					if ( isset( $_REQUEST['id'], $_REQUEST['_wpnonce'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-' . $table->__get( '_args' )['plural'] )
					) {
						$id_vals = wp_unslash( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( is_array( $id_vals ) ) {
							$delete_count = count( array_map( 'sanitize_text_field', $id_vals ) );
						}

						if ( 1 < $delete_count ) {
							$pluralalization = 's';
						}

						// translators: %d: count of Email Templates Deleted, %s add 's' if plural.
						$message[] = sprintf( __( '%1$d Email Template%2$s deleted.', 'linkbuildr' ), $delete_count, $pluralalization );
					}
				}

				$edit_form_url = get_admin_url( get_current_blog_id(), 'admin.php?page=email-template-form' );

				$variables['page']          = $page;
				$variables['table']         = $table;
				$variables['logo_url']      = self::$logo_url;
				$variables['edit_form_url'] = $edit_form_url;
				$variables['message']       = $message;

				echo self::render_template( 'email-templates-list.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Creates the markup for the Email Templates Table page
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 */
		public static function markup_email_template_form_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				$variables = array();

				global $wpdb;

				$email_templates_table_name = self::$table_names['email_templates_table'];

				$message    = array();
				$notice     = array();
				$title_type = 'New';

				// this is default $item which will be used for new records.
				$default = array(
					'id'           => 0,
					'templatename' => '',
					'sender'       => 'Post Author',
					'subject'      => '',
					'content'      => '',
					'tweet'        => '',
				);

				// here we are verifying does this request is post back and have correct nonce.
				if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), basename( __FILE__ ) ) ) {
					// combine our default item with request params.
					$item = shortcode_atts( $default, $_REQUEST );
					// validate data, and if all ok save item to database.
					// if id is zero insert otherwise update.
					$item_valid = self::Email_Template_Form_Validator( $item );
					if ( true === $item_valid ) {
						if ( 0 === intval( $item['id'] ) ) {
							$result     = $wpdb->insert( $email_templates_table_name, $item ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$item['id'] = $wpdb->insert_id;
							if ( $result ) {
								// translators: %s: The name of the Email Template saved.
								$message[] = sprintf( __( '%s was successfully saved', 'linkbuildr' ), $item['templatename'] );
							} else {
								$notice[] = __( 'There was an error while saving item', 'linkbuildr' );
							}
						} else {
							$result = $wpdb->update( $email_templates_table_name, $item, array( 'id' => $item['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							if ( false !== $result ) {
								// translators: %s: The name of the Email Template updated.
								$message[] = sprintf( __( '%s was successfully updated', 'linkbuildr' ), $item['templatename'] );
							} else {
								$notice[] = __( 'There was an error while updating item', 'linkbuildr' );
							}
						}
						wp_cache_flush();
					} else {
						// if $item_valid not true it contains error message(s).
						array_push( $notice, $item_valid );
					}
				} else {
					// if this is not post back we load item to edit or give new one to create.
					$item = $default;
					if ( isset( $_REQUEST['id'] ) ) {
						$id_val = intval( wp_unslash( $_REQUEST['id'] ) );
						$item   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $email_templates_table_name WHERE id = %d", $id_val ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

						if ( ! $item ) {
							$item     = $default;
							$notice[] = __( 'Item not found', 'linkbuildr' );
						} else {
							$title_type = 'Edit';
						}
					}
				}

				// here we adding our custom meta box.

				$backlink_url = get_admin_url( get_current_blog_id(), 'admin.php?page=email-templates' );
				$view_nonce   = wp_create_nonce( basename( __FILE__ ) );

				$contentformat = stripslashes( esc_attr( $item['content'] ) );
				$subjectformat = stripslashes( esc_attr( $item['subject'] ) );
				$tweetformat   = stripslashes( esc_attr( $item['tweet'] ) );

				$variables['nonce']         = $view_nonce;
				$variables['logo_url']      = self::$logo_url;
				$variables['backlink_url']  = $backlink_url;
				$variables['message']       = $message;
				$variables['notice']        = $notice;
				$variables['title_type']    = $title_type;
				$variables['item']          = $item;
				$variables['contentformat'] = $contentformat;
				$variables['subjectformat'] = $subjectformat;
				$variables['tweetformat']   = $tweetformat;

				echo self::render_template( 'email-template-form.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Simple function that validates data and retrieve bool on success
		 * and error message(s) on error
		 *
		 * @since 1.0.0
		 *
		 * @param Object $item an instance of the Email Template to be validated.
		 * @return bool|string a boolean value of true if valid and error messages if not.
		 */
		protected static function Email_Template_Form_Validator( $item ) {
			$messages = array();

			if ( empty( $item['templatename'] ) ) {
				$messages[] = __( 'Template Name is required', 'linkbuildr' );
			}
			if ( empty( $item['subject'] ) ) {
				$messages[] = __( 'Subject is required', 'linkbuildr' );
			}
			if ( empty( $item['content'] ) ) {
				$messages[] = __( 'Content is required', 'linkbuildr' );
			}
			if ( empty( $item['tweet'] ) ) {
				$messages[] = __( 'Tweet is required', 'linkbuildr' );
			}

			if ( empty( $messages ) ) {
				return true;
			}
			return $messages;
		}

		/**
		 * Registers settings sections, fields and settings
		 *
		 * @since 1.0.0
		 */
		public function register_settings() {

		}

		/**
		 * Validates submitted setting values before they get saved to the database. Invalid data will be overwritten with defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_settings the new settings to be validated.
		 * @return array array of new settings.
		 */
		public function validate_settings( $new_settings ) {
			$new_settings = shortcode_atts( $this->settings, $new_settings );

			if ( ! is_string( $new_settings['db-version'] ) ) {
				$new_settings['db-version'] = self::VERSION;
			}

			return $new_settings;
		}

		/**
		 * Get Count of Sites without assigned emails addresses
		 *
		 * @since 1.0.0
		 *
		 * @return Int number of sites without emails.
		 */
		protected static function getEmptySites() {
			global $wpdb;

			$site_contacts_table_name = self::$table_names['site_contacts_table'];
			$total_items              = $wpdb->get_var( "SELECT COUNT(id) FROM $site_contacts_table_name WHERE email=''" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			return $total_items;
		}

	}
}
