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
 * Requires At Least: 3.8
 * Tested Up To: 4.7.2
 * Version: 8.40.1-2
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
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
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
		public $check;			// NgfbCheck
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

		private static $instance;

		/*
		 * Ngfb Constructor
		 */
		public function __construct() {

			require_once( dirname( __FILE__ ).'/lib/config.php' );
			$this->cf = NgfbConfig::get_config();			// unfiltered - $cf['*'] array is not available yet
			NgfbConfig::set_constants( __FILE__ );
			NgfbConfig::require_libs( __FILE__ );			// includes the register.php class library
			$this->reg = new NgfbRegister( $this );			// activate, deactivate, uninstall hooks

			add_action( 'init', array( &$this, 'set_config' ), NGFB_INIT_PRIORITY - 3 );	// 11 by default
			add_action( 'init', array( &$this, 'set_options' ), NGFB_INIT_PRIORITY - 2 );	// 12 by default
			add_action( 'init', array( &$this, 'set_objects' ), NGFB_INIT_PRIORITY - 1 );	// 13 by default
			add_action( 'init', array( &$this, 'init_plugin' ), NGFB_INIT_PRIORITY );	// 14 by default

			add_action( 'widgets_init', array( &$this, 'init_widgets' ), 10 );

			if ( is_admin() )
				add_action( 'ngfb_init_textdomain', 		// action is run after the debug property is defined
					array( __CLASS__, 'init_textdomain' ), -10, 1 );	// hooks override_textdomain_mofile if debug enabled
		}

		public static function &get_instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self;
			return self::$instance;
		}

		// runs at init priority 11 by default
		// called by activate_plugin() as well
		public function set_config() {
			$this->cf = NgfbConfig::get_config( false, true );	// apply filters - define the $cf['*'] array
		}

		// runs at init priority 12 by default
		// called by activate_plugin() as well
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

				// if multisite options are found, check for overwrite of site specific options
				if ( is_array( $this->options ) && is_array( $this->site_options ) ) {
					$blog_id = get_current_blog_id();	// since wp 3.1
					$defined_constants = get_defined_constants( true );	// $categorize = true
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
						$constant_name = 'NGFB_ID_'.$blog_id.'_OPT_'.strtoupper( $key );
						if ( isset( $defined_constants['user'][$constant_name] ) )
							$this->options[$key] = $defined_constants['user'][$constant_name];
					}
				}
			}
		}

		// runs at init priority 13 by default
		// called by activate_plugin() as well
		public function set_objects( $activate = false ) {
			
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

			do_action( 'ngfb_init_textdomain', $this->debug->enabled );

			if ( $activate === true && 
				$this->debug->enabled )
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
			$this->filters = new NgfbFilters( $this );		// integration filters
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
				$this->debug->mark( 'init objects action' );	// begin timer

			do_action( 'ngfb_init_objects', $activate );

			if ( $this->debug->enabled )
				$this->debug->mark( 'init objects action' );	// end timer

			// check and create the default options array
			// execute after all objects are defined, so all 'wpsso_get_site_defaults' filters are available
			if ( is_multisite() && 
				( ! is_array( $this->site_options ) || empty( $this->site_options ) ) ) {
				if ( $this->debug->enabled )
					$this->debug->log( 'setting site_options to site_defaults' );
				$this->site_options = $this->opt->get_site_defaults();
				unset( $this->site_options['options_filtered'] );	// just in case
			}

			// end here when called for plugin activation (the init_plugin() hook handles the rest)
			if ( $activate == true || ( 
				! empty( $_GET['action'] ) && $_GET['action'] == 'activate-plugin' &&
				! empty( $_GET['plugin'] ) && $_GET['plugin'] == NGFB_PLUGINBASE ) ) {
				if ( $this->debug->enabled )
					$this->debug->log( 'exiting early: init_plugin hook will follow' );
				return;
			}

			// check and upgrade options if necessary
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
					'cache_expire_sharing_buttons' => '__return_zero',
				) );
			}
		}

		// runs at init priority 14 by default
		public function init_plugin() {
			if ( $this->debug->enabled )
				$this->debug->mark( 'plugin initialization' );	// begin timer

			if ( $this->debug->enabled ) {
				foreach ( array( 'wp_head', 'wp_footer', 'admin_head', 'admin_footer' ) as $action ) {
					foreach ( array( -9000, 9000 ) as $prio ) {
						add_action( $action, create_function( '',
							'echo "<!-- ngfb '.$action.' action hook priority '.
								$prio.' mark -->\n";' ), $prio );
						add_action( $action, array( &$this, 'show_debug' ), $prio + 1 );
					}
				}
				foreach ( array( 'wp_footer', 'admin_footer' ) as $action ) {
					foreach ( array( 9900 ) as $prio ) {
						add_action( $action, array( &$this, 'show_config' ), $prio );
					}
				}
			}

			if ( $this->debug->enabled )
				$this->debug->log( 'running init_plugin action' );

			do_action( 'ngfb_init_plugin' );

			if ( $this->debug->enabled )
				$this->debug->mark( 'plugin initialization' );	// end timer
		}

		// runs at widgets_init priority 10
		public function init_widgets() {
			$opts = get_option( NGFB_OPTIONS_NAME );
			if ( ! empty( $opts['plugin_widgets'] ) ) {
				foreach ( $this->cf['plugin'] as $ext => $info ) {
					if ( isset( $info['lib']['widget'] ) && is_array( $info['lib']['widget'] ) ) {
						foreach ( $info['lib']['widget'] as $id => $name ) {
							$classname = apply_filters( $ext.'_load_lib', false, 'widget/'.$id );
							if ( $classname !== false && class_exists( $classname ) )
								register_widget( $classname );
						}
					}
				}
			}
		}

		// runs at ngfb_init_textdomain priority -10
		public static function init_textdomain( $debug_enabled ) {
			if ( $debug_enabled )
				add_filter( 'load_textdomain_mofile', 
					array( Ngfb::get_instance(), 'override_textdomain_mofile' ), 10, 3 );
			load_plugin_textdomain( 'nextgen-facebook', false, 'nextgen-facebook/languages/' );
		}

		// only runs when debug is enabled
		public function override_textdomain_mofile( $wp_mofile, $domain ) {
			if ( strpos( $domain, 'nextgen-facebook' ) === 0 ) {	// optimize
				foreach ( $this->cf['plugin'] as $ext => $info ) {
					if ( $info['slug'] === $domain ) {
						$constant_name = strtoupper( $ext ).'_PLUGINDIR';
						if ( defined( $constant_name ) &&
							$plugin_dir = constant( strtoupper( $ext ).'_PLUGINDIR' ) ) {
							$plugin_mofile = $plugin_dir.'languages/'.basename( $wp_mofile );
							if ( $plugin_mofile !== $wp_mofile &&
								is_readable( $plugin_mofile ) ) {
								global $l10n;
								unset( $l10n[$domain] );	// prevent merging
								return $plugin_mofile;
							}
						}
						break;	// stop here
					}
				}
			}
			return $wp_mofile;
		}

		// only runs when debug is enabled
		public function show_debug() { 
			$this->debug->show_html( null, 'debug log' );
		}

		// only runs when debug is enabled
		public function show_config() { 
			if ( ! $this->debug->enabled )	// just in case
				return;

			// show constants
			$defined_constants = get_defined_constants( true );
			$defined_constants['user']['NGFB_NONCE'] = '********';
			if ( is_multisite() )
				$this->debug->show_html( SucomUtil::preg_grep_keys( '/^(MULTISITE|^SUBDOMAIN_INSTALL|.*_SITE)$/', 
					$defined_constants['user'] ), 'multisite constants' );
			$this->debug->show_html( SucomUtil::preg_grep_keys( '/^NGFB_/',
				$defined_constants['user'] ), 'ngfb constants' );

			// show active plugins
			$this->debug->show_html( print_r( SucomUtil::active_plugins(), true ), 'active plugins' );

			// show available modules
			$this->debug->show_html( print_r( $this->is_avail, true ), 'available features' );

			// show all plugin options
			$opts = $this->options;
			foreach ( $opts as $key => $val ) {
				switch ( $key ) {
					case ( strpos( $key, '_js_' ) !== false ? true : false ):
					case ( strpos( $key, '_css_' ) !== false ? true : false ):
					case ( preg_match( '/(_css|_js|_html)$/', $key ) ? true : false ):
					case ( preg_match( '/_(key|secret|tid|token)$/', $key ) ? true : false ):
						$opts[$key] = '[removed]';
						break;
				}
			}
			$this->debug->show_html( $opts, 'ngfb settings' );
		}
	}

	global $ngfb;
	$ngfb =& Ngfb::get_instance();
}

?>
