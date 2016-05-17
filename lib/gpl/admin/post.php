<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminPost' ) ) {

	class NgfbGplAdminPost {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'post_header_rows' => 4,	// $table_rows, $form, $head, $mod
			) );
		}

		public function filter_post_header_rows( $table_rows, $form, $head, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-about-msg-post' ).'</td>';

			$form_rows = array(
				'og_art_section' => array(
					'tr_class' => ( isset( $head['og:type'] ) && $head['og:type'] === 'article' ? '' : 'hide_in_basic' ),
					'label' => _x( 'Article Topic', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'post-og_art_section', 'td_class' => 'blank',
					'content' => $form->get_no_select( 'og_art_section', array( -1 ), '', '', false ),
				),
				'og_title' => array(
					'label' => _x( 'Default Title', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_title', 'td_class' => 'blank',
					'no_auto_draft' => true,
					'content' => $form->get_no_input_value( $this->p->webpage->get_title( $this->p->options['og_title_len'],
						'...', $mod, true, false, true, 'none' ), 'wide' ),	// $md_idx = 'none'
				),
				'og_desc' => array(
					'label' => _x( 'Default Description (Facebook / Open Graph, LinkedIn, Pinterest Rich Pin)', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'post-og_desc', 'td_class' => 'blank',
					'no_auto_draft' => true,
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['og_desc_len'],
						'...', $mod, true, true, true, 'none' ), '', '', $this->p->options['og_desc_len'] ),	// $md_idx = 'none'
				),
				'schema_desc' => array(
					'label' => _x( 'Google / Schema Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_desc', 'td_class' => 'blank',
					'no_auto_draft' => true,
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['schema_desc_len'], 
						'...', $mod ), '', '', $this->p->options['schema_desc_len'] ),
				),
				'seo_desc' => array(
					'tr_class' => ( $this->p->options['add_meta_name_description'] ? '' : 'hide_in_basic' ),
					'label' => _x( 'Google Search / SEO Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-seo_desc', 'td_class' => 'blank',
					'no_auto_draft' => true,
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['seo_desc_len'], 
						'...', $mod, true, false ), '', '', $this->p->options['seo_desc_len'] ),	// $add_hashtags = false
				),
				'tc_desc' => array(
					'label' => _x( 'Twitter Card Description', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-tc_desc', 'td_class' => 'blank',
					'no_auto_draft' => true,
					'content' => $form->get_no_textarea_value( $this->p->webpage->get_description( $this->p->options['tc_desc_len'],
						'...', $mod ), '', '', $this->p->options['tc_desc_len'] ),
				),
				'sharing_url' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Sharing URL', 'option label', 'nextgen-facebook' ),
					'th_class' => 'medium', 'tooltip' => 'meta-sharing_url', 'td_class' => 'blank',
					'no_auto_draft' => ( $mod['post_type'] === 'attachment' ? false : true ),
					'content' => $form->get_no_input_value( $this->p->util->get_sharing_url( $mod['use_post'] ), 'wide' ),
				),
			);

			$auto_draft_msg = sprintf( __( 'Save a draft version or publish the %s to update this value.',
				'nextgen-facebook' ), ucfirst( $mod['post_type'] ) );

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod, $auto_draft_msg );
		}
	}
}

?>