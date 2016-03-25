<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbGplAdminSharing' ) ) {

	class NgfbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_cache_rows' => 3,		// $table_rows, $form, $network
				'buttons_include_rows' => 2,		// $table_rows, $form
				'buttons_preset_rows' => 2,		// $table_rows, $form, $network
				'post_social_settings_tabs' => 1,	// $tabs
				'post_buttons_rows' => 4,		// $table_rows, $form, $head, $mod
				'style_sharing_rows' => 2,		// $table_rows, $form
				'style_content_rows' => 2,		// $table_rows, $form
				'style_excerpt_rows' => 2,		// $table_rows, $form
				'style_sidebar_rows' => 2,		// $table_rows, $form
				'style_shortcode_rows' => 2,		// $table_rows, $form
				'style_widget_rows' => 2,		// $table_rows, $form
				'style_admin_edit_rows' => 2,		// $table_rows, $form
			), 30 );
		}

		public function filter_plugin_cache_rows( $table_rows, $form, $network = false ) {

			$table_rows['plugin_file_cache_exp'] = $form->get_th_html( _x( 'Social File Cache Expiry',
				'option label', 'nextgen-facebook' ), null, 'plugin_file_cache_exp' ).
			'<td nowrap class="blank">'.$form->get_no_select( 'plugin_file_cache_exp', 
				$this->p->cf['form']['file_cache_hrs'], 'medium', '', true ).
					_x( 'hours', 'option comment', 'nextgen-facebook' ).'</td>'.
			$this->p->admin->get_site_use( $form, $network, 'plugin_file_cache_exp' );

			return $table_rows;
		}

		public function filter_buttons_include_rows( $table_rows, $form ) {

			$add_to_checkboxes = '';
			foreach ( $this->p->util->get_post_types() as $post_type )
				$add_to_checkboxes .= '<p>'.$form->get_no_checkbox( 'buttons_add_to_'.$post_type->name ).' '.
					$post_type->label.' '.( empty( $post_type->description ) ? '' :
						'('.$post_type->description.')' ).'</p>';

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'ngfb' ) ).'</td>';

			$table_rows['buttons_add_to'] = $form->get_th_html( _x( 'Include on Post Types',
				'option label', 'nextgen-facebook' ), null, 'buttons_add_to' ).
				'<td class="blank">'.$add_to_checkboxes.'</td>';

			return $table_rows;
		}

		public function filter_buttons_preset_rows( $table_rows, $form ) {

			$presets = array( 'shortcode' => 'Shortcode', 'widget' => 'Widget' );
			$show_on = apply_filters( $this->p->cf['lca'].'_buttons_show_on', 
				$this->p->cf['sharing']['show_on'], '' );
			foreach ( $show_on as $type => $label )
				$presets[$type] = $label;
			asort( $presets );

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'ngfb' ) ).'</td>';

			foreach( $presets as $filter_id => $filter_name )
				$table_rows[] = $form->get_th_html( sprintf( _x( '%s Preset',
					'option label', 'nextgen-facebook' ), $filter_name ), null, 'buttons_preset' ).
				'<td class="blank">'.$form->get_no_select( 'buttons_preset_'.$filter_id, 
					array_merge( array( '' ), array_keys( $this->p->cf['opt']['preset'] ) ) ).'</td>';

			return $table_rows;
		}

		public function filter_post_social_settings_tabs( $tabs ) {
			$new_tabs = array();
			foreach ( $tabs as $key => $val ) {
				$new_tabs[$key] = $val;
				if ( $key === 'media' )	// insert the social sharing tab after the media tab
					$new_tabs['buttons'] = _x( 'Sharing Buttons',
						'metabox tab', 'nextgen-facebook' );
			}
			return $new_tabs;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			if ( empty( $mod['post_status'] ) || $mod['post_status'] === 'auto-draft' ) {
				$table_rows['save_a_draft'] = '<td><blockquote class="status-info"><p class="centered">'.
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'nextgen-facebook' ), ucfirst( $mod['post_type'] ) ).'</p></td>';
				return $table_rows;	// abort
			}

			$size_info = $this->p->media->get_size_info( 'thumbnail' );
			$table_rows[] = '<td colspan="3" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'ngfb' ) ).'</td>';

			/*
			 * Twitter
			 */
			$caption_len = $this->p->util->get_tweet_max_len( get_post_permalink( $mod['id'] ) );
			$caption_text = $this->p->webpage->get_caption( $this->p->options['twitter_caption'],
				$caption_len, $mod, true, true );	// $use_cache = true, $add_hashtags = true

			$form_rows['twitter_desc'] = array(
				'label' => _x( 'Tweet Text', 'option label', 'nextgen-facebook' ),
				'th_class' => 'medium', 'tooltip' => 'post-twitter_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
			);

			/*
			 * Pinterest
			 */
			$caption_len = $this->p->options['pin_cap_len'];
			$caption_text = $this->p->webpage->get_caption( $this->p->options['pin_caption'],
				$caption_len, $mod );

			$media = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-pinterest-button',
				$mod, 'rp', array( 'pid', 'img_url' ) );

			if ( ! empty( $media['pid'] ) )
				list( $media['img_url'], $img_width, $img_height,
					$img_cropped ) = $this->p->media->get_attachment_image_src( $media['pid'],
						'thumbnail', false ); 

			$form_rows['pin_desc'] = array(
				'label' => _x( 'Pinterest Caption Text', 'option label', 'nextgen-facebook' ),
				'th_class' => 'medium', 'tooltip' => 'post-pin_desc', 'td_class' => 'blank top',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ).
					( empty( $media['img_url'] ) ? '' : '</td><td class="top" style="width:'.
					$size_info['width'].'px;"><img src="'.$media['img_url'].'" style="max-width:'.
					$size_info['width'].'px;">' ),
			);

			/*
			 * Tumblr
			 */
			$caption_len = $this->p->options['tumblr_cap_len'];
			$caption_text = $this->p->webpage->get_caption( $this->p->options['tumblr_caption'],
				$caption_len, $mod );

			$media = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-tumblr-button',
				$mod, 'og', array( 'pid', 'img_url' ) );

			if ( ! empty( $media['pid'] ) )
				list( $media['img_url'], $img_width, $img_height,
					$img_cropped ) = $this->p->media->get_attachment_image_src( $media['pid'],
						'thumbnail', false ); 

			$form_rows['tumblr_img_desc'] = array(
				'label' => _x( 'Tumblr Image Caption', 'option label', 'nextgen-facebook' ),
				'th_class' => 'medium', 'tooltip' => 'post-tumblr_img_desc', 'td_class' => 'blank top',
				'content' => ( empty( $media['img_url'] ) ?
					'<em>'.sprintf( __( 'Caption disabled - no suitable image found for the %s button',
						'nextgen-facebook' ), 'Tumblr' ).'</em>' :
					$form->get_no_textarea_value( $caption_text, '', '', $caption_len ).
					'</td><td class="top" style="width:'.$size_info['width'].'px;"><img src="'.
					$media['img_url'].'" style="max-width:'.$size_info['width'].'px;">' ),
			);

			$form_rows['tumblr_vid_desc'] = array(
				'label' => _x( 'Tumblr Video Caption', 'option label', 'nextgen-facebook' ),
				'th_class' => 'medium', 'tooltip' => 'post-tumblr_vid_desc', 'td_class' => 'blank',
				'content' => '<em>'.sprintf( __( 'Caption disabled - no suitable video found for the %s button',
					'nextgen-facebook' ), 'Tumblr' ).'</em>',
			);

			/*
			 * Disable Buttons Checkbox
			 */
			$form_rows['buttons_disabled'] = array(
				'label' => _x( 'Disable Sharing Buttons', 'option label', 'nextgen-facebook' ),
				'th_class' => 'medium', 'tooltip' => 'post-buttons_disabled', 'td_class' => 'blank',
				'content' => $form->get_no_checkbox( 'buttons_disabled' ),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
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
