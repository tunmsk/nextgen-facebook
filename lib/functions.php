<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'ngfb_get_social_buttons' ) ) {
	function ngfb_get_social_buttons( $ids = array(), $atts = array() ) {
		return ngfb_get_sharing_buttons( $ids, $atts );
	}
}

if ( ! function_exists( 'ngfb_get_sharing_buttons' ) ) {
	function ngfb_get_sharing_buttons( $ids = array(), $atts = array(), $expire = 86400 ) {

		$ngfb =& Ngfb::get_instance();
		if ( $ngfb->debug->enabled )
			$ngfb->debug->mark();
		$html = false;

		if ( ! is_array( $ids ) ) {
			error_log( __FUNCTION__.'() error: sharing button ids must be an array' );
			if ( $ngfb->debug->enabled )
				$ngfb->debug->log( 'sharing button ids must be an array' );
		} elseif ( ! is_array( $atts ) ) {
			error_log( __FUNCTION__.'() error: sharing button attributes must be an array' );
			if ( $ngfb->debug->enabled )
				$ngfb->debug->log( 'sharing button attributes must be an array' );
		} elseif ( ! $ngfb->is_avail['ssb'] ) {
			$html = '<!-- '.$ngfb->cf['lca'].' sharing buttons are disabled -->';
			if ( $ngfb->debug->enabled )
				$ngfb->debug->log( 'sharing buttons are disabled' );
		} else {
			$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 
			$cache_salt = __FUNCTION__.'(locale:'.SucomUtil::get_locale().
				'_url:'.$ngfb->util->get_sharing_url( $atts['use_post'] ).
				'_ids:'.( implode( '_', $ids ) ).
				'_atts:'.( implode( '_', $atts ) ).')';
			$cache_id = $ngfb->cf['lca'].'_'.md5( $cache_salt );
			$cache_type = 'object cache';

			// clear the cache if $expire = 0
			if ( empty( $expire ) ) {
				if ( $ngfb->is_avail['cache']['transient'] )
					delete_transient( $cache_id );
				elseif ( $ngfb->is_avail['cache']['object'] )
					wp_cache_delete( $cache_id, __FUNCTION__ );
				return $ngfb->debug->get_html().$html;
			} elseif ( ! isset( $atts['read_cache'] ) || $atts['read_cache'] ) {
				if ( $ngfb->is_avail['cache']['transient'] ) {
					if ( $ngfb->debug->enabled )
						$ngfb->debug->log( $cache_type.': transient salt '.$cache_salt );
					$html = get_transient( $cache_id );
				} elseif ( $ngfb->is_avail['cache']['object'] ) {
					if ( $ngfb->debug->enabled )
						$ngfb->debug->log( $cache_type.': wp_cache salt '.$cache_salt );
					$html = wp_cache_get( $cache_id, __FUNCTION__ );
				} else $html = false;
			} else $html = false;

			if ( $html !== false ) {
				if ( $ngfb->debug->enabled )
					$ngfb->debug->log( $cache_type.': html retrieved from cache '.$cache_id );
				return $ngfb->debug->get_html().$html;
			}

			$html = '<!-- '.$ngfb->cf['lca'].' '.__FUNCTION__.' function begin -->'."\n".
				$ngfb->sharing->get_script( 'sharing-buttons-header', $ids ).
				$ngfb->sharing->get_html( $ids, $atts ).
				$ngfb->sharing->get_script( 'sharing-buttons-footer', $ids ).
				'<!-- '.$ngfb->cf['lca'].' '.__FUNCTION__.' function end -->';

			if ( $ngfb->is_avail['cache']['transient'] ||
				$ngfb->is_avail['cache']['object'] ) {

				if ( $ngfb->is_avail['cache']['transient'] )
					set_transient( $cache_id, $html, $expire );
				elseif ( $ngfb->is_avail['cache']['object'] )
					wp_cache_set( $cache_id, $html, __FUNCTION__, $expire );

				if ( $ngfb->debug->enabled )
					$ngfb->debug->log( $cache_type.': html saved to cache '.
						$cache_id.' ('.$expire.' seconds)');
			}
		}
		return $ngfb->debug->get_html().$html;
	}
}

if ( ! function_exists( 'ngfb_get_sharing_url' ) ) {
	function ngfb_get_sharing_url( $use_post = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		return $ngfb->util->get_sharing_url( $use_post, $add_page );
	}
}

if ( ! function_exists( 'ngfb_get_short_url' ) ) {
	function ngfb_get_short_url( $use_post = false, $add_page = true ) {
		$ngfb =& Ngfb::get_instance();
		return apply_filters( 'ngfb_shorten_url', 
			$ngfb->util->get_sharing_url( $use_post, $add_page ),
			$ngfb->options['plugin_shortener'] );
	}
}

if ( ! function_exists( 'ngfb_schema_attributes' ) ) {
	function ngfb_schema_attributes( $attr = '' ) {
		$ngfb =& Ngfb::get_instance();
		echo $ngfb->schema->add_head_attributes( $attr );
	}
}

if ( ! function_exists( 'ngfb_clear_all_cache' ) ) {
	function ngfb_clear_all_cache( $clear_external = false ) {
		$ngfb =& Ngfb::get_instance();
		return $ngfb->util->clear_all_cache( $clear_external, __FUNCTION__, true );
	}
}

if ( ! function_exists( 'ngfb_clear_post_cache' ) ) {
	function ngfb_clear_post_cache( $post_id ) {
		$ngfb =& Ngfb::get_instance();
		if ( is_object( $ngfb->m['util']['post'] ) )
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

?>
