<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! function_exists( 'ngfb_get_page_mod' ) ) {
	function ngfb_get_page_mod( $use_post = false ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->util ) ) {
			return $ngfb->util->get_page_mod( $use_post );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_get_post_mod' ) ) {
	function ngfb_get_post_mod( $post_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( isset( $ngfb->m['util']['post'] ) ) {
			$ngfb->m['util']['post']->get_mod( $post_id );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_get_term_mod' ) ) {
	function ngfb_get_term_mod( $term_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( isset( $ngfb->m['util']['term'] ) ) {
			$ngfb->m['util']['term']->get_mod( $term_id );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_get_user_mod' ) ) {
	function ngfb_get_user_mod( $user_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( isset( $ngfb->m['util']['user'] ) ) {
			$ngfb->m['util']['user']->get_mod( $user_id );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_get_sharing_url' ) ) {
	function ngfb_get_sharing_url( $mod = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->util ) ) {
			return $ngfb->util->get_sharing_url( $mod, $add_page );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_get_short_url' ) ) {
	function ngfb_get_short_url( $mod = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->util ) ) {
			$sharing_url = $ngfb->util->get_sharing_url( $mod, $add_page );
			return apply_filters( 'ngfb_shorten_url', $sharing_url, $ngfb->options['plugin_shortener'] );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ngfb_schema_attributes' ) ) {
	function ngfb_schema_attributes( $attr = '' ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->schema ) ) {
			echo $ngfb->schema->filter_head_attributes( $attr );
		}
	}
}

if ( ! function_exists( 'ngfb_clear_all_cache' ) ) {
	function ngfb_clear_all_cache( $clear_ext = false ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->util ) ) {
			$ngfb->util->clear_all_cache( $clear_ext, __FUNCTION__.'_function', true );
		}
	}
}

if ( ! function_exists( 'ngfb_clear_post_cache' ) ) {
	function ngfb_clear_post_cache( $post_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( isset( $ngfb->m['util']['post'] ) ) {
			$ngfb->m['util']['post']->clear_cache( $post_id );
		}
	}
}

if ( ! function_exists( 'ngfb_is_mobile' ) ) {
	function ngfb_is_mobile() {
		// return null if the content is not allowed to vary
		// make sure the class exists in case we're called before the library is loaded 
		if ( ! SucomUtil::get_const( 'NGFB_VARY_USER_AGENT_DISABLE' ) && class_exists( 'SucomUtil' ) ) {
			return SucomUtil::is_mobile();
		} else {
			return null;
		}
	}
}

/*
 * Sharing Buttons
 */
if ( ! function_exists( 'ngfb_get_sharing_buttons' ) ) {
	function ngfb_get_sharing_buttons( $ids = array(), $atts = array(), $cache_exp = false ) {

		$ngfb =& Ngfb::get_instance();

		if ( $ngfb->debug->enabled ) {
			$ngfb->debug->mark();
		}

		$error_msg = false;

		if ( ! is_array( $ids ) ) {
			$error_msg = 'sharing button ids must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! is_array( $atts ) ) {
			$error_msg = 'sharing button attributes must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! $ngfb->avail['p_ext']['ssb'] ) {
			$error_msg = 'sharing buttons are disabled';
		} elseif ( empty( $ids ) ) {	// nothing to do
			$error_msg = 'no buttons requested';
		}

		if ( $error_msg !== false ) {
			if ( $ngfb->debug->enabled )
				$ngfb->debug->log( 'exiting early: '.$error_msg );
			return '<!-- '.__FUNCTION__.' exiting early: '.$error_msg.' -->'."\n";
		}

		$lca = $ngfb->cf['lca'];
		$type = __FUNCTION__;
		$atts['use_post'] = SucomUtil::sanitize_use_post( $atts );
		if ( $ngfb->debug->enabled ) {
			$ngfb->debug->log( 'required call to get_page_mod()' );
		}
		$mod = $ngfb->util->get_page_mod( $atts['use_post'] );
		$sharing_url = $ngfb->util->get_sharing_url( $mod );
		$buttons_array = array();
		$buttons_index = $ngfb->sharing->get_buttons_cache_index( $type, $atts, $ids );
		$cache_salt = __FUNCTION__.'('.SucomUtil::get_mod_salt( $mod, $sharing_url ).')';
		$cache_id = $lca.'_'.md5( $cache_salt );
		$cache_exp = (int) apply_filters( $lca.'_cache_expire_sharing_buttons',
			( $cache_exp === false ? $ngfb->options['plugin_sharing_buttons_cache_exp'] : $cache_exp ) );

		if ( $ngfb->debug->enabled ) {
			$ngfb->debug->log( 'sharing url = '.$sharing_url );
			$ngfb->debug->log( 'buttons index = '.$buttons_index );
			$ngfb->debug->log( 'transient expire = '.$cache_exp );
			$ngfb->debug->log( 'transient salt = '.$cache_salt );
		}

		if ( $cache_exp > 0 ) {
			$buttons_array = get_transient( $cache_id );
			if ( isset( $buttons_array[$buttons_index] ) ) {
				if ( $ngfb->debug->enabled )
					$ngfb->debug->log( $type.' buttons index found in array from transient '.$cache_id );
			} elseif ( $ngfb->debug->enabled )
				$ngfb->debug->log( $type.' buttons index not in array from transient '.$cache_id );
		} elseif ( $ngfb->debug->enabled )
			$ngfb->debug->log( $type.' buttons array transient is disabled' );

		if ( ! isset( $buttons_array[$buttons_index] ) ) {

			// returns html or an empty string
			$buttons_array[$buttons_index] = $ngfb->sharing->get_html( $ids, $atts, $mod );

			if ( ! empty( $buttons_array[$buttons_index] ) ) {
				$buttons_array[$buttons_index] = '
<!-- '.$lca.' '.__FUNCTION__.' function begin -->
<!-- generated on '.date( 'c' ).' -->'."\n".
$ngfb->sharing->get_script( 'sharing-buttons-header', $ids ).
$buttons_array[$buttons_index]."\n".	// buttons html is trimmed, so add newline
$ngfb->sharing->get_script( 'sharing-buttons-footer', $ids ).
'<!-- '.$lca.' '.__FUNCTION__.' function end -->'."\n\n";

				if ( $cache_exp > 0 ) {
					// update the transient array and keep the original expiration time
					$cache_exp = SucomUtil::update_transient_array( $cache_id, $buttons_array, $cache_exp );
					if ( $ngfb->debug->enabled )
						$ngfb->debug->log( $type.' buttons html saved to transient '.
							$cache_id.' ('.$cache_exp.' seconds)' );
				}
			}
		}

		return $buttons_array[$buttons_index];
	}
}

// deprecated
if ( ! function_exists( 'ngfb_get_social_buttons' ) ) {
	function ngfb_get_social_buttons( $ids = array(), $atts = array() ) {
		return ngfb_get_sharing_buttons( $ids, $atts );
	}
}

?>
