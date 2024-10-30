<?php
/**
 * Linkbuildr: Linkbuildr_Events class
 *
 * The Linkbuildr Events class uses the Module_Abstract class format and
 * implements the Linkbuildr's Event handling.
 *
 * @package Linkbuildr
 * @subpackage Linkbuildr_Events
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_Events' ) ) {

	/**
	 * Linkbuildr_Events class, implementing Linkbuildr's event handling.
	 *
	 * Registers Block Editor scripts for Client Side even handling, and handles all post status transition handling.
	 *
	 * @since 1.0.0
	 *
	 * @see Module_Abstract
	 */
	class Linkbuildr_Events extends Module_Abstract {
		/**
		 * Defines which class properties are readable.
		 *
		 * @since 1.0.0
		 * @var Array $readable_properties Required as part of the Module_Abstract architecture,
		 *                                 contains String values of the names of class properties that are readable.
		 */
		protected static $readable_properties = array();

		/**
		 * Defines which class properties are writable.
		 *
		 * @since 1.0.0
		 * @var String $writeable_properties Required as part of the Module_Abstract architecture,
		 *                                   contains String values of the names of class properties that are writable.
		 */
		protected static $writeable_properties = array();

		/**
		 * Stores instances of Modules in Linkbuilder used in Linkbuildr_Events.
		 *
		 * @since 1.0.0
		 * @var Array will contain Object instances of Modules used in Linkbuildr_Events using the String class names as Keys
		 */
		protected $modules;

		/**
		 * Const storing the version to be used when enqueuing/registering assets.
		 *
		 * @since 1.0.0
		 * @var String contains the version to be used when enqueing assets.
		 */
		const VERSION = '1.0';

		/**
		 * Const storing the prefix to be used when enqueuing/registering assets.
		 *
		 * @since 1.0.0
		 * @var String contains the prefix to be used when enqueing assets.
		 */
		const PREFIX = 'linkbuildr_';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {
			$this->modules = array(
				'Linkbuildr_Settings' => Linkbuildr_Settings::get_instance(),
			);

			$this->register_hook_callbacks();
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
		 * Register callbacks for actions and filters
		 *
		 * @since 1.0.0
		 */
		public function register_hook_callbacks() {
			add_action( 'init', array( $this, 'register_linkbuilder_meta' ) );
			add_action( 'init', array( $this, 'upgrade' ), 11 );
			add_action( 'init', array( $this, 'block_scripts_register' ) );
			add_action( 'wp', array( $this, 'unsubscribe_check' ) );

			add_action( 'publish_post', array( $this, 'On_Publish_Post' ), 10, 2 );
			add_action( 'draft_post', array( $this, 'On_Save_Post_Draft' ), 10, 2 );

			add_action( 'wp_insert_post', array( $this, 'set_linkbuildr_meta_default' ), 9, 3 );

			add_action( 'post_submitbox_misc_actions', __CLASS__ . '::lBSubmitboxMiscActions' );

			add_action( 'enqueue_block_editor_assets', array( $this, 'block_script_enqueue' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_thickbox' ) );

			add_filter( 'the_content', __CLASS__ . '::display_unsubscribe_message' );
		}

		/**
		 * Checks for unsubscribe landingpage and unsubscribe param in request.
		 *
		 * @since 1.1.0
		 */
		public function unsubscribe_check() {
			global $post;
			global $wp_query;
			$linkbuildr_unsubscribe_landing   = get_option( 'linkbuildr_unsubsribe_landing' );
			$current_expected_landing_page_id = $linkbuildr_unsubscribe_landing['linkbuildr_unsubsribe_landing_post_id'];

			if ( $post ) {
				if ( intval( $current_expected_landing_page_id ) === $post->ID ) {
					if ( ! isset( $_GET['linkbuildr_unsubscribe_contact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $linkbuildr_unsubscribe_landing['linkbuildr_unsubsribe_404_landing_not_in_use'] ) ) {
							if ( $linkbuildr_unsubscribe_landing['linkbuildr_unsubsribe_404_landing_not_in_use'] ) {
								$wp_query->set_404();
								status_header( 404 );
								get_template_part( 404 );
								exit();
							}
						}
					}
				}
			}
		}

		/**
		 * Handles conditionally adding the unsubscribe message to the post/page content in a filter
		 *
		 * @since 1.1.0
		 *
		 * @param string $content the content of the current post to be filtered and have the unsubscribe message added.
		 * @return string message to display on page.
		 */
		public static function display_unsubscribe_message( $content ) {
			global $wp_query;
			global $post;
			$site_contacts_table_name = Linkbuildr_Settings::$table_names['site_contacts_table'];
			$valid_unsubscribe        = false;
			$retval                   = $content;

			$linkbuildr_unsubscribe_landing   = get_option( 'linkbuildr_unsubsribe_landing' );
			$current_expected_landing_page_id = $linkbuildr_unsubscribe_landing['linkbuildr_unsubsribe_landing_post_id'];
			if ( $post ) {
				if ( intval( $current_expected_landing_page_id ) === $post->ID ) {
					if ( isset( $_GET['linkbuildr_unsubscribe_contact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						// Sanitizing incoming query param, so can be used to query for Site Contacts.
						$sanitized_unsubscribe_token = sanitize_key( wp_unslash( $_GET['linkbuildr_unsubscribe_contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

						if ( $sanitized_unsubscribe_token ) {
							global $wpdb;
							$sc_to_unsubscribe = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE unsubscribed_key = %s", $sanitized_unsubscribe_token ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

							if ( $sc_to_unsubscribe ) {
								$unsubscribe_messages = get_option( 'linkbuildr_unsubsribe_message' );
								if ( $sc_to_unsubscribe['unsubscribed'] ) {
									$source_message = $unsubscribe_messages['already_unsubscribed_message'];
								} else {
									$source_message = $unsubscribe_messages['unsubsribe_message'];
									$wpdb->update( $site_contacts_table_name, array( 'unsubscribed' => 1 ), array( 'id' => $sc_to_unsubscribe['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
								}
								$valid_unsubscribe = true;

								$short_code_values = array(
									'[contactemail]'    => $sc_to_unsubscribe['email'],
									'[contactname]'     => $sc_to_unsubscribe['firstname'],
									'[contactsitename]' => $sc_to_unsubscribe['sitename'],
								);

								$final_onpage_msg = self::replaceShortCodes( $short_code_values, $source_message );

								$retval = $final_onpage_msg . $content;
							}
						}
					}
				}
			}
			return $retval;
		}

		/**
		 * Initializes post meta variable
		 *
		 * @since 1.0.0
		 */
		public function register_linkbuilder_meta() {

			register_post_meta(
				'post',
				'linkbuildr_send_email_on_publish',
				array(
					'show_in_rest' => true,
					'single'       => true,
					'type'         => 'boolean',
				)
			);

			register_post_meta(
				'post',
				'linkbuildr_not_new_post',
				array(
					'show_in_rest' => true,
					'single'       => true,
					'type'         => 'boolean',
				)
			);
		}

		/**
		 * Sets the default Post Meta values for a new post
		 *
		 * @since 1.3.0
		 *
		 * @param int     $post_id id of the current post.
		 * @param Object  $post the current post Object.
		 * @param boolean $update a semi inaccurate boolean of whether this is a post update or not.
		 */
		public function set_linkbuildr_meta_default( $post_id, $post, $update ) {
			$current_linkbuildr_send_on_publish_value = get_post_meta( $post_id, 'linkbuildr_send_email_on_publish', true );

			if ( update_post_meta( $post_id, 'linkbuildr_not_new_post', true ) ) {
				$default_value = get_option( 'linkbuildr_default_send_on_publish' );

				update_post_meta( $post_id, 'linkbuildr_send_email_on_publish', $default_value );
			}
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
			return true;
		}

		/**
		 * Calls add_thinkbox to enqueue thickbox assets.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_thickbox() {
			add_thickbox();
		}

		/**
		 * Registers Scripts related to Gutenberg/Block Editor.
		 *
		 * @since 1.0.0
		 */
		public function block_scripts_register() {
			wp_register_script(
				'linkbuildr-block-editor-scripts',
				plugin_dir_url( dirname( __FILE__ ) ) . 'js/linkbuildr-block.min.js',
				array( 'lodash', 'wp-plugins', 'wp-editor', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-compose' ),
				self::VERSION,
				true
			);
		}

		/**
		 * Enqueues Scripts related to Gutenberg/Block Editor.
		 *
		 * @since 1.0.0
		 */
		public function block_script_enqueue() {
			wp_enqueue_script( 'linkbuildr-block-editor-scripts' );
		}

		/**
		 * Extracts domains from <a> tags found in the $html passed to it.
		 *
		 * @since 1.3.0
		 *
		 * @param string $html A Block of html formatted text to be parsed.
		 * @return Array an array of Domains parsed from the $html parameter.
		 */
		protected static function domain_extractor( $html ) {
			$domain_array = array();

			if ( $html ) {
				$dom = new DOMDocument();
				$dom->loadHtml( $html );

				$link_tags  = $dom->getElementsByTagName( 'a' );
				$link_count = $link_tags->length;

				for ( $i = 0; $i < $link_count; $i++ ) {
					$href = $link_tags->item( $i )->getAttribute( 'href' );
					if ( $href ) {
						$domain = self::parse_domain( $href );

						if ( '' !== $domain ) {
							$domain_array[] = $domain;
						}
					}
				}
			}
			return $domain_array;
		}

		/**
		 * Function that parses a block of text and returns an Array of urls found in links
		 *
		 * @since 1.0.0
		 *
		 * @param string $html A Block of html formatted text to be parsed.
		 * @return Array
		 */
		protected static function linkExtractor( $html ) {
			$link_array = array();
			// The following regex grabs the url up to the third / or first closing " or 'after the url has begin hopfully grabbing only the root domain.
			// URL's are required to contain a http://starting the link in the hyperlink.
			// <a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>  //full URL string grab code.
			if ( preg_match_all( '/<a\s+.*?href=[\"\']?([^\/\"\' >]*[^\/]*\/[^\/]*\/[^\/|\'\"]*)[\"\']?[^>]*>(.*?)<\/a>/i', $html, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					array_push( $link_array, array( $match[1], $match[2] ) );   // match 1 should be the root domain, 2 is the link label text.
				}
			}
			return $link_array;
		}

		/**
		 * Function that takesx a PostSiteContact and changes it's is_sent value to true.
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 *
		 * @param Object $post_site_contact the post site contact to have it's is_sent set to true.
		 * @return Object the Updated Post Site Contact
		 */
		protected static function setPostSiteContactEntryToSent( $post_site_contact ) {
			global $wpdb;
			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];
			$post_site_contact['is_sent']   = 2;

			$retval = $wpdb->update( $posts_site_contacts_table_name, $post_site_contact, array( 'id' => $post_site_contact['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

			return $retval;
		}

		/**
		 * Function that adds a new entry into post_site_contacts table.
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 *
		 * @param Int  $post_id_local post_id for new post_site_contact entry.
		 * @param Int  $blog_id_local blog_id for new post_site_contact entry.
		 * @param Int  $site_id_local site_id for new post_site_contact entry.
		 * @param Bool $is_valid boolean flag to tell if the site_contact associated with the post is valid.
		 * @return Object the new post_site_contact entry.
		 */
		protected static function addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $site_id_local, $is_valid = false ) {
			global $wpdb;

			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];

			$post_site_contact_entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%d AND site_contact_id=%d AND blog_id=%d LIMIT 1", array( $post_id_local, $site_id_local, $blog_id_local ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			if ( ! $post_site_contact_entry ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$posts_site_contacts_table_name,
					array(
						'post_id'         => $post_id_local,
						'site_contact_id' => $site_id_local,
						'blog_id'         => $blog_id_local,
						'is_valid'        => ( $is_valid ? 2 : 1 ),
						'is_sent'         => 1,
					),
					array(
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
					)
				);

				$new_post_site_contact_id = $wpdb->insert_id;

				$post_site_contact_entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE id=%s", $new_post_site_contact_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

			}
			return $post_site_contact_entry;
		}

		/**
		 * Tied to the Post Status Transition Event 'publish_post' this function processes the contents of the post and associated information, conditionally triggering emails to the appropriate contacts.
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 *
		 * @param Int    $post_id the ID of the post being published.
		 * @param Object $post the post being published.
		 */
		public function On_Publish_Post( $post_id, $post ) {
			global $wpdb;

			$post_id_local = $post_id;
			$blog_id_local = get_current_blog_id();

			// check nonce.
			$is_valid_nonce = ( ( isset( $_POST['linkbuildr_email_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['linkbuildr_email_nonce'] ), basename( __FILE__ ) ) ) ? 'true' : 'false' );

			// Exits script depending on save status.
			if ( ! $is_valid_nonce ) {
				return;
			}

			// Updates the checkbox choice in the database.
			if ( array_key_exists( 'linkbuildr_classic_editor_check', $_POST ) ) {
				if ( array_key_exists( 'linkbuildr_send_email_on_publish_post', $_POST ) && 'on' === $_POST['linkbuildr_send_email_on_publish_post'] ) {
					update_post_meta( $post_id_local, 'linkbuildr_send_email_on_publish', true );
				} else {
					update_post_meta( $post_id_local, 'linkbuildr_send_email_on_publish', false );
				}
			}

			$site_contacts_table_name       = Linkbuildr_Settings::$table_names['site_contacts_table'];
			$email_templates_table_name     = Linkbuildr_Settings::$table_names['email_templates_table'];
			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];

			$post_content = get_post( $post_id_local );
			$content      = $post->post_content;  // result is the text from post that was just published.

			$domain_array = self::domain_extractor( $content );

			$unique_domains_array = array_unique( $domain_array );

			$site_url    = get_site_url();
			$site_domain = self::parse_domain( $site_url );

			foreach ( $unique_domains_array as $domain ) {
				if ( self::check_domain_viability( $domain ) ) {
					$domain_site_contacts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE domain = '%s'", $domain ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL,WordPress.DB.PreparedSQLPlaceholders
					if ( ! $domain_site_contacts ) {

						// Since this is a new Site Contact, generate an unsubscribe_key for it.
						$dsc_unsubscribe_key = self::generate_unsubscribe_key( $domain . time() );

						$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$site_contacts_table_name,
							array(
								'domain'            => $domain,
								'sitename'          => '',
								'firstname'         => '',
								'email'             => '',
								'templatechoice'    => '',
								'email_template_id' => null,
								'unsubscribed'      => 0,
								'unsubscribed_key'  => $dsc_unsubscribe_key,
							)
						);
						$new_site_contact_id = $wpdb->insert_id;
						wp_cache_flush();
						self::addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $new_site_contact_id );
					} else {
						$domain_site_contact = $domain_site_contacts[0];
						if ( '' !== $domain_site_contact['email'] && null !== $domain_site_contact['email_template_id'] && ! $domain_site_contact['unsubscribed'] ) {
							$email_template = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $email_templates_table_name WHERE id=%s", $domain_site_contact['email_template_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

							if ( $email_template ) {
								$post_site_contact                          = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%s AND site_contact_id=%s", array( $post_id_local, $domain_site_contact['id'] ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
								$linkbuildr_send_email_on_publish_post_meta = get_post_meta( $post_id_local, 'linkbuildr_send_email_on_publish', true );

								if ( $linkbuildr_send_email_on_publish_post_meta && ( 'true' !== $post_site_contact['is_sent'] ) ) {
									self::sendEmailForPostPublish( $post_content, $email_template, $domain_site_contact );
									self::setPostSiteContactEntryToSent( $post_site_contact );
								}
							}
						} else {
							self::addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $domain_site_contact['id'] );
						}
					}
				}
			}
		}

		/**
		 * Tied to the Post Status Transition Event 'draft_post' this function processes the contents of the post and associated information, creating new Site Contact entries for new urls/domains linked to in the post
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 *
		 * @param Int    $post_id the ID of the post being saved as draft.
		 * @param Object $post the post being saved as draft.
		 */
		public function On_Save_Post_Draft( $post_id, $post ) {
			// A function to perform when a post is saved.
			global $wpdb;

			$blog_id_local = get_current_blog_id();

			// check nonce.
			$is_valid_nonce = ( isset( $_POST['linkbuildr_email_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['linkbuildr_email_nonce'] ), basename( __FILE__ ) ) ) ? 'true' : 'false';

			// Exits script depending on save status.
			if ( ! $is_valid_nonce ) {
				return;
			}

			$is_new_post = ! get_post_meta( $post_id, 'linkbuildr_not_new_post', true );
			if ( $is_new_post ) {
				update_post_meta( $post_id, 'linkbuildr_not_new_post', true );
			}

			// Updates the checkbox choice in the database.
			if ( array_key_exists( 'linkbuildr_classic_editor_check', $_POST ) ) {
				if ( array_key_exists( 'linkbuildr_send_email_on_publish_post', $_POST ) && 'on' === $_POST['linkbuildr_send_email_on_publish_post'] ) {
					update_post_meta( $post_id, 'linkbuildr_send_email_on_publish', true );
				} else {
					update_post_meta( $post_id, 'linkbuildr_send_email_on_publish', false );
				}
			}

			$site_contacts_table_name       = Linkbuildr_Settings::$table_names['site_contacts_table'];
			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];

			$post_id_local = $post_id;
			$blog_id_local = get_current_blog_id();

			$content_post = get_post( $post_id_local );
			$content      = $content_post->post_content;  // result is the text from post that was just published.

			$domain_array = self::domain_extractor( $content );

			$unique_domains_array = array_unique( $domain_array );

			$site_url    = get_site_url();
			$site_domain = self::parse_domain( $site_url );

			foreach ( $unique_domains_array as $domain ) {
				if ( self::check_domain_viability( $domain ) ) {
					$domain_site_contact = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE domain = '%s'", $domain ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL,WordPress.DB.PreparedSQLPlaceholders
					if ( ! $domain_site_contact ) {

						// Since this is a new Site Contact, generate an unsubscribe_key for it.
						$dsc_unsubscribe_key = self::generate_unsubscribe_key( $domain . time() );

						$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$site_contacts_table_name,
							array(
								'domain'            => $domain,
								'sitename'          => '',
								'firstname'         => '',
								'email'             => '',
								'templatechoice'    => '',
								'email_template_id' => null,
								'unsubscribed'      => 0,
								'unsubscribed_key'  => $dsc_unsubscribe_key,
							)
						);
						$new_site_contact_id = $wpdb->insert_id;
						wp_cache_flush();
						self::addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $new_site_contact_id );
					} else {
						$domain_site_contact = $domain_site_contact[0];
						if ( '' !== $domain_site_contact['email'] && null !== $domain_site_contact['email_template_id'] ) {
							self::addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $domain_site_contact['id'], true );
						} else {
							self::addNewPostSiteContactEntry( $post_id_local, $blog_id_local, $domain_site_contact['id'] );
						}
					}
				}
			}

			// get all postSiteContactEntries.
			$post_site_contacts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%s AND blog_id=%s", array( $post_id_local, $blog_id_local ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			foreach ( $post_site_contacts as $post_site_contact ) {
				// If it's sent then we leave it as a record.
				if ( ! filter_var( $post_site_contact['is_sent'], FILTER_VALIDATE_BOOLEAN ) ) {
					// get Site Contact Domain for each.
					$site_contact = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $site_contacts_table_name WHERE id=%s", $post_site_contact['site_contact_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
					$domain       = $site_contact['domain'];

					// Compare Site Contact Domains to $unique_domains_array.
					if ( ! in_array( $domain, $unique_domains_array, true ) ) {
						// Remove entries in PostSiteContacts not found in $unique_domains_array.
						$wpdb->query( $wpdb->prepare( "DELETE FROM $posts_site_contacts_table_name WHERE id=%s", $post_site_contact['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
					}
				}
			}
		}

		/**
		 * Compares the domain to ignore list and current site domain
		 *
		 * @since 1.3.0
		 *
		 * @param String $domain a domain from a link in a post.
		 * @return Boolean true if the domain is not ignored and not the current site domain.
		 */
		protected static function check_domain_viability( $domain ) {
			global $wpdb;

			$site_url    = get_site_url();
			$site_domain = self::parse_domain( $site_url );

			if ( $domain === $site_domain ) {
				return false;
			}

			$ignore_domains_table_name  = Linkbuildr_Settings::$table_names['ignore_domains'];
			$cache_key                  = 'ignore_domains_index_list';
			$_ignore_domains_index_list = wp_cache_get( $cache_key );

			if ( false === $_ignore_domains_index_list ) {
				$domain_only = function( $result_array ) {
					return $result_array['domain'];
				};

				$ignore_domains_items = array_map( $domain_only, $wpdb->get_results( $wpdb->prepare( "SELECT domain FROM $ignore_domains_table_name" ), ARRAY_A ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

				$_ignore_domains_index_list = array_flip( $ignore_domains_items );
				wp_cache_set( $cache_key, $_ignore_domains_index_list );
			}

			if ( isset( $_ignore_domains_index_list[ $domain ] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Returns a body of text with shortcodes replaces with corresponding values.
		 *
		 * @since 1.0.0
		 *
		 * @param Array  $code_values an Array of key value pairs representing the shortcodes to be replaced.
		 * @param String $incoming_string the body of text to have shortcode values replaced in it.
		 * @return String the body of text with shortcode instances in it replaced.
		 */
		protected static function replaceShortCodes( $code_values, $incoming_string ) {
			$retval = $incoming_string;

			foreach ( $code_values as $short_code => $short_code_value ) {
				$retval = str_replace( $short_code, $short_code_value, $retval );
			}

			// nl2br replaces line breaks with <br> tags to retain formatting from the email template.
			return nl2br( $retval );
		}

		/**
		 * Generates unsubscribe link
		 *
		 * @since 1.1.0
		 *
		 * @param Object $site_contact the Site Contact the unsubscribe link is being generated for.
		 * @return String the markup for the unsubscribe link.
		 */
		protected static function addUnsubscribeLink( $site_contact ) {
			$unsubscribe_text            = get_option( 'linkbuildr_unsubsribe_link_text' );
			$unsubscribe_parameter       = 'linkbuildr_unsubscribe_contact';
			$unsubscribe_landing_post_id = get_option( 'linkbuildr_unsubsribe_landing' )['linkbuildr_unsubsribe_landing_post_id'];
			$unsubscribe_landing_url     = get_permalink( intval( $unsubscribe_landing_post_id ) );
			$permalink_contains_param    = false;

			if ( strpos( $unsubscribe_landing_url, '?' ) !== false ) {
				$permalink_contains_param = true;
			}

			$parameter_concatinator = ( $permalink_contains_param ? '&' : '?' );

			$unsubscribe_link = '<div>&nbsp;</div><a href="' . $unsubscribe_landing_url . $parameter_concatinator . $unsubscribe_parameter . '=' . $site_contact['unsubscribed_key'] . '" >' . $unsubscribe_text . '</a><div>&nbsp;</div>';

			return $unsubscribe_link;
		}

		/**
		 * Generates social share buttons markup based on incoming post information.
		 *
		 * @since 1.0.0
		 *
		 * @param String $site_url the url of the site.
		 * @param String $post_url the url of the post.
		 * @param String $post_title the title of the post.
		 * @param String $tweet_content the content of the suggested tweet.
		 * @return String the markup for the social buttons.
		 */
		protected static function addSocialToEmailContent( $site_url, $post_url, $post_title, $tweet_content ) {
			$social_share_links = '<div>&nbsp;</div><p style="font-size:14px;font-weight:600;margin-bottom:10px;">Share:</p><div style="display:inline-block; padding-right:8px; padding-top:6px; padding-bottom:16px;"><a href="https://twitter.com/intent/tweet?text=' . $tweet_content . '" style="color:#fff;background:#00abf0;padding:10px 10px;border-radius:4px;font-weight:700;"><img src="' . $site_url . '/wp-content/plugins/linkbuildr/img/social-twsm.png" title="twitter" style="width:30px;vertical-align: middle;" /></a></div><div style="display:inline-block; padding-right:8px; padding-top:6px; padding-bottom:16px;"><a href="https://www.facebook.com/sharer/sharer.php?u=' . $post_url . '" class="popup" style="color:#fff;background:#3a579a;padding:10px 10px;border-radius:4px;font-weight:700;"><img src="' . $site_url . '/wp-content/plugins/linkbuildr/img/social-fbsm.png" title="facebook" style="width:30px;vertical-align: middle;" /></a></div><div style="display:inline-block; padding-right:8px; padding-top:6px; padding-bottom:16px;"><a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $post_url . '&amp;title=' . $post_title . '&amp;summary=' . $post_title . '" class="popup"  style="color:#fff;background:#72a2cf;padding:10px 10px;border-radius:4px;font-weight:700;"><img src="' . $site_url . '/wp-content/plugins/linkbuildr/img/social-lnsm.png" title="linkedin" style="width:30px;vertical-align: middle;" /></a></div><div>&nbsp;</div>';
			return $social_share_links;
		}

		/**
		 * Sends an email based on the email template to the site contact referenced in the post.
		 *
		 * @since 1.0.0
		 *
		 * @param Object $post_content The content of the post.
		 * @param Object $email_template The email template to be used for the email.
		 * @param Object $site_contact The site contact object corresponding to a link in the post being published.
		 */
		protected static function sendEmailForPostPublish( $post_content, $email_template, $site_contact ) {
			$post_url   = get_permalink( $post_content->ID );  // result is the url link from the post that was just published.
			$post_title = $post_content->post_title;

			$author_name = get_the_author_meta( 'display_name', $post_content->post_author );

			$to         = $site_contact['email'];
			$site_name  = get_bloginfo( 'name' );
			$from_email = $site_name . ' <' . $email_template['sender'] . '>';
			if ( ( 'Post Author' === $email_template['sender'] ) || ( '' === $email_template['sender'] ) ) {
				$from_email = $author_name . ' <' . get_the_author_meta( 'user_email', $post_content->post_author ) . '>';
			}

			$short_code_values = array(
				'[posturl]'         => get_permalink( $post_content->ID ),
				'[contactname]'     => $site_contact['firstname'],
				'[contactsitename]' => $site_contact['sitename'],
				'[author]'          => $author_name,
			);

			$email_subject = self::replaceShortCodes( $short_code_values, $email_template['subject'] );

			$raw_tweet_content = self::replaceShortCodes( $short_code_values, $email_template['tweet'] );
			$tweet_content     = rawurlencode( stripslashes( $raw_tweet_content ) );

			$main_email_content = self::replaceShortCodes( $short_code_values, $email_template['content'] );
			$raw_email_content  = $main_email_content . self::addSocialToEmailContent( get_site_url(), $post_url, $post_title, $tweet_content ) . self::addUnsubscribeLink( $site_contact );

			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'From: ' . $from_email . "\r\n";
			wp_mail(
				$to,
				stripslashes( $email_subject ),
				stripslashes( $raw_email_content ),
				$headers
			);  // send alert to this person that they were linked to using the template info.
		}

		/**
		 * Adds html to create a checkbox next to the Publish button in Classic Editor.
		 *
		 * @since 1.0.0
		 */
		public static function lBSubmitboxMiscActions() {
			global $wpdb;
			global $post;

			$posts_site_contacts_table_name = Linkbuildr_Settings::$table_names['posts_site_contacts_table'];

			$post_id_local = $post->ID;
			$post_type     = null;
			if ( isset( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			$action = null;
			if ( isset( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$action = sanitize_text_field( wp_unslash( $_GET['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( ( null === $post_type ) || ( 'post' === $post_type ) ) {
				$post_status = get_post_status( $post_id_local );

				if ( ( null === $action ) ||
					( ( ( 'edit' === $action ) ||
					( 'publish' === $post_status ) ) &&
					( 'private' !== $post_status ) &&
					( 'trash' !== $post_status ) ) ) {

					$variables = array();

					$variables['post_status'] = $post_status;
					if ( 'publish' === $post_status ) {
						$blog_id_local                                       = get_current_blog_id();
						$sent_post_site_contacts                             = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $posts_site_contacts_table_name WHERE post_id=%s AND blog_id=%s AND is_sent='true'", array( $post_id_local, $blog_id_local ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						$sent_email_count                                    = count( $sent_post_site_contacts );
						$variables['sent_email_count']                       = $sent_email_count;
						$variables['plugin_dir']                             = plugin_dir_url( dirname( __FILE__ ) );
						$variables['plugin_basename']                        = plugin_basename( __FILE__ );
						$variables['linkbuildr_send_email_on_publish_value'] = get_post_meta( $post_id_local, 'linkbuildr_send_email_on_publish', true );

						if ( 0 < $sent_email_count ) {
							echo self::render_template( 'classic-editor-submit-misc-actions.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
					} else {
						$variables['plugin_dir']                             = plugin_dir_url( dirname( __FILE__ ) );
						$variables['plugin_basename']                        = plugin_basename( __FILE__ );
						$variables['linkbuildr_send_email_on_publish_value'] = get_post_meta( $post_id_local, 'linkbuildr_send_email_on_publish', true );

						echo self::render_template( 'classic-editor-submit-misc-actions.php', $variables ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
			}
		}
	} // end Linkbuildr_Events.
}
