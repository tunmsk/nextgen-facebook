<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbMeta' ) ) {

	class NgfbMeta {

		protected $p;
		protected $form;
		protected $opts = array();	// cache for options
		protected $defs = array();	// cache for default values

		protected static $head_meta_tags = false;
		protected static $head_meta_info = array();
		protected static $last_column_id = null;	// cache_id of the last column request in list table
		protected static $last_column_array = array();	// array of column values for last column requested 

		protected static $rename_md_options_keys = array(
			'ngfb' => array(
				499 => array(
					'link_desc' => 'seo_desc',
					'meta_desc' => 'seo_desc',
				),
				503 => array(
					'schema_recipe_calories' => 'schema_recipe_nutri_cal',
				),
			),
		);

		public static $mod_array = array(
			'id' => 0,
			'name' => false,
			'obj' => false,
			'use_post' => false,
			/*
			 * Post
			 */
			'is_post' => false,		// is post module
			'is_home' => false,		// home page (index or static)
			'is_home_page' => false,	// static front page
			'is_home_index' => false,	// static posts page or home index
			'post_type' => false,
			'post_status' => false,
			'post_author' => false,
			'post_coauthors' => array(),
			/*
			 * Term
			 */
			'is_term' => false,		// is term module
			'tax_slug' => '',		// empty string by default
			/*
			 * User
			 */
			'is_user' => false,		// is user module
		);

		public function __construct() {
		}

		public function get_mod( $mod_id ) {
			return $this->must_be_extended( __METHOD__, self::$mod_array );
		}

		public function get_posts( array $mod, $posts_per_page = false, $paged = false ) {
			return $this->must_be_extended( __METHOD__, $array() );	// return empty array
		}

		public function get_posts_mods( array $mod, $posts_per_page = false, $paged = false ) {
			$ret = array();
			foreach ( $this->get_posts( $mod, $posts_per_page, $paged ) as $post ) {
				if ( ! empty( $post->ID ) )	// just in case
					$ret[] = $this->p->m['util']['post']->get_mod( $post->ID );
			}
			return $ret;
		}

		protected function add_actions() {
			return $this->must_be_extended( __METHOD__ );
		}

		public function add_metaboxes() {
			return $this->must_be_extended( __METHOD__ );
		}

		public function show_metabox_social_settings( $obj ) {
			return $this->must_be_extended( __METHOD__ );
		}

		protected function get_social_tabs( $metabox, array &$mod ) {
			switch ( $metabox ) {
				case 'social_settings':
					$tabs = array(
						'text' => _x( 'Edit Text', 'metabox tab', 'nextgen-facebook' ),
						'media' => _x( 'Select Media', 'metabox tab', 'nextgen-facebook' ),
						'preview' => _x( 'Preview', 'metabox tab', 'nextgen-facebook' ),
						'tags' => _x( 'Head Tags', 'metabox tab', 'nextgen-facebook' ),
						'validate' => _x( 'Validate', 'metabox tab', 'nextgen-facebook' ),
					);
					// keep it clean and remove non-functional form tabs (if required)
					if ( ! empty( $this->p->options['plugin_hide_pro'] ) )
						foreach ( array( 'text', 'media' ) as $key )
							SucomUtil::move_to_end( $tabs, $key );
					break;
				default:
					$tabs = array();	// just in case
					break;
			}
			return apply_filters( $this->p->cf['lca'].'_'.$mod['name'].'_'.$metabox.'_tabs', $tabs, $mod );
		}

		protected function get_table_rows( &$metabox, &$key, &$head_info, &$mod ) {
			$table_rows = array();
			switch ( $key ) {
				case 'preview':
					$table_rows = $this->get_rows_social_preview( $this->form, $head_info, $mod );
					break;

				case 'tags':	
					$table_rows = $this->get_rows_head_tags( $this->form, $head_info, $mod );
					break; 

				case 'validate':
					$table_rows = $this->get_rows_validate( $this->form, $head_info, $mod );
					break; 

			}
			return $table_rows;
		}

		public function get_rows_social_preview( $form, $head_info, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$table_rows = array();
			$prev_width = 600;
			$prev_height = 315;
			$div_style = 'width:'.$prev_width.'px; height:'.$prev_height.'px;';
			$refresh_cache = $this->p->util->is_force_regen( $mod, 'og' ) ? '?force_regen='.time() : '';
			$media_url = SucomUtil::get_mt_media_url( $head_info, 'og:image' ).$refresh_cache;

			$have_sizes = ( ! empty( $head_info['og:image:width'] ) && $head_info['og:image:width'] > 0 && 
					! empty( $head_info['og:image:height'] ) && $head_info['og:image:height'] > 0 ) ? true : false;

			$is_sufficient = ( $have_sizes === true && 
				$head_info['og:image:width'] >= $prev_width && 
				$head_info['og:image:height'] >= $prev_height ) ? true : false;

			if ( ! empty( $media_url ) ) {
				if ( $have_sizes === true ) {
					$image_preview_html = '<div class="preview_img" style="'.$div_style.' 
					background-size:'.( $is_sufficient === true ? 
						'cover' : $head_info['og:image:width'].' '.$head_info['og:image:height'] ).'; 
					background-image:url('.$media_url.');" />'.( $is_sufficient === true ? 
						'' : '<p>'.sprintf( _x( 'Image Dimensions Smaller<br/>than Suggested Minimum<br/>of %s',
							'preview image error', 'nextgen-facebook' ),
								$prev_width.'x'.$prev_height.'px' ).'</p>' ).'</div>';
				} else {
					$image_preview_html = '<div class="preview_img" style="'.$div_style.' 
					background-image:url('.$media_url.');" /><p>'.
					_x( 'Image Dimensions Unknown<br/>or Not Available',
						'preview image error', 'nextgen-facebook' ).'</p></div>';
				}
			} else $image_preview_html = '<div class="preview_img" style="'.$div_style.'"><p>'.
				_x( 'No Open Graph Image Found', 'preview image error', 'nextgen-facebook' ).'</p></div>';

			if ( isset( $mod['post_status'] ) &&
				$mod['post_status'] === 'auto-draft' ) {

				$auto_draft_msg = sprintf( __( 'Save a draft version or publish the %s to update this value.',
					'nextgen-facebook' ), SucomUtil::titleize( $mod['post_type'] ) );

				$table_rows[] = $form->get_th_html( _x( 'Sharing URL',
					'option label', 'nextgen-facebook' ), 'medium' ).
				'<td class="blank"><em>'.$auto_draft_msg.'</em></td>';
	
				$table_rows[] = $form->get_th_html( _x( 'Shortened URL',
					'option label', 'nextgen-facebook' ), 'medium' ).
				'<td class="blank"><em>'.$auto_draft_msg.'</em></td>';
	
			} else {
				$long_url = $this->p->util->get_sharing_url( $mod, false );	// $add_page = false

				if ( $mod['is_post'] ) {
					$short_url = wp_get_shortlink( $mod['id'], 'post' );
				} else {
					$short_url = apply_filters( $this->p->cf['lca'].'_shorten_url',
						$long_url, $this->p->options['plugin_shortener'] );
				}

				$table_rows[] = $form->get_th_html( _x( 'Sharing URL',
					'option label', 'nextgen-facebook' ), 'medium' ).
				'<td>'.$form->get_copy_input( $long_url ).'</td>';

				$table_rows[] = $form->get_th_html( _x( 'Shortened URL',
					'option label', 'nextgen-facebook' ), 'medium' ).
				'<td>'.$form->get_copy_input( $short_url ).'</td>';
			}

			$table_rows[] = $form->get_th_html( _x( 'Open Graph Example',
				'option label', 'nextgen-facebook' ), 'medium' ).
			'<td rowspan="2" style="background-color:#e9eaed;border:1px dotted #e0e0e0;">
			<div class="preview_box" style="width:'.( $prev_width + 40 ).'px;">
				<div class="preview_box" style="width:'.$prev_width.'px;">
					'.$image_preview_html.'
					<div class="preview_txt">
						<div class="preview_title">'.( empty( $head_info['og:title'] ) ?
							'No Title' : $head_info['og:title'] ).'</div>
						<div class="preview_desc">'.( empty( $head_info['og:description'] ) ?
							'No Description' : $head_info['og:description'] ).'</div>
						<div class="preview_by">'.( $_SERVER['SERVER_NAME'].
							( empty( $this->p->options['add_meta_property_article:author'] ) ||
								empty( $head_info['article:author:name'] ) ?
									'' : ' | By '.$head_info['article:author:name'] ) ).'</div>
					</div>
				</div>
			</div></td>';

			$table_rows[] = '<th class="medium textinfo" id="info-meta-social-preview">'.
				$this->p->msgs->get( 'info-meta-social-preview' ).'</th>';

			return $table_rows;
		}

		public function get_rows_head_tags( &$form, &$head_info, &$mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$table_rows = array();
			$script_class = '';

			if ( ! is_array( NgfbMeta::$head_meta_tags ) ) {	// just in case
				return $table_rows;
			}

			foreach ( NgfbMeta::$head_meta_tags as $parts ) {

				if ( count( $parts ) === 1 ) {

					if ( strpos( $parts[0], '<script ' ) === 0 ) {
						$script_class = 'script';
					} elseif ( strpos( $parts[0], '<noscript ' ) === 0 ) {
						$script_class = 'noscript';
					}

					$table_rows[] = '<td colspan="5" class="html '.
						$script_class.'"><pre>'.esc_html( $parts[0] ).'</pre></td>';

					if ( $script_class === 'script' || strpos( $parts[0], '</noscript>' ) === 0 ) {
						$script_class = '';
					}

				} elseif ( isset( $parts[5] ) ) {

					// skip meta tags with reserved values but display empty values
					if ( $parts[5] === NGFB_UNDEF_INT || $parts[5] === (string) NGFB_UNDEF_INT ) {
						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( $parts[3].' value is '.NGFB_UNDEF_INT.' (skipped)' );
						}
						continue;
					}

					if ( $parts[1] === 'meta' && $parts[2] === 'itemprop' && strpos( $parts[3], '.' ) !== 0 ) {
						$match_name = preg_replace( '/^.*\./', '', $parts[3] );
					} else {
						$match_name = $parts[3];
					}

					// convert mixed case itemprop names (for example) to lower case
					$opt_name = strtolower( 'add_'.$parts[1].'_'.$parts[2].'_'.$parts[3] );

					$tr_class = ( empty( $script_class ) ? '' : ' '.$script_class ).
						( empty( $parts[0] ) ? ' is_disabled' : ' is_enabled' ).
						( empty( $parts[5] ) && ! empty( $this->p->options[$opt_name] ) ? ' is_empty' : '' ).
						( isset( $this->p->options[$opt_name] ) ? ' is_standard' : ' is_internal hide_row_in_basic' ).'">';

					$table_rows[] = '<tr class="'.trim( $tr_class ).
					'<th class="xshort">'.$parts[1].'</th>'.
					'<th class="xshort">'.$parts[2].'</th>'.
					'<td class="">'.( empty( $parts[6] ) ? 
						'' : '<!-- '.$parts[6].' -->' ).$match_name.'</td>'.
					'<th class="xshort">'.$parts[4].'</th>'.
					'<td class="wide">'.( strpos( $parts[5], 'http' ) === 0 ? 
						'<a href="'.$parts[5].'">'.$parts[5].'</a>' : $parts[5] ).'</td>';
				}
			}

			return $table_rows;
		}

		public function get_rows_validate( &$form, &$head_info, &$mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$table_rows = array();
			$sharing_url = $this->p->util->get_sharing_url( $mod, false );	// $add_page = false
			$sharing_url_encoded = urlencode( $sharing_url );

			$amp_url = $mod['is_post'] && 
				function_exists( 'amp_get_permalink' ) ?
					'https://validator.ampproject.org/#url='.urlencode( amp_get_permalink( $mod['id'] ) ) : '';

			$bing_url = 'https://www.bing.com/webmaster/diagnostics/markup/validator?url='.$sharing_url_encoded;
			$facebook_url = 'https://developers.facebook.com/tools/debug/og/object?q='.$sharing_url_encoded;
			$google_url = 'https://search.google.com/structured-data/testing-tool/u/0/#url='.$sharing_url_encoded;
			$pinterest_url = 'https://developers.pinterest.com/tools/url-debugger/?link='.$sharing_url_encoded;
			$twitter_url = 'https://cards-dev.twitter.com/validator';
			$w3c_url = 'https://validator.w3.org/nu/?doc='.$sharing_url_encoded;

			// Facebook
			$table_rows[] = $form->get_th_html( _x( 'Facebook Debugger', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'Facebook and most social websites read Open Graph meta tags.', 'nextgen-facebook' ).' '.__( 'The Facebook debugger allows you to refresh Facebook\'s cache, while also validating the Open Graph meta tag values.', 'nextgen-facebook' ).' '.__( 'The Facebook debugger remains the most stable and reliable method to verify Open Graph meta tags.', 'nextgen-facebook' ).'</p><p><i>'.__( 'You may have to click the "Fetch new scrape information" button a few times to refresh Facebook\'s cache.', 'nextgen-facebook' ).'</i></p></td><td class="validate">'.$form->get_button( _x( 'Validate Open Graph', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $facebook_url, true ).'</td>';

			// Google
			$table_rows[] = $form->get_th_html( _x( 'Google Structured Data Testing Tool', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'Verify that Google can correctly parse your structured data markup (meta tags, Schema, Microdata, and JSON-LD markup) for Google Search and Google+.', 'nextgen-facebook' ).'</p></td><td class="validate">'.$form->get_button( _x( 'Validate Data Markup', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $google_url, true ).'</td>';

			// Pinterest
			$table_rows[] = $form->get_th_html( _x( 'Pinterest Rich Pin Validator', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'Validate the Open Graph / Rich Pin meta tags and apply to have them shown on Pinterest zoomed pins.', 'nextgen-facebook' ).'</p></td><td class="validate">'.$form->get_button( _x( 'Validate Rich Pins', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $pinterest_url, true ).'</td>';

			// Twitter
			$table_rows[] = $form->get_th_html( _x( 'Twitter Card Validator', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'The Twitter Card Validator does not accept query arguments &ndash; copy-paste the following sharing URL into the validation input field.', 'nextgen-facebook' ).'</p><p>'.$form->get_copy_input( $sharing_url ).'</p></td><td class="validate">'.$form->get_button( _x( 'Validate Twitter Card', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $twitter_url, true ).'</td>';

			// W3C
			$table_rows[] = $form->get_th_html( _x( 'W3C Markup Validation', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'Validate the HTML syntax and HTML 5 conformance of your meta tags and theme templates markup.', 'nextgen-facebook' ).'</p>'.( empty( $this->p->options['schema_add_noscript'] ) ? '' : '<p><i>'.sprintf( __( 'When the %1$s option is enabled, the W3C validator will show errors for itemprop attributes in meta elements. You may ignore these errors or disable the %1$s option.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_google', 'Meta Property Containers' ) ).'</i></p>' ).'</td><td class="validate">'.$form->get_button( _x( 'Validate HTML Markup', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $w3c_url, true ).'</td>';

			// AMP
			if ( $mod['is_post'] ) {
				$table_rows[] = $form->get_th_html( _x( 'The AMP Validator', 'option label', 'nextgen-facebook' ), 'medium' ).'<td class="validate"><p>'.__( 'Validate the HTML syntax and HTML AMP conformance of your meta tags and the AMP markup of your templates.', 'nextgen-facebook' ).'</p>'.( $this->p->is_avail['amp_endpoint'] ? '' : '<p><i>'.sprintf( __( 'The <a href="%s">AMP plugin by Automattic</a> is required to validate AMP formatted webpages.', 'nextgen-facebook' ), 'https://wordpress.org/plugins/amp/' ).'</i></p>' ).'</td><td class="validate">'.$form->get_button( _x( 'Validate AMP Markup', 'submit button', 'nextgen-facebook' ), 'button-secondary', null, $amp_url, true, ( $amp_url ? false : true ) ).'</td>';
			}

			return $table_rows;
		}

		/*
		 * Return a specific option from the custom social settings meta with fallback for 
		 * multiple option keys. If $md_idx is an array, then get the first non-empty option 
		 * from the options array. This is an easy way to provide a fallback value for the 
		 * first array key. Use 'none' as a key name to skip this fallback behavior.
		 *
		 * Example: get_options_multi( $id, array( 'rp_desc', 'og_desc' ) );
		 */
		public function get_options_multi( $mod_id, $mixed = false, $filter_opts = true ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'mod_id' => $mod_id, 
					'mixed' => $mixed, 
					'filter_opts' => $filter_opts, 
				) );
			}

			if ( empty( $mod_id ) ) {
				return null;
			}

			// return the whole options array
			if ( $mixed === false ) {
				$md_val = $this->get_options( $mod_id, $mixed, $filter_opts );
			// return the first matching index value
			} else {
				if ( ! is_array( $mixed ) ) {		// convert a string to an array
					$mixed = array( $mixed );
				} else {
					$mixed = array_unique( $mixed );	// prevent duplicate idx values
				}
				foreach ( $mixed as $md_idx ) {
					if ( $md_idx === 'none' ) {	// special index keyword
						return null;
					} elseif ( empty( $md_idx ) ) {	// just in case
						continue;
					} else {
						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'getting id '.$mod_id.' option '.$md_idx.' value' );
						}
						if ( ( $md_val = $this->get_options( $mod_id, $md_idx, $filter_opts ) ) !== null ) {
							if ( $this->p->debug->enabled ) {
								$this->p->debug->log( 'option '.$md_idx.' value found (not null)' );
							}
							break;		// stop after first match
						}
					}
				}
			}

			if ( $md_val !== null ) {
				if ( $this->p->debug->enabled ) {
					$mod = $this->get_mod( $mod_id );
					$this->p->debug->log( 'custom '.$mod['name'].' '.( $mixed === false ? 'options' : 
						( is_array( $mixed ) ? implode( ', ', $mixed ) : $mixed ) ).' = '.
						( is_array( $md_val ) ? print_r( $md_val, true ) : '"'.$md_val.'"' ) );
				}
			}

			return $md_val;
		}

		public function get_options( $mod_id, $idx = false, $filter_opts = true ) {
			return $this->must_be_extended( __METHOD__, 
				( $idx !== false ? null : array() ) );	// return an empty array or null
		}

		public function get_defaults( $mod_id, $idx = false ) {

			if ( ! isset( $this->defs[$mod_id] ) )
				$this->defs[$mod_id] = array();

			$opts =& $this->p->options;		// shortcut
			$md_defs =& $this->defs[$mod_id];	// shortcut

			if ( ! NgfbOptions::can_cache() || 
				empty( $md_defs['options_filtered'] ) ) {

				$md_defs = array(
					'options_filtered' => '',
					'options_version' => '',
					'og_art_section' => isset( $opts['og_art_section'] ) ? $opts['og_art_section'] : 'none',
					'og_title' => '',
					'og_desc' => '',
					'seo_desc' => '',
					'tc_desc' => '',
					'pin_desc' => '',
					'schema_desc' => '',
					'sharing_url' => '',
					'og_img_width' => '',
					'og_img_height' => '',
					'og_img_crop' => empty( $opts['og_img_crop'] ) ? 0 : 1,
					'og_img_crop_x' => empty( $opts['og_img_crop_x'] ) ? 'center' : $opts['og_img_crop_x'],
					'og_img_crop_y' => empty( $opts['og_img_crop_y'] ) ? 'center' : $opts['og_img_crop_y'],
					'og_img_id' => '',
					'og_img_id_pre' => empty( $opts['og_def_img_id_pre'] ) ? '' : $opts['og_def_img_id_pre'],
					'og_img_url' => '',
					'og_img_max' => isset( $opts['og_img_max'] ) ? (int) $opts['og_img_max'] : 1,	// cast as integer
					'og_vid_url' => '',
					'og_vid_embed' => '',
					'og_vid_title' => '',
					'og_vid_desc' => '',
					'og_vid_max' => isset( $opts['og_vid_max'] ) ? (int) $opts['og_vid_max'] : 1,	// cast as integer
					'og_vid_prev_img' => empty( $opts['og_vid_prev_img'] ) ? 0 : 1,
					'rp_img_width' => '',
					'rp_img_height' => '',
					'rp_img_crop' => empty( $opts['rp_img_crop'] ) ? 0 : 1,
					'rp_img_crop_x' => empty( $opts['rp_img_crop_x'] ) ? 'center' : $opts['rp_img_crop_x'],
					'rp_img_crop_y' => empty( $opts['rp_img_crop_y'] ) ? 'center' : $opts['rp_img_crop_y'],
					'rp_img_id' => '',
					'rp_img_id_pre' => empty( $opts['og_def_img_id_pre'] ) ? '' : $opts['og_def_img_id_pre'],
					'rp_img_url' => '',
					'schema_img_width' => '',
					'schema_img_height' => '',
					'schema_img_crop' => empty( $opts['schema_img_crop'] ) ? 0 : 1,
					'schema_img_crop_x' => empty( $opts['schema_img_crop_x'] ) ? 'center' : $opts['schema_img_crop_x'],
					'schema_img_crop_y' => empty( $opts['schema_img_crop_y'] ) ? 'center' : $opts['schema_img_crop_y'],
					'schema_img_id' => '',
					'schema_img_id_pre' => empty( $opts['og_def_img_id_pre'] ) ? '' : $opts['og_def_img_id_pre'],
					'schema_img_url' => '',
					'schema_img_max' => isset( $opts['schema_img_max'] ) ? (int) $opts['schema_img_max'] : 1,	// cast as integer
					'product_avail' => 'none',
					'product_condition' => 'none',
					'product_price' => '0.00',
					'product_currency' => NGFB_PROD_CURRENCY,
				);

				$md_defs = apply_filters( $this->p->cf['lca'].'_get_md_defaults',
					$md_defs, $this->get_mod( $mod_id ) );

				if ( NgfbOptions::can_cache() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'setting options_filtered to true' );
					$md_defs['options_filtered'] = true;
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'options_filtered value unchanged' );

			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_defaults filter skipped' );

			if ( $idx !== false ) {
				if ( isset( $md_defs[$idx] ) )
					return $md_defs[$idx];
				else return null;
			} else return $md_defs;
		}

		public function save_options( $mod_id, $rel_id = false ) {
			return $this->must_be_extended( __METHOD__, $mod_id );
		}

		public function clear_cache( $mod_id, $rel_id = false ) {
			// nothing to do
			return $mod_id;
		}

		public function delete_options( $mod_id, $rel_id = false ) {
			return $this->must_be_extended( __METHOD__, $mod_id );
		}

		protected function not_implemented( $method, $ret = true ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->log( $method.' not implemented in this version',
					get_class( $this ) );	// log the extended class name
			return $ret;
		}

		protected function must_be_extended( $method, $ret = true ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->log( $method.' must be extended',
					get_class( $this ) );	// log the extended class name
			return $ret;
		}

		protected function verify_submit_nonce() {
			if ( empty( $_POST ) ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'empty POST for submit' );
				}
				return false;
			} elseif ( empty( $_POST[ NGFB_NONCE ] ) ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'submit POST missing nonce token' );
				}
				return false;
			} elseif ( ! wp_verify_nonce( $_POST[ NGFB_NONCE ], NgfbAdmin::get_nonce() ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'submit nonce token validation failed' );
				if ( is_admin() )
					$this->p->notice->err( __( 'Nonce token validation failed for the submitted form (update ignored).',
						'nextgen-facebook' ) );
				return false;
			} else return true;
		}

		protected function get_submit_opts( $mod_id ) {

			$mod = $this->get_mod( $mod_id );
			$md_defs = $this->get_defaults( $mod['id'] );
			$md_prev = $this->get_options( $mod['id'] );

			/*
			 * Remove plugin / extension version strings.
			 */
			$unset_idx = array( 'options_filtered', 'options_version' );

			foreach ( $this->p->cf['plugin'] as $ext => $info ) {
				if ( isset( $info['opt_version'] ) ) {
					$unset_idx[] = 'plugin_'.$ext.'_opt_version';
				}
			}

			foreach ( $unset_idx as $md_idx ) {
				unset( $md_defs[$md_idx], $md_prev[$md_idx] );
			}

			/*
			 * Merge and sanitize the new options.
			 */
			$md_opts = empty( $_POST[ NGFB_META_NAME ] ) ?			// make sure we have an array
				array() : $_POST[ NGFB_META_NAME ];
			$md_opts = SucomUtil::restore_checkboxes( $md_opts );
			$md_opts = array_merge( $md_prev, $md_opts );				// update the previous options array
			$md_opts = $this->p->opt->sanitize( $md_opts, $md_defs, false, $mod );	// $network = false

			/*
			 * Check image size options (id, prefix, width, height, crop, etc.).
			 */
			foreach ( array( 'rp', 'og' ) as $md_pre ) {
				if ( empty( $md_opts[$md_pre.'_img_id'] ) ) {
					unset( $md_opts[$md_pre.'_img_id_pre'] );
				}
				$force_regen = false;
				foreach ( array( 'width', 'height', 'crop', 'crop_x', 'crop_y' ) as $md_suffix ) {
					// if option is the same as the default, then unset it
					if ( isset( $md_opts[$md_pre.'_img_'.$md_suffix] ) &&
						isset( $md_defs[$md_pre.'_img_'.$md_suffix] ) &&
							$md_opts[$md_pre.'_img_'.$md_suffix] === $md_defs[$md_pre.'_img_'.$md_suffix] ) {
						unset( $md_opts[$md_pre.'_img_'.$md_suffix] );
					}
					$check_current = isset( $md_opts[$md_pre.'_img_'.$md_suffix] ) ?
						$md_opts[$md_pre.'_img_'.$md_suffix] : '';
					$check_previous = isset( $md_prev[$md_pre.'_img_'.$md_suffix] ) ?
						$md_prev[$md_pre.'_img_'.$md_suffix] : '';
					if ( $check_current !== $check_previous ) {
						$force_regen = true;
					}
				}
				if ( $force_regen !== false ) {
					$this->p->util->set_force_regen( $mod, $md_pre );
				}
			}

			/*
			 * Remove "use plugin settings", or "same as default" option values, or empty strings.
			 */
			foreach ( $md_opts as $md_idx => $md_val ) {
				// use strict comparison to manage conversion (don't allow string to integer conversion, for example)
				if ( $md_val === '' || $md_val === NGFB_UNDEF_INT || $md_val === (string) NGFB_UNDEF_INT || 
					( isset( $md_defs[$md_idx] ) && ( $md_val === $md_defs[$md_idx] || $md_val === (string) $md_defs[$md_idx] ) ) ) {
					unset( $md_opts[$md_idx] );
				}
			}

			/*
			 * Renumber multi options (example: recipe ingredients and instructions).
			 */
			foreach ( $this->p->cf['opt']['md_multi'] as $md_multi => $is_multi ) {
				$md_renum = array();	// start with a fresh array
				foreach ( SucomUtil::preg_grep_keys( '/^'.$md_multi.'_[0-9]+$/', $md_opts ) as $md_idx => $md_val ) {
					unset( $md_opts[$md_idx] );
					if ( ! empty( $md_val ) ) {
						$md_renum[] = $md_val;
					}
				}
				foreach ( $md_renum as $num => $md_val ) {	// start at 0
					$md_opts[$md_multi.'_'.$num] = $md_val;
				}
			}

			/*
			 * Mark the new options as current.
			 */
			if ( ! empty( $md_opts ) ) {
				$md_opts['options_version'] = $this->p->cf['opt']['version'];
				foreach ( $this->p->cf['plugin'] as $ext => $info ) {
					if ( isset( $info['opt_version'] ) ) {
						$md_opts['plugin_'.$ext.'_opt_version'] = $info['opt_version'];
					}
				}
			}

			return $md_opts;
		}

		// return sortable column keys and their query sort info
		public static function get_sortable_columns( $col_idx = false ) { 
			$sort_cols = NgfbConfig::$cf['edit']['columns'];
			if ( $col_idx !== false ) {
				if ( isset( $sort_cols[$col_idx] ) )
					return $sort_cols[$col_idx];
				else return null;
			} else return $sort_cols;
		}

		// called from the uninstall static method
		public static function get_column_meta_keys() { 
			$meta_keys = array();
			$sort_cols = self::get_sortable_columns();
			foreach ( $sort_cols as $col_idx => $col_info ) {
				if ( ! empty( $col_info['meta_key'] ) ) {
					$meta_keys[$col_idx] = $col_info['meta_key'];
				}
			}
			return $meta_keys;
		}

		public static function get_column_headers() { 
			$headers = array();
			$sort_cols = self::get_sortable_columns();
			foreach ( $sort_cols as $col_idx => $col_info ) {
				if ( ! empty( $col_info['header'] ) ) {
					$headers[$col_idx] = _x( $col_info['header'],
						'column header', 'nextgen-facebook' );
				}
			}
			return $headers;
		}

		public function update_sortable_meta( $obj_id, $col_idx, $content ) { 
			return $this->must_be_extended( __METHOD__ );
		}

		public function add_sortable_columns( $columns ) { 
			$lca = $this->p->cf['lca'];
			foreach ( self::get_sortable_columns() as $col_idx => $col_info ) {
				if ( ! empty( $col_info['orderby'] ) ) {
					$columns[$lca.'_'.$col_idx] = $lca.'_'.$col_idx;
				}
			}
			return $columns;
		}

		public function set_column_orderby( $query ) { 
			$lca = $this->p->cf['lca'];
			$col_name = $query->get( 'orderby' );
			if ( is_string( $col_name ) && strpos( $col_name, $lca.'_' ) === 0 ) {
				$col_idx = str_replace( $lca.'_', '', $col_name );
				if ( ( $col_info = self::get_sortable_columns( $col_idx ) ) !== null ) {
					foreach ( array( 'meta_key', 'orderby' ) as $set_name ) {
						if ( ! empty( $col_info[$set_name] ) ) {
							$query->set( $set_name, $col_info[$set_name] );
						}
					}
				}
			}
		}

		public function add_mod_column_headings( $columns, $mod_name = '' ) { 
			if ( ! empty( $mod_name ) ) {
				$lca = $this->p->cf['lca'];
				foreach ( self::get_column_headers() as $col_idx => $col_header ) {
					if ( ! empty( $this->p->options['plugin_'.$col_idx.'_col_'.$mod_name] ) ) {
						$columns[$lca.'_'.$col_idx] = $col_header;
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'adding '.$lca.'_'.$col_idx.' column' );
					}
				}
			}
			return $columns;
		}

		public function get_og_img_column_html( $head_info, $mod ) {
			$value = false;
			$force_regen = $this->p->util->is_force_regen( $mod, 'og' );	// false by default

			if ( isset( $head_info['og:image:id'] ) && 
				$head_info['og:image:id'] > 0 ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'getting thumbnail for image id '.$head_info['og:image:id'] );

				list(
					$og_img_thumb['og:image'],
					$og_img_thumb['og:image:width'],
					$og_img_thumb['og:image:height'],
					$og_img_thumb['og:image:cropped'],
					$og_img_thumb['og:image:id']
				) = $this->p->media->get_attachment_image_src( $head_info['og:image:id'], 'thumbnail', false, $force_regen );
				if ( ! empty( $og_img_thumb['og:image'] ) )	// just in case
					$head_info =& $og_img_thumb;
			}

			$refresh_cache = $force_regen ? '?force_regen='.time() : '';
			$media_url = SucomUtil::get_mt_media_url( $head_info, 'og:image' ).$refresh_cache;

			if ( ! empty( $media_url ) )
				$value = '<div class="preview_img" style="background-image:url('.$media_url.');"></div>';

			return $value;
		}

		public function get_og_image( $num, $size_name, $mod_id, $check_dupes = true, $force_regen = false, $md_pre = 'og' ) {
			return $this->must_be_extended( __METHOD__, array() );
		}

		public function get_md_image( $num, $size_name, array $mod, $check_dupes = true, $force_regen = false, $md_pre = 'og', $mt_pre = 'og' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'num' => $num,
					'size_name' => $size_name,
					'mod' => $mod,
					'check_dupes' => $check_dupes,
					'force_regen' => $force_regen,
					'md_pre' => $md_pre,
					'mt_pre' => $mt_pre,
				), get_class( $this ) );
			}

			$meta_ret = array();

			if ( empty( $mod['id'] ) )
				return $meta_ret;

			// unless $md_pre is 'none' allways fallback to the 'og' custom meta
			foreach( array_unique( array( $md_pre, 'og' ) ) as $prefix ) {

				if ( $prefix === 'none' )	// special index keyword
					break;
				elseif ( empty( $prefix ) )	// skip empty md_pre values
					continue;

				$meta_image = SucomUtil::get_mt_prop_image( $mt_pre );

				// get the image id, library prefix, and/or url values
				$pid = $this->get_options( $mod['id'], $prefix.'_img_id' );
				$pre = $this->get_options( $mod['id'], $prefix.'_img_id_pre' );
				$url = $this->get_options( $mod['id'], $prefix.'_img_url' );

				if ( $pid > 0 ) {
					$pid = $pre === 'ngg' ? 'ngg-'.$pid : $pid;

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'using custom '.$prefix.' image id = "'.$pid.'"',
							get_class( $this ) );	// log extended class name

					list( 
						$meta_image[$mt_pre.':image'],
						$meta_image[$mt_pre.':image:width'],
						$meta_image[$mt_pre.':image:height'],
						$meta_image[$mt_pre.':image:cropped'],
						$meta_image[$mt_pre.':image:id']
					) = $this->p->media->get_attachment_image_src( $pid, $size_name, $check_dupes, $force_regen );
				}

				if ( empty( $meta_image[$mt_pre.':image'] ) && ! empty( $url ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'using custom '.$prefix.' image url = "'.$url.'"',
							get_class( $this ) );	// log extended class name

					$width = $this->get_options( $mod['id'], $prefix.'_img_url:width' );
					$height = $this->get_options( $mod['id'], $prefix.'_img_url:height' );

					list(
						$meta_image[$mt_pre.':image'],
						$meta_image[$mt_pre.':image:width'],
						$meta_image[$mt_pre.':image:height']
					) = array(
						$url,
						( $width > 0 ? $width : NGFB_UNDEF_INT ), 
						( $height > 0 ? $height : NGFB_UNDEF_INT )
					);
				}

				if ( ! empty( $meta_image[$mt_pre.':image'] ) &&
					$this->p->util->push_max( $meta_ret, $meta_image, $num ) )
						return $meta_ret;
			}

			foreach ( apply_filters( $this->p->cf['lca'].'_'.$mod['name'].'_image_ids', array(), $size_name, $mod['id'] ) as $pid ) {
				if ( $pid > 0 ) {	// quick sanity check
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'adding image pid: '.$pid );

					$meta_image = SucomUtil::get_mt_prop_image( $mt_pre );

					list( 
						$meta_image[$mt_pre.':image'],
						$meta_image[$mt_pre.':image:width'],
						$meta_image[$mt_pre.':image:height'],
						$meta_image[$mt_pre.':image:cropped'],
						$meta_image[$mt_pre.':image:id']
					) = $this->p->media->get_attachment_image_src( $pid, $size_name, $check_dupes, $force_regen );

					if ( ! empty( $meta_image[$mt_pre.':image'] ) &&
						$this->p->util->push_max( $meta_ret, $meta_image, $num ) )
							return $meta_ret;
				}
			}

			foreach ( apply_filters( $this->p->cf['lca'].'_'.$mod['name'].'_image_urls', array(), $size_name, $mod['id'] ) as $url ) {
				if ( strpos( $url, '://' ) !== false ) {	// quick sanity check

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'adding image url: '.$url );

					$meta_image = SucomUtil::get_mt_prop_image( $mt_pre );
					$meta_image[$mt_pre.':image'] = $url;
					$this->p->util->add_image_url_size( $mt_pre.':image', $meta_image );

					if ( ! empty( $meta_image[$mt_pre.':image'] ) &&
						$this->p->util->push_max( $meta_ret, $meta_image, $num ) )
							return $meta_ret;
				}
			}

			return $meta_ret;
		}

		public function get_og_video( $num = 0, $mod_id, $check_dupes = false, $md_pre = 'og', $mt_pre = 'og' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'num' => $num,
					'mod_id' => $mod_id,
					'check_dupes' => $check_dupes,
					'md_pre' => $md_pre,
					'mt_pre' => $mt_pre,
				), get_class( $this ) );
			}

			$mod = $this->get_mod( $mod_id );	// required for get_content_videos()
			$og_ret = array();
			$og_video = array();

			if ( empty( $mod_id ) ) {
				return $og_ret;
			}

			foreach( array_unique( array( $md_pre, 'og' ) ) as $prefix ) {

				$html = $this->get_options( $mod_id, $prefix.'_vid_embed' );
				$url = $this->get_options( $mod_id, $prefix.'_vid_url' );

				if ( ! empty( $html ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'fetching video(s) from custom '.$prefix.' embed code',
							get_class( $this ) );	// log extended class name
					}
					$og_video = $this->p->media->get_content_videos( $num, $mod, $check_dupes, $html );
					if ( ! empty( $og_video ) ) {
						return array_merge( $og_ret, $og_video );
					}
				}

				if ( ! empty( $url ) && ( $check_dupes == false || $this->p->util->is_uniq_url( $url ) ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'fetching video from custom '.$prefix.' url '.$url,
							get_class( $this ) );	// log extended class name
					}
					$og_video = $this->p->media->get_video_info( $url, 
						NGFB_UNDEF_INT, NGFB_UNDEF_INT, $check_dupes, true );	// $fallback = true
					if ( $this->p->util->push_max( $og_ret, $og_video, $num ) )  {
						return $og_ret;
					}
				}
			}
			return $og_ret;
		}

		public function get_og_video_preview_image( $mod, $check_dupes = false, $md_pre = 'og' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'mod' => $mod,
					'check_dupes' => $check_dupes,
					'md_pre' => $md_pre,
				), get_class( $this ) );
			}

			$og_image = array();

			// fallback to value from general plugin settings
			if ( ( $use_prev_img = $this->get_options( $mod['id'], 'og_vid_prev_img' ) ) === null )
				$use_prev_img = $this->p->options['og_vid_prev_img'];

			// get video preview images if allowed
			if ( ! empty( $use_prev_img ) ) {

				// assumes the first video will have a preview image
				$og_video = $this->p->og->get_all_videos( 1, $mod, $check_dupes, $md_pre );

				if ( ! empty( $og_video ) && is_array( $og_video ) ) {
					foreach ( $og_video as $video ) {
						if ( ! empty( $video['og:image'] ) ) {
							$og_image[] = $video;
							break;
						}
					}
				}
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'use_prev_img is 0 - skipping retrieval of video preview image' );

			return $og_image;
		}

		// $wp_meta can be a post/term/user meta array or empty / false
		protected function get_custom_fields( array $md_opts, $wp_meta = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( empty( $wp_meta ) || ! is_array( $wp_meta ) ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'wp_meta provided is empty or not an array' );
				}
				return $md_opts;
			}

			$charset = get_bloginfo( 'charset' );	// required for html_entity_decode()

			foreach ( (array) apply_filters( $this->p->cf['lca'].'_get_cf_md_idx',
				$this->p->cf['opt']['cf_md_idx'] ) as $cf_idx => $md_idx ) {

				// custom fields can be disabled by filters
				if ( empty( $md_idx ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'custom field '.$cf_idx.' index is disabled' );
					}
					continue;
				// check that a custom field meta key has been defined
				// example: 'plugin_cf_img_url' = '_format_image_url'
				} elseif ( ! empty( $this->p->options[$cf_idx] ) ) {
					$meta_key = $this->p->options[$cf_idx];
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'custom field '.$cf_idx.' option has meta key '.$meta_key );
					}
				} else {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'custom field '.$cf_idx.' option is empty' );
					}
					continue;
				}

				// empty or not, if the array element is set, use it
				if ( isset( $wp_meta[$meta_key][0] ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $meta_key.' meta key found for '.$md_idx.' option' );
					}
					$mixed =& $wp_meta[$meta_key][0];
				} else continue;

				$mixed = maybe_unserialize( $mixed );
				$values = array();

				// decode the string or each array element
				if ( is_array( $mixed ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $meta_key.' is array of '.count( $mixed ).' values (decoding each value)' );
					}
					foreach ( $mixed as $value ) {
						if ( is_array( $value ) ) {
							$value = SucomUtil::array_implode( $value );
						}
						$values[] = trim( html_entity_decode( SucomUtil::decode_utf8( $value ), ENT_QUOTES, $charset ) );
					}
				} else {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'decoding '.$meta_key.' as string of '.strlen( $mixed ).' chars' );
					}
					$values[] = trim( html_entity_decode( SucomUtil::decode_utf8( $mixed ), ENT_QUOTES, $charset ) );
				}

				if ( ! empty( $this->p->cf['opt']['md_multi'][$md_idx] ) ) {
					if ( ! is_array( $mixed ) ) {
						$values = array_map( 'trim', explode( PHP_EOL, reset( $values ) ) );	// explode first element into array
						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'exploded '.$meta_key.' into array of '.count( $values ).' elements' );
						}
					}
					$is_multi = true;		// increment the option name
				} else $is_multi = false;

				// increment the option name, starting with 0
				if ( $is_multi ) {
					// remove any old values from the options array
					$md_opts = SucomUtil::preg_grep_keys( '/^'.$md_idx.'_[0-9]+$/', $md_opts, true );	// $invert = true
					foreach ( $values as $num => $value ) {
						$md_opts[$md_idx.'_'.$num] = $value;
						$md_opts[$md_idx.'_'.$num.':is'] = 'disabled';
					}
				} else {
					$md_opts[$md_idx] = reset( $values );	// get first element of $values array
					$md_opts[$md_idx.':is'] = 'disabled';
				}
			}

			return $md_opts;
		}
	}
}

?>
