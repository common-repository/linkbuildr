<?php
/**
 * Linkbuildr: Module_Abstract
 *
 * The abstract/parent class to all other class modules
 *
 * @package Linkbuildr
 * @since 1.0.0
 */

if ( ! class_exists( 'Module_Abstract' ) ) {

	/**
	 * Module_Abstract class holds functions used uniformly across all modules, and abstract definition of all required methods
	 *
	 * @since 1.0.0
	 */
	abstract class Module_Abstract {
		/**
		 * Defines instances used.
		 *
		 * @since 1.0.0
		 * @var Array instances contains instances of modules.
		 */
		private static $instances = array();

		/**
		 * Public getter for protected variables
		 *
		 * @param string $variable the variable to return the value of.
		 * @throws Exception If the variable requested is not found.
		 * @return mixed
		 */
		public function __get( $variable ) {
			$module = get_called_class();

			if ( in_array( $variable, $module::$readable_properties, true ) ) {
				return $this->$variable;
			} else {
				throw new Exception( __METHOD__ . ' error: $' . $variable . " doesn't exist or isn't readable." );
			}
		}

		/**
		 * Public setter for protected variables
		 *
		 * @param string $variable the name of the variable to set the value of.
		 * @param mixed  $value the value to set the variable to.
		 * @throws Exception If the variable requested is not found.
		 * @throws Exception If the data value is invalid for the found variable.
		 */
		public function __set( $variable, $value ) {
			$module = get_called_class();

			if ( in_array( $variable, $module::$writeable_properties, true ) ) {
				$this->$variable = $value;

				if ( ! $this->is_valid() ) {
					throw new Exception( __METHOD__ . ' error: $' . $value . ' is not valid.' );
				}
			} else {
				throw new Exception( __METHOD__ . ' error: $' . $variable . " doesn't exist or isn't writable." );
			}
		}

		/**
		 * Provides access to a single instance of a module using the singleton pattern
		 *
		 * @return object
		 */
		public static function get_instance() {
			$module = get_called_class();

			if ( ! isset( self::$instances[ $module ] ) ) {
				self::$instances[ $module ] = new $module();
			}

			return self::$instances[ $module ];
		}

		/**
		 * Render a template
		 *
		 * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
		 * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
		 * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
		 * fully customize the output.
		 *
		 * @param  string $default_template_path The path to the template, relative to the plugin's `views` folder.
		 * @param  array  $variables             An array of variables to pass into the template's scope, indexed with the variable name so that it can be extract()-ed.
		 * @param  string $require               'once' to use require_once() | 'always' to use require().
		 * @return string
		 */
		protected static function render_template( $default_template_path = false, $variables = array(), $require = 'once' ) {
			do_action( 'render_template_pre', $default_template_path, $variables );

			$template_path = locate_template( basename( $default_template_path ) );
			if ( ! $template_path ) {
				$template_path = dirname( __DIR__ ) . '/views/' . $default_template_path;
			}
			$template_path = apply_filters( 'template_path', $template_path );

			if ( is_file( $template_path ) ) {
				extract( $variables ); // phpcs:ignore WordPress.PHP.DontExtract
				ob_start();

				if ( 'always' === $require ) {
					require $template_path;
				} else {
					require_once $template_path;
				}

				$template_content = apply_filters( 'template_content', ob_get_clean(), $default_template_path, $template_path, $variables );
			} else {
				$template_content = '';
			}

			do_action( 'render_template_post', $default_template_path, $variables, $template_path, $template_content );
			return $template_content;
		}

		/**
		 * Generates the unsubscribe token, used to validate the user coming in to unsubscribe
		 *
		 * @since 1.1.0
		 *
		 * @param String $input a seeder string for the SHA256 to use to generate the token.
		 * @return String the token for use in url param to validate user to unsubscribe.
		 */
		protected static function generate_unsubscribe_key( $input ) {
			return hash( 'sha256', $input );
		}

		/**
		 * Parses the domain from a passed in url value
		 *
		 * @since 1.3.0
		 *
		 * @param String $url the url of which to parse the domain out of.
		 * @return String The domain parsed out of the url passed in.
		 */
		protected static function parse_domain( $url ) {
			$domain     = '';
			$parsed_url = wp_parse_url( trim( $url ) );
			if ( array_key_exists( 'host', $parsed_url ) && $parsed_url['host'] ) {
				$domain = $parsed_url['host'];
			} elseif ( array_key_exists( 'path', $parsed_url ) && $parsed_url['path'] ) {
				$exploded_path = explode( '/', $parsed_url['path'], 2 );
				$domain        = array_shift( $exploded_path );
			}
			return $domain;
		}

		/**
		 * Constructor
		 */
		abstract protected function __construct();

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 *
		 * @param bool $network_wide flag to indicate if the activation is across multiple sites/blogs.
		 */
		abstract public function activate( $network_wide );

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 */
		abstract public function deactivate();

		/**
		 * Register callbacks for actions and filters
		 */
		abstract public function register_hook_callbacks();

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary
		 *
		 * @param string $db_version version of the db expected.
		 */
		abstract public function upgrade( $db_version = 0 );

		/**
		 * Checks that the object is in a correct state
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them.
		 * @return bool
		 */
		abstract protected function is_valid( $property = 'all' );
	}
}
