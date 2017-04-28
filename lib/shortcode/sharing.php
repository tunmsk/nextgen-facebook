<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbShortcodeSharing' ) ) {

	class NgfbShortcodeSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! is_admin() ) {
				if ( $this->p->avail['p_ext']['ssb'] ) {
					$this->wpautop();
					$this->add();
				}
			}
		}

		public function wpautop() {
			// make sure wpautop() does not have a higher priority than 10, otherwise it will
			// format the shortcode output (shortcode filters are run at priority 11).
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				$default_priority = 10;
				foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {
					$filter_priority = has_filter( $filter_name, 'wpautop' );
					if ( $filter_priority !== false && $filter_priority > $default_priority ) {
						remove_filter( $filter_name, 'wpautop' );
						add_filter( $filter_name, 'wpautop' , $default_priority );
						$this->p->debug->log( 'wpautop() priority changed from '.$filter_priority.' to '.$default_priority );
					}
				}
			}
		}

		public function add() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
        			add_shortcode( NGFB_SHARING_SHORTCODE_NAME, array( &$this, 'shortcode' ) );
				$this->p->debug->log( '['.NGFB_SHARING_SHORTCODE_NAME.'] sharing shortcode added' );
			}
		}

		public function remove() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				remove_shortcode( NGFB_SHARING_SHORTCODE_NAME );
				$this->p->debug->log( '['.NGFB_SHARING_SHORTCODE_NAME.'] sharing shortcode removed' );
			}
		}

		public function shortcode( $atts, $content = null ) {

			if ( SucomUtil::is_amp() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				return $content;
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				return $content;
			}

			$lca = $this->p->cf['lca'];
			$atts = (array) apply_filters( $lca.'_sharing_shortcode_atts', $atts, $content );

			if ( empty( $atts['buttons'] ) ) {	// nothing to do
				return '<!-- '.$lca.' sharing shortcode: no buttons defined -->'."\n\n";
			}

			$atts['use_post'] = SucomUtil::sanitize_use_post( $atts, true );	// $default = true
			$atts['css_class'] = empty( $atts['css_class'] ) ? '' : $atts['css_class'];
			$atts['filter_id'] = empty( $atts['filter_id'] ) ? 'shortcode' : $atts['filter_id'];
			$atts['preset_id'] = empty( $atts['preset_id'] ) ? $this->p->options['buttons_preset_shortcode'] : $atts['preset_id'];

			$type = 'sharing_shortcode_'.NGFB_SHARING_SHORTCODE_NAME;
			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'required call to get_page_mod()' );
			}
			$mod = $this->p->util->get_page_mod( $atts['use_post'] );
			$atts['url'] = empty( $atts['url'] ) ? $this->p->util->get_sharing_url( $mod ) : $atts['url'];
			$buttons_array = array();
			$buttons_index = $this->p->sharing->get_buttons_cache_index( $type, $atts );
			$cache_salt = __METHOD__.'('.SucomUtil::get_mod_salt( $mod, $atts['url'] ).')';
			$cache_id = $lca.'_'.md5( $cache_salt );
			$cache_exp = (int) apply_filters( $lca.'_cache_expire_sharing_buttons',
				$this->p->options['plugin_sharing_buttons_cache_exp'] );

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = '.$atts['url'] );
				$this->p->debug->log( 'buttons index = '.$buttons_index );
				$this->p->debug->log( 'transient expire = '.$cache_exp );
				$this->p->debug->log( 'transient salt = '.$cache_salt );
			}

			if ( $cache_exp > 0 ) {
				$buttons_array = get_transient( $cache_id );
				if ( isset( $buttons_array[$buttons_index] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' buttons index found in array from transient '.$cache_id );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' buttons index not in array from transient '.$cache_id );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( $type.' buttons array transient is disabled' );

			if ( ! isset( $buttons_array[$buttons_index] ) ) {

				$ids = array_map( 'trim', explode( ',', $atts['buttons'] ) );
				unset ( $atts['buttons'] );

				// returns html or an empty string
				$buttons_array[$buttons_index] = $this->p->sharing->get_html( $ids, $atts, $mod );

				if ( ! empty( $buttons_array[$buttons_index] ) ) {
					$buttons_array[$buttons_index] = '
<!-- '.$lca.' '.$type.' begin -->
<!-- generated on '.date( 'c' ).' -->'."\n".
$this->p->sharing->get_script( 'shortcode-header', $ids ).
'<div class="'.$lca.'-shortcode-buttons">'."\n".
$buttons_array[$buttons_index]."\n".	// buttons html is trimmed, so add newline
'</div><!-- .'.$lca.'-shortcode-buttons -->'."\n".
$this->p->sharing->get_script( 'shortcode-footer', $ids ).
'<!-- '.$lca.' '.$type.' end -->'."\n\n";
	
					if ( $cache_exp > 0 ) {
						// update the transient array and keep the original expiration time
						$cache_exp = SucomUtil::update_transient_array( $cache_id, $buttons_array, $cache_exp );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $type.' buttons html saved to transient '.
								$cache_id.' ('.$cache_exp.' seconds)' );
					}
				}
			}

			return $buttons_array[$buttons_index];
		}
	}
}

?>
