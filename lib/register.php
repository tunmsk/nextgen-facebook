<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbRegister' ) ) {

	class NgfbRegister {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			register_activation_hook( NGFB_FILEPATH, array( &$this, 'network_activate' ) );
			register_deactivation_hook( NGFB_FILEPATH, array( &$this, 'network_deactivate' ) );
			register_uninstall_hook( NGFB_FILEPATH, array( __CLASS__, 'network_uninstall' ) );

			add_action( 'wpmu_new_blog', array( &$this, 'wpmu_new_blog' ), 10, 6 );
			add_action( 'wpmu_activate_blog', array( &$this, 'wpmu_activate_blog' ), 10, 5 );
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

		public static function network_uninstall() {
			$sitewide = true;
			$cf = NgfbConfig::get_config();

			// uninstall from the individual blogs first
			self::do_multisite( $sitewide, array( __CLASS__, 'uninstall_plugin' ) );

			if ( ! defined( 'NGFB_SITE_OPTIONS_NAME' ) )
				define( 'NGFB_SITE_OPTIONS_NAME', $cf['lca'].'_site_options' );

			$opts = get_site_option( NGFB_SITE_OPTIONS_NAME );

			if ( empty( $opts['plugin_preserve'] ) )
				delete_site_option( NGFB_SITE_OPTIONS_NAME );
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
			$lca = $this->p->cf['lca'];
			$uca = $this->p->cf['uca'];
			$short = $this->p->cf['plugin'][$lca]['short'];

			foreach ( array( 'wp', 'php' ) as $key ) {
				switch ( $key ) {
					case 'wp':
						$label = 'WordPress';
						global $wp_version;
						$version = $wp_version;
						break;
					case 'php':
						$label = 'PHP';
						$version = phpversion();
						break;
				}
				$short = $this->p->cf['plugin'][$lca]['short'];
				$min_version = $this->p->cf[$key]['min_version'];

				if ( version_compare( $version, $min_version, '<' ) ) {
					require_once( ABSPATH.'wp-admin/includes/plugin.php' );
					deactivate_plugins( NGFB_PLUGINBASE );
					error_log( NGFB_PLUGINBASE.' requires '.$label.' '.$min_version.' or higher ('.$version.' reported).' );
					wp_die( '<p>The '.$short.' plugin cannot be activated &mdash; '.
						$short.' requires '.$label.' version '.$min_version.' or newer.</p>' );
				}
			}

			set_transient( $lca.'_activation_redirect', true, 60 * 60 );

			$this->p->set_config();
			$this->p->set_objects( true );	// $activate = true

			if ( ! is_array( $this->p->options ) || empty( $this->p->options ) ||
				( defined( $uca.'_RESET_ON_ACTIVATE' ) && constant( $uca.'_RESET_ON_ACTIVATE' ) ) ) {

				$this->p->options = $this->p->opt->get_defaults();
				delete_option( constant( $uca.'_OPTIONS_NAME' ) );
				add_option( constant( $uca.'_OPTIONS_NAME' ), $this->p->options, null, 'yes' );	// autoload = yes

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'default options have been added to the database' );

				if ( defined( $uca.'_RESET_ON_ACTIVATE' ) && constant( $uca.'_RESET_ON_ACTIVATE' ) )
					$this->p->notice->inf( $uca.'_RESET_ON_ACTIVATE constant is true &ndash; 
						plugin options have been reset to their default values.', true );
			}

			if ( empty( $this->p->options['plugin_filter_content'] ) )
				$this->p->notice->inf( '<strong>The '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content', 'Apply WordPress Content Filters' ).' advanced option is currently disabled</strong>. The use of WordPress content filters allows '.$short.' to fully render your content text for meta tag descriptions and detect additional images / embedded videos provided by shortcodes (for example).<br/><br/><strong>Some theme / plugins have badly coded content filters, so this option is disabled by default</strong>. '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content', 'If you use any shortcodes in your content text, this option should be enabled' ).' &mdash; if you experience back-end / front-end display issues after enabling this option, determine which theme / plugin is at fault and report the problem to the appropriate theme / plugin author(s).', true );

			$this->p->util->clear_all_cache();
		}

		private function deactivate_plugin() {
			// clear all cached objects and transients
			$this->p->util->delete_expired_db_transients( true );
			$this->p->util->delete_expired_file_cache( true );
			$this->p->notice->trunc();
		}

		private static function uninstall_plugin() {
			$cf = NgfbConfig::get_config();

			if ( ! defined( 'NGFB_OPTIONS_NAME' ) )
				define( 'NGFB_OPTIONS_NAME', $cf['lca'].'_options' );

			if ( ! defined( 'NGFB_META_NAME' ) )
				define( 'NGFB_META_NAME', '_'.$cf['lca'].'_meta' );

			if ( ! defined( 'NGFB_PREF_NAME' ) )
				define( 'NGFB_PREF_NAME', '_'.$cf['lca'].'_pref' );

			$slug = $cf['plugin'][$cf['lca']]['slug'];
			$opts = get_option( NGFB_OPTIONS_NAME );

			if ( empty( $opts['plugin_preserve'] ) ) {
				delete_option( NGFB_OPTIONS_NAME );
				delete_post_meta_by_key( NGFB_META_NAME );
				foreach ( array( NGFB_META_NAME, NGFB_PREF_NAME ) as $meta_key ) {
					foreach ( get_users( array( 'meta_key' => $meta_key ) ) as $user ) {
						delete_user_option( $user->ID, $meta_key );
						NgfbUser::delete_metabox_prefs( $user->ID );
					}
				}
				foreach ( NgfbTaxonomy::get_public_terms() as $term_id )
					NgfbTaxonomy::delete_term_meta( $term_id, NGFB_META_NAME );
			}

			// delete transients
			global $wpdb;
			$dbquery = 'SELECT option_name FROM '.$wpdb->options.' WHERE option_name LIKE \'_transient_timeout_'.$cf['lca'].'_%\';';
			$expired = $wpdb->get_col( $dbquery ); 
			foreach( $expired as $transient ) { 
				$key = str_replace('_transient_timeout_', '', $transient);
				if ( ! empty( $key ) )
					delete_transient( $key );
			}
		}
	}
}

?>
