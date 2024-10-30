<?php
/**
 * Linkbuildr: Linkbuildr_Migration class
 *
 * The Linkbuildr Migration class uses the Module_Abstract class format and
 * implements the Linkbuildr's Migration handling.
 *
 * @package Linkbuildr
 * @subpackage Linkbuildr_Migration
 * @since 1.0.0
 */

if ( ! class_exists( 'Linkbuildr_Migration' ) ) {

	/**
	 * Linkbuildr_Migration class, implementing Linkbuildr's migration handling.
	 *
	 * Check db current version, compare with expected, upgrade as necisary.
	 *
	 * @since 1.0.0
	 *
	 * @see Module_Abstract
	 */
	class Linkbuildr_Migration extends Module_Abstract {
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
		const VERSION = '1.3';

		/**
		 * Const storing the version to be used when enqueuing/registering assets.
		 *
		 * @since 1.1.0
		 * @var String contains the version to be used when enqueing assets.
		 */
		const DB_VERSION_OPTION = 'LINKBUILDR_DB_VERSION';

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
			$this->register_hook_callbacks();

			$this->modules = array(
				'Linkbuildr_Settings' => Linkbuildr_Settings::get_instance(),
			);
		}

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 *
		 * @since 1.0.0
		 *
		 * @global Object $wpdb WordPress global db reference.
		 *
		 * @param Bool $network_wide Flag to indicate if the activation is Multisite/Network wide.
		 */
		public function activate( $network_wide ) {
			global $wpdb;

			$site_contacts_table_name       = $wpdb->base_prefix . 'linkbuildr_site_contacts';
			$email_templates_table_name     = $wpdb->base_prefix . 'linkbuildr_email_templates';
			$posts_site_contacts_table_name = $wpdb->base_prefix . 'linkbuildr_posts_site_contacts';

			// sql to create your table.
			// NOTICE that.
			// 1. each field MUST be in separate line.
			// 2. There must be two spaces between PRIMARY KEY and its name.
			// Like this: PRIMARY KEY[space][space](id).
			// otherwise dbDelta will not work.
			$email_templates_table_sql = 'CREATE TABLE IF NOT EXISTS ' . $email_templates_table_name . ' (
				id int(11) NOT NULL AUTO_INCREMENT,
				templatename VARCHAR(255) NOT NULL,
				sender VARCHAR(255) NOT NULL,
				subject VARCHAR(255) NOT NULL,
				content VARCHAR(5000) NOT NULL,
				tweet VARCHAR(500) NOT NULL,
				PRIMARY KEY (id)
			);';

			$site_contact_table_sql = 'CREATE TABLE IF NOT EXISTS ' . $site_contacts_table_name . ' (
				id int(11) NOT NULL AUTO_INCREMENT, 
				domain VARCHAR(255) NOT NULL, 
				sitename VARCHAR(255) NOT NULL, 
				firstname VARCHAR(100) NOT NULL,
				email VARCHAR(100) NOT NULL,
				templatechoice VARCHAR(255) NOT NULL,
				email_template_id int(11),
				PRIMARY KEY (id),
				FOREIGN KEY (email_template_id)
				REFERENCES ' . $email_templates_table_name . '(id)
			);';

			$posts_site_contacts_sql = 'CREATE TABLE IF NOT EXISTS ' . $posts_site_contacts_table_name . ' (
				id int(11) NOT NULL AUTO_INCREMENT,
				post_id int(11) NOT NULL,
				blog_id varchar(100) NOT NULL,
				site_contact_id int(11),
				is_valid ENUM(\'false\', \'true\') NOT NULL DEFAULT \'false\',
				is_sent ENUM(\'false\', \'true\') NOT NULL DEFAULT \'false\',
				PRIMARY KEY (id),
				INDEX (site_contact_id),
				INDEX (post_id, blog_id),
				FOREIGN KEY (site_contact_id)
				REFERENCES ' . $site_contacts_table_name . '(id)
			);';

			$wpdb->query( $email_templates_table_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			$wpdb->query( $site_contact_table_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
			$wpdb->query( $posts_site_contacts_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

			// Add Default Email Template here.
			$default_template = array(
				'templatename' => 'Default',
				'sender'       => 'Post Author',
				'subject'      => 'Gave you a shoutout',
				'content'      => 'Hey [contactname], 

I mentioned [contactsitename] in my latest post: [posturl], and I thought you might want to check it out.
				
Best,
[author]',
				'tweet'        => 'Shoutout to [author] for mentioning me in their recent post: [posturl]',
			);

			// Check if the default template already exists.
			$existing_default_template = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $email_templates_table_name WHERE templatename = %s AND sender = %s AND subject = %s", array( $default_template['templatename'], $default_template['sender'], $default_template['subject'] ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

			// If it doesn't then add it to db.
			if ( ! $existing_default_template ) {
				$wpdb->insert( $email_templates_table_name, $default_template ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}

			add_option( self::DB_VERSION_OPTION, '1.0' );
		}

		/**
		 * Runs activation code on a new WPMS site when it's created
		 *
		 * @param Int $blog_id the ID of the site being activated.
		 */
		public function activate_new_site( $blog_id ) {
			switch_to_blog( $blog_id );
			$this->single_activate( true );
			restore_current_blog();
		}

		/**
		 * Prepares a single blog to use the plugin
		 *
		 * @param Bool $network_wide Flag to indicate if the activation is Multisite/Network wide.
		 */
		protected function single_activate( $network_wide ) {
			foreach ( $this->modules as $module ) {
				$module->activate( $network_wide );
			}
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @since 1.0.0
		 */
		public function deactivate() {
			foreach ( $this->modules as $module ) {
				$module->deactivate();
			}
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since 1.0.0
		 */
		public function register_hook_callbacks() {
			add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
			add_action( 'init', array( $this, 'upgrade' ), 11 );
		}

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary.
		 *
		 * @since 1.1.0
		 * @since 1.0.0
		 *
		 * @param string $db_version the version of the db to compare with the stored version value and know if we need to upgrade.
		 */
		public function upgrade( $db_version = 0 ) {
			global $wpdb;
			$linkbuildr_current_db_version = get_option( self::DB_VERSION_OPTION );

			if ( version_compare( $linkbuildr_current_db_version, self::VERSION, '==' ) ) {
				return;
			} else { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement

				if ( 1.1 > floatval( $linkbuildr_current_db_version ) ) {
					$site_contacts_table_name                 = $wpdb->base_prefix . 'linkbuildr_site_contacts';
					$add_unsubscribe_to_site_contacts_sql     = 'ALTER TABLE ' . $site_contacts_table_name . ' ADD COLUMN unsubscribed BOOLEAN NOT NULL DEFAULT 0;';
					$add_unsubscribe_key_to_site_contacts_sql = 'ALTER TABLE ' . $site_contacts_table_name . ' ADD COLUMN unsubscribed_key VARCHAR(255);';

					try {
						$wpdb->query( $add_unsubscribe_to_site_contacts_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
						$wpdb->query( $add_unsubscribe_key_to_site_contacts_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

						update_option( self::DB_VERSION_OPTION, '1.1' );
					} catch ( Exception $e ) {
						error_log( 'Error in Linkbuildr db upgrade: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					}

					$site_contact_table_items = $wpdb->get_results( "SELECT id, domain, sitename, firstname, email, email_template_id FROM $site_contacts_table_name", ARRAY_A );// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

					foreach ( $site_contact_table_items as $sc_item ) {
						$sc_item['unsubscribed_key'] = self::generate_unsubscribe_key( strval( $sc_item['id'] ) . $sc_item['domain'] . time() );
						$wpdb->update( $site_contacts_table_name, $sc_item, array( 'id' => $sc_item['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
					}

					self::add_settings_defaults();
				}

				if ( 1.3 > floatval( $linkbuildr_current_db_version ) ) {
					$ignore_domain_table_name = $wpdb->base_prefix . 'linkbuildr_ignore_domains';

					$add_domain_ignore_list_table_sql = 'CREATE TABLE IF NOT EXISTS ' . $ignore_domain_table_name . ' (
						id int(11) NOT NULL AUTO_INCREMENT,
						domain VARCHAR(255) NOT NULL,
						PRIMARY KEY (id)
					);';

					try {
						$wpdb->query( $add_domain_ignore_list_table_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

						update_option( self::DB_VERSION_OPTION, '1.3' );
					} catch ( Exception $e ) {
						error_log( 'Error in Linkbuildr db upgrade: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					}

					$default_ignore_domains = array(
						array( 'domain' => 'google.com' ),
						array( 'domain' => 'youtube.com' ),
						array( 'domain' => 'facebook.com' ),
						array( 'domain' => 'baidu.com' ),
						array( 'domain' => 'wikipedia.org' ),
						array( 'domain' => 'twitter.com' ),
						array( 'domain' => 'yahoo.com' ),
						array( 'domain' => 'pornhub.com' ),
						array( 'domain' => 'instagram.com' ),
						array( 'domain' => 'xvideos.com' ),
						array( 'domain' => 'yandex.ru' ),
						array( 'domain' => 'ampproject.org' ),
						array( 'domain' => 'xnxx.com' ),
						array( 'domain' => 'amazon.com' ),
						array( 'domain' => 'live.com' ),
						array( 'domain' => 'vk.com' ),
						array( 'domain' => 'netflix.com' ),
						array( 'domain' => 'qq.com' ),
						array( 'domain' => 'whatsapp.com' ),
						array( 'domain' => 'mail.ru' ),
						array( 'domain' => 'reddit.com' ),
						array( 'domain' => 'yahoo.co.jp' ),
						array( 'domain' => 'google.com.br' ),
						array( 'domain' => 'bing.com' ),
						array( 'domain' => 'ok.ru' ),
						array( 'domain' => 'xhamster.com' ),
						array( 'domain' => 'sogou.com' ),
						array( 'domain' => 'ebay.com' ),
						array( 'domain' => 'bit.ly' ),
						array( 'domain' => 'twitch.tv' ),
						array( 'domain' => 'linkedin.com' ),
						array( 'domain' => 'samsung.com' ),
						array( 'domain' => 'sm.cn' ),
						array( 'domain' => 'msn.com' ),
						array( 'domain' => 'office.com' ),
						array( 'domain' => 'globo.com' ),
						array( 'domain' => 'taobao.com' ),
						array( 'domain' => 'pinterest.com' ),
						array( 'domain' => 'google.de' ),
						array( 'domain' => 'microsoft.com' ),
						array( 'domain' => 'accuweather.com' ),
						array( 'domain' => 'naver.com' ),
						array( 'domain' => 'aliexpress.com' ),
						array( 'domain' => 'fandom.com' ),
						array( 'domain' => 'quora.com' ),
						array( 'domain' => 'github.com' ),
						array( 'domain' => 'imdb.com' ),
						array( 'domain' => 'uol.com.br' ),
						array( 'domain' => 'docomo.ne.jp' ),
						array( 'domain' => 'youporn.com' ),
						array( 'domain' => 'bbc.co.uk' ),
						array( 'domain' => 'microsoftonline.co' ),
						array( 'domain' => 'paypal.com' ),
						array( 'domain' => 'google.fr' ),
						array( 'domain' => 'yidianzixun.com' ),
						array( 'domain' => 'wordpress.com' ),
						array( 'domain' => 'news.google.com' ),
						array( 'domain' => 'sohu.com' ),
						array( 'domain' => 'duckduckgo.com' ),
						array( 'domain' => 'google.co.uk' ),
						array( 'domain' => '10086.cn' ),
						array( 'domain' => 'iqiyi.com' ),
						array( 'domain' => 'booking.com' ),
						array( 'domain' => 'amazon.co.jp' ),
						array( 'domain' => 'cricbuzz.com' ),
						array( 'domain' => 'taboola.com' ),
						array( 'domain' => 'amazon.de' ),
						array( 'domain' => 'cnn.com' ),
						array( 'domain' => 'jd.com' ),
						array( 'domain' => 'apple.com' ),
						array( 'domain' => 'google.it' ),
						array( 'domain' => 'bilibili.com' ),
						array( 'domain' => 'google.co.jp' ),
						array( 'domain' => 'livejasmin.com' ),
						array( 'domain' => 'tmall.com' ),
						array( 'domain' => 'news.yahoo.co.jp' ),
						array( 'domain' => 'youtu.be' ),
						array( 'domain' => 'tribunnews.com' ),
						array( 'domain' => 'amazon.co.uk' ),
						array( 'domain' => 'chaturbate.com' ),
						array( 'domain' => 'google.co.in' ),
						array( 'domain' => 'craigslist.org' ),
						array( 'domain' => 'imgur.com' ),
						array( 'domain' => 'bbc.com' ),
						array( 'domain' => 'fc2.com' ),
						array( 'domain' => 'tsyndicate.com' ),
						array( 'domain' => 'redtube.com' ),
						array( 'domain' => 'tumblr.com' ),
						array( 'domain' => 'foxnews.com' ),
						array( 'domain' => 'rakuten.co.jp' ),
						array( 'domain' => 'google.es' ),
						array( 'domain' => 'outbrain.com' ),
						array( 'domain' => 'discordapp.com' ),
						array( 'domain' => 'amazon.in' ),
						array( 'domain' => 'crptgate.co' ),
						array( 'domain' => 'weather.com' ),
						array( 'domain' => 'toutiao.com' ),
						array( 'domain' => 'youku.com' ),
						array( 'domain' => 'adobe.com' ),
						array( 'domain' => 'news.yandex.ru' ),
						array( 'domain' => 'www.google.com' ),
						array( 'domain' => 'www.youtube.com' ),
						array( 'domain' => 'www.facebook.com' ),
						array( 'domain' => 'www.baidu.com' ),
						array( 'domain' => 'www.wikipedia.org' ),
						array( 'domain' => 'www.twitter.com' ),
						array( 'domain' => 'www.yahoo.com' ),
						array( 'domain' => 'www.pornhub.com' ),
						array( 'domain' => 'www.instagram.com' ),
						array( 'domain' => 'www.xvideos.com' ),
						array( 'domain' => 'www.yandex.ru' ),
						array( 'domain' => 'www.ampproject.org' ),
						array( 'domain' => 'www.xnxx.com' ),
						array( 'domain' => 'www.amazon.com' ),
						array( 'domain' => 'www.live.com' ),
						array( 'domain' => 'www.vk.com' ),
						array( 'domain' => 'www.netflix.com' ),
						array( 'domain' => 'www.qq.com' ),
						array( 'domain' => 'www.whatsapp.com' ),
						array( 'domain' => 'www.mail.ru' ),
						array( 'domain' => 'www.reddit.com' ),
						array( 'domain' => 'www.yahoo.co.jp' ),
						array( 'domain' => 'www.google.com.br' ),
						array( 'domain' => 'www.bing.com' ),
						array( 'domain' => 'www.ok.ru' ),
						array( 'domain' => 'www.xhamster.com' ),
						array( 'domain' => 'www.sogou.com' ),
						array( 'domain' => 'www.ebay.com' ),
						array( 'domain' => 'www.bit.ly' ),
						array( 'domain' => 'www.twitch.tv' ),
						array( 'domain' => 'www.linkedin.com' ),
						array( 'domain' => 'www.samsung.com' ),
						array( 'domain' => 'www.sm.cn' ),
						array( 'domain' => 'www.msn.com' ),
						array( 'domain' => 'www.office.com' ),
						array( 'domain' => 'www.globo.com' ),
						array( 'domain' => 'www.taobao.com' ),
						array( 'domain' => 'www.pinterest.com' ),
						array( 'domain' => 'www.google.de' ),
						array( 'domain' => 'www.microsoft.com' ),
						array( 'domain' => 'www.accuweather.com' ),
						array( 'domain' => 'www.naver.com' ),
						array( 'domain' => 'www.aliexpress.com' ),
						array( 'domain' => 'www.fandom.com' ),
						array( 'domain' => 'www.quora.com' ),
						array( 'domain' => 'www.github.com' ),
						array( 'domain' => 'www.imdb.com' ),
						array( 'domain' => 'www.uol.com.br' ),
						array( 'domain' => 'www.docomo.ne.jp' ),
						array( 'domain' => 'www.youporn.com' ),
						array( 'domain' => 'www.bbc.co.uk' ),
						array( 'domain' => 'www.microsoftonline.co' ),
						array( 'domain' => 'www.paypal.com' ),
						array( 'domain' => 'www.google.fr' ),
						array( 'domain' => 'www.yidianzixun.com' ),
						array( 'domain' => 'www.wordpress.com' ),
						array( 'domain' => 'www.sohu.com' ),
						array( 'domain' => 'www.duckduckgo.com' ),
						array( 'domain' => 'www.google.co.uk' ),
						array( 'domain' => 'www.10086.cn' ),
						array( 'domain' => 'www.iqiyi.com' ),
						array( 'domain' => 'www.booking.com' ),
						array( 'domain' => 'www.amazon.co.jp' ),
						array( 'domain' => 'www.cricbuzz.com' ),
						array( 'domain' => 'www.taboola.com' ),
						array( 'domain' => 'www.amazon.de' ),
						array( 'domain' => 'www.cnn.com' ),
						array( 'domain' => 'www.jd.com' ),
						array( 'domain' => 'www.apple.com' ),
						array( 'domain' => 'www.google.it' ),
						array( 'domain' => 'www.bilibili.com' ),
						array( 'domain' => 'www.google.co.jp' ),
						array( 'domain' => 'www.livejasmin.com' ),
						array( 'domain' => 'www.tmall.com' ),
						array( 'domain' => 'www.youtu.be' ),
						array( 'domain' => 'www.tribunnews.com' ),
						array( 'domain' => 'www.amazon.co.uk' ),
						array( 'domain' => 'www.chaturbate.com' ),
						array( 'domain' => 'www.google.co.in' ),
						array( 'domain' => 'www.craigslist.org' ),
						array( 'domain' => 'www.imgur.com' ),
						array( 'domain' => 'www.bbc.com' ),
						array( 'domain' => 'www.fc2.com' ),
						array( 'domain' => 'www.tsyndicate.com' ),
						array( 'domain' => 'www.redtube.com' ),
						array( 'domain' => 'www.tumblr.com' ),
						array( 'domain' => 'www.foxnews.com' ),
						array( 'domain' => 'www.rakuten.co.jp' ),
						array( 'domain' => 'www.google.es' ),
						array( 'domain' => 'www.outbrain.com' ),
						array( 'domain' => 'www.discordapp.com' ),
						array( 'domain' => 'www.amazon.in' ),
						array( 'domain' => 'www.crptgate.co' ),
						array( 'domain' => 'www.weather.com' ),
						array( 'domain' => 'www.toutiao.com' ),
						array( 'domain' => 'www.youku.com' ),
						array( 'domain' => 'www.adobe.com' ),
						array( 'domain' => 'yandex.com' ),
						array( 'domain' => 'www.yandex.com' ),
					);

					foreach ( $default_ignore_domains as $ignore_domain ) {
						$wpdb->insert( $ignore_domain_table_name, $ignore_domain ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}

					$site_contacts_table_name = $wpdb->base_prefix . 'linkbuildr_site_contacts';

					$current_site_contacts = $wpdb->get_results( "SELECT id, domain FROM $site_contacts_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL

					foreach ( $current_site_contacts as $csc ) {
						if ( false !== strpos( $csc->domain, 'http' ) ) {
							$new_domain = self::parse_domain( $csc->domain );
							$wpdb->update( $site_contacts_table_name, array( 'domain' => $new_domain ), array( 'id' => $csc->id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						}
					}

					$linkbuildr_default_send_on_publish = 1;
					update_option( 'linkbuildr_default_send_on_publish', $linkbuildr_default_send_on_publish );
				}
			}
		}

		/**
		 * Add the default values for all settings
		 *
		 * @since 1.1.0
		 */
		protected function add_settings_defaults() {
			$unsubscribe_text = __( 'unsubscribe', 'linkbuildr' );

			$check_page_exist = get_page_by_title( $unsubscribe_text, 'OBJECT', 'page' );

			if ( null === $check_page_exist ) {
				$landing_page_id = wp_insert_post(
					array(
						'comment_status' => 'close',
						'ping_status'    => 'close',
						'post_author'    => 1,
						'post_title'     => ucwords( $unsubscribe_text ),
						'post_name'      => strtolower( str_replace( ' ', '-', $unsubscribe_text ) ),
						'post_status'    => 'publish',
						'post_content'   => '',
						'post_type'      => 'page',
					)
				);
			} else {
				if ( 'trash' === $check_page_exist->post_status ) {
					wp_update_post(
						array(
							'ID'             => $check_page_exist->ID,
							'comment_status' => 'close',
							'ping_status'    => 'close',
							'post_author'    => 1,
							'post_title'     => ucwords( $unsubscribe_text ),
							'post_name'      => strtolower( str_replace( ' ', '-', $unsubscribe_text ) ),
							'post_status'    => 'publish',
							'post_content'   => '',
							'post_type'      => 'page',
						)
					);
				}

				$landing_page_id = $check_page_exist->ID;
			}

			$linkbuildr_unsubsribe_landing = array(
				'linkbuildr_unsubsribe_landing_post_id' => $landing_page_id,
				'linkbuildr_unsubsribe_404_landing_not_in_use' => 1,
			);

			$linkbuildr_unsubsribe_link_text = $unsubscribe_text;

			$linkbuildr_unsubsribe_message = array(
				'unsubsribe_message'           => '[contactemail] has been unsubscribed.
We\'re sorry to see you go [contactname]!
How will you know when we link to [contactsitename]?',
				'already_unsubscribed_message' => '[contactemail] is already unsubscribed from emails related to links to [contactsitename].',
			);

			$linkbuildr_show_notifications = 1;

			update_option( 'linkbuildr_unsubsribe_link_text', $linkbuildr_unsubsribe_link_text );
			update_option( 'linkbuildr_unsubsribe_message', $linkbuildr_unsubsribe_message );
			update_option( 'linkbuildr_show_notifications', $linkbuildr_show_notifications );
			update_option( 'linkbuildr_unsubsribe_landing', $linkbuildr_unsubsribe_landing );
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
	}
}
