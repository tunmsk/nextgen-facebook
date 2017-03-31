<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbRegister' ) ) {

	class NgfbRegister {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			register_activation_hook( NGFB_FILEPATH, array( &$this, 'network_activate' ) );
			register_deactivation_hook( NGFB_FILEPATH, array( &$this, 'network_deactivate' ) );

			if ( is_multisite() ) {
				add_action( 'wpmu_new_blog', array( &$this, 'wpmu_new_blog' ), 10, 6 );
				add_action( 'wpmu_activate_blog', array( &$this, 'wpmu_activate_blog' ), 10, 5 );
			}
		}

		// fires immediately after a new site is created
		public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			switch_to_blog( $blog_id );
			$this->activate_plugin();
			restore_current_blog();
		}

		// fires immediately after a site is activated
		// (not called when users and sites are created by a Super Admin)
		public function wpmu_activate_blog( $blog_id, $user_id, $password, $signup_title, $meta ) {
			switch_to_blog( $blog_id );
			$this->activate_plugin();
			restore_current_blog();
		}

		public function network_activate( $sitewide ) {
			self::do_multisite( $sitewide, array( &$this, 'activate_plugin' ) );
		}

		public function network_deactivate( $sitewide ) {
			self::do_multisite( $sitewide, array( &$this, 'deactivate_plugin' ) );
		}

		// called from uninstall.php for network or single site
		public static function network_uninstall() {
			$sitewide = true;

			// uninstall from the individual blogs first
			self::do_multisite( $sitewide, array( __CLASS__, 'uninstall_plugin' ) );

			$var_const = NgfbConfig::get_variable_constants();
			$opts = get_site_option( $var_const['NGFB_SITE_OPTIONS_NAME'], array() );

			if ( empty( $opts['plugin_preserve'] ) ) {
				delete_site_option( $var_const['NGFB_SITE_OPTIONS_NAME'] );
			}
		}

		private static function do_multisite( $sitewide, $method, $args = array() ) {
			if ( is_multisite() && $sitewide ) {
				global $wpdb;
				$dbquery = 'SELECT blog_id FROM '.$wpdb->blogs;
				$ids = $wpdb->get_col( $dbquery );
				foreach ( $ids as $id ) {
					switch_to_blog( $id );
					call_user_func_array( $method, array( $args ) );
				}
				restore_current_blog();
			} else call_user_func_array( $method, array( $args ) );
		}

		private function activate_plugin() {

			$this->check_required( NgfbConfig::$cf );

			$this->p->set_config();
			$this->p->set_options();
			$this->p->set_objects( true );	// $activate = true
			$this->p->util->clear_all_cache( true );	// $clear_ext = true

			$plugin_version = NgfbConfig::$cf['plugin']['ngfb']['version'];

			NgfbUtil::save_all_times( 'ngfb', $plugin_version );
			set_transient( 'ngfb_activation_redirect', true, 60 * 60 );

			if ( ! is_array( $this->p->options ) || empty( $this->p->options ) ||
				( defined( 'NGFB_RESET_ON_ACTIVATE' ) && constant( 'NGFB_RESET_ON_ACTIVATE' ) ) ) {

				$this->p->options = $this->p->opt->get_defaults();
				unset( $this->p->options['options_filtered'] );	// just in case

				delete_option( constant( 'NGFB_OPTIONS_NAME' ) );
				add_option( constant( 'NGFB_OPTIONS_NAME' ), $this->p->options, null, 'yes' );	// autoload = yes

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'default options have been added to the database' );
				}

				if ( defined( 'NGFB_RESET_ON_ACTIVATE' ) && constant( 'NGFB_RESET_ON_ACTIVATE' ) ) {
					$this->p->notice->warn( 'NGFB_RESET_ON_ACTIVATE constant is true &ndash; 
						plugin options have been reset to their default values.' );
				}
			}
		}

		private function deactivate_plugin() {

			// clear all cached objects and transients
			$this->p->util->clear_all_cache( false );	// $clear_ext = false

			// trunc all stored notices for all users
			$this->p->notice->trunc_all();

			if ( is_object( $this->p->admin ) ) {		// just in case
				$this->p->admin->reset_check_head_count();
			}
		}

		private static function uninstall_plugin() {

			$var_const = NgfbConfig::get_variable_constants();
			$opts = get_option( $var_const['NGFB_OPTIONS_NAME'], array() );

			delete_option( $var_const['NGFB_TS_NAME'] );
			delete_option( $var_const['NGFB_NOTICE_NAME'] );

			if ( empty( $opts['plugin_preserve'] ) ) {

				delete_option( $var_const['NGFB_OPTIONS_NAME'] );
				delete_post_meta_by_key( $var_const['NGFB_META_NAME'] );

				foreach ( get_users() as $user ) {

					// site specific user options
					delete_user_option( $user->ID, $var_const['NGFB_NOTICE_NAME'] );
					delete_user_option( $user->ID, $var_const['NGFB_DISMISS_NAME'] );

					// global / network user options
					delete_user_meta( $user->ID, $var_const['NGFB_META_NAME'] );
					delete_user_meta( $user->ID, $var_const['NGFB_PREF_NAME'] );

					NgfbUser::delete_metabox_prefs( $user->ID );
				}

				foreach ( NgfbTerm::get_public_terms() as $term_id ) {
					NgfbTerm::delete_term_meta( $term_id, $var_const['NGFB_META_NAME'] );
				}
			}

			/*
			 * Delete All Transients
			 */
			global $wpdb;
			$prefix = '_transient_';	// clear all transients, even if no timeout value
			$dbquery = 'SELECT option_name FROM '.$wpdb->options.
				' WHERE option_name LIKE \''.$prefix.'ngfb_%\';';
			$expired = $wpdb->get_col( $dbquery ); 

			foreach( $expired as $option_name ) { 
				$transient_name = str_replace( $prefix, '', $option_name );
				if ( ! empty( $transient_name ) ) {
					delete_transient( $transient_name );
				}
			}
		}

		private static function check_required( $cf ) {

			$plugin_name = $cf['plugin']['ngfb']['name'];
			$plugin_version = $cf['plugin']['ngfb']['version'];

			foreach ( array( 'wp', 'php' ) as $key ) {
				if ( empty( $cf[$key]['min_version'] ) ) {
					return;
				}
				switch ( $key ) {
					case 'wp':
						global $wp_version;
						$app_version = $wp_version;
						break;
					case 'php':
						$app_version = phpversion();
						break;
				}

				$app_label = $cf[$key]['label'];
				$min_version = $cf[$key]['min_version'];
				$version_url = $cf[$key]['version_url'];

				if ( version_compare( $app_version, $min_version, '>=' ) ) {
					continue;
				}

				load_plugin_textdomain( 'nextgen-facebook', false, 'nextgen-facebook/languages/' );

				if ( ! function_exists( 'deactivate_plugins' ) ) {
					require_once trailingslashit( ABSPATH ).'wp-admin/includes/plugin.php';
				}

				deactivate_plugins( NGFB_PLUGINBASE, true );	// $silent = true

				wp_die( 
					'<p>'.sprintf( __( 'You are using %1$s version %2$s &mdash; <a href="%4$s">this %1$s version is outdated, unsupported, insecure</a> and may lack important features.',
						'nextgen-facebook' ), $app_label, $app_version, $min_version, $version_url ).'</p>'.
					'<p>'.sprintf( __( '%1$s requires %2$s version %3$s or higher and has been deactivated.',
						'nextgen-facebook' ), $plugin_name, $app_label, $min_version ).'</p>'.
					'<p>'.sprintf( __( 'Please upgrade %1$s before trying to re-activate the %2$s plugin.',
						'nextgen-facebook' ), $app_label, $plugin_name ).'</p>'
				);
			}
		}
	}
}

?>
