<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuGeneral' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbSubmenuGeneral extends NgfbAdmin {

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
			add_meta_box( $this->pagehook.'_opengraph',
				_x( 'All Social Websites / Open Graph', 'metabox title', 'nextgen-facebook' ), 
					array( &$this, 'show_metabox_opengraph' ), $this->pagehook, 'normal' );

			add_meta_box( $this->pagehook.'_publishers',
				_x( 'Specific Websites and Publishers', 'metabox title', 'nextgen-facebook' ), 
					array( &$this, 'show_metabox_publishers' ), $this->pagehook, 'normal' );

			// issues a warning notice if the default image size is too small
			if ( ! SucomUtil::get_const( 'NGFB_CHECK_DEFAULT_IMAGE' ) )
				$og_image = $this->p->media->get_default_image( 1, $this->p->cf['lca'].'-opengraph', false );
		}

		public function show_metabox_opengraph() {
			$metabox = 'og';
			$tabs = apply_filters( $this->p->cf['lca'].'_general_og_tabs', array( 
				'general' => _x( 'Site Information', 'metabox tab', 'nextgen-facebook' ),
				'content' => _x( 'Descriptions', 'metabox tab', 'nextgen-facebook' ),	// same text as Social Settings tab
				'author' => _x( 'Authorship', 'metabox tab', 'nextgen-facebook' ),
				'images' => _x( 'Images', 'metabox tab', 'nextgen-facebook' ),
				'videos' => _x( 'Videos', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title )
				$table_rows[$key] = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
					$this->get_table_rows( $metabox, $key ), $this->form );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		public function show_metabox_publishers() {
			$metabox = 'pub';
			$tabs = apply_filters( $this->p->cf['lca'].'_general_pub_tabs', array( 
				'facebook' => _x( 'Facebook', 'metabox tab', 'nextgen-facebook' ),
				'google' => _x( 'Google / Schema', 'metabox tab', 'nextgen-facebook' ),
				'pinterest' => _x( 'Pinterest', 'metabox tab', 'nextgen-facebook' ),
				'twitter' => _x( 'Twitter', 'metabox tab', 'nextgen-facebook' ),
				'other' => _x( 'Other', 'metabox tab', 'nextgen-facebook' ),
			) );
			$table_rows = array();
			foreach ( $tabs as $key => $title )
				$table_rows[$key] = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
					$this->get_table_rows( $metabox, $key ), $this->form );
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();
			$user_names = $this->p->m['util']['user']->get_form_display_names();
			$user_contacts = $this->p->m['util']['user']->get_form_contact_fields();

			switch ( $metabox.'-'.$key ) {

				case 'og-general':

					$table_rows['og_art_section'] = $this->form->get_th_html( _x( 'Default Article Topic',
						'option label', 'nextgen-facebook' ), null, 'og_art_section' ).
					'<td>'.$this->form->get_select( 'og_art_section', $this->p->util->get_topics() ).'</td>';

					$table_rows['og_site_name'] = $this->form->get_th_html( _x( 'Site Name',
						'option label', 'nextgen-facebook' ), null, 'og_site_name', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'og_site_name', $this->p->options ),
						null, null, null, get_bloginfo( 'name', 'display' ) ).'</td>';

					$table_rows['og_site_description'] = $this->form->get_th_html( _x( 'Site Description',
						'option label', 'nextgen-facebook' ), null, 'og_site_description', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_textarea( SucomUtil::get_key_locale( 'og_site_description', $this->p->options ),
						null, null, null, get_bloginfo( 'description', 'display' ) ).'</td>';

					break;

				case 'og-content':

					$table_rows['og_title_sep'] = $this->form->get_th_html( _x( 'Title Separator',
						'option label', 'nextgen-facebook' ), null, 'og_title_sep' ).
					'<td>'.$this->form->get_input( 'og_title_sep', 'short' ).'</td>';

					$table_rows['og_title_len'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Maximum Title Length',
						'option label', 'nextgen-facebook' ), null, 'og_title_len' ).
					'<td>'.$this->form->get_input( 'og_title_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['og_desc_len'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Maximum Description Length',
						'option label', 'nextgen-facebook' ), null, 'og_desc_len' ).
					'<td>'.$this->form->get_input( 'og_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['og_desc_hashtags'] = $this->form->get_th_html( _x( 'Add Hashtags to Descriptions',
						'option label', 'nextgen-facebook' ), null, 'og_desc_hashtags' ).
					'<td>'.$this->form->get_select( 'og_desc_hashtags', 
						range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' '.
							_x( 'tag names', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['og_page_title_tag'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Add Page Title in Tags / Hashtags',
						'option label', 'nextgen-facebook' ), null, 'og_page_title_tag' ).
					'<td>'.$this->form->get_checkbox( 'og_page_title_tag' ).'</td>';

					$table_rows['og_page_parent_tags'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Add Parent Page Tags / Hashtags',
						'option label', 'nextgen-facebook' ), null, 'og_page_parent_tags' ).
					'<td>'.$this->form->get_checkbox( 'og_page_parent_tags' ).'</td>';

					break;

				case 'og-author':

					$table_rows['og_author_field'] = $this->form->get_th_html( _x( 'Author Profile URL Field',
						'option label', 'nextgen-facebook' ), null, 'og_author_field' ).
					'<td>'.$this->form->get_select( 'og_author_field', $user_contacts ).'</td>';

					$table_rows['og_author_fallback'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Fallback to Author\'s Archive Page',
						'option label', 'nextgen-facebook' ), null, 'og_author_fallback' ).
					'<td>'.$this->form->get_checkbox( 'og_author_fallback' ).'</td>';

					$table_rows['og_def_author_id'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Default Author when Missing',
						'option label', 'nextgen-facebook' ), null, 'og_def_author_id' ).
					'<td>'.$this->form->get_select( 'og_def_author_id', $user_names, null, null, true ).'</td>';

					$table_rows['og_def_author_on_index'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Author on Indexes',
						'option label', 'nextgen-facebook' ), null, 'og_def_author_on_index' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_index' ).' '.
						_x( 'defines index / archive webpages as articles', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['og_def_author_on_search'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Author on Search Results',
						'option label', 'nextgen-facebook' ), null, 'og_def_author_on_search' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_search' ).' '.
						_x( 'defines search webpages as articles', 'option comment', 'nextgen-facebook' ).'</td>';

					break;

				case 'og-images':

					$table_rows['og_img_max'] = $this->form->get_th_html( _x( 'Maximum Images to Include',
						'option label', 'nextgen-facebook' ), null, 'og_img_max' ).
					'<td>'.$this->form->get_select( 'og_img_max', 
						range( 0, $this->p->cf['form']['max_media_items'] ), 'short', null, true ).
					( empty( $this->form->options['og_vid_prev_img'] ) ?
						'' : ' '._x( '<em>video preview images are enabled</em> (and included first)',
							'option comment', 'nextgen-facebook' ) ).'</td>';

					$table_rows['og_img'] = $this->form->get_th_html( _x( 'Open Graph Image Dimensions',
						'option label', 'nextgen-facebook' ), null, 'og_img_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'og_img', false, false ).'</td>';

					$table_rows['og_def_img_id'] = $this->form->get_th_html( _x( 'Default / Fallback Image ID',
						'option label', 'nextgen-facebook' ), null, 'og_def_img_id' ).
					'<td>'.$this->form->get_image_upload_input( 'og_def_img' ).'</td>';

					$table_rows['og_def_img_url'] = $this->form->get_th_html( _x( 'or Default / Fallback Image URL',
						'option label', 'nextgen-facebook' ), null, 'og_def_img_url' ).
					'<td>'.$this->form->get_image_url_input( 'og_def_img' ).'</td>';

					$table_rows['og_def_img_on_index'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Image on Indexes',
						'option label', 'nextgen-facebook' ), null, 'og_def_img_on_index' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_index' ).'</td>';

					$table_rows['og_def_img_on_search'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Image on Search Results',
						'option label', 'nextgen-facebook' ), null, 'og_def_img_on_search' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_search' ).'</td>';

					if ( $this->p->is_avail['media']['ngg'] === true ) {
						$table_rows['og_ngg_tags'] = '<tr class="hide_in_basic">'.
						$this->form->get_th_html( _x( 'Add Tags from NGG Featured Image',
							'option label', 'nextgen-facebook' ), null, 'og_ngg_tags' ).
						'<td>'.$this->form->get_checkbox( 'og_ngg_tags' ).'</td>';
					}

					break;

				case 'og-videos':

					break;

				case 'pub-facebook':

					$table_rows['fb_publisher_url'] = $this->form->get_th_html( _x( 'Facebook Business Page URL',
						'option label', 'nextgen-facebook' ), null, 'fb_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'fb_publisher_url', $this->p->options ), 'wide' ).'</td>';

					$table_rows['fb_app_id'] = $this->form->get_th_html( _x( 'Facebook Application ID',
						'option label', 'nextgen-facebook' ), null, 'fb_app_id' ).
					'<td>'.$this->form->get_input( 'fb_app_id' ).'</td>';

					$table_rows['fb_admins'] = $this->form->get_th_html( _x( 'or Facebook Admin Username(s)',
						'option label', 'nextgen-facebook' ), null, 'fb_admins' ).
					'<td>'.$this->form->get_input( 'fb_admins' ).'</td>';

					$table_rows['fb_author_name'] = $this->form->get_th_html( _x( 'Author Name Format',
						'option label', 'nextgen-facebook' ), null, 'fb_author_name' ).
					'<td>'.$this->form->get_select( 'fb_author_name', 
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$table_rows['fb_lang'] = $this->form->get_th_html( _x( 'Default Content Language',
						'option label', 'nextgen-facebook' ), null, 'fb_lang' ).
					'<td>'.$this->form->get_select( 'fb_lang', SucomUtil::get_pub_lang( 'facebook' ) ).'</td>';

					break;

				case 'pub-google':

					$table_rows['seo_publisher_url'] = $this->form->get_th_html( _x( 'Google+ Business Page URL',
						'option label', 'nextgen-facebook' ), null, 'google_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'seo_publisher_url', $this->p->options ), 'wide' ).'</td>';

					$table_rows['seo_desc_len'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Search / SEO Description Length',
						'option label', 'nextgen-facebook' ), null, 'google_desc_len' ).
					'<td>'.$this->form->get_input( 'seo_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['seo_author_field'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Author Link URL Field',
						'option label', 'nextgen-facebook' ), null, 'google_author_field' ).
					'<td>'.$this->form->get_select( 'seo_author_field', $user_contacts ).'</td>';

					$table_rows['seo_def_author_id'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Default Author when Missing',
						'option label', 'nextgen-facebook' ), null, 'google_def_author_id' ).
					'<td>'.$this->form->get_select( 'seo_def_author_id', $user_names, null, null, true ).'</td>';

					$table_rows['seo_def_author_on_index'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Author on Indexes',
						'option label', 'nextgen-facebook' ), null, 'google_def_author_on_index' ).
					'<td>'.$this->form->get_checkbox( 'seo_def_author_on_index' ).'</td>';

					$table_rows['seo_def_author_on_search'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Default Author on Search Results',
						'option label', 'nextgen-facebook' ), null, 'google_def_author_on_search' ).
					'<td>'.$this->form->get_checkbox( 'seo_def_author_on_search' ).'</td>';

					$table_rows['subsection_google_schema'] = '<td></td><td class="subsection"><h4>'.
						_x( 'Google Structured Data / Schema Markup',
							'metabox title', 'nextgen-facebook' ).'</h4></td>';

					$noscript_disabled = apply_filters( $this->p->cf['lca'].'_add_schema_noscript_array', true ) ? false : true;

					$table_rows['schema_add_noscript'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Use Meta Property Containers',
						'option label', 'nextgen-facebook' ), null, 'schema_add_noscript' ).
					'<td>'.( $noscript_disabled ? $this->form->get_no_checkbox( 'schema_add_noscript', '', '', 0 ).
							' <em>'._x( 'option disabled by extension plugin or custom filter',
								'option comment', 'nextgen-facebook' ).'</em>' :
							$this->form->get_checkbox( 'schema_add_noscript' ) ).'</td>';

					$users = SucomUtil::get_user_select( array( 'editor', 'administrator' ) );

					$table_rows['schema_social_json'] = $this->form->get_th_html( _x( 'Include Google Structured Data',
						'option label', 'nextgen-facebook' ), null, 'schema_social_json' ).
					'<td>'.
					'<p>'.$this->form->get_checkbox( 'schema_website_json' ).' '.
						sprintf( __( '<a href="%s">WebSite Information</a> for Google Search',
							'nextgen-facebook' ), 'https://developers.google.com/structured-data/site-name' ).'</p>'.
					'<p>'.$this->form->get_checkbox( 'schema_organization_json' ).
						' Site Publisher / <a href="https://developers.google.com/structured-data/customize/social-profiles">'.
							'Organization Social Profile</a></p>'.
					'<p>'.$this->form->get_checkbox( 'schema_person_json' ).
						' <a href="https://developers.google.com/structured-data/customize/social-profiles">'.
							'Person Social Profile</a> for the Site Owner '.
								$this->form->get_select( 'schema_person_id', $users, null, null, true ).'</p>'.
					'</td>';

					$table_rows['schema_alt_name'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Website Alternate Name',
						'option label', 'nextgen-facebook' ), null, 'schema_alt_name' ).
					'<td>'.$this->form->get_input( 'schema_alt_name', 'wide' ).'</td>';

					$table_rows['schema_logo_url'] = $this->form->get_th_html( '<a href="https://developers.google.com/structured-data/customize/logos">'.
						_x( 'Business Logo Image URL', 'option label', 'nextgen-facebook' ).'</a>', null, 'schema_logo_url' ).
					'<td>'.$this->form->get_input( 'schema_logo_url', 'wide' ).'</td>';

					$table_rows['schema_banner_url'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Business Banner (600x60) Image URL',
						'option label', 'nextgen-facebook' ), null, 'schema_banner_url' ).
					'<td>'.$this->form->get_input( 'schema_banner_url', 'wide' ).'</td>';

					$table_rows['schema_img_max'] = $this->form->get_th_html( _x( 'Maximum Images to Include',
						'option label', 'nextgen-facebook' ), null, 'schema_img_max' ).
					'<td>'.$this->form->get_select( 'schema_img_max', 
						range( 0, $this->p->cf['form']['max_media_items'] ), 'short', null, true ).
					( empty( $this->form->options['og_vid_prev_img'] ) ?
						'' : ' '._x( '<em>video preview images are enabled</em> (and included first)',
							'option comment', 'nextgen-facebook' ) ).'</td>';

					$table_rows['schema_img'] = $this->form->get_th_html( _x( 'Schema Image Dimensions',
						'option label', 'nextgen-facebook' ), null, 'schema_img_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'schema_img', false, false ).'</td>';

					$table_rows['schema_desc_len'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Maximum Description Length',
						'option label', 'nextgen-facebook' ), null, 'schema_desc_len' ).
					'<td>'.$this->form->get_input( 'schema_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['schema_author_name'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Author / Person Name Format',
						'option label', 'nextgen-facebook' ), null, 'schema_author_name' ).
					'<td>'.$this->form->get_select( 'schema_author_name', 
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$schema_types = $this->p->schema->get_schema_types_select();
					$schema_select = '';
					foreach ( $this->p->util->get_post_types() as $post_type )
						$schema_select .= '<p>'.$this->form->get_select( 'schema_type_for_'.$post_type->name,
							$schema_types, 'schema_type' ).' for '.$post_type->label.'</p>'."\n";

					$table_rows['schema_type_for_home_page'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Default Item Type for Home Page',
						'option label', 'nextgen-facebook' ), null, 'schema_home_page' ).
					'<td>'.$this->form->get_select( 'schema_type_for_home_page', $schema_types, 'schema_type' ).'</td>';

					$table_rows['schema_type_for_ptn'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Default Item Type by Post Type',
						'option label', 'nextgen-facebook' ), null, 'schema_type_for_ptn' ).
					'<td>'.$schema_select.'</td>';

					break;

				case 'pub-pinterest':

					$table_rows[] = '<td colspan="2" style="padding-bottom:10px;">'.
						$this->p->msgs->get( 'info-pub-pinterest' ).'</td>';

					$table_rows['rp_publisher_url'] = $this->form->get_th_html( _x( 'Pinterest Company Page URL',
						'option label', 'nextgen-facebook' ), null, 'rp_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'rp_publisher_url', $this->p->options ), 'wide' ).'</td>';

					if ( ! SucomUtil::get_const( 'NGFB_RICH_PIN_DISABLE' ) ) {
						$table_rows['rp_img'] = $this->form->get_th_html( _x( 'Rich Pin Image Dimensions',
							'option label', 'nextgen-facebook' ), null, 'rp_img_dimensions' ).
						'<td>'.$this->form->get_image_dimensions_input( 'rp_img' ).'</td>';
					}

					$table_rows['rp_author_name'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Author Name Format',
						'option label', 'nextgen-facebook' ), null, 'rp_author_name' ).
					'<td>'.$this->form->get_select( 'rp_author_name',
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$table_rows['rp_dom_verify'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Pinterest Website Verification ID',
						'option label', 'nextgen-facebook' ), null, 'rp_dom_verify' ).
					'<td>'.$this->form->get_input( 'rp_dom_verify', 'api_key' ).'</td>';

					break;

				case 'pub-twitter':

					$table_rows['tc_site'] = $this->form->get_th_html( _x( 'Twitter Business @username',
						'option label', 'nextgen-facebook' ), null, 'tc_site', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'tc_site', $this->p->options ) ).'</td>';

					$table_rows['tc_desc_len'] = '<tr class="hide_in_basic">'.
					$this->form->get_th_html( _x( 'Maximum Description Length',
						'option label', 'nextgen-facebook' ), null, 'tc_desc_len' ).
					'<td>'.$this->form->get_input( 'tc_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'nextgen-facebook' ).'</td>';

					$table_rows['tc_sum'] = $this->form->get_th_html( _x( '<em>Summary</em> Card Image Dimensions',
						'option label', 'nextgen-facebook' ), null, 'tc_sum_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'tc_sum', false, false ).'</td>';

					$table_rows['tc_lrgimg'] = $this->form->get_th_html( _x( '<em>Large Image</em> Card Image Dimensions',
						'option label', 'nextgen-facebook' ), null, 'tc_lrgimg_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'tc_lrgimg', false, false ).'</td>';

					break;

				case 'pub-other':

					$table_rows['instgram_publisher_url'] = $this->form->get_th_html( _x( 'Instagram Business URL',
						'option label', 'nextgen-facebook' ), null, 'instgram_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'instgram_publisher_url', $this->p->options ), 'wide' ).'</td>';

					$table_rows['linkedin_publisher_url'] = $this->form->get_th_html( _x( 'LinkedIn Company Page URL',
						'option label', 'nextgen-facebook' ), null, 'linkedin_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'linkedin_publisher_url', $this->p->options ), 'wide' ).'</td>';

					$table_rows['myspace_publisher_url'] = $this->form->get_th_html( _x( 'MySpace Business Page URL',
						'option label', 'nextgen-facebook' ), null, 'myspace_publisher_url', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_key_locale( 'myspace_publisher_url', $this->p->options ), 'wide' ).'</td>';

					break;
			}
			return $table_rows;
		}
	}
}

?>