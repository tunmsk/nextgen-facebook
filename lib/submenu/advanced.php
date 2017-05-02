<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbSubmenuAdvanced' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSubmenuAdvanced extends NgfbAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;	// lowercase acronyn for plugin or extension
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_plugin',
				_x( 'Advanced Settings', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_plugin' ), $this->pagehook, 'normal' );

			add_meta_box( $this->pagehook.'_contact_fields',
				_x( 'Contact Field Names and Labels', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_contact_fields' ), $this->pagehook, 'normal' );

			add_meta_box( $this->pagehook.'_taglist',
				_x( 'Head Tags List', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_taglist' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_plugin() {
			$metabox = 'plugin';
			$tabs = apply_filters( $this->p->cf['lca'].'_advanced_'.$metabox.'_tabs', array(
				'settings' => _x( 'Plugin Settings', 'metabox tab', 'nextgen-facebook' ),
				'content' => _x( 'Content and Filters', 'metabox tab', 'nextgen-facebook' ),
				'integration' => _x( 'Integration', 'metabox tab', 'nextgen-facebook' ),
				'social' => _x( 'Custom Meta', 'metabox tab', 'nextgen-facebook' ),
				'cache' => _x( 'Cache Settings', 'metabox tab', 'nextgen-facebook' ),
				'apikeys' => _x( 'Service APIs', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title ) {
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ),
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
						array(), $this->form, false ) );	// $network = false
			}
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		public function show_metabox_contact_fields() {
			$metabox = 'cm';
			$tabs = apply_filters( $this->p->cf['lca'].'_advanced_'.$metabox.'_tabs', array(
				'custom' => _x( 'Custom Contacts', 'metabox tab', 'nextgen-facebook' ),
				'builtin' => _x( 'Built-In Contacts', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title ) {
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ),
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
						array(), $this->form, false ) );	// $network = false
			}
			$this->p->util->do_table_rows( array( '<td>'.$this->p->msgs->get( 'info-'.$metabox ).'</td>' ),
				'metabox-'.$metabox.'-info' );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		public function show_metabox_taglist() {
			$metabox = 'taglist';
			$tabs = apply_filters( $this->p->cf['lca'].'_advanced_'.$metabox.'_tabs', array(
				'fb' => _x( 'Facebook', 'metabox tab', 'nextgen-facebook' ),
				'og' => _x( 'Open Graph', 'metabox tab', 'nextgen-facebook' ),
				'twitter' => _x( 'Twitter', 'metabox tab', 'nextgen-facebook' ),
				'schema' => _x( 'Schema', 'metabox tab', 'nextgen-facebook' ),
				'other' => _x( 'SEO / Other', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title ) {
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ),
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
						array(), $this->form, false ) );	// $network = false
			}
			$this->p->util->do_table_rows( array( '<td>'.$this->p->msgs->get( 'info-'.$metabox ).'</td>' ),
				'metabox-'.$metabox.'-info' );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();
			switch ( $metabox.'-'.$key ) {
				case 'plugin-settings':

					$this->add_essential_advanced_table_rows( $table_rows, $this->form );

					break;
			}
			return $table_rows;
		}
	}
}

?>
