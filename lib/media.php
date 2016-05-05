<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbMedia' ) ) {

	class NgfbMedia {

		private $p;
		private $default_img_preg = array(
			'html_tag' => 'img',
			'pid_attr' => 'data-[a-z]+-pid',
			'ngg_src' => '[^\'"]+\/cache\/([0-9]+)_(crop)?_[0-9]+x[0-9]+_[^\/\'"]+|[^\'"]+-nggid0[1-f]([0-9]+)-[^\'"]+',
		);

		private static $image_src_info = null;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			// prevent image_downsize from lying about image width and height
			if ( is_admin() )
				add_filter( 'editor_max_image_size', array( &$this, 'editor_max_image_size' ), 10, 3 );

			add_filter( 'init', array( &$this, 'allow_img_data_attributes' ) );
			add_filter( 'wp_get_attachment_image_attributes', array( &$this, 'add_attachment_image_attributes' ), 10, 2 );
			add_filter( 'get_image_tag', array( &$this, 'add_image_tag' ), 10, 6 );
		}

		// note that $size_name can be a string or an array()
		public function editor_max_image_size( $max_sizes = array(), $size_name = '', $context = '' ) {
			// allow only our sizes to exceed the editor width
			if ( is_string( $size_name ) &&
				strpos( $size_name, $this->p->cf['lca'].'-' ) === 0 )
					$max_sizes = array( 0, 0 );
			return $max_sizes;
		}

		public function allow_img_data_attributes() {
			global $allowedposttags;
			$allowedposttags['img']['data-wp-pid'] = true;
		}

		// $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment );
		public function add_attachment_image_attributes( $attr, $attach ) {
			$attr['data-wp-pid'] = $attach->ID;
			return $attr;
		}

		// $html = apply_filters( 'get_image_tag', $html, $id, $alt, $title, $align, $size );
		public function add_image_tag( $html, $id, $alt, $title, $align, $size ) {
			if ( strpos( $html, ' data-wp-pid=' ) === false )
				$html = preg_replace( '/ *\/?>/', ' data-wp-pid="'.$id.'"$0', $html );
			return $html;
		}

		public function get_size_info( $size_name = 'thumbnail' ) {
			if ( is_integer( $size_name ) ) 
				return;
			if ( is_array( $size_name ) ) 
				return;

			global $_wp_additional_image_sizes;

			if ( isset( $_wp_additional_image_sizes[$size_name]['width'] ) )
				$width = intval( $_wp_additional_image_sizes[$size_name]['width'] );
			else $width = get_option( $size_name.'_size_w' );

			if ( isset( $_wp_additional_image_sizes[$size_name]['height'] ) )
				$height = intval( $_wp_additional_image_sizes[$size_name]['height'] );
			else $height = get_option( $size_name.'_size_h' );

			if ( isset( $_wp_additional_image_sizes[$size_name]['crop'] ) )
				$crop = $_wp_additional_image_sizes[$size_name]['crop'];
			else $crop = get_option( $size_name.'_crop' );

			if ( ! is_array( $crop ) )
				$crop = empty( $crop ) ? false : true;

			return array( 'width' => $width, 'height' => $height, 'crop' => $crop );
		}

		public function get_post_images( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true, $md_pre = 'og' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'size_name' => $size_name,
					'post_id' => $post_id,
					'check_dupes' => $check_dupes,
					'md_pre' => $md_pre,
				) );
			}

			$og_ret = array();
			$force_regen = false;

			if ( ! empty( $post_id ) ) {

				// set the $force_regen value and remove the transient
				if ( ! empty( $this->p->options['plugin_auto_img_resize'] ) ) {
					$force_regen_transient_id = $this->p->cf['lca'].'_post_'.$post_id.'_regen_'.$md_pre;
					$force_regen = get_transient( $force_regen_transient_id );
					if ( $force_regen !== false )
						delete_transient( $force_regen_transient_id );
				} 

				// get_og_images() also provides filter hooks for additional image ids and urls
				// unless $md_pre is 'none', get_og_image() will fallback to the 'og' custom meta
				$og_ret = array_merge( $og_ret, $this->p->m['util']['post']->get_og_image( 1,
					$size_name, $post_id, $check_dupes, $force_regen, $md_pre ) );
			}

			// allow for empty post_id in order to execute featured / attached image filters for modules
			if ( ! $this->p->util->is_maxed( $og_ret, $num ) ) {
				$num_diff = SucomUtil::count_diff( $og_ret, $num );
				$og_ret = array_merge( $og_ret, $this->get_featured( $num_diff, 
					$size_name, $post_id, $check_dupes, $force_regen ) );
			}

			// 'ngfb_attached_images' filter is used by the buddypress module
			if ( ! $this->p->util->is_maxed( $og_ret, $num ) ) {
				$num_diff = SucomUtil::count_diff( $og_ret, $num );
				$og_ret = array_merge( $og_ret, $this->get_attached_images( $num_diff, 
					$size_name, $post_id, $check_dupes, $force_regen ) );
			}

			return $og_ret;
		}

		public function get_featured( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true, $force_regen = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'size_name' => $size_name,
					'post_id' => $post_id,
					'check_dupes' => $check_dupes,
					'force_regen' => $force_regen,
				) );
			}

			$og_ret = array();
			$og_image = SucomUtil::get_mt_prop_image( 'og' );

			if ( ! empty( $post_id ) ) {
				// check for an attachment page, just in case
				if ( ( is_attachment( $post_id ) || get_post_type( $post_id ) === 'attachment' ) &&
					wp_attachment_is_image( $post_id ) ) {

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'post_type is an attachment - using post_id '.$post_id. ' as the image ID' );
					$pid = $post_id;

				} elseif ( $this->p->is_avail['post_thumbnail'] == true && 
					has_post_thumbnail( $post_id ) )
						$pid = get_post_thumbnail_id( $post_id );

				else $pid = false;

				if ( ! empty( $pid ) ) {
					list(
						$og_image['og:image'],
						$og_image['og:image:width'],
						$og_image['og:image:height'],
						$og_image['og:image:cropped'], 
						$og_image['og:image:id']
					) = $this->get_attachment_image_src( $pid, $size_name, $check_dupes, $force_regen );

					if ( ! empty( $og_image['og:image'] ) )
						$this->p->util->push_max( $og_ret, $og_image, $num );
				}
			}
			return apply_filters( $this->p->cf['lca'].'_og_featured', $og_ret, $num, 
				$size_name, $post_id, $check_dupes, $force_regen );
		}

		public function get_first_attached_image_id( $post_id ) {
			if ( ! empty( $post_id ) ) {
				// check for an attachment page, just in case
				if ( ( is_attachment( $post_id ) || get_post_type( $post_id ) === 'attachment' ) &&
					wp_attachment_is_image( $post_id ) )
						return $post_id;
				else {
					$images = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
					$attach = reset( $images );
					if ( ! empty( $attach->ID ) )
						return $attach->ID;
				}
			}
			return false;
		}

		public function get_attachment_image( $num = 0, $size_name = 'thumbnail', $attach_id, $check_dupes = true, $force_regen = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array( 
					'num' => $num,
					'size_name' => $size_name,
					'attach_id' => $attach_id,
					'check_dupes' => $check_dupes,
					'force_regen' => $force_regen,
				) );
			}

			$og_ret = array();
			$og_image = SucomUtil::get_mt_prop_image( 'og' );

			if ( ! empty( $attach_id ) ) {
				if ( wp_attachment_is_image( $attach_id ) ) {	// since wp 2.1.0 
					list(
						$og_image['og:image'],
						$og_image['og:image:width'],
						$og_image['og:image:height'],
						$og_image['og:image:cropped'],
						$og_image['og:image:id']
					) = $this->get_attachment_image_src( $attach_id, $size_name, $check_dupes, $force_regen );
					if ( ! empty( $og_image['og:image'] ) &&
						$this->p->util->push_max( $og_ret, $og_image, $num ) )
							return $og_ret;
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'attachment id '.$attach_id.' is not an image' );
			}
			return $og_ret;
		}

		public function get_attached_images( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true, $force_regen = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'size_name' => $size_name,
					'post_id' => $post_id,
					'check_dupes' => $check_dupes,
					'force_regen' => $force_regen,
				) );
			}

			$og_ret = array();
			$og_image = SucomUtil::get_mt_prop_image( 'og' );

			if ( ! empty( $post_id ) ) {

				$images = get_children( array(
					'post_parent' => $post_id,
					'post_type' => 'attachment',
					'post_mime_type' => 'image'
				), OBJECT );	// OBJECT, ARRAY_A, or ARRAY_N

				$attach_ids = array();
				foreach ( $images as $attach ) {
					if ( ! empty( $attach->ID ) )
						$attach_ids[] = $attach->ID;
				}
				rsort( $attach_ids, SORT_NUMERIC ); 

				$attach_ids = array_unique( apply_filters( $this->p->cf['lca'].'_attached_image_ids', $attach_ids, $post_id ) );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'found '.count( $attach_ids ).' attached images for post_id '.$post_id );

				foreach ( $attach_ids as $pid ) {
					list(
						$og_image['og:image'],
						$og_image['og:image:width'],
						$og_image['og:image:height'],
						$og_image['og:image:cropped'],
						$og_image['og:image:id']
					) = $this->get_attachment_image_src( $pid, $size_name, $check_dupes, $force_regen );
					if ( ! empty( $og_image['og:image'] ) &&
						$this->p->util->push_max( $og_ret, $og_image, $num ) )
							break;	// stop here and apply the 'ngfb_attached_images' filter
				}
			}

			// 'ngfb_attached_images' filter is used by the buddypress module
			return apply_filters( $this->p->cf['lca'].'_attached_images', $og_ret, $num, $size_name, $post_id, $check_dupes );
		}

		/* Use these static methods in get_attachment_image_src() to set/reset information about
		 * the image being processed for down-stream filters / methods lacking this information.
		 * They can call NgfbMedia::get_image_src_info() to retrieve the image information.
		 */
		public static function set_image_src_info( $image_src_args = null ) {
			self::$image_src_info = $image_src_args;
		}

		public static function get_image_src_info( $idx = false ) {
			if ( $idx !== false ) 
				if ( isset( self::$image_src_info[$idx] ) )
					return self::$image_src_info[$idx];
				else return null;
			else return self::$image_src_info;
		}

		// by default, return an empty image array
		public static function reset_image_src_info( $image_src_ret = array( null, null, null, null, null ) ) {
			self::$image_src_info = null;
			return $image_src_ret;
		}

		// make sure every return is wrapped with self::reset_image_src_info()
		public function get_attachment_image_src( $pid, $size_name = 'thumbnail', $check_dupes = true, $force_regen = false ) {

			self::set_image_src_info( $args = array(
				'pid' => $pid,
				'size_name' => $size_name,
				'check_dupes' => $check_dupes,
				'force_regen' => $force_regen,
			) );

			if ( $this->p->debug->enabled )
				$this->p->debug->args( $args );

			$size_info = $this->get_size_info( $size_name );
			$img_url = '';
			$img_width = -1;
			$img_height = -1;
			$img_cropped = $size_info['crop'] === false ? 0 : 1;	// get_size_info() returns false, true, or an array

			if ( $this->p->is_avail['media']['ngg'] === true && 
				strpos( $pid, 'ngg-' ) === 0 ) {

				if ( ! empty( $this->p->m['media']['ngg'] ) )
					return self::reset_image_src_info( $this->p->m['media']['ngg']->get_image_src( $pid, 
						$size_name, $check_dupes ) );
				else {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'ngg module is not available: image ID '.$attr_value.' ignored' ); 

					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'The NextGEN Gallery integration module provided by %1$s is required to read information for image ID %2$s.', 'nextgen-facebook' ), $this->p->cf['plugin'][$this->p->cf['lca']]['short'].' Pro', $pid ) ); 

					return self::reset_image_src_info(); 
				}
			} elseif ( ! wp_attachment_is_image( $pid ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: attachment '.$pid.' is not an image' ); 
				return self::reset_image_src_info(); 
			}

			$use_full = false;
			$img_meta = wp_get_attachment_metadata( $pid );

			if ( isset( $img_meta['width'] ) && isset( $img_meta['height'] ) ) {
				if ( $img_meta['width'] === $size_info['width'] && 
					$img_meta['height'] === $size_info['height'] )
						$use_full = true;
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'full size image '.$img_meta['file'].' dimensions '.$img_meta['width'].'x'.$img_meta['height'] );
			} elseif ( $this->p->debug->enabled ) {
				if ( isset( $img_meta['file'] ) )
					$this->p->debug->log( 'full size image '.$img_meta['file'].' dimensions are missing' );
				else $this->p->debug->log( 'full size image file path meta for '.$pid.' is missing' );
			}

			if ( $use_full === true ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'requesting full size instead - image dimensions same as '.
						$size_name.' ('.$size_info['width'].'x'.$size_info['height'].')' );

			} elseif ( strpos( $size_name, $this->p->cf['lca'].'-' ) === 0 ) {		// only resize our own custom image sizes

				if ( ! empty( $this->p->options['plugin_auto_img_resize'] ) ) {		// auto-resize images option must be enabled

					// does the image metadata contain our image sizes?
					if ( $force_regen === true || empty( $img_meta['sizes'][$size_name] ) ) {
						$is_accurate_width = false;
						$is_accurate_height = false;
					} else {
						// is the width and height in the image metadata accurate?
						$is_accurate_width = ! empty( $img_meta['sizes'][$size_name]['width'] ) &&
							$img_meta['sizes'][$size_name]['width'] == $size_info['width'] ? true : false;
						$is_accurate_height = ! empty( $img_meta['sizes'][$size_name]['height'] ) &&
							$img_meta['sizes'][$size_name]['height'] == $size_info['height'] ? true : false;

						// if not cropped, make sure the resized image respects the original aspect ratio
						if ( $is_accurate_width && $is_accurate_height && empty( $img_cropped ) &&
							isset( $img_meta['width'] ) && isset( $img_meta['height'] ) ) {

							if ( $img_meta['width'] > $img_meta['height'] ) {
								$ratio = $img_meta['width'] / $size_info['width'];
								$check = 'height';
							} else {
								$ratio = $img_meta['height'] / $size_info['height'];
								$check = 'width';
							}
							$should_be = (int) round( $img_meta[$check] / $ratio );

							// allow for a +/-1 pixel difference
							if ( $img_meta['sizes'][$size_name][$check] < ( $should_be - 1 ) ||
								$img_meta['sizes'][$size_name][$check] > ( $should_be + 1 ) ) {
									$is_accurate_width = false;
									$is_accurate_height = false;
							}
						}
					}

					// depending on cropping, one or both sides of the image must be accurate
					// if not, attempt to create a resized image by calling image_make_intermediate_size()
					if ( ( empty( $size_info['crop'] ) && ( ! $is_accurate_width && ! $is_accurate_height ) ) ||
						( ! empty( $size_info['crop'] ) && ( ! $is_accurate_width || ! $is_accurate_height ) ) ) {

						if ( $this->p->debug->enabled ) {
							if ( empty( $img_meta['sizes'][$size_name] ) )
								$this->p->debug->log( $size_name.' size not defined in the image meta' );
							else $this->p->debug->log( 'image metadata ('.
								( empty( $img_meta['sizes'][$size_name]['width'] ) ? 0 : 
									$img_meta['sizes'][$size_name]['width'] ).'x'.
								( empty( $img_meta['sizes'][$size_name]['height'] ) ? 0 : 
									$img_meta['sizes'][$size_name]['height'] ).') does not match '.
								$size_name.' ('.$size_info['width'].'x'.$size_info['height'].
									( $img_cropped === 0 ? '' : ' cropped' ).')' );
						}

						$fullsizepath = get_attached_file( $pid );
						$resized = image_make_intermediate_size( $fullsizepath, 
							$size_info['width'], $size_info['height'], $size_info['crop'] );

						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'WordPress image_make_intermediate_size() reported '.
								( $resized === false ? 'failure' : 'success' ) );

						if ( $resized !== false ) {
							$img_meta['sizes'][$size_name] = $resized;
							wp_update_attachment_metadata( $pid, $img_meta );
						}
					}
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'image metadata check skipped: plugin_auto_img_resize option is disabled' );
			}

			// some image_downsize hooks may return only 3 elements, use array_pad to sanitize the returned array
			list( $img_url, $img_width, $img_height, $img_intermediate ) = apply_filters( $this->p->cf['lca'].'_image_downsize', 
				array_pad( image_downsize( $pid, ( $use_full === true ? 'full' : $size_name ) ), 4, null ), $pid, $size_name );
			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'image_downsize returned '.$img_url.' ('.$img_width.'x'.$img_height.')' );

			if ( empty( $img_url ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: image_downsize returned an empty url' );
				return self::reset_image_src_info();
			}

			$accept_img_size = apply_filters( $this->p->cf['lca'].'_attached_accept_img_size', 
				( empty( $this->p->options['plugin_ignore_small_img'] ) ? true : false ),
					$img_url, $img_width, $img_height, $size_name, $pid );

			// check for resulting image dimensions that may be too small
			if ( empty( $accept_img_size ) ) {

				$is_sufficient_w = $img_width >= $size_info['width'] ? true : false;
				$is_sufficient_h = $img_height >= $size_info['height'] ? true : false;

				$size_label = $this->p->util->get_image_size_label( $size_name );
				$msg_id = 'wp_'.$pid.'_'.$img_width.'x'.$img_height.'_'.
					$size_name.'_'.$size_info['width'].'x'.$size_info['height'].'_rejected';

				// depending on cropping, one or both sides of the image must be large enough / sufficient
				// return an empty array after showing an appropriate warning
				if ( ( empty( $size_info['crop'] ) && ( ! $is_sufficient_w && ! $is_sufficient_h ) ) ||
					( ! empty( $size_info['crop'] ) && ( ! $is_sufficient_w || ! $is_sufficient_h ) ) ) {

					if ( isset( $img_meta['width'] ) && isset( $img_meta['height'] ) &&
						$img_meta['width'] < $size_info['width'] && 
							$img_meta['height'] < $size_info['height'] ) {

						$size_text = $img_meta['width'].'x'.$img_meta['height'].' ('.
							__( 'full size original', 'nextgen-facebook' ).')';
					} else $size_text = $img_width.'x'.$img_height;

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: image ID '.$pid.' rejected - '.
							$size_text.' too small for the '.$size_name.' ('.
								$size_info['width'].'x'.$size_info['height'].
									( $img_cropped === 0 ? '' : ' cropped' ).') image size' );

					if ( is_admin() )
						$this->p->notice->err( sprintf( __( '%1$s image ID %2$s ignored &mdash; the resulting image of %3$s is too small for the required %4$s image dimensions.', 'nextgen-facebook' ), __( 'Media Library', 'nextgen-facebook' ), $pid, $size_text, '<b>'.$size_label.'</b> ('.$size_info['width'].'x'.$size_info['height'].( $img_cropped === 0 ? '' : ' <i>'.__( 'cropped', 'nextgen-facebook' ).'</i>' ).')' ).' '.$this->p->msgs->get( 'notice-image-rejected', array( 'size_label' => $size_label ) ), false, true, $msg_id, true );
					return self::reset_image_src_info();

				} elseif ( ! $this->check_image_id_min_max( $pid, $size_name, $img_width, $img_height, 
					__( 'Media Library', 'nextgen-facebook' ), $msg_id ) )
						return self::reset_image_src_info();

				elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'returned image dimensions ('.$img_width.'x'.$img_height.') are sufficient' );
			}

			if ( ! $check_dupes || $this->p->util->is_uniq_url( $img_url, $size_name ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'applying rewrite_url filter for '.$img_url );
				return self::reset_image_src_info( array( apply_filters( $this->p->cf['lca'].'_rewrite_url', $img_url ),
					$img_width, $img_height, $img_cropped, $pid ) );
			}

			return self::reset_image_src_info();
		}

		public function get_default_image( $num = 1, $size_name = 'thumbnail', $check_dupes = true, $force_regen = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'size_name' => $size_name,
					'check_dupes' => $check_dupes,
					'force_regen' => $force_regen,
				) );
			}

			$og_ret = array();
			$og_image = SucomUtil::get_mt_prop_image( 'og' );

			foreach ( array( 'id', 'id_pre', 'url', 'url:width', 'url:height' ) as $key )
				$img[$key] = empty( $this->p->options['og_def_img_'.$key] ) ?
					'' : $this->p->options['og_def_img_'.$key];

			if ( empty( $img['id'] ) && empty( $img['url'] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: no default image defined' );
				return $og_ret;
			}

			if ( ! empty( $img['id'] ) ) {
				$img['id'] = $img['id_pre'] === 'ngg' ?
					'ngg-'.$img['id'] : $img['id'];

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'using default image pid: '.$img['id'] );

				list(
					$og_image['og:image'],
					$og_image['og:image:width'],
					$og_image['og:image:height'],
					$og_image['og:image:cropped'],
					$og_image['og:image:id']
				) = $this->get_attachment_image_src( $img['id'], $size_name, $check_dupes, $force_regen );
			}

			if ( empty( $og_image['og:image'] ) && 
				! empty( $img['url'] ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'using default image url: '.$img['url'] );

				$og_image = array(
					'og:image' => $img['url'],
					'og:image:width' => $img['url:width'],
					'og:image:height' => $img['url:height'],
				);
			}

			if ( ! empty( $og_image['og:image'] ) && 
				$this->p->util->push_max( $og_ret, $og_image, $num ) )
					return $og_ret;

			return $og_ret;
		}

		public function get_content_images( $num = 0, $size_name = 'thumbnail', $mod = true, $check_dupes = true, $content = '' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'size_name' => $size_name,
					'mod' => $mod,
					'check_dupes' => $check_dupes,
					'content strlen' => strlen( $content ),
				) );
			}

			// $mod = true | false | post_id | $mod array
			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $mod );

			$og_ret = array();

			// allow custom content to be passed as argument
			// allow empty post_ids to get additional content from filter hooks
			if ( empty( $content ) ) {
				$content_provided = false;
				$content = $this->p->webpage->get_content( $mod );
			} else $content_provided = true;

			if ( empty( $content ) ) { 
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}

			$og_image = SucomUtil::get_mt_prop_image( 'og' );
			$size_info = $this->get_size_info( $size_name );
			$img_preg = $this->default_img_preg;
			// allow the html_tag and pid_attr regex to be modified
			foreach( array( 'html_tag', 'pid_attr' ) as $type ) {
				$filter_name = $this->p->cf['lca'].'_content_image_preg_'.$type;
				if ( has_filter( $filter_name ) ) {
					$img_preg[$type] = apply_filters( $filter_name, $this->default_img_preg[$type] );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'filtered image preg '.$type.' = \''.$img_preg[$type].'\'' );
				}
			}

			// img attributes in order of preference
			if ( preg_match_all( '/<(('.$img_preg['html_tag'].')[^>]*? ('.$img_preg['pid_attr'].')=[\'"]([0-9]+)[\'"]|'.
				'(img)[^>]*? (data-share-src|src)=[\'"]([^\'"]+)[\'"])[^>]*>/s', $content, $all_matches, PREG_SET_ORDER ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( count( $all_matches ).' x matching <'.$img_preg['html_tag'].'/> html tag(s) found' );

				foreach ( $all_matches as $img_num => $img_arr ) {

					$tag_value = $img_arr[0];

					if ( empty( $img_arr[5] ) ) {
						$tag_name = $img_arr[2];	// img
						$attr_name = $img_arr[3];	// data-wp-pid
						$attr_value = $img_arr[4];	// id
					} else {
						$tag_name = $img_arr[5];	// img
						$attr_name = $img_arr[6];	// data-share-src|src
						$attr_value = $img_arr[7];	// url
					}

					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'match '.$img_num.': '.$tag_name.' '.$attr_name.'="'.$attr_value.'"' );

					switch ( $attr_name ) {

						// wordpress media library image id
						case 'data-wp-pid':

							if ( $this->p->debug->enabled )
								$this->p->debug->log( 'WP image attribute ID found: '.$attr_value );

							list(
								$og_image['og:image'],
								$og_image['og:image:width'],
								$og_image['og:image:height'],
								$og_image['og:image:cropped'],
								$og_image['og:image:id']
							) = $this->get_attachment_image_src( $attr_value, $size_name, false );

							break;

						// check for other data attributes like 'data-ngg-pid'
						case ( preg_match( '/^'.$img_preg['pid_attr'].'$/', $attr_name ) ? true : false ):

							// build a filter hook for 3rd party modules to return image information
							$filter_name = $this->p->cf['lca'].'_get_content_'.
								$tag_name.'_'.( preg_replace( '/-/', '_', $attr_name ) );

							list(
								$og_image['og:image'],
								$og_image['og:image:width'],
								$og_image['og:image:height'],
								$og_image['og:image:cropped'],
								$og_image['og:image:id']
							) = apply_filters( $filter_name, array( null, null, null, null, null ),
								$attr_value, $size_name, false );

							break;

						default:
							// prevent duplicates by silently ignoring ngg images (already processed by the ngg module)
							if ( $this->p->is_avail['media']['ngg'] === true && 
								! empty( $this->p->m['media']['ngg'] ) &&
									( preg_match( '/ class=[\'"]ngg[_-]/', $tag_value ) || 
										preg_match( '/^('.$img_preg['ngg_src'].')$/', $attr_value ) ) )
											break;	// stop here

							// recognize gravatar images in the content
							if ( preg_match( '/^(https?:)?(\/\/([^\.]+\.)?gravatar\.com\/avatar\/[a-zA-Z0-9]+)/',
								$attr_value, $match ) ) {

								$og_image['og:image'] = SucomUtil::get_prot().':'.$match[2].'?s='.$size_info['width'].'&d=404&r=G';
								$og_image['og:image:width'] = $size_info['width'];
								$og_image['og:image:height'] = $size_info['width'];	// square image

								if ( $this->p->debug->enabled )
									$this->p->debug->log( 'gravatar image found: '.$og_image['og:image'] );

								break;	// stop here
							}

							// check for image ID in class for old content w/o the data-wp-pid attribute
							if ( preg_match( '/class="[^"]+ wp-image-([0-9]+)/',
								$tag_value, $match ) ) {
								list(
									$og_image['og:image'],
									$og_image['og:image:width'],
									$og_image['og:image:height'],
									$og_image['og:image:cropped'],
									$og_image['og:image:id']
								) = $this->get_attachment_image_src( $match[1], $size_name, false );
								break;	// stop here
							} else {
								$og_image = array(
									'og:image' => $attr_value,
									'og:image:width' => -1,
									'og:image:height' => -1,
								);
							}

							if ( ! empty( $og_image['og:image'] ) ) {

								foreach ( array( 'width', 'height' ) as $key )
									if ( preg_match( '/ '.$key.'=[\'"]?([0-9]+)[\'"]?/i', $tag_value, $match ) ) 
										$og_image['og:image:'.$key] = $match[1];

								// get the width and height of the image file using http / https
								if ( empty( $og_image['og:image:width'] ) || $og_image['og:image:width'] < 0 ||
									empty( $og_image['og:image:height'] ) || $og_image['og:image:height'] < 0 ) {

									$this->p->util->add_image_url_sizes( 'og:image', $og_image );
									if ( $this->p->debug->enabled )
										$this->p->debug->log( 'add_image_url_sizes() returned '.
											$og_image['og:image:width'].'x'.$og_image['og:image:height'] );
								} else {
									if ( $this->p->debug->enabled )
										$this->p->debug->log( 'width / height attribute values: '.
											$og_image['og:image:width'].'x'.$og_image['og:image:height'] );
								}
							}

							$is_sufficient_w = $og_image['og:image:width'] >= $size_info['width'] ? true : false;
							$is_sufficient_h = $og_image['og:image:height'] >= $size_info['height'] ? true : false;

							$accept_img_size = apply_filters( $this->p->cf['lca'].'_content_accept_img_size', 
								( empty( $this->p->options['plugin_ignore_small_img'] ) ? true : false ),
								$og_image['og:image'], 
								$og_image['og:image:width'], 
								$og_image['og:image:height'], 
								$size_name, $mod );

							// make sure the image width and height are large enough
							if ( ( $attr_name == 'src' && $accept_img_size ) ||
								( $attr_name == 'src' && $size_info['crop'] && 
									( $is_sufficient_w && $is_sufficient_h ) ) ||
								( $attr_name == 'src' && ! $size_info['crop'] && 
									( $is_sufficient_w || $is_sufficient_h ) ) ||
								$attr_name == 'data-share-src' ) {

								// data-share-src attribute used and/or image size is acceptable
								// check for relative urls, just in case
								$og_image['og:image'] = $this->p->util->fix_relative_url( $og_image['og:image'] );

							} else {
								if ( $this->p->debug->enabled )
									$this->p->debug->log( 'content image rejected: '.
										'width / height missing or too small for '.$size_name );
								if ( is_admin() ) {
									$short = $this->p->cf['plugin'][$this->p->cf['lca']]['short'];
									$size_label = $this->p->util->get_image_size_label( $size_name );
									$msg_id = 'content_'.$og_image['og:image'].'_'.$size_name.'_rejected';

									if ( ! $content_provided )
										$data_wp_pid_msg = ' '.sprintf( __( '%1$s includes an additional \'data-wp-pid\' attribute for Media Library images &mdash; if this image was selected from the Media Library before %2$s was first activated, try removing and adding the image back to your content.', 'nextgen-facebook' ), $short, $short );
									else $data_wp_pid_msg = '';

									$this->p->notice->err( sprintf( __( 'Image %1$s in content ignored &mdash; the image width / height is too small for the required %2$s image dimensions.', 'nextgen-facebook' ), $og_image['og:image'], '<b>'.$size_label.'</b> ('.$size_name.')' ).$data_wp_pid_msg, false, true, $msg_id, true );
								}
								$og_image = array();
							}
							break;
					}
					if ( ! empty( $og_image['og:image'] ) && 
						( $check_dupes === false || $this->p->util->is_uniq_url( $og_image['og:image'], $size_name ) ) )
							if ( $this->p->util->push_max( $og_ret, $og_image, $num ) )
								return $og_ret;
				}
				return $og_ret;
			}
			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'no matching <'.$img_preg['html_tag'].'/> html tag(s) found' );
			return $og_ret;
		}

		public function get_default_video( $num = 0, $check_dupes = true ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'check_dupes' => $check_dupes,
				) );
			}
			$og_ret = array();

			$embed_url = empty( $this->p->options['og_def_vid_url'] ) ?
				'' : $this->p->options['og_def_vid_url'];

			if ( ! empty( $embed_url ) && 
				( $check_dupes == false || $this->p->util->is_uniq_url( $embed_url, 'video' ) ) ) {	// $context = 'video'

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'using default video url = '.$embed_url );

				// fallback to video url if necessary
				$og_video = $this->get_video_info( $embed_url, 0, 0, $check_dupes, true );
				if ( ! empty( $og_video ) && 
					$this->p->util->push_max( $og_ret, $og_video, $num ) ) 
						return $og_ret;
			}
			return $og_ret;
		}

		/*
		 * Check the content for generic <iframe|embed/> html tags. Apply ngfb_content_videos filter for more specialized checks.
		 */
		public function get_content_videos( $num = 0, $mod = true, $check_dupes = true, $content = '' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->args( array(
					'num' => $num,
					'mod' => $mod,
					'check_dupes' => $check_dupes,
					'content' => strlen( $content ).' chars',
				) );
			}

			// $mod = true | false | post_id | $mod array
			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $mod );

			$og_ret = array();

			// allow custom content to be passed as an argument in $content
			// allow empty post IDs to get additional content from filter hooks
			if ( empty( $content ) ) {
				$content_provided = false;
				$content = $this->p->webpage->get_content( $mod );
			} else $content_provided = true;

			if ( empty( $content ) ) { 
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}

			// detect standard iframe/embed tags - use the ngfb_content_videos filter for additional html5/javascript methods
			// the src url must contain '/embed|embed_code|swf|video|v/' in to be recognized as an embedded video url
			if ( preg_match_all( '/<(iframe|embed)[^<>]*? src=[\'"]([^\'"<>]+\/(embed|embed_code|swf|video|v)\/[^\'"<>]+)[\'"][^<>]*>/i',
				$content, $match_all, PREG_SET_ORDER ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( count( $match_all ).' x video <iframe|embed/> html tag(s) found' );

				foreach ( $match_all as $media ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( '<'.$media[1].'/> html tag found = '.$media[2] );
					$embed_url = $media[2];
					if ( ! empty( $embed_url ) && 
						( $check_dupes == false || 
							$this->p->util->is_uniq_url( $embed_url, 'video' ) ) ) {	// $context = 'video'

						$embed_width = preg_match( '/ width=[\'"]?([0-9]+)[\'"]?/i', 
							$media[0], $match) ? $match[1] : -1;
						$embed_height = preg_match( '/ height=[\'"]?([0-9]+)[\'"]?/i', 
							$media[0], $match) ? $match[1] : -1;

						$og_video = $this->get_video_info( $embed_url, $embed_width, $embed_height, $check_dupes );

						if ( ! empty( $og_video ) && 
							$this->p->util->push_max( $og_ret, $og_video, $num ) ) 
								return $og_ret;
					}
				}
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no <iframe|embed/> html tag(s) found' );

			// additional filters / Pro modules may detect other embedded video markup
			$filter_name = $this->p->cf['lca'].'_content_videos';
			if ( has_filter( $filter_name ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'applying filter '.$filter_name ); 
				// should return an array of arrays
				if ( ( $match_all = apply_filters( $filter_name, false, $content ) ) !== false ) {
					if ( is_array( $match_all ) ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( count( $match_all ).' x videos returned by '.$filter_name.' filter' );
						foreach ( $match_all as $media ) {
							if ( ! empty( $media[0] ) && 
								( $check_dupes == false || 
									$this->p->util->is_uniq_url( $media[0], 'video' ) ) ) {	// $context = 'video'

								$og_video = $this->get_video_info( $media[0], $media[1], $media[2], $check_dupes );
								if ( ! empty( $og_video ) && 
									$this->p->util->push_max( $og_ret, $og_video, $num ) ) 
										return $og_ret;
							}
						}
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( $filter_name.' filter did not return false or an array' ); 
				}
			}

			return $og_ret;
		}

		public function get_video_info( $embed_url, $embed_width = -1, $embed_height = -1, $check_dupes = true, $fallback = false ) {
			if ( empty( $embed_url ) ) 
				return array();

			$filter_name = $this->p->cf['lca'].'_video_info';
			if ( ! has_filter( $filter_name ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: no filter(s) for '.$filter_name ); 
				return array();
			}

			$og_video = array_merge( 
				SucomUtil::get_mt_prop_video( 'og' ), 
				SucomUtil::get_mt_prop_image( 'og' ),
				array( 
					'og:video:width' => $embed_width,			// default width
					'og:video:height' => $embed_height,			// default height
				)
			);

			$og_video = apply_filters( $filter_name, $og_video, $embed_url, $embed_width, $embed_height );

			foreach ( array( 'og:video', 'og:image' ) as $prefix ) {
				$media_url = SucomUtil::get_mt_media_url( $prefix, $og_video );
				$have_media[$prefix] = empty( $media_url ) ? false : true;

				if ( ! $media_url || 
					( $check_dupes && ! $this->p->util->is_uniq_url( $media_url, 'video' ) ) ) {	// $context = 'video'
					foreach( SucomUtil::preg_grep_keys( '/^'.$prefix.'(:.*)?$/', $og_video ) as $k => $v )
						unset ( $og_video[$k] );
				} elseif ( $prefix === 'og:image' ) {
					if ( $og_video['og:image:width'] <= 0 || 
						$og_video['og:image:height'] <= 0 )
							$this->p->util->add_image_url_sizes( 'og:image', $og_video );
				}
			}

			// fallback to the original url
			if ( ! $have_media['og:video'] && $fallback ) {
				if ( ! $check_dupes || $this->p->util->is_uniq_url( $embed_url, 'video' ) ) {	// $context = 'video'
					$og_video['og:video:url'] = $embed_url;
					$have_media['og:video'] = true;
				}
			}

			if ( ! $have_media['og:video'] && 
				! $have_media['og:image'] ) 
					return array();
			else return $og_video;
		}

		public function check_image_id_min_max( $pid, $size_name, $img_width, $img_height, $media_lib_name, $msg_id ) {

			if ( $img_width > 0 && $img_height > 0 )	// just in case
				$img_ratio = $img_width >= $img_height ? 
					$img_width / $img_height : 
					$img_height / $img_width;
			else $img_ratio = 0;

			switch ( $size_name ) {
				case $this->p->cf['lca'].'-opengraph':
					$std_name = 'Facebook / Open Graph';
					$max_ratio = $this->p->cf['head']['max']['og_img_ratio'];
					$min_width = $this->p->cf['head']['min']['og_img_width'];
					$min_height = $this->p->cf['head']['min']['og_img_height'];
					break;
				case $this->p->cf['lca'].'-schema':
					$std_name = 'Google / Schema';
					$max_ratio = 0;
					$min_width = $this->p->cf['head']['min']['schema_img_width'];
					$min_height = 0;
					break;
				default:
					$max_ratio = 0;
					$min_width = 0;
					$min_height = 0;
					break;
			}

			// check the maximum image aspect ratio
			if ( $max_ratio > 0 && $img_ratio >= $max_ratio ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: image ID '.$pid.' rejected - '.
						$img_width.'x'.$img_height.' aspect ratio is equal to/or greater than '.
							$max_ratio.':1' );

				if ( is_admin() )
					$this->p->notice->err( sprintf( __( '%1$s image ID %2$s ignored &mdash; the resulting image of %3$s has an aspect ratio equal to/or greater than %4$d:1.', 'nextgen-facebook' ), $media_lib_name, $pid, $img_width.'x'.$img_height, $max_ratio ).' '.$this->p->msgs->get( 'notice-image-rejected', array( 'size_label' => $size_label ) ), false, true, $msg_id, true );

				return false;
			}

			// check the minimum image width and/or height
			if ( ( $min_width > 0 || $min_height > 0 ) &&
				( $img_width < $min_width || $img_height < $min_height ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: image ID '.$pid.' rejected - '.
						$img_width.'x'.$img_height.' smaller than minimum '.
							$min_width.'x'.$min_height.' for '.$size_name );

				if ( is_admin() )
					$this->p->notice->err( sprintf( __( '%1$s image ID %2$s ignored &mdash; the resulting image of %3$s is smaller than the minimum %4$s allowed by the %5$s standard.', 'nextgen-facebook' ), $media_lib_name, $pid, $img_width.'x'.$img_height, $min_width.'x'.$min_height, $std_name ).' '.$this->p->msgs->get( 'notice-image-rejected', array( 'size_label' => $size_label ) ), false, true, $msg_id, true );

				return false;
			}

			return true;
		}
	}
}

?>
