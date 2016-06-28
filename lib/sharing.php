<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbSharing' ) ) {

	class NgfbSharing {

		protected $p;
		protected $website = array();
		protected $plugin_filepath = '';
		protected $buttons_for_type = array();		// cache for have_buttons_for_type()
		protected $post_buttons_disabled = array();	// cache for is_post_buttons_disabled()

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url = '';

		public static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'buttons_on_index' => 0,
					'buttons_on_front' => 0,
					'buttons_add_to_post' => 1,
					'buttons_add_to_page' => 1,
					'buttons_add_to_attachment' => 1,
					'buttons_pos_content' => 'bottom',
					'buttons_pos_excerpt' => 'bottom',
					'buttons_use_social_css' => 1,
					'buttons_enqueue_social_css' => 1,
					'buttons_css_sharing' => '',		// all buttons
					'buttons_css_content' => '',		// post/page content
					'buttons_css_excerpt' => '',		// post/page excerpt
					'buttons_css_admin_edit' => '',
					'buttons_css_sidebar' => '',
					'buttons_css_shortcode' => '',
					'buttons_css_widget' => '',
					'buttons_js_sidebar' => '/* Save an empty style text box to reload the default javascript */

jQuery("#ngfb-sidebar").mouseenter( function(){ 
	jQuery("#ngfb-sidebar-buttons").css({
		display:"block",
		width:"auto",
		height:"auto",
		overflow:"visible",
		"border-style":"solid",
	}); } );
jQuery("#ngfb-sidebar-header").click( function(){ 
	jQuery("#ngfb-sidebar-buttons").toggle(); } );',
					'buttons_preset_content' => '',
					'buttons_preset_excerpt' => '',
					'buttons_preset_admin_edit' => 'small_share_count',
					'buttons_preset_sidebar' => 'large_share_vertical',
					'buttons_preset_shortcode' => '',
					'buttons_preset_widget' => '',
				),
			),
		);

		public function __construct( &$plugin, $plugin_filepath = NGFB_FILEPATH ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );	// begin timer

			$this->plugin_filepath = $plugin_filepath;

			self::$sharing_css_name = 'sharing-styles-id-'.get_current_blog_id().'.min.css';
			self::$sharing_css_file = NGFB_CACHEDIR.self::$sharing_css_name;
			self::$sharing_css_url = NGFB_CACHEURL.self::$sharing_css_name;

			$this->set_objects();

			add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_styles' ) );
			add_action( 'wp_head', array( &$this, 'show_header' ), NGFB_HEAD_PRIORITY );
			add_action( 'wp_footer', array( &$this, 'show_footer' ), NGFB_FOOTER_PRIORITY );

			if ( $this->have_buttons_for_type( 'content' ) )
				$this->add_buttons_filter( 'the_content' );

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {
				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,				// add sharing options and css file contents to defaults
				'get_md_defaults' => 1,				// add sharing options to meta data defaults
				'text_filter_has_changes_before' => 2,		// remove the buttons filter from content, excerpt, etc.
				'text_filter_has_changes_after' => 2,		// re-add the buttons filter to content, excerpt, etc.
			) );

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) )
					add_action( 'add_meta_boxes', array( &$this, 'add_post_buttons_metabox' ) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options' => 3,			// update the sharing css file
					'option_type' => 2,			// identify option type for sanitation
					'post_cache_transients' => 4,		// clear transients on post save
					'secondary_action_buttons' => 4,	// add a reload default styles button
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 3,		// include sharing, shortcode, and widget status
					'status_pro_features' => 3,		// include social file cache status
				), 10, 'ngfb' );				// hook into the extension name instead

				$this->p->util->add_plugin_actions( $this, array( 
					'load_setting_page_reload_default_sharing_styles' => 4,
				) );
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );	// end timer
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['ngfb']['lib']['website'] as $id => $name ) {
				$classname = NgfbConfig::load_lib( false, 'website/'.$id, 'ngfbwebsite'.$id );
				if ( $classname !== false && class_exists( $classname ) ) {
					$this->website[$id] = new $classname( $this->p );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $classname.' class loaded' );
				}
			}
		}

		public function filter_get_md_defaults( $def_opts ) {
			return array_merge( $def_opts, array(
				'email_title' => '',		// Email Subject
				'email_desc' => '',		// Email Message
				'twitter_desc' => '',		// Tweet Text
				'pin_desc' => '',		// Pinterest Caption Text
				'tumblr_img_desc' => '',	// Tumblr Image Caption
				'tumblr_vid_desc' => '',	// Tumblr Video Caption
				'buttons_disabled' => 0,	// Disable Sharing Buttons
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			$def_opts = array_merge( $def_opts, self::$cf['opt']['defaults'] );
			$def_opts = $this->p->util->add_ptns_to_opts( $def_opts, 'buttons_add_to' );
			$plugin_dir = trailingslashit( realpath( dirname( $this->plugin_filepath ) ) );
			$url_path = parse_url( trailingslashit( plugins_url( '', $this->plugin_filepath ) ), PHP_URL_PATH );	// relative URL
			$tabs = apply_filters( $this->p->cf['lca'].'_sharing_styles_tabs', $this->p->cf['sharing']['styles'] );

			foreach ( $tabs as $id => $name ) {
				$buttons_css_file = $plugin_dir.'css/'.$id.'-buttons.css';

				// css files are only loaded once (when variable is empty) into defaults to minimize disk i/o
				if ( empty( $def_opts['buttons_css_'.$id] ) ) {
					if ( ! file_exists( $buttons_css_file ) )
						continue;
					elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) )
						$this->p->notice->err( sprintf( __( 'Failed to open the %s file for reading.',
							'nextgen-facebook' ), $buttons_css_file ) );
					else {
						$css_data = fread( $fh, filesize( $buttons_css_file ) );
						fclose( $fh );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'read css from file '.$buttons_css_file );
						foreach ( array( 
							'plugin_url_path' => $url_path,
						) as $macro => $value )
							$css_data = preg_replace( '/%%'.$macro.'%%/', $value, $css_data );
						$def_opts['buttons_css_'.$id] = $css_data;
					}
				}
			}
			return $def_opts;
		}

		public function filter_save_options( $opts, $options_name, $network ) {
			// update the combined and minimized social stylesheet
			if ( $network === false )
				$this->update_sharing_css( $opts );
			return $opts;
		}

		public function filter_option_type( $type, $key ) {

			if ( ! empty( $type ) )
				return $type;

			switch ( $key ) {
				// integer options that must be 1 or more (not zero)
				case 'stumble_badge':
				case ( preg_match( '/_order$/', $key ) ? true : false ):
					return 'pos_num';	// cast as integer
					break;
				// text strings that can be blank
				case 'gp_expandto':
				case 'pin_desc':
				case 'tumblr_img_desc':
				case 'tumblr_vid_desc':
				case 'twitter_desc':
					return 'ok_blank';
					break;
				// options that cannot be blank
				case 'fb_markup': 
				case 'gp_lang': 
				case 'gp_action': 
				case 'gp_size': 
				case 'gp_annotation': 
				case 'twitter_count': 
				case 'twitter_size': 
				case 'linkedin_counter':
				case 'managewp_type':
				case 'pin_button_lang':
				case 'pin_button_shape':
				case 'pin_button_color':
				case 'pin_button_height':
				case 'pin_count_layout':
				case 'pin_caption':
				case 'tumblr_button_style':
				case 'tumblr_caption':
				case ( strpos( $key, 'buttons_pos_' ) === 0 ? true : false ):
				case ( preg_match( '/^[a-z]+_script_loc$/', $key ) ? true : false ):
					return 'not_blank';
					break;
			}
			return $type;
		}

		public function filter_post_cache_transients( $transients, $post_id, $locale, $sharing_url ) {
			$locale_salt = 'locale:'.$locale.'_post:'.$post_id;
			$show_on = apply_filters( $this->p->cf['lca'].'_buttons_show_on', 
				$this->p->cf['sharing']['show_on'], null );

			foreach( $show_on as $type_id => $type_name ) {
				$transients[__CLASS__.'::get_buttons'][] = $locale_salt.'_type:'.$type_id;
				$transients[__CLASS__.'::get_buttons'][] = $locale_salt.'_type:'.$type_id.'_prot:https';
				$transients[__CLASS__.'::get_buttons'][] = $locale_salt.'_type:'.$type_id.'_mobile:true';
				$transients[__CLASS__.'::get_buttons'][] = $locale_salt.'_type:'.$type_id.'_mobile:true_prot:https';
			}

			return $transients;
		}

		// hooked to 'ngfb_status_gpl_features'
		public function filter_status_gpl_features( $features, $ext, $info ) {
			if ( ! empty( $info['lib']['submenu']['buttons'] ) )
				$features['(sharing) Sharing Buttons'] = array(
					'classname' => $ext.'Sharing',
				);
			if ( ! empty( $info['lib']['submenu']['styles'] ) )
				$features['(sharing) Sharing Stylesheet'] = array(
					'status' => $this->p->options['buttons_use_social_css'] ? 'on' : 'off',
				);
			if ( ! empty( $info['lib']['shortcode']['sharing'] ) )
				$features['(sharing) Sharing Shortcode'] = array(
					'classname' => $ext.'ShortcodeSharing',
				);
			if ( ! empty( $info['lib']['widget']['sharing'] ) )
				$features['(sharing) Sharing Widget'] = array(
					'classname' => $ext.'WidgetSharing'
				);
			return $features;
		}

		// hooked to 'ngfb_status_pro_features'
		public function filter_status_pro_features( $features, $ext, $info ) {
			if ( ! empty( $info['lib']['submenu']['buttons'] ) ) {
				$aop = $this->p->check->aop( $ext, true, $this->p->is_avail['aop'] );
				$features['(tool) Sharing Buttons Image / JavaScript File Cache'] = array( 
					'td_class' => $aop ? '' : 'blank',
					'status' => $this->p->options['plugin_file_cache_exp'] ?
						( $aop ? 'on' : 'rec' ) : 'off',
				);
				$features['(tool) Sharing Styles Editor'] = array( 
					'td_class' => $aop ? '' : 'blank',
					'status' => $aop ? 'on' : 'rec',
				);
			}
			return $features;
		}

		public function filter_secondary_action_buttons( $actions, $menu_id, $menu_name, $menu_lib ) {
			if ( $menu_id === 'styles' )
				$actions['reload_default_sharing_styles'] = _x( 'Reload Default Styles', 'submit button', 'nextgen-facebook' );
			return $actions;
		}

		public function action_load_setting_page_reload_default_sharing_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {
			$opts =& $this->p->options;
			$def_opts = $this->p->opt->get_defaults();
			$tabs = apply_filters( $this->p->cf['lca'].'_sharing_styles_tabs', 
				$this->p->cf['sharing']['styles'] );

			foreach ( $tabs as $id => $name )
				if ( isset( $opts['buttons_css_'.$id] ) &&
					isset( $def_opts['buttons_css_'.$id] ) )
						$opts['buttons_css_'.$id] = $def_opts['buttons_css_'.$id];

			$this->update_sharing_css( $opts );
			$this->p->opt->save_options( NGFB_OPTIONS_NAME, $opts, false );
			$this->p->notice->upd( __( 'All sharing styles have been reloaded with their default settings and saved.', 'nextgen-facebook' ) );
		}

		public function wp_enqueue_styles() {
			if ( ! empty( $this->p->options['buttons_use_social_css'] ) ) {
				if ( ! file_exists( self::$sharing_css_file ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'updating '.self::$sharing_css_file );
					$this->update_sharing_css( $this->p->options );
				}
				if ( ! empty( $this->p->options['buttons_enqueue_social_css'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'wp_enqueue_style = '.$this->p->cf['lca'].'_sharing_buttons' );
					wp_register_style( $this->p->cf['lca'].'_sharing_buttons', self::$sharing_css_url, 
						false, $this->p->cf['plugin'][$this->p->cf['lca']]['version'] );
					wp_enqueue_style( $this->p->cf['lca'].'_sharing_buttons' );
				} else {
					if ( ! is_readable( self::$sharing_css_file ) ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( self::$sharing_css_file.' is not readable' );
						if ( is_admin() )
							$this->p->notice->err( sprintf( __( 'The %s file is not readable.',
								'nextgen-facebook' ), self::$sharing_css_file ), true );
					} else {
						echo '<style type="text/css">';
						if ( ( $fsize = @filesize( self::$sharing_css_file ) ) > 0 &&
							$fh = @fopen( self::$sharing_css_file, 'rb' ) ) {
							echo fread( $fh, $fsize );
							fclose( $fh );
						}
						echo '</style>',"\n";
					}
				}
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'buttons_use_social_css option is disabled' );
		}

		public function update_sharing_css( &$opts ) {

			if ( empty( $opts['buttons_use_social_css'] ) ) {
				$this->unlink_sharing_css();
				return;
			}

			$css_data = '';
			$tabs = apply_filters( $this->p->cf['lca'].'_sharing_styles_tabs', 
				$this->p->cf['sharing']['styles'] );

			foreach ( $tabs as $id => $name )
				if ( isset( $opts['buttons_css_'.$id] ) )
					$css_data .= $opts['buttons_css_'.$id];

			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
				false, 'ext/compressor', 'SuextMinifyCssCompressor' );

			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );
			else {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'failed to load minify class SuextMinifyCssCompressor' );
				if ( is_admin() )
					$this->p->notice->err( __( 'Failed to load the minify class SuextMinifyCssCompressor.',
						'nextgen-facebook' ), true );
			}

			if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {
				if ( ( $written = fwrite( $fh, $css_data ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed writing to '.self::$sharing_css_file );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'Failed writing to the % file.',
							'nextgen-facebook' ), self::$sharing_css_file ), true );
				} elseif ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'updated css file '.self::$sharing_css_file.' ('.$written.' bytes written)' );
					if ( is_admin() )
						$this->p->notice->upd( sprintf( __( 'Updated the <a href="%1$s">%2$s</a> stylesheet (%3$d bytes written).',
							'nextgen-facebook' ), self::$sharing_css_url, self::$sharing_css_file, $written ), 
								true, true, 'updated_'.self::$sharing_css_file, true );
				}
				fclose( $fh );
			} else {
				if ( ! is_writable( NGFB_CACHEDIR ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( NGFB_CACHEDIR.' is not writable', true );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'The %s folder is not writable.',
							'nextgen-facebook' ), NGFB_CACHEDIR ), true );
				}
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'failed opening '.self::$sharing_css_file.' for writing' );
				if ( is_admin() )
					$this->p->notice->err( sprintf( __( 'Failed to open file %s for writing.',
						'nextgen-facebook' ), self::$sharing_css_file ), true );
			}
		}

		public function unlink_sharing_css() {
			if ( file_exists( self::$sharing_css_file ) ) {
				if ( ! @unlink( self::$sharing_css_file ) ) {
					if ( is_admin() )
						$this->p->notice->err( __( 'Error removing the minimized stylesheet &mdash; does the web server have sufficient privileges?', 'nextgen-facebook' ), true );
				}
			}
		}

		public function add_post_buttons_metabox() {
			if ( ! is_admin() )
				return;

			// get the current object / post type
			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid post object' );
				return;
			}

			if ( ! empty( $this->p->options[ 'buttons_add_to_'.$post_obj->post_type ] ) ) {
				// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
				add_meta_box( '_'.$this->p->cf['lca'].'_share',
					_x( 'Sharing Buttons', 'metabox title', 'nextgen-facebook' ),
						array( &$this, 'show_admin_sharing' ), $post_obj->post_type, 'side', 'high' );
			}
		}

		public function filter_text_filter_has_changes_before( $ret, $filter_name ) {
			return ( $this->remove_buttons_filter( $filter_name ) ? true : $ret );
		}

		public function filter_text_filter_has_changes_after( $ret, $filter_name ) {
			return ( $this->add_buttons_filter( $filter_name ) ? true : $ret );
		}

		public function show_header() {
			echo $this->get_script_loader();
			echo $this->get_script( 'header' );
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_footer() {
			if ( $this->have_buttons_for_type( 'sidebar' ) )
				echo $this->show_sidebar();
			elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no buttons enabled for sidebar' );
			echo $this->get_script( 'footer' );
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_sidebar() {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$lca = $this->p->cf['lca'];
			$js = trim( preg_replace( '/\/\*.*\*\//', '', 
				$this->p->options['buttons_js_sidebar'] ) );
			$text = '';	// variable must be passed by reference
			$text = $this->get_buttons( $text, 'sidebar', false );	// $use_post = false
			if ( ! empty( $text ) ) {
				echo '<div id="'.$lca.'-sidebar">';
				echo '<div id="'.$lca.'-sidebar-header"></div>';
				echo $text;
				echo '</div>', "\n";
				echo '<script type="text/javascript">'.$js.'</script>', "\n";
			}
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_admin_sharing( $post_obj ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$lca = $this->p->cf['lca'];
			$css_data = '#side-sortables #_'.$lca.'_share .inside table.sucom-setting { padding:0; }'.
				$this->p->options['buttons_css_admin_edit'];

			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
				false, 'ext/compressor', 'SuextMinifyCssCompressor' );

			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );

			echo '<style type="text/css">'.$css_data.'</style>', "\n";
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"><tr><td>';
			if ( get_post_status( $post_obj->ID ) === 'publish' || 
				$post_obj->post_type === 'attachment' ) {

				$content = '';
				echo $this->get_script_loader();
				echo $this->get_script( 'header' );
				echo $this->get_buttons( $content, 'admin_edit' );
				echo $this->get_script( 'footer' );

				if ( $this->p->debug->enabled )
					$this->p->debug->show_html( null, 'Debug Log' );

			} else echo '<p class="centered">'.sprintf( __( '%s must be published<br/>before it can be shared.',
				'nextgen-facebook' ), ucfirst( $post_obj->post_type ) ).'</p>';
			echo '</td></tr></table>';
		}

		public function add_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = add_filter( $type, array( &$this, 'get_buttons_'.$type ), NGFB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.
						' added ('.( $ret  ? 'true' : 'false' ).')' );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_buttons_'.$type.' method is missing' );
			return $ret;
		}

		public function remove_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = remove_filter( $type, array( &$this, 'get_buttons_'.$type ), NGFB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.
						' removed ('.( $ret  ? 'true' : 'false' ).')' );
			}
			return $ret;
		}

		public function get_buttons_the_excerpt( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$id = $this->p->cf['lca'].' excerpt-buttons';
			$text = preg_replace_callback( '/(<!-- '.$id.' begin -->.*<!-- '.$id.' end -->)(<\/p>)?/Usi', 
				array( __CLASS__, 'remove_paragraph_tags' ), $text );
			return $text;
		}

		public function get_buttons_get_the_excerpt( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_the_content( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			return $this->get_buttons( $text, 'content' );
		}

		public function get_buttons( &$text, $type = 'content', $use_post = true, $location = '', $atts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( is_admin() ) {
				if ( strpos( $type, 'admin_' ) !== 0 ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: '.$type.' ignored in back-end'  );
					return $text;
				}
			} elseif ( $this->p->is_avail['amp_endpoint'] && is_amp_endpoint() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: buttons not allowed in amp endpoint'  );
				return $text;
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: buttons not allowed in rss feeds'  );
				return $text;
			} elseif ( ! is_singular() ) {
				if ( empty( $this->p->options['buttons_on_index'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: buttons_on_index not enabled' );
					return $text;
				}
			} elseif ( is_front_page() ) {
				if ( empty( $this->p->options['buttons_on_front'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: buttons_on_front not enabled' );
					return $text;
				}
			} elseif ( is_singular() ) {
				if ( $this->is_post_buttons_disabled() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: post buttons are disabled' );
					return $text;
				}
			}

			if ( ! $this->have_buttons_for_type( $type ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: no sharing buttons enabled' );
				return $text;
			}

			$lca = $this->p->cf['lca'];
			$mod = $this->p->util->get_page_mod( $use_post );	// get post/user/term id, module name, and module object reference
			$html = false;

			if ( $this->p->is_avail['cache']['transient'] ) {

				$sharing_url = $this->p->util->get_sharing_url( $mod, true );
				$cache_salt = __METHOD__.'('.apply_filters( $lca.'_buttons_cache_salt', 
					SucomUtil::get_mod_salt( $mod ).'_type:'.$type.
					( SucomUtil::is_mobile() ? '_mobile:true' : '' ).
					( SucomUtil::is_https() ? '_prot:https' : '' ).
					( empty( $mod['id'] ) ? '_url:'.$sharing_url : '' ),
						$type, $use_post ).')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				$cache_type = 'object cache';

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': transient salt '.$cache_salt );

				$html = get_transient( $cache_id );
			}

			if ( $html !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': '.$type.' html retrieved from transient '.$cache_id );
			} else {
				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
					if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) )
						$sorted_ids[ zeroise( $this->p->options[$pre.'_order'], 3 ).'-'.$id ] = $id;
				ksort( $sorted_ids );

				$atts['use_post'] = $use_post;
				$atts['css_id'] = $css_type = $type.'-buttons';

				if ( ! empty( $this->p->options['buttons_preset_'.$type] ) ) {
					$atts['preset_id'] = $this->p->options['buttons_preset_'.$type];
					$css_preset = $lca.'-preset-'.$atts['preset_id'];
				} else $css_preset = '';

				$buttons_html = $this->get_html( $sorted_ids, $atts, $mod );

				if ( trim( $buttons_html ) ) {
					$html = '
<!-- '.$lca.' '.$css_type.' begin -->
<!-- generated on '.date( 'c' ).' -->
<div class="'.
	( $css_preset ? $css_preset.' ' : '' ).
	( $use_post ? $lca.'-'.$css_type.'">' : '" id="'.$lca.'-'.$css_type.'">' ).
$buttons_html."\n".
'</div><!-- .'.$lca.'-'.$css_type.' -->
<!-- '.$lca.' '.$css_type.' end -->'."\n\n";

					if ( $this->p->is_avail['cache']['transient'] ) {
						set_transient( $cache_id, $html, $this->p->options['plugin_object_cache_exp'] );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $cache_type.': '.$type.' html saved to transient '.
								$cache_id.' ('.$this->p->options['plugin_object_cache_exp'].' seconds)' );
					}
				}
			}

			if ( empty( $location ) ) {
				$location = empty( $this->p->options['buttons_pos_'.$type] ) ? 
					'bottom' : $this->p->options['buttons_pos_'.$type];
			}

			switch ( $location ) {
				case 'top': 
					$text = $html.$text; 
					break;
				case 'bottom': 
					$text = $text.$html; 
					break;
				case 'both': 
					$text = $html.$text.$html; 
					break;
			}

			return $text.( $this->p->debug->enabled ? $this->p->debug->get_html() : '' );
		}

		// get_html() is called by the widget, shortcode, function, and perhaps some filter hooks
		public function get_html( array &$ids, array &$atts, &$mod = false ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];
			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;
			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $use_post );	// get post/user/term id, module name, and module object reference

			$html_ret = '';
			$html_begin = "\n".'<div class="ngfb-buttons '.SucomUtil::get_locale( $mod ).'">'."\n";
			$html_end = "\n".'</div><!-- .ngfb-buttons.'.SucomUtil::get_locale( $mod ).' -->';

			$preset_id = empty( $atts['preset_id'] ) ? 
				'' : preg_replace( '/[^a-z0-9\-_]/', '', $atts['preset_id'] );
			$filter_id = empty( $atts['filter_id'] ) ? 
				'' : preg_replace( '/[^a-z0-9\-_]/', '', $atts['filter_id'] );

			// possibly dereference the opts variable to prevent passing on changes
			if ( empty( $preset_id ) && empty( $filter_id ) )
				$custom_opts =& $this->p->options;
			else $custom_opts = $this->p->options;

			// apply the presets to $custom_opts
			if ( ! empty( $preset_id ) && ! empty( $this->p->cf['opt']['preset'] ) ) {
				if ( isset( $this->p->cf['opt']['preset'][$preset_id] ) &&
					is_array( $this->p->cf['opt']['preset'][$preset_id] ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'applying preset_id '.$preset_id.' to options' );
					$custom_opts = array_merge( $custom_opts, 
						$this->p->cf['opt']['preset'][$preset_id] );

				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( $preset_id.' preset_id missing or not array'  );
			} 

			if ( ! empty( $filter_id ) ) {
				$filter_name = $lca.'_sharing_html_'.$filter_id.'_options';
				if ( has_filter( $filter_name ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'applying filter_id '.$filter_id.' to options ('.$filter_name.')' );
					$custom_opts = apply_filters( $filter_name, $custom_opts );

				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'no filter(s) found for '.$filter_name );
			}

			foreach ( $ids as $id ) {
				if ( isset( $this->website[$id] ) ) {
					if ( method_exists( $this->website[$id], 'get_html' ) ) {
						if ( $this->allow_for_platform( $id ) ) {
							$html_ret .= $this->website[$id]->get_html( $atts, $custom_opts, $mod )."\n";
						} elseif ( $this->p->debug->enabled )
							$this->p->debug->log( $id.' not allowed for platform' );
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( 'get_html method missing for '.$id );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'website object missing for '.$id );
			}

			$html_ret = trim( $html_ret );
			if ( ! empty( $html_ret ) )
				$html_ret = $html_begin.$html_ret.$html_end;
			return $html_ret;
		}

		// add javascript for enabled buttons in content, widget, shortcode, etc.
		public function get_script( $pos = 'header', $request_ids = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$enabled_ids = array();

			// there are no widgets on the admin back-end, so don't bother checking
			if ( ! is_admin() ) {
				if ( class_exists( 'NgfbWidgetSharing' ) ) {
					$widget = new NgfbWidgetSharing();
			 		$widget_settings = $widget->get_settings();
				} else $widget_settings = array();

				// check for enabled buttons in ACTIVE widget(s)
				foreach ( $widget_settings as $num => $instance ) {
					if ( is_object( $widget ) && is_active_widget( false,
						$widget->id_base.'-'.$num, $widget->id_base ) ) {

						foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
							if ( array_key_exists( $id, $instance ) && 
								! empty( $instance[$id] ) )
									$enabled_ids[] = $id;
						}
					}
				}
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'enabled widget ids: '.
						SucomDebug::pretty_array( $enabled_ids, true ) );
			}

			$exit_message = false;
			if ( is_admin() ) {
				if ( ( $post_obj = SucomUtil::get_post_object() ) === false ||
					( get_post_status( $post_obj->ID ) !== 'publish' && $post_obj->post_type !== 'attachment' ) )
						$exit_message = 'must be published or attachment for admin buttons';
			} elseif ( ! is_singular() ) {
				if ( empty( $this->p->options['buttons_on_index'] ) )
					$exit_message = 'buttons_on_index not enabled';
			} elseif ( is_front_page() ) {
				if ( empty( $this->p->options['buttons_on_front'] ) )
					$exit_message = 'buttons_on_front not enabled';
			} elseif ( is_singular() ) {
				if ( $this->is_post_buttons_disabled() )
					$exit_message = 'post buttons are disabled';
			}

			if ( $exit_message ) {
				if ( empty( $request_ids ) && empty( $enabled_ids ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: '.$exit_message  );
					return '<!-- ngfb '.$pos.': '.$exit_message.' -->'."\n";
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'ignoring exit message: have requested or enabled ids' );
			} elseif ( is_admin() ) {
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
					foreach ( SucomUtil::preg_grep_keys( '/^'.$pre.'_on_admin_/', $this->p->options ) as $key => $val ) {
						if ( ! empty( $val ) )
							$enabled_ids[] = $id;
					}
				}
			} else {
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
					foreach ( SucomUtil::preg_grep_keys( '/^'.$pre.'_on_/', $this->p->options ) as $key => $val ) {
						// exclude buttons enabled for admin editing pages
						if ( strpos( $key, $pre.'_on_admin_' ) === false && ! empty( $val ) )
							$enabled_ids[] = $id;
					}
				}
			}

			if ( empty( $request_ids ) ) {
				if ( empty( $enabled_ids ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: no buttons enabled or requested' );
					return '<!-- ngfb '.$pos.': no buttons enabled or requested -->'."\n";
				} else $include_ids = $enabled_ids;
			} else {
				$include_ids = array_diff( $request_ids, $enabled_ids );
				if ( empty( $include_ids ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: no scripts after removing enabled buttons' );
					return '<!-- ngfb '.$pos.': no scripts after removing enabled buttons -->'."\n";
				}
			}

			natsort( $include_ids );
			$include_ids = array_unique( $include_ids );
			$html = '<!-- ngfb '.$pos.' javascript begin -->'."\n".
				'<!-- generated on '.date( 'c' ).' -->'."\n";

			if ( strpos( $pos, '-header' ) ) 
				$script_loc = 'header';
			elseif ( strpos( $pos, '-footer' ) ) 
				$script_loc = 'footer';
			else $script_loc = $pos;

			if ( ! empty( $include_ids ) ) {
				foreach ( $include_ids as $id ) {
					$id = preg_replace( '/[^a-z]/', '', $id );
					$opt_name = $this->p->cf['opt']['pre'][$id].'_script_loc';

					if ( isset( $this->website[$id] ) &&
						method_exists( $this->website[$id], 'get_script' ) ) {

						if ( isset( $this->p->options[$opt_name] ) && 
							$this->p->options[$opt_name] === $script_loc )
								$html .= $this->website[$id]->get_script( $pos )."\n";
						else $html .= '<!-- ngfb '.$pos.': '.$id.' script location is '.$this->p->options[$opt_name].' -->'."\n";
					}
				}
			}

			$html .= '<!-- ngfb '.$pos.' javascript end -->'."\n";

			return $html;
		}

		public function get_script_loader( $pos = 'id' ) {

			$lang = empty( $this->p->options['gp_lang'] ) ? 'en-US' : $this->p->options['gp_lang'];
			$lang = apply_filters( $this->p->cf['lca'].'_pub_lang', $lang, 'google', 'current' );

			return '<script type="text/javascript" id="ngfb-header-script">
	window.___gcfg = { lang: "'.$lang.'" };
	function '.$this->p->cf['lca'].'_insert_js( script_id, url, async ) {
		if ( document.getElementById( script_id + "-js" ) ) return;
		var async = typeof async !== "undefined" ? async : true;
		var script_pos = document.getElementById( script_id );
		var js = document.createElement( "script" );
		js.id = script_id + "-js";
		js.async = async;
		js.type = "text/javascript";
		js.language = "JavaScript";
		js.src = url;
		script_pos.parentNode.insertBefore( js, script_pos );
	};
</script>'."\n";
		}

		public static function get_css_class_id( $css_name, &$atts = array(), $css_class_extra = '' ) {

			foreach ( array( 'css_class', 'css_id' ) as $key )
				if ( empty( $atts[$key] ) )
					$atts[$key] = 'button';

			$css_class = $css_name.'-'.$atts['css_class'];
			$css_id = $css_name.'-'.$atts['css_id'];

			if ( is_singular() || in_the_loop() ) {
				global $post;
				if ( ! empty( $post->ID ) )
					$css_id .= '-post-'.$post->ID;
			}

			if ( ! empty( $css_class_extra ) ) 
				$css_class = $css_class_extra.' '.$css_class;

			return 'class="'.$css_class.'" id="'.$css_id.'"';
		}

		public function have_buttons_for_type( $type ) {
			if ( isset( $this->buttons_for_type[$type] ) )
				return $this->buttons_for_type[$type];
			foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
				if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) &&		// check if button is enabled
					$this->allow_for_platform( $id ) )			// check if allowed on platform
						return $this->buttons_for_type[$type] = true;
			}
			return $this->buttons_for_type[$type] = false;
		}

		public function allow_for_platform( $id ) {
			$pre = isset( $this->p->cf['opt']['pre'][$id] ) ?
				$this->p->cf['opt']['pre'][$id] : $id;
			if ( isset( $this->p->options[$pre.'_platform'] ) ) {
				switch( $this->p->options[$pre.'_platform'] ) {
					case 'any':
						return true;
					case 'desktop':
						return SucomUtil::is_desktop();
					case 'mobile':
						return SucomUtil::is_mobile();
					default:
						return true;
				}
			}
			return true;
		}

		public function is_post_buttons_disabled() {
			$ret = false;

			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid post object' );
				return $ret;
			} else $post_id = empty( $post_obj->ID ) ? 0 : $post_obj->ID;

			if ( empty( $post_id ) )
				return $ret;

			if ( isset( $this->post_buttons_disabled[$post_id] ) )
				return $this->post_buttons_disabled[$post_id];

			if ( $this->p->m['util']['post']->get_options( $post_id, 'buttons_disabled' ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'post '.$post_id.': sharing buttons disabled by meta data option' );
				$ret = true;
			} elseif ( ! empty( $post_obj->post_type ) && 
				empty( $this->p->options['buttons_add_to_'.$post_obj->post_type] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'post '.$post_id.': sharing buttons not enabled for post type '.$post_obj->post_type );
				$ret = true;
			}

			return $this->post_buttons_disabled[$post_id] = apply_filters( $this->p->cf['lca'].'_post_buttons_disabled', $ret, $post_id );
		}

		public function remove_paragraph_tags( $match = array() ) {
			if ( empty( $match ) || ! is_array( $match ) ) return;
			$text = empty( $match[1] ) ? '' : $match[1];
			$suff = empty( $match[2] ) ? '' : $match[2];
			$ret = preg_replace( '/(<\/*[pP]>|\n)/', '', $text );
			return $suff.$ret; 
		}

		public function get_website_object_ids( $website_obj = array() ) {
			$ids = array();

			if ( empty( $website_obj ) )
				$website_keys = array_keys( $this->website );
			else $website_keys = array_keys( $website_obj );

			$website_ids = $this->p->cf['plugin']['ngfb']['lib']['website'];

			foreach ( $website_keys as $id )
				$ids[$id] = isset( $website_ids[$id] ) ?
					$website_ids[$id] : ucfirst( $id );

			return $ids;
		}

		public function get_tweet_text( array &$mod, $atts = array(), $opt_pre = 'twitter', $md_pre = 'twitter' ) {
			if ( isset( $atts['tweet'] ) )	// just in case
				return $atts['tweet'];
			else {
				$lca = $this->p->cf['lca'];
				$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
				$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// required by get_sharing_url()
				$atts['add_hashtags'] = isset( $atts['add_hashtags'] ) ? $atts['add_hashtags'] : true;
	
				$caption_type = empty( $this->p->options[$opt_pre.'_caption'] ) ?
					'title' : $this->p->options[$opt_pre.'_caption'];
	
				$caption_len = $this->get_tweet_max_len( $opt_pre );

				return $this->p->webpage->get_caption( $caption_type, $caption_len,
					$mod, true, $atts['add_hashtags'], false, $md_pre.'_desc' );
			}
		}

		// $opt_pre can be twitter, buffer, etc.
		public function get_tweet_max_len( $opt_pre = 'twitter' ) {

			$short_len = 23;	// twitter counts 23 characters for any url

			if ( isset( $this->p->options['tc_site'] ) && 
				! empty( $this->p->options[$opt_pre.'_via'] ) ) {
					$tc_site = preg_replace( '/^@/', '', $this->p->options['tc_site'] );
					$site_len = empty( $tc_site ) ? 0 : strlen( $tc_site ) + 6;
			} else $site_len = 0;

			$max_len = $this->p->options[$opt_pre.'_cap_len'] - $short_len - $site_len;

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'max tweet length is '.$max_len.' chars ('.
					$this->p->options[$opt_pre.'_cap_len'].' minus '.
					$site_len.' for site name and '.$short_len.' for url)' );

			return $max_len;
		}
	}
}

?>
