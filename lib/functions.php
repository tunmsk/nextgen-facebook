<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'ngfb_get_social_buttons' ) ) {
	function ngfb_get_social_buttons( $ids = array(), $atts = array() ) {
		return ngfb_get_sharing_buttons( $ids, $atts );
	}
}

if ( ! function_exists( 'ngfb_get_sharing_buttons' ) ) {
	function ngfb_get_sharing_buttons( $ids = array(), $atts = array(), $cache_exp = false ) {

		$ngfb =& Ngfb::get_instance();
		if ( $ngfb->debug->enabled )
			$ngfb->debug->mark();

		$error_msg = false;
		if ( ! is_array( $ids ) ) {
			$error_msg = 'sharing button ids must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! is_array( $atts ) ) {
			$error_msg = 'sharing button attributes must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! $ngfb->is_avail['ssb'] ) {
			$error_msg = 'sharing buttons are disabled';
		} elseif ( empty( $ids ) ) {	// nothing to do
			$error_msg = 'no buttons requested';
		}

		if ( $error_msg !== false ) {
			if ( $ngfb->debug->enabled )
				$ngfb->debug->log( 'exiting early: '.$error_msg );
			return '<!-- '.__FUNCTION__.' exiting early: '.$error_msg.' -->'."\n".
				( $ngfb->debug->enabled ? $ngfb->debug->get_html() : '' );
		}

		$lca = $ngfb->cf['lca'];
		$type = __FUNCTION__;
		$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 
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

		return $buttons_array[$buttons_index].
			( $ngfb->debug->enabled ? $ngfb->debug->get_html() : '' );
	}
}

if ( ! function_exists( 'ngfb_get_sharing_url' ) ) {
	function ngfb_get_sharing_url( $mod = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		return $ngfb->util->get_sharing_url( $mod, $add_page );
	}
}

if ( ! function_exists( 'ngfb_get_short_url' ) ) {
	function ngfb_get_short_url( $mod = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		return apply_filters( 'ngfb_shorten_url', 
			$ngfb->util->get_sharing_url( $mod, $add_page ),
			$ngfb->options['plugin_shortener'] );
	}
}

if ( ! function_exists( 'ngfb_schema_attributes' ) ) {
	function ngfb_schema_attributes( $attr = '' ) {
		$ngfb =& Ngfb::get_instance();
		echo $ngfb->schema->filter_head_attributes( $attr );
	}
}

if ( ! function_exists( 'ngfb_clear_all_cache' ) ) {
	function ngfb_clear_all_cache( $clear_external = false ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->util ) )	// just in case
			return $ngfb->util->clear_all_cache( $clear_external, __FUNCTION__, true );
	}
}

if ( ! function_exists( 'ngfb_clear_post_cache' ) ) {
	function ngfb_clear_post_cache( $post_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->m['util']['post'] ) )	// just in case
			$ngfb->m['util']['post']->clear_cache( $post_id );
	}
}

if ( ! function_exists( 'ngfb_is_mobile' ) ) {
	function ngfb_is_mobile() {
		if ( class_exists( 'SucomUtil' ) )	// just in case
			return SucomUtil::is_mobile();
		else return null;
	}
}

/*
 * Define NGFB_READ_WPSEO_META as true in your wp-config.php file to allow
 * reading of Yoast SEO post, term, and user meta - even when the Yoast SEO
 * plugin is not active.
 */
