<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbGplEcomWoocommerce' ) ) {

	class NgfbGplEcomWoocommerce {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! empty( $this->p->avail['p_ext']['ssb'] ) ) {
				$classname = __CLASS__.'Sharing';
				if ( class_exists( $classname ) ) {
					$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'NgfbGplEcomWoocommerceSharing' ) ) {

	class NgfbGplEcomWoocommerceSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );

			if ( is_admin() && empty( $this->p->options['plugin_hide_pro'] ) ) {
				$this->p->util->add_plugin_filters( $this, array(
					'buttons_show_on' => 2,
					'sharing_styles_tabs' => 1,
					'styles_woo_short_rows' => 2,
					'buttons_position_rows' => 2,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			$opts_def['buttons_css_woo_short'] = '/* Save an empty style text box to reload the default example styles.
 * These styles are provided as examples only - modifications may be
 * necessary to customize the layout for your website. Social sharing
 * buttons can be aligned vertically, horizontally, floated, etc.
 */

.ngfb-woo_short-buttons {
	display:block;
	margin:10px auto;
	text-align:center;
}';
			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre.'_on_woo_short'] = 0;
			}
			$opts_def['buttons_pos_woo_short'] = 'bottom';
			$opts_def['buttons_preset_woo_short'] = '';

			return $opts_def;
		}

		public function filter_buttons_show_on( $show_on = array(), $opt_pre ) {
			$show_on['woo_short'] = 'Woo Short';
			$this->p->options[$opt_pre.'_on_woo_short:is'] = 'disabled';
			return $show_on;
		}

		public function filter_sharing_styles_tabs( $tabs ) {
			$tabs['woo_short'] = 'Woo Short';
			$this->p->options['buttons_css_woo_short:is'] = 'disabled';
			return $tabs;
		}

		public function filter_styles_woo_short_rows( $table_rows, $form ) {
			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows['buttons_css_woo_short'] = '<th class="textinfo">
			<p>Social sharing buttons added to the WooCommerce Short Description are assigned the \'ngfb-woo_short-buttons\' class, which itself contains the \'ngfb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>
			<p>Example:</p><pre>
.ngfb-woo_short-buttons
    .ngfb-buttons
        .facebook-button { }</pre>
			<p>The Woo Short social sharing buttons are subject to preset values selected on the '.$this->p->util->get_admin_url( 'sharing#sucom-tabset_sharing-tab_preset', 'Sharing Buttons' ).' settings page.</p>
			<p><strong>Selected preset:</strong> '.
			( empty( $this->p->options['buttons_preset_woo_short'] ) ?
				'[None]' : $this->p->options['buttons_preset_woo_short'] ).
			'</p></th><td><textarea disabled="disabled" class="tall code">'.
				$this->p->options['buttons_css_woo_short'].'</textarea></td>';

			return $table_rows;
		}

		public function filter_buttons_position_rows( $table_rows, $form ) {
			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows['buttons_pos_woo_short'] = $form->get_th_html( _x( 'Position in Woo Short Text',
				'option label', 'nextgen-facebook' ), null, 'buttons_pos_woo_short' ).
			'<td class="blank">'.$this->p->cf['sharing']['position'][$this->p->options['buttons_pos_woo_short']].'</td>';

			return $table_rows;
		}
	}
}

?>
