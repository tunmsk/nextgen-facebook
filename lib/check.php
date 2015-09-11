<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbCheck' ) ) {

	class NgfbCheck {

		private $p;
		private $active_plugins = array();

		private static $c = array();
		private static $extend_checks = array(
			'seo' => array(
				'seou' => 'SEO Ultimate',
			),
			'util' => array(
				'um' => 'Update Manager',
			),
		);

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( is_object( $this->p->debug ) && 
				method_exists( $this->p->debug, 'mark' ) )
					$this->p->debug->mark();

			$this->active_plugins = NgfbUtil::active_plugins();

			if ( ! is_admin() ) {
				// disable jetPack open graph meta tags
				if ( class_exists( 'JetPack' ) || isset( $this->active_plugins['jetpack/jetpack.php'] ) ) {
					add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
					add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
					add_filter( 'jetpack_disable_twitter_cards', '__return_true', 99 );
				}
	
				// disable Yoast SEO opengraph, twitter, publisher, and author meta tags
				if ( function_exists( 'wpseo_init' ) || isset( $this->active_plugins['wordpress-seo/wp-seo.php'] ) ) {
					global $wpseo_og;
					if ( is_object( $wpseo_og ) && 
						( $prio = has_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ) ) ) ) {
							$ret = remove_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ), $prio );
					}
					if ( ! empty( $this->p->options['tc_enable'] ) && 
						$this->is_aop( $this->p->cf['lca'] ) ) {

						global $wpseo_twitter;
						if ( is_object( $wpseo_twitter ) && 
							( $prio = has_action( 'wpseo_head', array( $wpseo_twitter, 'twitter' ) ) ) )
								$ret = remove_action( 'wpseo_head', array( $wpseo_twitter, 'twitter' ), $prio );
					}
					if ( ! empty( $this->p->options['seo_publisher_url'] ) ) {
						global $wpseo_front;
						if ( is_object( $wpseo_front ) && 
							( $prio = has_action( 'wpseo_head', array( $wpseo_front, 'publisher' ) ) ) )
								$ret = remove_action( 'wpseo_head', array( $wpseo_front, 'publisher' ), $prio );
					}
					if ( ! empty( $this->p->options['seo_def_author_id'] ) &&
						! empty( $this->p->options['seo_def_author_on_index'] ) ) {
						global $wpseo_front;
						if ( is_object( $wpseo_front ) && 
							( $prio = has_action( 'wpseo_head', array( $wpseo_front, 'author' ) ) ) )
								$ret = remove_action( 'wpseo_head', array( $wpseo_front, 'author' ), $prio );
					}
					if ( ! empty( $this->p->options['schema_website_json'] ) ) {
						add_filter( 'wpseo_json_ld_output', '__return_empty_array', 99 );
					}
				}
			}
			do_action( $this->p->cf['lca'].'_init_check', $this->active_plugins );
		}

		private function get_avail_check( $key ) {
			switch ( $key ) {
				case 'aop':
					return ( ! defined( 'NGFB_PRO_MODULE_DISABLE' ) ||
					( defined( 'NGFB_PRO_MODULE_DISABLE' ) && ! NGFB_PRO_MODULE_DISABLE ) ) &&
					file_exists( NGFB_PLUGINDIR.'lib/pro/' ) ? true : false;
					break;
				case 'mt':
				case 'metatags':
					return ( ! defined( 'NGFB_META_TAGS_DISABLE' ) || 
					( defined( 'NGFB_META_TAGS_DISABLE' ) && ! NGFB_META_TAGS_DISABLE ) ) &&
					empty( $_SERVER['NGFB_META_TAGS_DISABLE'] ) &&
					empty( $_GET['NGFB_META_TAGS_DISABLE'] ) ? true : false;	// allow meta tags to be disabled with query argument
					break;
				case 'ssb':
					return ( ! defined( 'NGFB_SOCIAL_SHARING_DISABLE' ) || 
					( defined( 'NGFB_SOCIAL_SHARING_DISABLE' ) && ! NGFB_SOCIAL_SHARING_DISABLE ) ) &&
					empty( $_SERVER['NGFB_SOCIAL_SHARING_DISABLE'] ) &&
					file_exists( NGFB_PLUGINDIR.'lib/sharing.php' ) &&
					class_exists( $this->p->cf['lca'].'sharing' ) ? true : false;
					break;
			}
		}

		public function get_avail() {
			$ret = array();

			$ret['curl'] = function_exists( 'curl_init' ) ? true : false;
			$ret['postthumb'] = function_exists( 'has_post_thumbnail' ) ? true : false;
			$ret['metatags'] = $this->get_avail_check( 'mt' );
			$ret['aop'] = $this->get_avail_check( 'aop' );
			$ret['ssb'] = $this->get_avail_check( 'ssb' );

			foreach ( $this->p->cf['cache'] as $name => $val ) {
				$constant_name = 'NGFB_'.strtoupper( $name ).'_CACHE_DISABLE';
				$ret['cache'][$name] = defined( $constant_name ) &&
					constant( $constant_name ) ? false : true;
			}

			foreach ( SucomUtil::array_merge_recursive_distinct( $this->p->cf['*']['lib']['pro'], 
				self::$extend_checks ) as $sub => $lib ) {

				$ret[$sub] = array();
				$ret[$sub]['*'] = false;
				foreach ( $lib as $id => $name ) {
					$chk = array();
					$ret[$sub][$id] = false;	// default value
					switch ( $sub.'-'.$id ) {
						/*
						 * 3rd Party Plugins
						 */
						case 'ecom-edd':
							$chk['class'] = 'Easy_Digital_Downloads';
							$chk['plugin'] = 'easy-digital-downloads/easy-digital-downloads.php';
							break;
						case 'ecom-marketpress':
							$chk['class'] = 'MarketPress';
							$chk['plugin'] = 'wordpress-ecommerce/marketpress.php';
							break;
						case 'ecom-woocommerce':
							$chk['class'] = 'Woocommerce';
							$chk['plugin'] = 'woocommerce/woocommerce.php';
							break;
						case 'ecom-wpecommerce':
							$chk['class'] = 'WP_eCommerce';
							$chk['plugin'] = 'wp-e-commerce/wp-shopping-cart.php';
							break;
						case 'forum-bbpress':
							$chk['class'] = 'bbPress';
							$chk['plugin'] = 'bbpress/bbpress.php';
							break;
						case 'lang-polylang':
							$chk['class'] = 'Polylang';
							$chk['plugin'] = 'polylang/polylang.php';
							break;
						case 'media-ngg':
							$chk['class'] = 'nggdb';	// C_NextGEN_Bootstrap
							$chk['plugin'] = 'nextgen-gallery/nggallery.php';
							break;
						case 'media-photon':
							if ( class_exists( 'Jetpack' ) && 
								method_exists( 'Jetpack', 'get_active_modules' ) && 
								in_array( 'photon', Jetpack::get_active_modules() ) )
									$ret[$sub]['*'] = $ret[$sub][$id] = true;
							break;
						case 'seo-aioseop':
							$chk['class'] = 'All_in_One_SEO_Pack';
							$chk['plugin'] = 'all-in-one-seo-pack/all-in-one-seo-pack.php';
							break;
						case 'seo-headspace2':
							$chk['class'] = 'HeadSpace_Plugin';
							$chk['plugin'] = 'headspace2/headspace.php';
							break;
						case 'seo-seou':
							$chk['class'] = 'SEO_Ultimate';
							$chk['plugin'] = 'seo-ultimate/seo-ultimate.php';
							break;
						case 'seo-wpseo':
							$chk['function'] = 'wpseo_init';
							$chk['plugin'] = 'wordpress-seo/wp-seo.php';
							break;
						case 'social-buddypress':
							$chk['class'] = 'BuddyPress';
							$chk['plugin'] = 'buddypress/bp-loader.php';
							break;
						/*
						 * Pro Version Features / Options
						 */
						case 'head-twittercard':
							$chk['optval'] = 'tc_enable';
							break;
						case 'media-gravatar':
							$chk['optval'] = 'plugin_gravatar_api';
							break;
						case 'media-slideshare':
							$chk['optval'] = 'plugin_slideshare_api';
							break;
						case 'media-vimeo':
							$chk['optval'] = 'plugin_vimeo_api';
							break;
						case 'media-wistia':
							$chk['optval'] = 'plugin_wistia_api';
							break;
						case 'media-youtube':
							$chk['optval'] = 'plugin_youtube_api';
							break;
						case 'admin-apikeys':
						case 'admin-sharing':
						case 'admin-style':
							if ( $ret['ssb'] === true )
								$ret[$sub]['*'] = $ret[$sub][$id] = true;
							break;
						case 'admin-general':
						case 'admin-advanced':
						case 'admin-image-dimensions':
						case 'admin-post':
						case 'admin-taxonomy':
						case 'admin-user':
						case 'util-post':
						case 'util-taxonomy':
						case 'util-user':
							$ret[$sub]['*'] = $ret[$sub][$id] = true;
							break;
						case 'util-language':
							$chk['optval'] = 'plugin_filter_lang';
							break;
						case 'util-restapi':
							$chk['plugin'] = 'rest-api/plugin.php';
							break;
						case 'util-shorten':
							$chk['optval'] = 'plugin_shortener';
							break;
						case 'util-um':
							$chk['class'] = 'NgfbUm';
							$chk['plugin'] = 'nextgen-facebook-um/nextgen-facebook-um.php';
							break;
					}
					if ( ! empty( $chk ) ) {
						if ( isset( $chk['plugin'] ) || isset( $chk['class'] ) || isset( $chk['function'] ) ) {
							if ( ( ! empty( $chk['plugin'] ) && isset( $this->active_plugins[$chk['plugin']] ) ) ||
								( ! empty( $chk['class'] ) && class_exists( $chk['class'] ) ) ||
								( ! empty( $chk['function'] ) && function_exists( $chk['function'] ) ) ) {

								// check if an option value is also required
								if ( isset( $chk['optval'] ) ) {
									if ( $this->has_optval( $chk['optval'] ) )
										$ret[$sub]['*'] = $ret[$sub][$id] = true;
								} else $ret[$sub]['*'] = $ret[$sub][$id] = true;
							}
						} if ( isset( $chk['optval'] ) ) {
							if ( $this->has_optval( $chk['optval'] ) )
								$ret[$sub]['*'] = $ret[$sub][$id] = true;
						}
					}
				}
			}
			return apply_filters( $this->p->cf['lca'].'_get_avail', $ret );
		}

		private function has_optval( $opt_name ) { 
			if ( ! empty( $opt_name ) && 
				! empty( $this->p->options[$opt_name] ) && 
					$this->p->options[$opt_name] !== 'none' )
						return true;
		}

		public function is_aop( $lca = '' ) { 
			return $this->aop( $lca, true, $this->get_avail_check( 'aop' ) );
		}

		public function aop( $lca = '', $li = true, $rv = true ) {
			$lca = empty( $lca ) ? 
				$this->p->cf['lca'] : $lca;
			$kn = $lca.'-'.$li.'-'.$rv;
			if ( isset( self::$c[$kn] ) )
				return self::$c[$kn];
			$on = 'plugin_'.$lca.'_tid';
			$uca = strtoupper( $lca );
			$ins = ( defined( $uca.'_PLUGINDIR' ) &&
				is_dir( constant( $uca.'_PLUGINDIR' ).'lib/pro/' ) ) ? $rv : false;
			return self::$c[$kn] = $li === true ? 
				( ( ! empty( $this->p->options[$on] ) && 
					$ins && class_exists( 'SucomUpdate' ) &&
						( $um = SucomUpdate::get_umsg( $lca ) ? 
							false : $ins ) ) ? $um : false ) : $ins;
		}

		public function conflict_warnings() {
			if ( ! is_admin() ) 
				return;

			$lca = $this->p->cf['lca'];
			$base = $this->p->cf['plugin'][$lca]['base'];
			$short = $this->p->cf['plugin'][$lca]['short'];
			$short_pro = $short.' Pro';
			$purchase_url = $this->p->cf['plugin'][$lca]['url']['purchase'];
			$log_pre =  __( 'plugin conflict detected', NGFB_TEXTDOM ) . ' - ';
			$err_pre =  __( 'Plugin conflict detected', NGFB_TEXTDOM ) . ' - ';
			$user_id = get_current_user_id();

			// PHP
			if ( empty( $this->p->is_avail['curl'] ) ) {
				if ( ! empty( $this->p->options['plugin_shortener'] ) && 
					$this->p->options['plugin_shortener'] !== 'none' ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'url shortening is enabled but curl function is missing' );
					$this->p->notice->err( sprintf( __( 'URL shortening has been enabled, but PHP\'s <a href="%s" target="_blank">Client URL Library</a> (cURL) is missing.', NGFB_TEXTDOM ), 'http://ca3.php.net/curl' ).' '.__( 'Please contact your hosting provider to have the missing library installed.', NGFB_TEXTDOM ) );
				} elseif ( ! empty( $this->p->options['plugin_file_cache_exp'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'file caching is enabled but curl function is missing' );
					$this->p->notice->err( sprintf( __( 'The file caching feature has been enabled but PHP\'s <a href="%s" target="_blank">Client URL Library</a> (cURL) is missing.', NGFB_TEXTDOM ), 'http://ca3.php.net/curl' ).' '.__( 'Please contact your hosting provider to have the missing library installed.', NGFB_TEXTDOM ) );
				}
			}

			// Yoast SEO 
			if ( $this->p->is_avail['seo']['wpseo'] === true ) {
				$opts = get_option( 'wpseo_social' );
				if ( ! empty( $opts['opengraph'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo opengraph meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Open Graph meta data</em>\' Facebook option in the <a href="%s">Yoast SEO: Social</a> settings.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social#top#facebook' ) ) );
				}
				if ( ! empty( $this->p->options['tc_enable'] ) && 
					! empty( $opts['twitter'] ) &&
					$this->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo twitter meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Twitter card meta data</em>\' Twitter option in the <a href="%s">Yoast SEO: Social</a> settings.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social#top#twitterbox' ) ) );
				}
				if ( ! empty( $opts['googleplus'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo googleplus meta data option is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please uncheck the \'<em>Add Google+ specific post meta data</em>\' Google+ option in the <a href="%s">Yoast SEO: Social</a> settings.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social#top#google' ) ) );
				}
				if ( ! empty( $opts['plus-publisher'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'wpseo google plus publisher option is defined' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please remove the \'<em>Google Publisher Page</em>\' value entered in the <a href="%s">Yoast SEO: Social</a> settings.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=wpseo_social#top#google' ) ) );
				}

				// disable incorrect error from Yoast SEO notifications
				$dismissed = get_user_option( 'wpseo_dismissed_conflicts', $user_id );
				if ( ! is_array( $dismissed['open_graph'] ) ||
					! in_array( $base, $dismissed['open_graph'] ) ) {
					$dismissed['open_graph'][] = $base;
					update_user_option( $user_id, 'wpseo_dismissed_conflicts', $dismissed );
				}
			}

			// SEO Ultimate
			if ( $this->p->is_avail['seo']['seou'] === true ) {
				$opts = get_option( 'seo_ultimate' );
				if ( ! empty( $opts['modules'] ) && is_array( $opts['modules'] ) ) {
					if ( array_key_exists( 'opengraph', $opts['modules'] ) && $opts['modules']['opengraph'] !== -10 ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $log_pre.'seo ultimate opengraph module is enabled' );
						$this->p->notice->err( $err_pre.sprintf( __( 'Please disable the \'<em>Open Graph Integrator</em>\' module in the <a href="%s">SEO Ultimate plugin Module Manager</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=seo' ) ) );
					}
				}
			}

			// All in One SEO Pack
			if ( $this->p->is_avail['seo']['aioseop'] === true ) {
				$opts = get_option( 'aioseop_options' );
				if ( ! empty( $opts['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_opengraph'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'aioseop social meta fetaure is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please deactivate the \'<em>Social Meta</em>\' feature in the <a href="%s">All in One SEO Pack Feature Manager</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=all-in-one-seo-pack/aioseop_feature_manager.php' ) ) );
				}
				if ( array_key_exists( 'aiosp_google_disable_profile', $opts ) && empty( $opts['aiosp_google_disable_profile'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $log_pre.'aioseop google plus profile is enabled' );
					$this->p->notice->err( $err_pre.sprintf( __( 'Please check the \'<em>Disable Google Plus Profile</em>\' option in the <a href="%s">All in One SEO Pack Plugin Options</a>.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=all-in-one-seo-pack/aioseop_class.php' ) ) );
				}
			}

			// JetPack Photon
			if ( $this->p->is_avail['media']['photon'] === true && 
				! $this->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $log_pre.'jetpack photon is enabled' );
				$this->p->notice->err( $err_pre.__( '<strong>JetPack\'s Photon module cripples the WordPress image size functions on purpose</strong>.', NGFB_TEXTDOM ).' '.sprintf( __( 'Please <a href="%s">deactivate the JetPack Photon module</a> or deactivate the %s Free plugin.', NGFB_TEXTDOM ), get_admin_url( null, 'admin.php?page=jetpack' ), $short ).' '.sprintf( __( 'You may also upgrade to the <a href="%s">%s version</a> which includes an <a href="%s">integration module for JetPack Photon</a> to re-enable image size functions specifically for %s images.', NGFB_TEXTDOM ), $purchase_url, $short_pro, 'http://surniaulula.com/codex/plugins/nextgen-facebook/notes/modules/jetpack-photon/', $short ) );
			}

			/*
			 * Other Conflicting Plugins
			 */

			// WooCommerce
			if ( class_exists( 'Woocommerce' ) && 
				! empty( $this->p->options['plugin_filter_content'] ) &&
				! $this->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $log_pre.'woocommerce shortcode support not available in the admin interface' );
				$this->p->notice->err( $err_pre.__( '<strong>WooCommerce does not include shortcode support in the admin interface</strong> (required by WordPress for its content filters).', NGFB_TEXTDOM ).' '.sprintf( __( 'Please uncheck the \'<em>Apply WordPress Content Filters</em>\' option on the <a href="%s">%s Advanced settings page</a>.', NGFB_TEXTDOM ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content' ), $this->p->cf['menu'] ).' '.sprintf( __( 'You may also upgrade to the <a href="%s">%s version</a> that includes an <a href="%s">integration module specifically for WooCommerce</a> (shortcodes, products, categories, tags, images, etc.).', NGFB_TEXTDOM ), $purchase_url, $short_pro, 'http://surniaulula.com/codex/plugins/nextgen-facebook/notes/modules/woocommerce/' ) );
			}
		}
	}
}

?>
