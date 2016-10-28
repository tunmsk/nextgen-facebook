<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuStyles' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSubmenuStyles extends NgfbAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_styles',
				_x( 'Social Sharing Styles', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_styles' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_styles() {

			if ( file_exists( NgfbSharing::$sharing_css_file ) &&
				( $fsize = filesize( NgfbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' <a href="'.NgfbSharing::$sharing_css_url.'">minimized css is '.$fsize.' bytes</a>';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->form->get_th_html( _x( 'Use the Social Stylesheet',
					'option label', 'nextgen-facebook' ), null, 'buttons_use_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',

				$this->form->get_th_html( _x( 'Enqueue the Stylesheet',
					'option label', 'nextgen-facebook' ), null, 'buttons_enqueue_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
			) );

			$metabox = 'styles';
			$tabs = apply_filters( $this->p->cf['lca'].'_sharing_styles_tabs', 
				$this->p->cf['sharing']['styles'] );
			$table_rows = array();
			foreach ( $tabs as $key => $title ) {
				$tabs[$key] = _x( $title, 'metabox tab', 'nextgen-facebook' );	// translate the tab title
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			}
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox, $key ) {
			return array();
		}
	}
}

?>
