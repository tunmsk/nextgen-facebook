<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuButtons' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSubmenuButtons extends NgfbAdmin {

		public $website = array();

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->set_objects();

			$this->p->util->add_plugin_actions( $this, array(
				'form_content_metaboxes_buttons' => 1,	// show two-column metaboxes
			) );
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['ngfb']['lib']['website'] as $id => $name ) {
				$classname = NgfbConfig::load_lib( false, 'website/'.$id, 'ngfbsubmenuwebsite'.$id );
				if ( $classname !== false && class_exists( $classname ) ) {
					$this->website[$id] = new $classname( $this->p );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $classname.' class loaded' );
				}
			}
		}

		// show two-column metaboxes for sharing buttons
		public function action_form_content_metaboxes_buttons( $pagehook ) {
			if ( isset( $this->website ) ) {
				foreach ( range( 1, ceil( count( $this->website ) / 2 ) ) as $row ) {
					echo '<div class="website-row">', "\n";
					foreach ( range( 1, 2 ) as $col ) {
						$pos_id = 'website-row-'.$row.'-col-'.$col;
						echo '<div class="website-col-'.$col.'" id="'.$pos_id.'" >';
						do_meta_boxes( $pagehook, $pos_id, null ); 
						echo '</div>', "\n";
					}
					echo '</div>', "\n";
				}
				echo '<div style="clear:both;"></div>';
			}
		}

		protected function add_meta_boxes() {
			$col = 0;
			$row = 0;

			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_sharing_buttons',
				_x( 'Social Sharing Buttons', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_sharing_buttons' ),
						$this->pagehook, 'normal' );

			$website_ids = $this->p->sharing->get_website_object_ids( $this->website );

			foreach ( $website_ids as $id => $name ) {
				$col = $col == 1 ? 2 : 1;
				$row = $col == 1 ? $row + 1 : $row;
				$pos_id = 'website-row-'.$row.'-col-'.$col;
				$name = $name == 'GooglePlus' ?
					'Google+' : $name;
				$args = array( 'id' => $id, 'name' => $name );

				add_meta_box( $this->pagehook.'_'.$id, $name, 
					array( &$this, 'show_metabox_website' ),
						$this->pagehook, $pos_id, 'default', $args );

				add_filter( 'postbox_classes_'.$this->pagehook.'_'.$this->pagehook.'_'.$id, 
					array( &$this, 'add_class_postbox_website' ) );
			}

			// these metabox ids should be closed by default (array_diff() selects everything except those listed)
			$ids = array_diff( array_keys( $website_ids ), array() );
			$this->p->m['util']['user']->reset_metabox_prefs( $this->pagehook, $ids, 'closed' );
		}

		public function add_class_postbox_website( $classes ) {
			$show_opts = NgfbUser::show_opts();
			$classes[] = 'postbox-website';
			if ( ! empty( $show_opts ) )
				$classes[] = 'postbox-show_'.$show_opts;
			return $classes;
		}

		public function show_metabox_sharing_buttons() {
			$lca = $this->p->cf['lca'];
			$metabox = 'buttons';
			$tabs = apply_filters( $lca.'_sharing_buttons_tabs', array(
				'include' => _x( 'Include Buttons', 'metabox tab', 'nextgen-facebook' ),
				'position' => _x( 'Buttons Position', 'metabox tab', 'nextgen-facebook' ),
				'preset' => _x( 'Buttons Presets', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title )
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ), 
					apply_filters( $lca.'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		public function show_metabox_website( $post, $callback ) {
			$lca = $this->p->cf['lca'];
			$args = $callback['args'];
			$metabox = 'website';
			$tabs = apply_filters( $lca.'_'.$metabox.'_'.$args['id'].'_tabs', array() );

			if ( empty( $tabs ) ) {
				$this->p->util->do_table_rows( apply_filters( $lca.'_'.$metabox.'_'.$args['id'].'_rows',
					array(), $this->form, $this ), 'metabox-'.$metabox.'-'.$args['id'], 'metabox-'.$metabox );
			} else {
				foreach ( $tabs as $tab => $title )
					$table_rows[$tab] = apply_filters( $lca.'_'.$metabox.'_'.$args['id'].'_'.$tab.'_rows',
						array(), $this->form, $this );
				$this->p->util->do_metabox_tabs( $metabox.'_'.$args['id'], $tabs, $table_rows );
			}
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();
			switch ( $metabox.'-'.$key ) {

				case 'buttons-include':

					$table_rows[] = $this->form->get_th_html( _x( 'Include on Archive Webpages',
						'option label', 'nextgen-facebook' ), null, 'buttons_on_index' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_index' ).'</td>';

					$table_rows[] = $this->form->get_th_html( _x( 'Include on Static Homepage',
						'option label', 'nextgen-facebook' ), null, 'buttons_on_front' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_front' ).'</td>';

					break;

				case 'buttons-position':

					$table_rows[] = $this->form->get_th_html( _x( 'Position in Content Text',
						'option label', 'nextgen-facebook' ), null, 'buttons_pos_content' ).
					'<td>'.$this->form->get_select( 'buttons_pos_content',
						$this->p->cf['sharing']['position'] ).'</td>';

					$table_rows[] = $this->form->get_th_html( _x( 'Position in Excerpt Text',
						'option label', 'nextgen-facebook' ), null, 'buttons_pos_excerpt' ).
					'<td>'.$this->form->get_select( 'buttons_pos_excerpt', 
						$this->p->cf['sharing']['position'] ).'</td>';

					break;
			}
			return $table_rows;
		}

		public function show_on_checkboxes( $opt_prefix ) {
			$col = 0;
			$max = 2;
			$html = '<table>';
			$lca = $this->p->cf['lca'];
			$aop = $this->p->check->aop( 'ngfb', true, $this->p->is_avail['aop'] );
			$show_on = apply_filters( $lca.'_buttons_show_on', 
				$this->p->cf['sharing']['show_on'], $opt_prefix );

			foreach ( $show_on as $opt_suffix => $short_desc ) {
				$col++;
				$class = isset( $this->p->options[$opt_prefix.'_on_'.$opt_suffix.':is'] ) &&
					$this->p->options[$opt_prefix.'_on_'.$opt_suffix.':is'] === 'disabled' &&
						! $aop ? 'show_on blank' : 'show_on';
				if ( $col == 1 )
					$html .= '<tr><td class="'.$class.'">';
				else $html .= '<td class="'.$class.'">';
				$html .= $this->form->get_checkbox( $opt_prefix.'_on_'.$opt_suffix ).
					_x( $short_desc, 'option value', 'nextgen-facebook' ).'&nbsp; ';
				if ( $col == $max ) {
					$html .= '</td></tr>';
					$col = 0;
				} else $html .= '</td>';
			}
			$html .= $col < $max ? '</tr>' : '';
			$html .= '</table>';
			return $html;
		}
	}
}

?>
