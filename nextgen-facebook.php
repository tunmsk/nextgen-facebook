<?php
/*
 * Plugin Name: NextGEN Facebook (NGFB)
 * Plugin Slug: nextgen-facebook
 * Text Domain: nextgen-facebook
 * Domain Path: /languages
 * Plugin URI: https://surniaulula.com/extend/plugins/nextgen-facebook/
 * Assets URI: https://surniaulula.github.io/nextgen-facebook/assets/
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Complete meta tags for the best looking shares on Facebook, Google, Pinterest, Twitter, etc - no matter how your webpage is shared!
 * Requires At Least: 3.7
 * Tested Up To: 4.6.1
 * Version: 8.37.4-dev2
 *
 * Version Numbering Scheme: {major}.{minor}.{bugfix}-{stage}{level}
 *
 *	{major}		Major code changes / re-writes or significant feature changes.
 *	{minor}		New features / options were added or improved.
 *	{bugfix}	Bugfixes or minor improvements.
 *	{stage}{level}	dev < a (alpha) < b (beta) < rc (release candidate) < # (production).
 *
 * See PHP's version_compare() documentation at http://php.net/manual/en/function.version-compare.php.
 * 
 * This script is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This script is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details at
 * http://www.gnu.org/licenses/.
 * 
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'Ngfb' ) ) {

	class Ngfb {
		/*
		 * Class Object Variables
		 */
		public $p;			// Ngfb
		public $admin;			// NgfbAdmin (admin menus and page loader)
		public $cache;			// SucomCache (object and file caching)
		public $debug;			// SucomDebug or SucomNoDebug
		public $head;			// NgfbHead
		public $loader;			// NgfbLoader
		public $media;			// NgfbMedia (images, videos, etc.)
		public $msgs;			// NgfbMessages (admin tooltip messages)
		public $notice;			// SucomNotice or SucomNoNotice
		public $og;			// NgfbOpenGraph
		public $weibo;			// NgfbWeibo
		public $tc;			// NgfbTwitterCard
		public $opt;			// NgfbOptions
		public $reg;			// NgfbRegister
		public $script;			// SucomScript (admin jquery tooltips)
		public $sharing;		// NgfbSharing (wp_head and wp_footer js and buttons)
		public $style;			// SucomStyle (admin styles)
		public $util;			// NgfbUtil (extends SucomUtil)
		public $webpage;		// SucomWebpage (title, desc, etc., plus shortcodes)

		/*
		 * Reference Variables (config, options, modules, etc.)
		 */
		public $m = array();		// plugin modules
		public $m_ext = array();	// plugin extension modules
		public $cf = array();		// config array defined in construct method
		public $is_avail = array();	// assoc array for other plugin checks
		public $options = array();	// individual blog/site options
		public $site_options = array();	// multisite options

		private static $instance = null;

		public static function &get_instance() {
			if ( self::$instance === null )
				self::$instance = new self;
			return self::$instance;
		}

		/*
		 * Ngfb Constructor
		 */
		public function __construct() {

			require_once( dirname( __FILE__ ).'/lib/config.php' );
			$this->cf = NgfbConfig::get_config();			// unfiltered - $cf['*'] array is not available yet
			NgfbConfig::set_constants( __FILE__ );
			NgfbConfig::require_libs( __FILE__ );			// includes the register.php class library
			$this->reg = new NgfbRegister( $this );			// activate, deactivate, uninstall hooks

			add_action( 'init', array( &$this, 'set_config' ), -10 );
			add_action( 'init', array( &$this, 'init_plugin' ), NGFB_INIT_PRIORITY );
			add_action( 'widgets_init', array( &$this, 'init_widgets' ), 10 );
		}

		// runs at init priority -10
		public function set_config() {
			$this->cf = NgfbConfig::get_config( false, true );	// apply filters - define the $cf['*'] array
		}

		// runs at init priority 13 (by default)
		public function init_plugin() {

			$this->set_objects();				// define the class object variables

			if ( $this->debug->enabled )
				$this->debug->mark( 'plugin initialization' );

			if ( $this->debug->enabled ) {
				foreach ( array( 'wp_head', 'wp_footer', 'admin_head', 'admin_footer' ) as $action ) {
					foreach ( array( -9999, 9999 ) as $prio ) {
						add_action( $action, create_function( '', 'echo "<!-- ngfb '.
							$action.' action hook priority '.$prio.' mark -->\n";' ), $prio );
						add_action( $action, array( &$this, 'show_debug_html' ), $prio );
					}
				}
			}

			if ( $this->debug->enabled )
				$this->debug->log( 'running init_plugin action' );
			do_action( 'ngfb_init_plugin' );

			if ( $this->debug->enabled )
				$this->debug->mark( 'plugin initialization' );
		}

		public function show_debug_html() { 
			if ( $this->debug->enabled )
				$this->debug->show_html();
		}

		public function init_widgets() {
			$opts = get_option( NGFB_OPTIONS_NAME );
			if ( ! empty( $opts['plugin_widgets'] ) ) {
				foreach ( $this->cf['plugin'] as $lca => $info ) {
					if ( isset( $info['lib']['widget'] ) && is_array( $info['lib']['widget'] ) ) {
						foreach ( $info['lib']['widget'] as $id => $name ) {
							$classname = apply_filters( $lca.'_load_lib', false, 'widget/'.$id );
							if ( $classname !== false && class_exists( $classname ) )
								register_widget( $classname );
						}
					}
				}
			}
		}

		// called by activate_plugin() as well
		public function set_objects( $activate = false ) {
			/*
			 * basic plugin setup (settings, check, debug, notices, utils)
			 */
			$this->set_options();	// filter and define the $this->options and $this->site_options properties
			$this->check = new NgfbCheck( $this );
			$this->is_avail = $this->check->get_avail();		// uses $this->options in checks

			// configure the debug class
			$html_debug = ! empty( $this->options['plugin_debug'] ) || 
				( defined( 'NGFB_HTML_DEBUG' ) && NGFB_HTML_DEBUG ) ? true : false;
			$wp_debug = defined( 'NGFB_WP_DEBUG' ) && NGFB_WP_DEBUG ? true : false;

			if ( ( $html_debug || $wp_debug ) &&			// only load debug class if one or more debug options enabled
				( $classname = NgfbConfig::load_lib( false, 'com/debug', 'SucomDebug' ) ) ) {
				$this->debug = new $classname( $this, array( 'html' => $html_debug, 'wp' => $wp_debug ) );
				if ( $this->debug->enabled ) {
					$this->debug->log( 'debug enabled on '.date( 'c' ) );
					$this->debug->log( $this->check->get_ext_list() );
				}
			} else $this->debug = new SucomNoDebug();			// make sure debug property is always available

			if ( $activate === true && $this->debug->enabled )
				$this->debug->log( 'method called for plugin activation' );

			if ( is_admin() && 					// only load notice class in the admin interface
				( $classname = NgfbConfig::load_lib( false, 'com/notice', 'SucomNotice' ) ) )
					$this->notice = new $classname( $this );
			else $this->notice = new SucomNoNotice();		// make sure notice property is always available

			$this->util = new NgfbUtil( $this );			// extends SucomUtil
			$this->opt = new NgfbOptions( $this );
			$this->cache = new SucomCache( $this );			// object and file caching
			$this->style = new SucomStyle( $this );			// admin styles
			$this->script = new SucomScript( $this );		// admin jquery tooltips
			$this->webpage = new SucomWebpage( $this );		// title, desc, etc., plus shortcodes
			$this->media = new NgfbMedia( $this );			// images, videos, etc.
			$this->head = new NgfbHead( $this );
			$this->og = new NgfbOpenGraph( $this );
			$this->weibo = new NgfbWeibo( $this );
			$this->tc = new NgfbTwitterCard( $this );
			$this->schema = new NgfbSchema( $this );

			if ( is_admin() ) {
				$this->msgs = new NgfbMessages( $this );	// admin tooltip messages
				$this->admin = new NgfbAdmin( $this );		// admin menus and page loader
			}

			if ( $this->is_avail['ssb'] )
				$this->sharing = new NgfbSharing( $this );	// wp_head and wp_footer js and buttons

			$this->loader = new NgfbLoader( $this, $activate );	// module loader

			if ( $this->debug->enabled )
				$this->debug->mark( 'init objects action' );
			do_action( 'ngfb_init_objects', $activate );
			if ( $this->debug->enabled )
				$this->debug->mark( 'init objects action' );

			/*
			 * check and create the default options array
			 * execute after all objects have been defines, so hooks into 'ngfb_get_defaults' are available
			 */
			if ( is_multisite() && ( ! is_array( $this->site_options ) || empty( $this->site_options ) ) ) {
				if ( $this->debug->enabled )
					$this->debug->log( 'setting site_options to site_defaults' );
				$this->site_options = $this->opt->get_site_defaults();
			}

			/*
			 * end here when called for plugin activation (the init_plugin() hook handles the rest)
			 */
			if ( $activate == true || ( 
				! empty( $_GET['action'] ) && $_GET['action'] == 'activate-plugin' &&
				! empty( $_GET['plugin'] ) && $_GET['plugin'] == NGFB_PLUGINBASE ) ) {
				if ( $this->debug->enabled )
					$this->debug->log( 'exiting early: init_plugin hook will follow' );
				return;
			}

			/*
			 * check and upgrade options if necessary
			 */
			if ( $this->debug->enabled )
				$this->debug->log( 'checking options' );
			$this->options = $this->opt->check_options( NGFB_OPTIONS_NAME, $this->options );

			if ( is_multisite() ) {
				if ( $this->debug->enabled )
					$this->debug->log( 'checking site_options' );
				$this->site_options = $this->opt->check_options( NGFB_SITE_OPTIONS_NAME, $this->site_options, true );
			}

			if ( $this->debug->enabled ) {
				if ( $this->debug->is_enabled( 'wp' ) ) {
					$this->debug->log( 'WP debug log mode is active' );
					$this->notice->warn( __( 'WP debug log mode is active &mdash; debug messages are being sent to the WordPress debug log.', 'nextgen-facebook' ) );
				} elseif ( $this->debug->is_enabled( 'html' ) ) {
					$this->debug->log( 'HTML debug mode is active' );
					$this->notice->warn( __( 'HTML debug mode is active &mdash; debug messages are being added to webpages as hidden HTML comments.', 'nextgen-facebook' ) );
				}
				$this->util->add_plugin_filters( $this, array( 
					'cache_expire_head_array' => '__return_zero',
					'cache_expire_setup_html' => '__return_zero',
				) );
			}
		}

		public function set_options() {
			$this->options = get_option( NGFB_OPTIONS_NAME );

			// look for alternate options name
			if ( ! is_array( $this->options ) ) {
				if ( defined( 'NGFB_OPTIONS_NAME_ALT' ) && NGFB_OPTIONS_NAME_ALT ) {
					$this->options = get_option( NGFB_OPTIONS_NAME_ALT );
					if ( is_array( $this->options ) ) {
						// auto-creates options with autoload = yes
						update_option( NGFB_OPTIONS_NAME, $this->options );
						delete_option( NGFB_OPTIONS_NAME_ALT );
					}
				}
			}

			if ( ! is_array( $this->options ) )
				$this->options = array();

			unset( $this->options['options_filtered'] );	// just in case

			$this->options = apply_filters( 'ngfb_get_options', $this->options );

			if ( is_multisite() ) {
				$this->site_options = get_site_option( NGFB_SITE_OPTIONS_NAME );

				// look for alternate site options name
				if ( ! is_array( $this->site_options ) ) {
					if ( defined( 'NGFB_SITE_OPTIONS_NAME_ALT' ) && NGFB_SITE_OPTIONS_NAME_ALT ) {
						$this->site_options = get_site_option( NGFB_SITE_OPTIONS_NAME_ALT );
						if ( is_array( $this->site_options ) ) {
							update_site_option( NGFB_SITE_OPTIONS_NAME, $this->site_options );
							delete_site_option( NGFB_SITE_OPTIONS_NAME_ALT );
						}
					}
				}

				if ( ! is_array( $this->site_options ) )
					$this->site_options = array();

				unset( $this->site_options['options_filtered'] );	// just in case

				$this->site_options = apply_filters( 'ngfb_get_site_options', $this->site_options );

				// if multisite options are found, check for overwrite of site specific options
				if ( is_array( $this->options ) && is_array( $this->site_options ) ) {
					$current_blog_id = function_exists( 'get_current_blog_id' ) ? 
						get_current_blog_id() : false;
					foreach ( $this->site_options as $key => $val ) {
						if ( strpos( $key, ':use' ) !== false )
							continue;
						if ( isset( $this->site_options[$key.':use'] ) ) {
							switch ( $this->site_options[$key.':use'] ) {
								case'force':
									$this->options[$key.':is'] = 'disabled';
									$this->options[$key] = $this->site_options[$key];
									break;
								case 'empty':
									if ( empty( $this->options[$key] ) )
										$this->options[$key] = $this->site_options[$key];
									break;
							}
						}
						// check for constant over-rides
						if ( $current_blog_id !== false ) {
							$constant_name = 'NGFB_OPTIONS_'.$current_blog_id.'_'.strtoupper( $key );
							if ( defined( $constant_name ) )
								$this->options[$key] = constant( $constant_name );
						}
					}
				}
			}
		}
	}

	global $ngfb;
	$ngfb =& Ngfb::get_instance();
}

?>
