<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbUser' ) ) {

	/*
	 * This class is extended by gpl/util/user.php or pro/util/user.php
	 * and the class object is created as $this->p->m['util']['user'].
	 */
	class NgfbUser extends NgfbMeta {

		protected static $pref = array();

		public function __construct() {
		}

		protected function add_actions() {

			add_filter( 'user_contactmethods', array( &$this, 'add_contact_methods' ), 20, 2 );

			if ( is_admin() ) {
				/**
				 * Hook a minimum number of admin actions to maximize performance.
				 * The user_id argument is always present when we're editing a user,
				 * but missing when viewing our own profile page.
				 */

				if ( ! empty( $_GET ) && ! isset( $_GET['updated'] ) ) {	// optimize hooks
					// common to your profile and user editing pages
					add_action( 'admin_init', array( &$this, 'add_metaboxes' ) );
					// load_meta_page() priorities: 100 post, 200 user, 300 term
					add_action( 'current_screen', array( &$this, 'load_meta_page' ), 200, 1 );
				}

				if ( ! empty( $this->p->options['plugin_og_img_col_user'] ) ||
					! empty( $this->p->options['plugin_og_desc_col_user'] ) ) {

					add_filter( 'manage_users_columns', array( $this, 'add_column_headings' ), 10, 1 );
					add_filter( 'manage_users_custom_column', array( $this, 'get_column_content',), 10, 3 );

					$this->p->util->add_plugin_filters( $this, array( 
						'og_img_user_column_content' => 4,
						'og_desc_user_column_content' => 4,
					) );
				}

				// exit here if not a user or profile page
				$user_id = SucomUtil::get_request_value( 'user_id' );
				if ( empty( $user_id ) )
					return;

				// hooks for user and profile editing
				add_action( 'edit_user_profile', array( &$this, 'show_metabox_section' ), 20 );

				add_action( 'edit_user_profile_update', array( &$this, 'sanitize_submit_cm' ), 5 );
				add_action( 'edit_user_profile_update', array( &$this, 'save_options' ), NGFB_META_SAVE_PRIORITY );
				add_action( 'edit_user_profile_update', array( &$this, 'clear_cache' ), NGFB_META_CACHE_PRIORITY );

				add_action( 'personal_options_update', array( &$this, 'sanitize_submit_cm' ), 5 ); 
				add_action( 'personal_options_update', array( &$this, 'save_options' ), NGFB_META_SAVE_PRIORITY ); 
				add_action( 'personal_options_update', array( &$this, 'clear_cache' ), NGFB_META_CACHE_PRIORITY ); 
			}
		}

		public function get_mod( $mod_id ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$mod = NgfbMeta::$mod_array;
			$mod['id'] = (int) $mod_id;
			$mod['name'] = 'user';
			$mod['obj'] =& $this;
			/*
			 * User
			 */
			$mod['is_user'] = true;

			return apply_filters( $this->p->cf['lca'].'_get_user_mod', $mod, $mod_id );
		}

		public function add_column_headings( $columns ) { 
			return $this->add_mod_column_headings( $columns, 'user' );
		}

		public function get_column_content( $value, $column_name, $user_id ) {
			$mod = $this->get_mod( $user_id );
			return $this->get_mod_column_content( $value, $column_name, $mod );
		}

		public function filter_og_img_user_column_content( $value, $column_name, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( ! empty( $value ) )
				return $value;

			// use the open graph image dimensions to reject images that are too small
			$size_name = $this->p->cf['lca'].'-opengraph';
			$check_dupes = false;	// using first image we find, so dupe checking is useless
			$force_regen = false;
			$md_pre = 'og';
			$og_image = array();

			if ( empty( $og_image ) )
				$og_image = $this->get_og_video_preview_image( $mod, $check_dupes, $md_pre );

			// get_og_images() also provides filter hooks for additional image ids and urls
			if ( empty( $og_image ) )
				$og_image = $this->get_og_image( 1, $size_name, $mod['id'], $check_dupes, $force_regen, $md_pre );

			if ( empty( $og_image ) )
				$og_image = $this->p->media->get_default_image( 1, $size_name, $check_dupes, $force_regen );

			if ( ! empty( $og_image ) && is_array( $og_image ) ) {
				$image = reset( $og_image );
				$value = $this->get_og_img_column_html( $image );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no image found for column value' );

			return $value;
		}

		public function filter_og_desc_user_column_content( $desc, $column_name, $mod ) {
			if ( ! empty( $desc ) )
				return $desc;

			$user_obj = get_userdata( $mod['id'] );	// get the user object
			if ( empty( $user_obj->ID ) )
				return $desc;

			$desc = $this->get_options( $mod['id'], 'og_desc' );

			if ( $this->p->debug->enabled ) {
				if ( empty( $desc ) )
					$this->p->debug->log( 'no custom description found' );
				else $this->p->debug->log( 'custom description = "'.$desc.'"' );
			}

			if ( empty( $desc ) ) {
				if ( ! empty( $user_obj->description ) )
					$desc = $user_obj->description;
				elseif ( ! empty( $user_obj->display_name ) )
					$desc = sprintf( 'Authored by %s', $user_obj->display_name );
			}

			return apply_filters( $this->p->cf['lca'].'_user_object_description', $desc, $user_obj );
		}

		// hooked into the current_screen action
		public function load_meta_page( $screen = false ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			// all meta modules set this property, so use it to optimize code execution
			if ( NgfbMeta::$head_meta_tags !== false || ! isset( $screen->id ) )
				return;

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'screen id: '.$screen->id );

			$lca = $this->p->cf['lca'];

			switch ( $screen->id ) {
				case 'profile':		// user profile page
				case 'user-edit':	// user editing page
				case ( strpos( $screen->id, 'profile_page_' ) === 0 ? true : false ):		// your profile page
				case ( strpos( $screen->id, 'users_page_'.$lca ) === 0 ? true : false ):	// custom social settings page
					break;
				default:
					return;
					break;
			}

			$user_id = SucomUtil::get_user_object( false, 'id' );
			$mod = $this->get_mod( $user_id );
			if ( $this->p->debug->enabled )
				$this->p->debug->log( SucomDebug::pretty_array( $mod ) );

			$add_metabox = empty( $this->p->options[ 'plugin_add_to_user' ] ) ? false : true;
			if ( apply_filters( $lca.'_add_metabox_user', $add_metabox, $user_id ) ) {

				do_action( $lca.'_admin_user_head', $mod, $screen->id );

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'setting head_meta_info static property' );

				// $use_post = false, $read_cache = false to generate notices etc.
				NgfbMeta::$head_meta_tags = $this->p->head->get_head_array( false, $mod, false );
				NgfbMeta::$head_meta_info = $this->p->head->extract_head_info( NgfbMeta::$head_meta_tags );

				// check for missing open graph image and issue warning
				if ( empty( NgfbMeta::$head_meta_info['og:image'] ) )
					$this->p->notice->err( $this->p->msgs->get( 'notice-missing-og-image' ) );

				if ( empty( NgfbMeta::$head_meta_info['og:description'] ) )
					$this->p->notice->err( $this->p->msgs->get( 'notice-missing-og-description' ) );
			}

			$action_query = $lca.'-action';
			if ( ! empty( $_GET[$action_query] ) ) {
				$action_name = SucomUtil::sanitize_hookname( $_GET[$action_query] );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'found action query: '.$action_name );
				if ( empty( $_GET[ NGFB_NONCE ] ) ) {	// NGFB_NONCE is an md5() string
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'nonce token query field missing' );
				} elseif ( ! wp_verify_nonce( $_GET[ NGFB_NONCE ], NgfbAdmin::get_nonce() ) ) {
					$this->p->notice->err( sprintf( __( 'Nonce token validation failed for %1$s action "%2$s".',
						'nextgen-facebook' ), 'user', $action_name ) );
				} else {
					$_SERVER['REQUEST_URI'] = remove_query_arg( array( $action_query, NGFB_NONCE ) );
					switch ( $action_name ) {
						default: 
							do_action( $lca.'_load_meta_page_user_'.$action_name, $user_id, $screen->id );
							break;
					}
				}
			}
		}

		public function add_metaboxes() {

			$user_id = SucomUtil::get_user_object( false, 'id' );

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'insufficient privileges to add metabox for user ID '.$user_id );
				return;
			}

			$lca = $this->p->cf['lca'];
			$add_metabox = empty( $this->p->options[ 'plugin_add_to_user' ] ) ? false : true;
			if ( apply_filters( $this->p->cf['lca'].'_add_metabox_user', $add_metabox, $user_id ) ) {
				add_meta_box( $lca.'_social_settings', _x( 'Social Settings', 'metabox title', 'nextgen-facebook' ),
					array( &$this, 'show_metabox_social_settings' ), $lca.'-user', 'normal', 'low' );
			}
		}

		public function show_metabox_section( $user ) {
			if ( ! current_user_can( 'edit_user', $user->ID ) )
				return;
			$lca = $this->p->cf['lca'];
			$pkg_type = $this->p->check->aop( $lca, true, $this->p->is_avail['aop'] ) ? 
				_x( 'Pro', 'package type', 'nextgen-facebook' ) :
				_x( 'Free', 'package type', 'nextgen-facebook' );
			echo "\n".'<!-- '.$lca.' user metabox section begin -->'."\n";
			echo '<h3 id="'.$lca.'-metaboxes">'.$this->p->cf['plugin'][$lca]['name'].' '.$pkg_type.'</h3>'."\n";
			echo '<div id="poststuff">'."\n";
			do_meta_boxes( $lca.'-user', 'normal', $user );
			echo "\n".'</div><!-- .poststuff -->'."\n";
			echo '<!-- '.$lca.' user metabox section end -->'."\n";
		}

		public function show_metabox_social_settings( $user_obj ) {

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];
			$metabox = 'social_settings';
			$mod = $this->get_mod( $user_obj->ID );
			$tabs = $this->get_social_tabs( $metabox, $mod );
			$opts = $this->get_options( $user_obj->ID );
			$def_opts = $this->get_defaults( $user_obj->ID );
			$this->form = new SucomForm( $this->p, NGFB_META_NAME, $opts, $def_opts );
			wp_nonce_field( NgfbAdmin::get_nonce(), NGFB_NONCE );

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( $metabox.' table rows' );	// start timer

			$table_rows = array();
			foreach ( $tabs as $key => $title ) {
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key, NgfbMeta::$head_meta_info, $mod ), 
					apply_filters( $lca.'_'.$mod['name'].'_'.$key.'_rows', array(), $this->form, NgfbMeta::$head_meta_info, $mod ) );
			}
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( $metabox.' table rows' );	// end timer
		}

		public function get_form_display_names( $roles = array( 'author', 'editor', 'administrator' ) ) {
			foreach ( $roles as $role ) {
				$query_args = array( 
					'role' => $role,
					'fields' => array( 'ID', 'display_name' ),
				);
				foreach ( get_users( $query_args ) as $user ) 
					$user_ids[$user->ID] = $user->display_name;
			}
			asort( $user_ids );
			return array_merge( array( 0 => 'none' ), $user_ids );
		}

		public function get_form_contact_fields( $fields = array() ) { 
			return array_merge( 
				array( 'none' => '[None]' ), 	// make sure none is first
				$this->add_contact_methods( array( 
					'author' => 'Author Archive', 
					'url' => 'Website'
				) )
			);
		}

		public function add_contact_methods( $fields = array(), $user = null ) { 
			$lca = $this->p->cf['lca'];
			$aop = $this->p->check->aop( $lca, true, $this->p->is_avail['aop'] );

			// unset built-in contact fields and/or update their labels
			if ( ! empty( $this->p->cf['wp']['cm'] ) && 
				is_array( $this->p->cf['wp']['cm'] ) && $aop ) {

				foreach ( $this->p->cf['wp']['cm'] as $cm_id => $name ) {
					$cm_opt = 'wp_cm_'.$cm_id.'_';
					if ( isset( $this->p->options[$cm_opt.'enabled'] ) ) {
						if ( ! empty( $this->p->options[$cm_opt.'enabled'] ) ) {
							if ( ! empty( $this->p->options[$cm_opt.'label'] ) )
								$fields[$cm_id] = $this->p->options[$cm_opt.'label'];
						} else unset( $fields[$cm_id] );
					}
				}
			}

			// loop through each social website option prefix
			if ( ! empty( $this->p->cf['opt']['pre'] ) && 
				is_array( $this->p->cf['opt']['pre'] ) ) {

				foreach ( $this->p->cf['opt']['pre'] as $cm_id => $cm_pre ) {
					$cm_opt = 'plugin_cm_'.$cm_pre.'_';

					// not all social websites have a contact fields, so check
					if ( isset( $this->p->options[$cm_opt.'name'] ) ) {

						if ( ! empty( $this->p->options[$cm_opt.'enabled'] ) && 
							! empty( $this->p->options[$cm_opt.'name'] ) && 
							! empty( $this->p->options[$cm_opt.'label'] ) ) {

							$fields[$this->p->options[$cm_opt.'name']] = $this->p->options[$cm_opt.'label'];
						}
					}
				}
			}

			asort( $fields );	// sort associative array by value

			return $fields;
		}

		public function sanitize_submit_cm( $user_id ) {

			if ( ! current_user_can( 'edit_user', $user_id ) )
				return;

			foreach ( $this->p->cf['opt']['pre'] as $cm_id => $cm_pre ) {
				$cm_opt = 'plugin_cm_'.$cm_pre.'_';
				// not all social websites have contact fields, so check
				if ( array_key_exists( $cm_opt.'name', $this->p->options ) ) {

					$enabled = $this->p->options[$cm_opt.'enabled'];
					$name = $this->p->options[$cm_opt.'name'];
					$label = $this->p->options[$cm_opt.'label'];

					if ( isset( $_POST[$name] ) && 
						! empty( $enabled ) && 
						! empty( $name ) && 
						! empty( $label ) ) {

						// sanitize values only for those enabled contact methods
						$val = wp_filter_nohtml_kses( $_POST[$name] );
						if ( ! empty( $val ) ) {
							switch ( $name ) {
								case $this->p->options['plugin_cm_skype_name']:
									// no change
									break;
								case $this->p->options['plugin_cm_twitter_name']:
									$val = substr( preg_replace( '/[^a-zA-Z0-9_]/', '', $val ), 0, 15 );
									if ( ! empty( $val ) ) 
										$val = '@'.$val;
									break;
								default:
									// all other contact methods are assumed to be URLs
									if ( strpos( $val, '://' ) === false )
										$val = '';
									break;
							}
						}
						$_POST[$name] = $val;
					}
				}
			}
			return $user_id;
		}

		// provides backwards compatibility for wp 3.0
		public static function get_user_id_contact_methods( $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( function_exists( 'wp_get_user_contact_methods' ) )	// since wp 3.7
				return wp_get_user_contact_methods( $user );
			else {
				$methods = array();
				if ( get_site_option( 'initial_db_version' ) < 23588 ) {
					$methods = array(
						'aim'    => __( 'AIM' ),
						'yim'    => __( 'Yahoo IM' ),
						'jabber' => __( 'Jabber / Google Talk' )
					); 
				}
				return apply_filters( 'user_contactmethods', $methods, $user );
			}
		}

		// returns an array of urls (or author names for the pinterest crawler)
		public function get_og_profile_urls( $user_ids, $crawler_name = null ) {
			$ret = array();

			if ( empty( $user_ids ) )
				return $ret;

			if ( $crawler_name === null )
				$crawler_name = SucomUtil::crawler_name();

			if ( ! is_array( $user_ids ) )
				$user_ids = array( $user_ids );

			foreach ( $user_ids as $user_id ) {
				if ( empty( $user_id ) )
					continue;

				if ( $crawler_name === 'pinterest' )
					$val = $this->get_author_meta( $user_id, $this->p->options['rp_author_name'] );
				else $val = $this->get_author_website( $user_id, $this->p->options['og_author_field'] );

				if ( ! empty( $val ) )	// make sure we don't add empty values
					$ret[] = $val;
			}

			return $ret;
		}

		public function get_author_meta( $user_id, $field_id ) {
			$value = '';
			$is_user = SucomUtil::user_exists( $user_id );
			if ( $is_user ) {
				switch ( $field_id ) {
					case 'none':
						break;
					case 'fullname':
						$value = get_the_author_meta( 'first_name', $user_id ).' '.
							get_the_author_meta( 'last_name', $user_id );
						break;
					case 'description':
						$value = preg_replace( '/[\s\n\r]+/s', ' ', 
							get_the_author_meta( $field_id, $user_id ) );
						break;
					default:
						$value = get_the_author_meta( $field_id, $user_id );
						break;
				}
				$value = trim( $value );	// just in case
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'user id '.$user_id.' is not a wordpress user' );
			$value = apply_filters( $this->p->cf['lca'].'_get_author_meta', $value, $user_id, $field_id, $is_user );
			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'user id '.$user_id.' '.$field_id.': '.$value );
			return $value;
		}

		public function get_author_website( $user_id, $field_id = 'url' ) {
			$url = '';
			$is_user = SucomUtil::user_exists( $user_id );
			if ( $is_user ) {
				switch ( $field_id ) {
					case 'none':
						break;
					case 'index':
						$url = get_author_posts_url( $user_id );
						break;
					default:
						$url = get_the_author_meta( $field_id, $user_id );
	
						// if empty or not a url, then fallback to the author index page,
						// if the requested field is the opengraph or link author field
						if ( empty( $url ) || ! preg_match( '/:\/\//', $url ) ) {
							if ( $this->p->options['og_author_fallback'] && 
								( $field_id === $this->p->options['og_author_field'] || 
									$field_id === $this->p->options['seo_author_field'] ) ) {
	
								if ( $this->p->debug->enabled )
									$this->p->debug->log( 'fetching the author index page url as fallback' );
								$url = get_author_posts_url( $user_id );
							}
						}
						break;
				}
				$url = trim( $url );	// just in case
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'user id '.$user_id.' is not a wordpress user' );
			$url = apply_filters( $this->p->cf['lca'].'_get_author_website', $url, $user_id, $field_id, $is_user );
			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'user id '.$user_id.' '.$field_id.': '.$url );
			return $url;
		}

		public function reset_metabox_prefs( $pagehook, $box_ids = array(), $meta_name = '', $section = '', $force = false ) {
			$user_id = get_current_user_id();	// since wp 3.0
			// define a new state to set for the box_ids given
			switch ( $meta_name ) {
				case 'order':	$meta_states = array( 'meta-box-order' ); break ;
				case 'hidden':	$meta_states = array( 'metaboxhidden' ); break ;
				case 'closed':	$meta_states = array( 'closedpostboxes' ); break ;
				default: $meta_states = array( 'meta-box-order', 'metaboxhidden', 'closedpostboxes' ); break;
			}
			foreach ( $meta_states as $state ) {
				// define the meta_key for that option
				$meta_key = $state.'_'.$pagehook; 
				// an empty box_ids array means reset the whole page
				if ( $force && empty( $box_ids ) )
					delete_user_option( $user_id, $meta_key, true );
				$is_changed = false;
				$is_default = false;
				$opts = get_user_option( $meta_key, $user_id );
				if ( ! is_array( $opts ) ) {
					$is_changed = true;
					$is_default = true;
					$opts = array();
				}
				if ( $is_default || $force ) {
					foreach ( $box_ids as $box_id ) {
						// change the order only if forced (default is controlled by add_meta_box() order)
						if ( $force && $state == 'meta-box-order' && ! empty( $opts[$section] ) ) {
							// don't proceed if the metabox is already first
							if ( strpos( $opts[$section], $pagehook.'_'.$box_id ) !== 0 ) {
								$boxes = explode( ',', $opts[$section] );
								// remove the box, no matter its position in the array
								if ( $key = array_search( $pagehook.'_'.$box_id, $boxes ) !== false )
									unset( $boxes[$key] );
								// assume we want to be top-most
								array_unshift( $boxes, $pagehook.'_'.$box_id );
								$opts[$section] = implode( ',', $boxes );
								$is_changed = true;
							}
						} else {
							// check to see if the metabox is present for that state
							$key = array_search( $pagehook.'_'.$box_id, $opts );

							// if we're not targetting , then clear it
							if ( empty( $meta_name ) && $key !== false ) {
								unset( $opts[$key] );
								$is_changed = true;
							// otherwise if we want a state, add if it's missing
							} elseif ( ! empty( $meta_name ) && $key === false ) {
								$opts[] = $pagehook.'_'.$box_id;
								$is_changed = true;
							}
						}
					}
				}
				if ( $is_default || $is_changed )
					update_user_option( $user_id, $meta_key, array_unique( $opts ), true );
			}
		}

		public static function delete_metabox_prefs( $user_id = false ) {
			$user_id = $user_id === false ? 
				get_current_user_id() : $user_id;
			$cf = NgfbConfig::get_config( false, true );

			$parent_slug = 'options-general.php';
			foreach ( array_keys( $cf['*']['lib']['setting'] ) as $lib_id ) {
				$menu_slug = $cf['lca'].'-'.$lib_id;
				self::delete_metabox_pagehook( $user_id, $menu_slug, $parent_slug );
			}

			$parent_slug = $cf['lca'].'-'.key( $cf['*']['lib']['submenu'] );
			foreach ( array_keys( $cf['*']['lib']['submenu'] ) as $lib_id ) {
				$menu_slug = $cf['lca'].'-'.$lib_id;
				self::delete_metabox_pagehook( $user_id, $menu_slug, $parent_slug );
			}
		}

		private static function delete_metabox_pagehook( $user_id, $menu_slug, $parent_slug ) {
			$pagehook = get_plugin_page_hookname( $menu_slug, $parent_slug);
			foreach ( array( 'meta-box-order', 'metaboxhidden', 'closedpostboxes' ) as $state ) {
				$meta_key = $state.'_'.$pagehook;
				if ( $user_id !== false )
					delete_user_option( $user_id, $meta_key, true );
				else foreach ( get_users( array( 'meta_key' => $meta_key ) ) as $user )
					delete_user_option( $user->ID, $meta_key, true );
			}
		}

		public static function save_pref( $user_prefs, $user_id = false ) {
			$user_id = $user_id === false ? 
				get_current_user_id() : $user_id;

			if ( ! current_user_can( 'edit_user', $user_id ) )
				return false;

			if ( ! is_array( $user_prefs ) || 
				empty( $user_prefs ) )
					return false;

			$old_prefs = self::get_pref( false, $user_id );	// get all prefs for user
			$new_prefs = array_merge( $old_prefs, $user_prefs );

			// don't bother saving unless we have to
			if ( $old_prefs !== $new_prefs ) {
				self::$pref[$user_id] = $new_prefs;	// update the pref cache
				unset( $new_prefs['options_filtered'] );
				update_user_meta( $user_id, NGFB_PREF_NAME, $new_prefs );
				return true;
			} else return false;
		}

		public static function get_pref( $idx = false, $user_id = false ) {
			$user_id = $user_id === false ? 
				get_current_user_id() : $user_id;

			if ( ! isset( self::$pref[$user_id]['options_filtered'] ) || 
				self::$pref[$user_id]['options_filtered'] !== true ) {

				$ngfb = Ngfb::get_instance();

				self::$pref[$user_id] = apply_filters( $ngfb->cf['lca'].'_get_user_pref',
					get_user_meta( $user_id, NGFB_PREF_NAME, true ), $user_id );

				if ( ! is_array( self::$pref[$user_id] ) )
					self::$pref[$user_id] = array();

				if ( ! isset( self::$pref[$user_id]['show_opts'] ) )
					self::$pref[$user_id]['show_opts'] = $ngfb->options['plugin_show_opts'];

				self::$pref[$user_id]['options_filtered'] = true;
			}

			if ( $idx !== false ) {
				if ( isset( self::$pref[$user_id][$idx] ) ) 
					return self::$pref[$user_id][$idx];
				else return false;
			} else return self::$pref[$user_id];
		}

		public static function is_show_all( $user_id = false ) {
			return $this->show_opts( 'all', $user_id );
		}

		public static function get_show_val( $user_id = false ) {
			return $this->show_opts( false, $user_id );
		}

		// returns the value for show_opts, or return true/false if a value to compare is provided
		public static function show_opts( $compare = false, $user_id = false ) {
			$user_id = $user_id === false ? 
				get_current_user_id() : $user_id;
			$value = self::get_pref( 'show_opts' );
			if ( $compare !== false )
				return $compare === $value ? true : false;
			else return $value;
		}

		public function clear_cache( $user_id, $rel_id = false ) {
			$lca = $this->p->cf['lca'];
			$locale = SucomUtil::get_locale();
			$sharing_url = $this->p->util->get_sharing_url( false );
			$locale_salt = 'locale:'.$locale.'_user:'.$user_id;
			$transients = array(
				'NgfbHead::get_head_array' => array( $locale_salt.'_url:'.$sharing_url ),
				'NgfbMeta::get_mod_column_content' => array( $locale_salt ),
			);
			$transients = apply_filters( $lca.'_user_cache_transients', $transients, $user_id, $locale, $sharing_url );

			$deleted = $this->p->util->clear_cache_objects( $transients );

			if ( ! empty( $this->p->options['plugin_cache_info'] ) && $deleted > 0 )
				$this->p->notice->inf( $deleted.' items removed from the WordPress object and transient caches.', 
					true, __FUNCTION__.'_items_removed', true );

			return $user_id;
		}
	}
}

?>
