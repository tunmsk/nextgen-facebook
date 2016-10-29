<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) )
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSubmenuWebsiteFacebook' ) ) {

	class NgfbSubmenuWebsiteFacebook {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'website_facebook_tabs' => 1,		// $tabs
				'website_facebook_all_rows' => 3,	// $table_rows, $form, $submenu
				'website_facebook_like_rows' => 3,	// $table_rows, $form, $submenu
				'website_facebook_share_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		public function filter_website_facebook_tabs( $tabs ) {
			return array( 
				'all' => _x( 'All Buttons', 'metabox tab', 'nextgen-facebook' ),
				'like' => _x( 'Like and Send', 'metabox tab', 'nextgen-facebook' ),
				'share' => _x( 'Share', 'metabox tab', 'nextgen-facebook' ),
			);
		}

		public function filter_website_facebook_all_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'fb_order', range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$submenu->show_on_checkboxes( 'fb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'fb_platform', $this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'. $form->get_select( 'fb_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Default Language',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'fb_lang', SucomUtil::get_pub_lang( 'facebook' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Type',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'fb_button', array( 'like' => 'Like and Send', 'share' => 'Share' ) ).'</td>';

			return $table_rows;
		}

		public function filter_website_facebook_like_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Markup Language',
				'option label (short)', 'nextgen-facebook' ), 'short' ).
			'<td>'.$form->get_select( 'fb_markup', array( 'html5' => 'HTML5', 'xfbml' => 'XFBML' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Include Send',
				'option label (short)', 'nextgen-facebook' ), 'short', null, 
			'The Send button is only available in combination with the XFBML <em>Markup Language</em>.' ).
			'<td>'.$form->get_checkbox( 'fb_send' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Layout',
				'option label (short)', 'nextgen-facebook' ), 'short', null, 
			'The Standard layout displays social text to the right of the button, and friends\' profile photos below (if <em>Show Faces</em> is also checked). The Button Count layout displays the total number of likes to the right of the button, and the Box Count layout displays the total number of likes above the button. See the <a href="https://developers.facebook.com/docs/plugins/like-button#faqlayout" target="_blank">Facebook Layout Settings FAQ</a> for more details.' ).
			'<td>'.$form->get_select( 'fb_layout', 
				array(
					'standard' => 'Standard',
					'button' => 'Button',
					'button_count' => 'Button Count',
					'box_count' => 'Box Count',
				) 
			).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Faces',
				'option label (short)', 'nextgen-facebook' ), 'short', null, 
			'Show profile photos below the Standard button (Standard button <em>Layout</em> only).' ).
			'<td>'.$form->get_checkbox( 'fb_show_faces' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Font',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'fb_font', 
				array( 
					'arial' => 'Arial',
					'lucida grande' => 'Lucida Grande',
					'segoe ui' => 'Segoe UI',
					'tahoma' => 'Tahoma',
					'trebuchet ms' => 'Trebuchet MS',
					'verdana' => 'Verdana',
				) 
			).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Color Scheme',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'fb_colorscheme', array( 'light' => 'Light', 'dark' => 'Dark' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Action Name',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'fb_action', array( 'like' => 'Like', 'recommend' => 'Recommend' ) ).'</td>';

			return $table_rows;
		}

		public function filter_website_facebook_share_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Layout',
				'option label (short)', 'nextgen-facebook' ), 'short' ).'<td>'.
			$form->get_select( 'fb_type', 
				array(
					'button' => 'Button',
					'button_count' => 'Button Count',
					'box_count' => 'Box Count',
					'icon' => 'Small Icon',
					'icon_link' => 'Icon Link',
					'link' => 'Text Link',
				) 
			).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'NgfbWebsiteFacebook' ) ) {

	class NgfbWebsiteFacebook {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'fb_order' => 4,
					'fb_on_content' => 1,
					'fb_on_excerpt' => 0,
					'fb_on_sidebar' => 0,
					'fb_on_admin_edit' => 1,
					'fb_platform' => 'any',
					'fb_script_loc' => 'header',
					'fb_button' => 'like',
					'fb_markup' => 'xfbml',
					'fb_send' => 1,
					'fb_layout' => 'button_count',
					'fb_font' => 'arial',
					'fb_show_faces' => 0,
					'fb_colorscheme' => 'light',
					'fb_action' => 'like',
					'fb_type' => 'button_count',
				),
			),
		);

		protected $p;
		protected $sdk_version = 'v2.6';

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$atts['send'] = $opts['fb_send'] ? 'true' : 'false';
			$atts['show_faces'] = $opts['fb_show_faces'] ? 'true' : 'false';

			$html = '';
			switch ( $opts['fb_button'] ) {
				case 'like':
					switch ( $opts['fb_markup'] ) {
						case 'xfbml':
							// XFBML
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							SucomUtil::get_atts_css_attr( $atts, 'facebook', 'fb-like' ).'><fb:like href="'.
							$atts['url'].'" send="'.$atts['send'].'" layout="'.$opts['fb_layout'].'" show_faces="'.
							$atts['show_faces'].'" font="'.$opts['fb_font'].'" action="'.
							$opts['fb_action'].'" colorscheme="'.$opts['fb_colorscheme'].'"></fb:like></div>';
							break;
						case 'html5':
							// HTML5
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							SucomUtil::get_atts_css_attr( $atts, 'facebook', 'fb-like' ).' data-href="'.
							$atts['url'].'" data-send="'.$atts['send'].'" data-layout="'.
							$opts['fb_layout'].'" data-show-faces="'.$atts['show_faces'].'" data-font="'.
							$opts['fb_font'].'" data-action="'.$opts['fb_action'].'" data-colorscheme="'.
							$opts['fb_colorscheme'].'"></div>';
							break;
					}
					break;
				case 'share':
					$html .= '<!-- Facebook Share Button --><div '.
					SucomUtil::get_atts_css_attr( $atts, 'fb-share', 'fb-share' ).'><fb:share-button href="'.
					$atts['url'].'" font="'.$opts['fb_font'].'" type="'.$opts['fb_type'].'"></fb:share-button></div>';
					break;
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );

			return $html;
		}

		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$app_id = empty( $this->p->options['fb_app_id'] ) ? '' : $this->p->options['fb_app_id'];
			$lang = empty( $this->p->options['fb_lang'] ) ? 'en_US' : $this->p->options['fb_lang'];
			$lang = apply_filters( $this->p->cf['lca'].'_pub_lang', $lang, 'facebook', 'current' );

			// do not use get_social_file_cache_url() since the facebook javascript does not work when hosted locally
			$js_url = apply_filters( $this->p->cf['lca'].'_js_url_facebook', 
				SucomUtil::get_prot().'://connect.facebook.net/'.$lang.'/sdk.js#xfbml=1&version='.
					$this->sdk_version.'&appId='.$app_id, $pos );

			$html = '<script type="text/javascript" id="fb-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "fb-script-'.$pos.'", "'.$js_url.'" );</script>';

			return $html;
		}
	}
}

?>