if ( defined( 'NGFB_READ_WPSEO_META' ) && NGFB_READ_WPSEO_META ) {

	add_filter( 'ngfb_get_post_options', 'filter_get_post_options_wpseo_meta', 10, 2 );
	add_filter( 'ngfb_get_term_options', 'filter_get_term_options_wpseo_meta', 10, 2 );
	add_filter( 'ngfb_get_user_options', 'filter_get_user_options_wpseo_meta', 10, 2 );

	if ( ! function_exists( 'filter_get_post_options_wpseo_meta' ) ) {
		function filter_get_post_options_wpseo_meta( $opts, $post_id ) {

			if ( empty( $opts['og_title'] ) )
				$opts['og_title'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_opengraph-title', true );

			if ( empty( $opts['og_title'] ) )	// fallback to the SEO title
				$opts['og_title'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_title', true );

			if ( empty( $opts['og_desc'] ) )
				$opts['og_desc'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_opengraph-description', true );

			if ( empty( $opts['og_desc'] ) )	// fallback to the SEO description
				$opts['og_desc'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_metadesc', true );

			if ( empty( $opts['og_img_id'] ) && empty( $opts['og_img_url'] ) )
				$opts['og_img_url'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_opengraph-image', true );

			if ( empty( $opts['tc_desc'] ) )
				$opts['tc_desc'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_twitter-description', true );

			if ( empty( $opts['schema_desc'] ) )
				$opts['schema_desc'] = (string) get_post_meta( $post_id,
					'_yoast_wpseo_metadesc', true );

			$opts['seo_desc'] = (string) get_post_meta( $post_id,
				'_yoast_wpseo_metadesc', true );

			return $opts;
		}
	}

	if ( ! function_exists( 'filter_get_term_options_wpseo_meta' ) ) {
		/*
		 * Yoast SEO does not support wordpress term meta (added in wp 4.4).
		 * Read term meta from the 'wpseo_taxonomy_meta' option instead.
		 */
		function filter_get_term_options_wpseo_meta( $opts, $term_id ) {

			$term_obj = get_term( $term_id );
			$tax_opts = get_option( 'wpseo_taxonomy_meta' );

			if ( ! isset( $term_obj->taxonomy ) || 
				! isset( $tax_opts[$term_obj->taxonomy][$term_id] ) )
					return $opts;

			$term_opts = $tax_opts[$term_obj->taxonomy][$term_id];

			if ( empty( $opts['og_title'] ) && 
				isset( $term_opts['wpseo_opengraph-title'] ) )
					$opts['og_title'] = (string) $term_opts['wpseo_opengraph-title'];

			if ( empty( $opts['og_title'] ) &&	// fallback to the SEO title
				isset( $term_opts['wpseo_title'] ) )
					$opts['og_title'] = (string) $term_opts['wpseo_title'];

			if ( empty( $opts['og_desc'] ) && 
				isset( $term_opts['wpseo_opengraph-description'] ) )
					$opts['og_desc'] = (string) $term_opts['wpseo_opengraph-description'];

			if ( empty( $opts['og_desc'] ) &&	// fallback to the SEO description
				isset( $term_opts['wpseo_desc'] ) )
					$opts['og_desc'] = (string) $term_opts['wpseo_desc'];

			if ( empty( $opts['og_img_id'] ) && empty( $opts['og_img_url'] ) &&
				isset( $term_opts['wpseo_opengraph-image'] ) )
					$opts['og_img_url'] = (string) $term_opts['wpseo_opengraph-image'];

			if ( empty( $opts['tc_desc'] ) &&
				isset( $term_opts['wpseo_twitter-description'] ) )
					$opts['tc_desc'] = (string) $term_opts['wpseo_twitter-description'];

			if ( empty( $opts['schema_desc'] ) && 
				isset( $term_opts['wpseo_desc'] ) )
					$opts['tc_desc'] = (string) $term_opts['wpseo_desc'];

			if ( isset( $term_opts['wpseo_desc'] ) )
				$opts['seo_desc'] = (string) $term_opts['wpseo_desc'];

			return $opts;
		}
	}

	if ( ! function_exists( 'filter_get_user_options_wpseo_meta' ) ) {
		/*
		 * Yoast SEO does not provide social settings for users.
		 */
		function filter_get_user_options_wpseo_meta( $opts, $user_id ) {

			if ( empty( $opts['og_title'] ) )
				$opts['og_title'] = (string) get_user_meta( $user_id,
					'wpseo_title', true );

			if ( empty( $opts['og_desc'] ) )
				$opts['og_desc'] = (string) get_user_meta( $user_id,
					'wpseo_metadesc', true );

			return $opts;
		}
	}
}

?>
