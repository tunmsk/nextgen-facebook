<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuWebsiteReddit' ) ) {

	class NgfbSubmenuWebsiteReddit {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'website_reddit_rows' => 3,		// $table_rows, $form, $submenu
			) );
		}

		public function filter_website_reddit_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'reddit_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'reddit' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'reddit_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Type',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'reddit_type', 
				array( 
					'static-wide' => 'Interactive Wide',
					'static-tall-text' => 'Interactive Tall Text',
					'static-tall-logo' => 'Interactive Tall Logo',
				)
			).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'NgfbWebsiteReddit' ) ) {

	class NgfbWebsiteReddit {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'reddit_order' => 9,
					'reddit_on_content' => 0,
					'reddit_on_excerpt' => 0,
					'reddit_on_sidebar' => 0,
					'reddit_on_admin_edit' => 1,
					'reddit_platform' => 'any',
					'reddit_type' => 'static-wide',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;      // get_sharing_url() argument

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $atts['use_post'], $atts['add_page'] ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], $atts['use_post'], $atts['add_page'] );

			if ( empty( $atts['title'] ) ) 
				$atts['title'] = $this->p->webpage->get_title( null, null, $mod, true, false, true, null );

			switch ( $opts['reddit_type'] ) {
				case 'static-tall-text':
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button2.js';
					break;
				case 'static-tall-logo':
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button3.js';
					break;
				case 'static-wide':
				default:	// just in case
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button1.js';
					break;
			}
			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_reddit', $js_url, '' ) );

			$html = '<!-- Reddit Button -->'.
			'<script type="text/javascript">reddit_url=\''.$atts['url'].'\'; reddit_title=\''.$atts['title'].'\';</script>'.
			'<div '.NgfbSharing::get_css_class_id( 'reddit', $atts ).'>'.
			'<script type="text/javascript" src="'.$js_url.'"></script></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}
	}
}

?>