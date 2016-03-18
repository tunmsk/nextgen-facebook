<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminStyle' ) ) {

	class NgfbGplAdminStyle {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'style_sharing_rows' => 2,	// $table_rows, $form
				'style_content_rows' => 2,	// $table_rows, $form
				'style_excerpt_rows' => 2,	// $table_rows, $form
				'style_sidebar_rows' => 2,	// $table_rows, $form
				'style_shortcode_rows' => 2,	// $table_rows, $form
				'style_widget_rows' => 2,	// $table_rows, $form
				'style_admin_edit_rows' => 2,	// $table_rows, $form
			) );
		}

		public function filter_style_sharing_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'sharing' );
		}

		public function filter_style_content_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'content' );
		}

		public function filter_style_excerpt_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'excerpt' );
		}

		public function filter_style_sidebar_rows( $table_rows, $form ) {
			$table_rows = array_merge( $table_rows, 
				$this->filter_style_common_rows( $table_rows, $form, 'sidebar' ) );

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Sidebar Javascript',
				'option label', 'nextgen-facebook' ), null, 'buttons_js_sidebar' ).
			'<td><textarea disabled="disabled" class="average code">'.
			$this->p->options['buttons_js_sidebar'].'</textarea></td>';

			return $table_rows;
		}

		public function filter_style_shortcode_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'shortcode' );
		}

		public function filter_style_widget_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'widget' );
		}

		public function filter_style_admin_edit_rows( $table_rows, $form ) {
			return $this->filter_style_common_rows( $table_rows, $form, 'admin_edit' );
		}

		public function filter_style_common_rows( &$table_rows, &$form, $idx ) {

			$text = $this->p->msgs->get( 'info-style-'.$idx );

			if ( isset( $this->p->options['buttons_preset_'.$idx] ) ) {
				$text .= '<p>The social sharing button options for the "'.$idx.'" style are subject to preset values selected on the '.$this->p->util->get_admin_url( 'sharing#sucom-tabset_sharing-tab_preset', 'Sharing Buttons' ).' settings page (used to modify the default behavior, size, counter orientation, etc.). The width and height values in your CSS should support these preset classes (if any).</p>';
				$text .= '<p><strong>Selected preset:</strong> '.
					( empty( $this->p->options['buttons_preset_'.$idx] ) ? '[None]' :
						$this->p->options['buttons_preset_'.$idx] ).'</p>';
			}

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';
		
			$table_rows[] = '<th class="textinfo">'.$text.'</th>'.
			'<td><textarea disabled="disabled" class="tall code">'.
			$this->p->options['buttons_css_'.$idx].'</textarea></td>';

			return $table_rows;
		}
	}
}

?>
