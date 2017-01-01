<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbFilters' ) ) {

	class NgfbFilters {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( is_admin() ) {
				// cleanup incorrect Yoast SEO notifications
				if ( SucomUtil::active_plugins( 'wordpress-seo/wp-seo.php' ) )
					add_action( 'admin_init', array( $this, 'cleanup_wpseo_notifications' ), 15 );
			} else {
				// disable jetPack open graph meta tags
				if ( SucomUtil::active_plugins( 'jetpack/jetpack.php' ) ) {
					add_filter( 'jetpack_enable_opengraph', '__return_false', 1000 );
					add_filter( 'jetpack_enable_open_graph', '__return_false', 1000 );
					add_filter( 'jetpack_disable_twitter_cards', '__return_true', 1000 );
				}

				// disable Yoast SEO social meta tags
				// execute after add_action( 'template_redirect', 'wpseo_frontend_head_init', 999 );
				if ( SucomUtil::active_plugins( 'wordpress-seo/wp-seo.php' ) )
					add_action( 'template_redirect', array( $this, 'cleanup_wpseo_filters' ), 9000 );

				// honor the FORCE_SSL constant on the front-end
				if ( ! empty( $this->p->options['plugin_honor_force_ssl'] ) &&
					empty( $_SERVER['HTTPS'] ) && SucomUtil::get_const( 'FORCE_SSL' ) )
						add_action( 'template_redirect', array( __CLASS__, 'force_ssl_redirect' ), -1000 );
			}
		}

		/*
		 * Action hook to honor the FORCE_SSL constant.
		 */
		public static function force_ssl_redirect() {
			if ( empty( $_SERVER['HTTPS'] ) ) {	// just in case
				// 301 redirect is considered a best practice for upgrading from HTTP to HTTPS
				// see https://en.wikipedia.org/wiki/HTTP_301 for more info
				wp_redirect( 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], 301 );
				exit();
			}
		}

		/*
		 * Cleanup incorrect Yoast SEO notifications.
		 */
		public function cleanup_wpseo_notifications() {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( method_exists( 'Yoast_Notification_Center', 'add_notification' ) ) {	// since wpseo v3.3
				$lca = $this->p->cf['lca'];
				$info = $this->p->cf['plugin'][$lca];
				$id = 'wpseo-conflict-'.md5( $info['base'] );
				$msg = '<style>#'.$id.'{display:none;}</style>';
				$notif_center = Yoast_Notification_Center::get();

				if ( ( $notif_obj = $notif_center->get_notification_by_id( $id ) ) && $notif_obj->message !== $msg ) {
					update_user_meta( get_current_user_id(), $notif_obj->get_dismissal_key(), 'seen' );
					$notif_obj = new Yoast_Notification( $msg, array( 'id' => $id ) );
					$notif_center->add_notification( $notif_obj );
				}
			}
		}

		/*
		 * Disable Yoast SEO social meta tags.
		 */
		public function cleanup_wpseo_filters() {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( isset( $GLOBALS['wpseo_og'] ) && is_object( $GLOBALS['wpseo_og'] ) && 
				( $prio = has_action( 'wpseo_head', array( $GLOBALS['wpseo_og'], 'opengraph' ) ) ) !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'removing wpseo_head action for opengraph' );
				$ret = remove_action( 'wpseo_head', array( $GLOBALS['wpseo_og'], 'opengraph' ), $prio );
			}

			if ( class_exists( 'WPSEO_Twitter' ) &&
				( $prio = has_action( 'wpseo_head', array( 'WPSEO_Twitter', 'get_instance' ) ) ) !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'removing wpseo_head action for twitter' );
				$ret = remove_action( 'wpseo_head', array( 'WPSEO_Twitter', 'get_instance' ), $prio );
			}

			if ( ! empty( $this->p->options['seo_publisher_url'] ) && isset( WPSEO_Frontend::$instance ) &&
				 ( $prio = has_action( 'wpseo_head', array( WPSEO_Frontend::$instance, 'publisher' ) ) ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'removing wpseo_head action for publisher' );
				$ret = remove_action( 'wpseo_head', array( WPSEO_Frontend::$instance, 'publisher' ), $prio );
			}

			if ( ! empty( $this->p->options['schema_website_json'] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'disabling wpseo_json_ld_output filter' );
				add_filter( 'wpseo_json_ld_output', '__return_empty_array', 9000 );
			}
		}
	}
}

?>
