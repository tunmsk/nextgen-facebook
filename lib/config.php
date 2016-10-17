<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbConfig' ) ) {

	class NgfbConfig {

		public static $cf = array(
			'lca' => 'ngfb',		// lowercase acronym
			'uca' => 'NGFB',		// uppercase acronym
			'menu' => 'NGFB',		// menu item label
			'color' => 'ff6600',		// menu item color - dark orange
			'feed_cache_exp' => 86400,	// 24 hours
			'plugin' => array(
				'ngfb' => array(
					'version' => '8.36.0-1',	// plugin version
					'opt_version' => '459',		// increment when changing default options
					'short' => 'NGFB',		// short plugin name
					'name' => 'NextGEN Facebook (NGFB)',
					'desc' => 'Complete meta tags for the best looking shares on Facebook, Google, Pinterest, Twitter, etc - no matter how your webpage is shared!',
					'slug' => 'nextgen-facebook',
					'base' => 'nextgen-facebook/nextgen-facebook.php',
					'update_auth' => 'tid',
					'text_domain' => 'nextgen-facebook',
					'domain_path' => '/languages',
					'img' => array(
						'icon_small' => 'images/icon-128x128.png',
						'icon_medium' => 'images/icon-256x256.png',
						'background' => 'images/background.jpg',
					),
					'url' => array(
						// wordpress
						'download' => 'https://wordpress.org/plugins/nextgen-facebook/',
						'review' => 'https://wordpress.org/support/view/plugin-reviews/nextgen-facebook?filter=5&rate=5#postform',
						'readme' => 'https://plugins.svn.wordpress.org/nextgen-facebook/trunk/readme.txt',
						'setup' => 'https://plugins.svn.wordpress.org/nextgen-facebook/trunk/setup.html',
						'wp_support' => 'https://wordpress.org/support/plugin/nextgen-facebook',
						// surniaulula
						'update' => 'https://surniaulula.com/extend/plugins/nextgen-facebook/update/',
						'purchase' => 'https://surniaulula.com/extend/plugins/nextgen-facebook/',
						'changelog' => 'https://surniaulula.com/extend/plugins/nextgen-facebook/changelog/',
						'codex' => 'https://surniaulula.com/codex/plugins/nextgen-facebook/',
						'faq' => 'https://surniaulula.com/codex/plugins/nextgen-facebook/faq/',
						'notes' => 'https://surniaulula.com/codex/plugins/nextgen-facebook/notes/',
						'feed' => 'https://surniaulula.com/category/application/wordpress/wp-plugins/ngfb/feed/',
						'pro_support' => 'http://nextgen-facebook.support.surniaulula.com/',
					),
					'lib' => array(			// libraries
						'profile' => array (	// lib file descriptions will be translated
							'social-settings' => 'Your Social Settings',
						),
						'setting' => array (	// lib file descriptions will be translated
							'image-dimensions' => 'Social and SEO Image Dimensions',
							'social-accounts' => 'Website Social Pages and Accounts',
							'contact-fields' => 'User Profile Contact Methods',
						),
						'submenu' => array (	// lib file descriptions will be translated
							'essential' => 'Essential Settings',
							'general' => 'General Settings',
							'advanced' => 'Advanced Settings',
							'buttons' => 'Sharing Buttons',
							'styles' => 'Sharing Styles',
							'setup' => '<color>Plugin Setup Guide and Notes</color>',
							'licenses' => 'Pro Licenses and Extension Plugins',
						),
						'sitesubmenu' => array(	// lib file descriptions will be translated
							'siteadvanced' => 'Advanced Settings',
							'sitesetup' => '<color>Plugin Setup Guide and Notes</color>',
							'sitelicenses' => 'Pro Licenses and Extension Plugins',
						),
						'website' => array(
							'email' => 'Email',
							'twitter' => 'Twitter',
							'facebook' => 'Facebook', 
							'gplus' => 'GooglePlus',
							'pinterest' => 'Pinterest',
							'linkedin' => 'LinkedIn',
							'buffer' => 'Buffer',
							'reddit' => 'Reddit',
							'managewp' => 'ManageWP',
							'stumbleupon' => 'StumbleUpon',
							'tumblr' => 'Tumblr',
							'youtube' => 'YouTube',
							'skype' => 'Skype',
							'whatsapp' => 'WhatsApp',
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
						'gpl' => array(
							'admin' => array(
								'general' => 'General Settings',
								'advanced' => 'Advanced Settings',
								'sharing' => 'Sharing Settings',
								'post' => 'Post Settings',
								'meta' => 'Term and User Settings',
							),
							'ecom' => array(
								'woocommerce' => 'WooCommerce',
							),
							'forum' => array(
								'bbpress' => 'bbPress',
							),
							'social' => array(
								'buddypress' => 'BuddyPress',
							),
							'util' => array(
								'post' => 'Custom Post Meta',
								'term' => 'Custom Term Meta',
								'user' => 'Custom User Meta',
							),
						),
						'pro' => array(
							'admin' => array(
								'general' => 'General Settings',
								'advanced' => 'Advanced Settings',
								'sharing' => 'Sharing Settings',
								'post' => 'Post Settings',
								'meta' => 'Term and User Settings',
							),
							'ecom' => array(
								'edd' => '(plugin) Easy Digital Downloads',
								'marketpress' => '(plugin) MarketPress',
								'woocommerce' => '(plugin) WooCommerce',
								'wpecommerce' => '(plugin) WP eCommerce',
								'yotpowc' => '(plugin) Yotpo Social Reviews for WooCommerce',
							),
							'event' => array(
								'tribe_events' => '(plugin) The Events Calendar',
							),
							'forum' => array(
								'bbpress' => '(plugin) bbPress',
							),
							'lang' => array(
								'polylang' => '(plugin) Polylang',
							),
							'media' => array(
								'facebook' => '(api) Facebook Video API',
								'gravatar' => '(api) Author Gravatar Image',
								'ngg' => '(plugin) NextGEN Gallery',
								'rtmedia' => '(plugin) rtMedia for WordPress, BuddyPress and bbPress',
								'slideshare' => '(api) Slideshare API',
								'upscale' => '(tool) WP Media Library Image Upscaling',
								'vimeo' => '(api) Vimeo Video API',
								'wistia' => '(api) Wistia Video API',
								'youtube' => '(api) YouTube Video / Playlist API',
							),
							'seo' => array(
								'aioseop' => '(plugin) All in One SEO Pack',
								'autodescription' => '(plugin) The SEO Framework',
								'headspace2' => '(plugin) HeadSpace2 SEO',
								'wpseo' => '(plugin) Yoast SEO',
							),
							'social' => array(
								'buddypress' => '(plugin) BuddyPress',
							),
							'util' => array(
								'checkimgdims' => '(tool) Verify Image Dimensions',
								'coauthors' => '(plugin) Co-Authors Plus',
								'language' => '(tool) WP Locale to Publisher Language Mapping',
								'shorten' => '(api) URL Shortening Service APIs',
								'post' => '(tool) Custom Post Meta',
								'restapi' => '(plugin) WordPress REST API (Version 2)',
								'term' => '(tool) Custom Term Meta',
								'user' => '(tool) Custom User Meta',
							),
						),
					),
				),
				'ngfbum' => array(
					'short' => 'NGFB UM',
					'name' => 'NextGEN Facebook (NGFB) Pro Update Manager',
					'desc' => 'NGFB extension to provide updates for the NextGEN Facebook (NGFB) Pro plugin and its Pro extensions.',
					'slug' => 'nextgen-facebook-um',
					'base' => 'nextgen-facebook-um/nextgen-facebook-um.php',
					'update_auth' => '',
					'img' => array(
						'icon_small' => 'https://surniaulula.github.io/nextgen-facebook-um/assets/icon-128x128.png',
						'icon_medium' => 'https://surniaulula.github.io/nextgen-facebook-um/assets/icon-256x256.png',
					),
					'url' => array(
						// github
						'readme' => 'https://raw.githubusercontent.com/SurniaUlula/nextgen-facebook-um/master/readme.txt',
						// surniaulula
						'download' => 'https://surniaulula.com/extend/plugins/nextgen-facebook-um/',
						'latest_zip' => 'https://surniaulula.com/extend/plugins/nextgen-facebook-um/latest/',
						'review' => '',
						'wp_support' => '',
						'update' => 'https://surniaulula.com/extend/plugins/nextgen-facebook-um/update/',
						'purchase' => '',
						'changelog' => 'https://surniaulula.com/extend/plugins/nextgen-facebook-um/changelog/',
						'codex' => 'https://surniaulula.com/codex/plugins/nextgen-facebook-um/',
						'faq' => 'https://surniaulula.com/codex/plugins/nextgen-facebook-um/faq/',
						'notes' => 'https://surniaulula.com/codex/plugins/nextgen-facebook-um/notes/',
						'feed' => 'https://surniaulula.com/category/application/wordpress/wp-plugins/nextgen-facebook-um/feed/',
						'pro_support' => '',
					),
				),
			),
			'opt' => array(						// options
				'defaults' => array(
					'options_filtered' => false,
					'schema_add_noscript' => 1,
					'schema_website_json' => 1,
					'schema_organization_json' => 1,
					'schema_person_json' => 0,
					'schema_person_id' => '',
					'schema_alt_name' => '',
					'schema_logo_url' => '',
					'schema_banner_url' => '',
					'schema_desc_len' => 250,			// meta itemprop="description" maximum text length (hard limit)
					// standard types
					'schema_type_for_archive_page' => 'webpage.collection',
					'schema_type_for_attachment' => 'webpage',
					'schema_type_for_home_page' => 'website',
					'schema_type_for_page' => 'webpage',		// article would be a good default, if the requirements weren't so strict
					'schema_type_for_post' => 'blog.posting',
					'schema_type_for_search_page' => 'webpage.search.results',
					'schema_type_for_user_page' => 'webpage.profile',
					// custom post types
					'schema_type_for_article' => 'article',
					'schema_type_for_book' => 'book',
					'schema_type_for_blog' => 'blog',
					'schema_type_for_business' => 'local.business',
					'schema_type_for_download' => 'product',
					'schema_type_for_event' => 'event',
					'schema_type_for_organization' => 'organization',
					'schema_type_for_other' => 'other',
					'schema_type_for_person' => 'person',
					'schema_type_for_place' => 'place',
					'schema_type_for_product' => 'product',
					'schema_type_for_recipe' => 'recipe',
					'schema_type_for_review' => 'review',
					'schema_type_for_tribe_events' => 'event',
					'schema_type_for_webpage' => 'webpage',
					'schema_type_for_website' => 'website',
					'schema_author_name' => 'display_name',
					'schema_img_max' => 1,
					'schema_img_width' => 800,		// must be at least 696px for Articles
					'schema_img_height' => 1600,
					'schema_img_crop' => 0,
					'schema_img_crop_x' => 'center',
					'schema_img_crop_y' => 'center',
					'seo_desc_len' => 156,			// meta name="description" maximum text length (hard limit)
					'seo_author_field' => '',		// default value set by NgfbOptions::get_defaults()
					'seo_publisher_url' => '',		// (localized)
					'fb_publisher_url' => '',		// (localized)
					'fb_app_id' => '',
					'fb_admins' => '',
					'fb_author_name' => 'display_name',
					'fb_lang' => 'en_US',
					'instgram_publisher_url' => '',		// (localized)
					'linkedin_publisher_url' => '',		// (localized)
					'myspace_publisher_url' => '',		// (localized)
					'og_site_name' => '',			// (localized)
					'og_site_description' => '',		// (localized)
					'og_art_section' => 'none',
					'og_img_width' => 600,
					'og_img_height' => 315,
					'og_img_crop' => 1,
					'og_img_crop_x' => 'center',
					'og_img_crop_y' => 'center',
					'og_img_max' => 1,
					'og_vid_max' => 1,
					'og_vid_https' => 1,
					'og_vid_autoplay' => 1,
					'og_vid_prev_img' => 0,
					'og_vid_html_type' => 1,
					'og_def_img_id_pre' => 'wp',
					'og_def_img_id' => '',
					'og_def_img_url' => '',
					'og_def_img_on_index' => 1,
					'og_def_img_on_search' => 0,
					'og_def_vid_url' => '',
					'og_def_vid_on_index' => 1,
					'og_def_vid_on_search' => 0,
					'og_ngg_tags' => 0,
					'og_page_parent_tags' => 0,
					'og_page_title_tag' => 0,
					'og_author_field' => '',		// default value set by NgfbOptions::get_defaults()
					'og_author_fallback' => 0,
					'og_title_sep' => '-',
					'og_title_len' => 70,
					'og_desc_len' => 300,			// maximum length in characters (hard limit)
					'og_desc_warn' => 200,			// recommended maximum length in characters for Facebook (soft limit)
					'og_desc_hashtags' => 3,
					'rp_publisher_url' => '',		// (localized)
					'rp_author_name' => 'display_name',	// rich-pin specific article:author
					'rp_img_width' => 800,
					'rp_img_height' => 1600,
					'rp_img_crop' => 0,
					'rp_img_crop_x' => 'center',
					'rp_img_crop_y' => 'center',
					'rp_dom_verify' => '',
					'tc_site' => '',			// Twitter Business @username (localized)
					'tc_desc_len' => 200,			// Maximum Description Length (hard limit)
					'tc_type_post' => 'summary_large_image',
					'tc_type_default' => 'summary',
					// summary card
					'tc_sum_width' => 600,			// Summary Card Image Dimensions
					'tc_sum_height' => 600,
					'tc_sum_crop' => 1,
					'tc_sum_crop_x' => 'center',
					'tc_sum_crop_y' => 'center',
					// large image summary card
					'tc_lrgimg_width' => 800,		// Large Image Card Img Dimensions
					'tc_lrgimg_height' => 1600,
					'tc_lrgimg_crop' => 0,
					'tc_lrgimg_crop_x' => 'center',
					'tc_lrgimg_crop_y' => 'center',
					// enable/disable header html tags
					'add_link_rel_author' => 1,
					'add_link_rel_publisher' => 1,
					'add_link_rel_shortlink' => 1,
					// facebook
					'add_meta_property_fb:admins' => 1,
					'add_meta_property_fb:app_id' => 1,
					// open graph
					'add_meta_property_og:locale' => 1,
					'add_meta_property_og:site_name' => 1,
					'add_meta_property_og:description' => 1,
					'add_meta_property_og:title' => 1,
					'add_meta_property_og:type' => 1,
					'add_meta_property_og:url' => 1,
					'add_meta_property_og:image:secure_url' => 1,
					'add_meta_property_og:image' => 1,
					'add_meta_property_og:image:width' => 1,
					'add_meta_property_og:image:height' => 1,
					'add_meta_property_og:video:secure_url' => 1,
					'add_meta_property_og:video:url' => 1,
					'add_meta_property_og:video:type' => 1,
					'add_meta_property_og:video:width' => 1,
					'add_meta_property_og:video:height' => 1,
					'add_meta_property_og:video:tag' => 1,
					'add_meta_property_og:altitude' => 1,
					'add_meta_property_og:latitude' => 1,
					'add_meta_property_og:longitude' => 1,
					// open graph (article)
					'add_meta_property_article:author' => 1,
					'add_meta_property_article:publisher' => 1,
					'add_meta_property_article:published_time' => 1,
					'add_meta_property_article:modified_time' => 1,
					'add_meta_property_article:section' => 1,
					'add_meta_property_article:tag' => 1,
					// open graph (book)
					'add_meta_property_book:author' => 1,
					'add_meta_property_book:isbn' => 1,
					'add_meta_property_book:release_date' => 1,
					'add_meta_property_book:tag' => 1,
					// open graph (music)
					'add_meta_property_music:album' => 1,
					'add_meta_property_music:album:disc' => 1,
					'add_meta_property_music:album:track' => 1,
					'add_meta_property_music:creator' => 1,
					'add_meta_property_music:duration' => 1,
					'add_meta_property_music:musician' => 1,
					'add_meta_property_music:release_date' => 1,
					'add_meta_property_music:song' => 1,
					'add_meta_property_music:song:disc' => 1,
					'add_meta_property_music:song:track' => 1,
					// open graph (place)
					'add_meta_property_place:location:altitude' => 1,
					'add_meta_property_place:location:latitude' => 1,
					'add_meta_property_place:location:longitude' => 1,
					'add_meta_property_place:street_address' => 1,
					'add_meta_property_place:locality' => 1,
					'add_meta_property_place:region' => 1,
					'add_meta_property_place:postal_code' => 1,
					'add_meta_property_place:country_name' => 1,
					// open graph (product)
					'add_meta_property_product:availability' => 1,
					'add_meta_property_product:price:amount' => 1,
					'add_meta_property_product:price:currency' => 1,
					// open graph (profile)
					'add_meta_property_profile:first_name' => 1,
					'add_meta_property_profile:last_name' => 1,
					'add_meta_property_profile:username' => 1,
					'add_meta_property_profile:gender' => 1,
					// open graph (video)
					'add_meta_property_video:actor' => 1,
					'add_meta_property_video:actor:role' => 1,
					'add_meta_property_video:director' => 1,
					'add_meta_property_video:writer' => 1,
					'add_meta_property_video:duration' => 1,
					'add_meta_property_video:release_date' => 1,
					'add_meta_property_video:tag' => 1,
					'add_meta_property_video:series' => 1,
					// seo
					'add_meta_name_author' => 1,
					'add_meta_name_canonical' => 0,
					'add_meta_name_description' => 1,
					'add_meta_name_generator' => 1,
					// pinterest
					'add_meta_name_p:domain_verify' => 1,
					// weibo
					'add_meta_name_weibo:article:create_at' => 1,
					'add_meta_name_weibo:article:update_at' => 1,
					// twitter cards
					'add_meta_name_twitter:card' => 1,
					'add_meta_name_twitter:creator' => 1,
					'add_meta_name_twitter:domain' => 1,
					'add_meta_name_twitter:site' => 1,
					'add_meta_name_twitter:title' => 1,
					'add_meta_name_twitter:description' => 1,
					'add_meta_name_twitter:image' => 1,
					'add_meta_name_twitter:image:width' => 1,
					'add_meta_name_twitter:image:height' => 1,
					'add_meta_name_twitter:player' => 1,
					'add_meta_name_twitter:player:stream' => 1,
					'add_meta_name_twitter:player:stream:content_type' => 1,
					'add_meta_name_twitter:player:width' => 1,
					'add_meta_name_twitter:player:height' => 1,
					// schema
					'add_meta_itemprop_name' => 1,
					'add_meta_itemprop_datepublished' => 1,
					'add_meta_itemprop_datemodified' => 1,
					'add_meta_itemprop_description' => 1,
					'add_meta_itemprop_url' => 1,
					'add_meta_itemprop_image' => 1,
					'add_meta_itemprop_image.url' => 1,
					'add_meta_itemprop_image.width' => 1,
					'add_meta_itemprop_image.height' => 1,
					'add_meta_itemprop_publisher.name' => 1,
					'add_meta_itemprop_author.url' => 1,
					'add_meta_itemprop_author.name' => 1,
					'add_meta_itemprop_author.description' => 1,
					'add_meta_itemprop_author.image' => 1,
					'add_meta_itemprop_contributor.url' => 1,
					'add_meta_itemprop_contributor.name' => 1,
					'add_meta_itemprop_contributor.description' => 1,
					'add_meta_itemprop_contributor.image' => 1,
					'add_meta_itemprop_address' => 1,
					'add_meta_itemprop_openinghoursspecification.dayofweek' => 1,
					'add_meta_itemprop_openinghoursspecification.opens' => 1,
					'add_meta_itemprop_openinghoursspecification.closes' => 1,
					'add_meta_itemprop_openinghoursspecification.validfrom' => 1,
					'add_meta_itemprop_openinghoursspecification.validthrough' => 1,
					'add_meta_itemprop_menu' => 1,
					'add_meta_itemprop_acceptsreservations' => 1,
					'add_meta_itemprop_ratingvalue' => 1,	// Schema AggregateRating
					'add_meta_itemprop_ratingcount' => 1,	// Schema AggregateRating
					'add_meta_itemprop_worstrating' => 1,	// Schema AggregateRating
					'add_meta_itemprop_bestrating' => 1,	// Schema AggregateRating
					'add_meta_itemprop_reviewcount' => 1,	// Schema AggregateRating
					'add_meta_itemprop_startdate' => 1,	// Schema Event
					'add_meta_itemprop_enddate' => 1,	// Schema Event
					'add_meta_itemprop_location' => 1,	// Schema Event
					'add_meta_itemprop_totaltime' => 1,	// Schema Recipe
					'add_meta_itemprop_recipeyield' => 1,	// Schema Recipe
					'add_meta_itemprop_ingredients' => 1,	// Schema Recipe
					/*
					 * Advanced Settings
					 */
					// Plugin Settings Tab
					'plugin_clear_on_save' => 1,			// Clear All Cache(s) on Save Settings
					'plugin_preserve' => 0,				// Preserve Settings on Uninstall
					'plugin_debug' => 0,				// Add Hidden Debug Messages
					'plugin_hide_pro' => 0,				// Hide All Pro Version Options
					'plugin_show_opts' => 'basic',			// Options to Show by Default
					// Content and Filters Tab
					'plugin_filter_title' => 0,			// Use Filtered (SEO) Title
					'plugin_filter_content' => 0,			// Apply WordPress Content Filters
					'plugin_filter_excerpt' => 0,			// Apply WordPress Excerpt Filters
					'plugin_p_strip' => 0,				// Content Starts at 1st Paragraph
					'plugin_use_img_alt' => 1,			// Use Image Alt if No Content
					'plugin_img_alt_prefix' => 'Image:',		// Image Alt Text Prefix
					'plugin_p_cap_prefix' => 'Caption:',		// WP Caption Prefix
					'plugin_content_img_max' => 5,			// Maximum Images from Content
					'plugin_content_vid_max' => 5,			// Maximum Videos from Content
					'plugin_gravatar_api' => 1,			// Include Author Gravatar Image
					'plugin_facebook_api' => 1,			// Check for Embedded Media from: Facebook
					'plugin_slideshare_api' => 1,			// Check for Embedded Media from: Slideshare
					'plugin_vimeo_api' => 1,			// Check for Embedded Media from: Vimeo
					'plugin_wistia_api' => 1,			// Check for Embedded Media from: Wistia
					'plugin_youtube_api' => 1,			// Check for Embedded Media from: Youtube
					// WP / Theme Integration Tab
					'plugin_html_attr_filter_name' => 'language_attributes',
					'plugin_html_attr_filter_prio' => 100,
					'plugin_head_attr_filter_name' => 'head_attributes',
					'plugin_head_attr_filter_prio' => 100,
					'plugin_check_head' => 1,			// Check for Duplicate Meta Tags
					'plugin_filter_lang' => 1,			// Use WP Locale for Language
					'plugin_auto_img_resize' => 1,			// Create Missing WP Media Sizes
					'plugin_check_img_dims' => 0,			// Enforce Image Dimensions Check
					'plugin_upscale_images' => 0,			// Allow Upscale of Smaller Images
					'plugin_upscale_img_max' => 33,			// Maximum Image Upscale Percent
					'plugin_shortcodes' => 1,			// Enable Plugin Shortcode(s)
					'plugin_widgets' => 1,				// Enable Plugin Widget(s)
					'plugin_page_excerpt' => 0,			// Enable WP Excerpt for Pages
					'plugin_page_tags' => 0,			// Enable WP Tags for Pages
					// Social Settings Meta Tab
					'plugin_og_img_col_post' => 1,
					'plugin_og_img_col_term' => 1,
					'plugin_og_img_col_user' => 1,
					'plugin_og_desc_col_post' => 0,
					'plugin_og_desc_col_term' => 0,
					'plugin_og_desc_col_user' => 0,
					'plugin_add_to_attachment' => 1,
					'plugin_add_to_page' => 1,
					'plugin_add_to_post' => 1,
					'plugin_add_to_product' => 1,
					'plugin_add_to_reply' => 0,	// bbpress
					'plugin_add_to_term' => 1,
					'plugin_add_to_topic' => 0,	// bbpress
					'plugin_add_to_user' => 1,
					'plugin_cf_img_url' => '_format_image_url',
					'plugin_cf_vid_url' => '_format_video_url',
					'plugin_cf_vid_embed' => '_format_video_embed',
					'plugin_cf_recipe_ingredients' => '_recipe_ingredients',
					// File and Object Cache Tab
					'plugin_object_cache_exp' => 259200,		// Object Cache Expiry (259200 secs = 3 days)
					'plugin_verify_certs' => 0,			// Verify SSL Certificates
					'plugin_cache_info' => 0,			// Report Cache Purge Count
					'plugin_file_cache_exp' => 0,			// File Cache Expiry
					// Service API Keys Tab
					'plugin_shortener' => 'none',
					'plugin_shortlink' => 1,			// Use Shortnened URL for WP Shortlink
					'plugin_min_shorten' => 23,
					'plugin_bitly_login' => '',
					'plugin_bitly_token' => '',
					'plugin_bitly_api_key' => '',
					'plugin_google_api_key' => '',
					'plugin_google_shorten' => 0,
					'plugin_owly_api_key' => '',
					'plugin_yourls_api_url' => '',
					'plugin_yourls_username' => '',
					'plugin_yourls_password' => '',
					'plugin_yourls_token' => '',
					// Contact Field Names and Labels
					'plugin_cm_fb_name' => 'facebook', 
					'plugin_cm_fb_label' => 'Facebook URL', 
					'plugin_cm_fb_enabled' => 1,
					'plugin_cm_gp_name' => 'gplus', 
					'plugin_cm_gp_label' => 'Google+ URL', 
					'plugin_cm_gp_enabled' => 1,
					'plugin_cm_instgram_name' => 'instagram', 
					'plugin_cm_instgram_label' => 'Instagram URL', 
					'plugin_cm_instgram_enabled' => 1,
					'plugin_cm_linkedin_name' => 'linkedin', 
					'plugin_cm_linkedin_label' => 'LinkedIn URL', 
					'plugin_cm_linkedin_enabled' => 1,
					'plugin_cm_myspace_name' => 'myspace', 
					'plugin_cm_myspace_label' => 'MySpace URL', 
					'plugin_cm_myspace_enabled' => 1,
					'plugin_cm_pin_name' => 'pinterest', 
					'plugin_cm_pin_label' => 'Pinterest URL', 
					'plugin_cm_pin_enabled' => 1,
					'plugin_cm_tumblr_name' => 'tumblr', 
					'plugin_cm_tumblr_label' => 'Tumblr URL', 
					'plugin_cm_tumblr_enabled' => 1,
					'plugin_cm_twitter_name' => 'twitter', 
					'plugin_cm_twitter_label' => 'Twitter @username', 
					'plugin_cm_twitter_enabled' => 1,
					'plugin_cm_yt_name' => 'youtube', 
					'plugin_cm_yt_label' => 'YouTube Channel URL', 
					'plugin_cm_yt_enabled' => 1,
					'plugin_cm_skype_name' => 'skype', 
					'plugin_cm_skype_label' => 'Skype Username', 
					'plugin_cm_skype_enabled' => 1,
					'wp_cm_aim_name' => 'aim', 
					'wp_cm_aim_label' => 'AIM', 
					'wp_cm_aim_enabled' => 1,
					'wp_cm_jabber_name' => 'jabber', 
					'wp_cm_jabber_label' => 'Google Talk', 
					'wp_cm_jabber_enabled' => 1,
					'wp_cm_yim_name' => 'yim',
					'wp_cm_yim_label' => 'Yahoo IM', 
					'wp_cm_yim_enabled' => 1,
					// Pro Licenses and Extension Plugins
					'plugin_ngfb_tid' => '',
				),
				'site_defaults' => array(
					'options_filtered' => false,
					/*
					 * Advanced Settings
					 */
					// Plugin Settings Tab
					'plugin_clear_on_save' => 1,			// Clear All Cache(s) on Save Settings
					'plugin_clear_on_save:use' => 'default',
					'plugin_preserve' => 0,				// Preserve Settings on Uninstall
					'plugin_preserve:use' => 'default',
					'plugin_debug' => 0,				// Add Hidden Debug Messages
					'plugin_debug:use' => 'default',
					'plugin_hide_pro' => 0,				// Hide All Pro Version Options
					'plugin_hide_pro:use' => 'default',
					'plugin_show_opts' => 'basic',			// Options to Show by Default
					'plugin_show_opts:use' => 'default',
					// Content and Filters Tab
					// Social Settings Tab
					// WP / Theme Integration Tab
					'plugin_check_head' => 1,			// Check for Duplicate Meta Tags
					'plugin_check_head:use' => 'default',
					'plugin_filter_lang' => 1,			// Use WP Locale for Language
					'plugin_filter_lang:use' => 'default',
					'plugin_auto_img_resize' => 1,			// Recreate Missing WP Media Sizes
					'plugin_auto_img_resize:use' => 'default',
					'plugin_check_img_dims' => 0,			// Enforce Image Dimensions Check
					'plugin_check_img_dims:use' => 'default',
					'plugin_upscale_images' => 0,			// Allow Upscale of Smaller Images
					'plugin_upscale_images:use' => 'default',
					'plugin_upscale_img_max' => 33,			// Maximum Image Upscale Percent
					'plugin_upscale_img_max:use' => 'default',
					'plugin_shortcodes' => 1,			// Enable Plugin Shortcode(s)
					'plugin_shortcodes:use' => 'default',
					'plugin_widgets' => 1,				// Enable Plugin Widget(s)
					'plugin_widgets:use' => 'default',
					'plugin_page_excerpt' => 0,			// Enable WP Excerpt for Pages
					'plugin_page_excerpt:use' => 'default',
					'plugin_page_tags' => 0,			// Enable WP Tags for Pages
					'plugin_page_tags:use' => 'default',
					// File and Object Cache Tab
					'plugin_object_cache_exp' => 259200,		// Object Cache Expiry (259200 secs = 3 days)
					'plugin_object_cache_exp:use' => 'default',
					'plugin_verify_certs' => 0,			// Verify SSL Certificates
					'plugin_verify_certs:use' => 'default',
					'plugin_cache_info' => 0,			// Report Cache Purge Count
					'plugin_cache_info:use' => 'default',
					'plugin_file_cache_exp' => 0,			// File Cache Expiry
					'plugin_file_cache_exp:use' => 'default',
					// Pro Licenses and Extension Plugins
					'plugin_ngfb_tid' => '',
					'plugin_ngfb_tid:use' => 'default',
				),
				'preset' => array(
					'small_share_count' => array(
						'twitter_size' => 'medium',
						'twitter_count' => 'horizontal',
						'fb_button' => 'share',
						'fb_send' => 0,
						'fb_show_faces' => 0,
						'fb_action' => 'like',
						'fb_type' => 'button_count',
						'gp_action' => 'share',
						'gp_size' => 'medium',
						'gp_annotation' => 'bubble',
						'gp_expandto' => '',
						'linkedin_counter' => 'right',
						'linkedin_showzero' => 1,
						'pin_button_shape' => 'rect',
						'pin_button_height' => 'small',
						'pin_count_layout' => 'beside',
						'buffer_count' => 'horizontal',
						'reddit_type' => 'static-wide',
						'managewp_type' => 'small',
						'stumble_badge' => 1,
						'tumblr_counter' => 'right',
					),
					'large_share_vertical' => array(
						'twitter_size' => 'medium',
						'twitter_count' => 'vertical',
						'fb_button' => 'share',
						'fb_send' => 0,
						'fb_show_faces' => 0,
						'fb_action' => 'like',
						'fb_type' => 'box_count',
						'fb_layout' => 'box_count',
						'gp_action' => 'share',
						'gp_size' => 'tall',
						'gp_annotation' => 'vertical-bubble',
						'gp_expandto' => '',
						'linkedin_counter' => 'top',
						'linkedin_showzero' => '1',
						'pin_button_shape' => 'rect',
						'pin_button_height' => 'large',
						'pin_count_layout' => 'above',
						'buffer_count' => 'vertical',
						'reddit_type' => 'static-tall-text',
						'managewp_type' => 'big',
						'stumble_badge' => 5,
						'tumblr_counter' => 'top',
					),
				),
				'pre' => array(
					'email' => 'email', 
					'facebook' => 'fb', 
					'gplus' => 'gp',
					'twitter' => 'twitter',
					'instagram' => 'instgram',
					'linkedin' => 'linkedin',
					'myspace' => 'myspace',
					'pinterest' => 'pin',
					'pocket' => 'pocket',
					'buffer' => 'buffer',
					'reddit' => 'reddit',
					'managewp' => 'managewp',
					'stumbleupon' => 'stumble',
					'tumblr' => 'tumblr',
					'youtube' => 'yt',
					'skype' => 'skype',
					'vk' => 'vk',
					'whatsapp' => 'wa',
				),
			),
			'wp' => array(				// wordpress
				'min_version' => '3.0',		// minimum wordpress version
				'cm' => array(
					'aim' => 'AIM',
					'jabber' => 'Google Talk',
					'yim' => 'Yahoo IM',
				),
				'admin' => array(
					'users' => array(
						'page' => 'users.php',
						'cap' => 'list_users',
					),
					'profile' => array(
						'page' => 'profile.php',
						'cap' => 'edit_posts',
					),
					'setting' => array(
						'page' => 'options-general.php',
						'cap' => 'manage_options',
					),
					'submenu' => array(
						'page' => 'admin.php',
						'cap' => 'manage_options',
					),
					'sitesubmenu' => array(
						'page' => 'admin.php',
						'cap' => 'manage_options',
					),
				),
			),
			'php' => array(				// php
				'min_version' => '5.2.0',	// minimum php version
			),
			'form' => array(
				'og_img_col_width' => '70px',
				'og_img_col_height' => '37px',
				'tooltip_class' => 'sucom_tooltip',
				'max_hashtags' => 10,
				'max_media_items' => 20,
				'yes_no' => array(
					'1' => 'Yes',
					'0' => 'No',
				),
				'weekdays' => array(
					'sunday' => 'Sunday',
					'monday' => 'Monday',
					'tuesday' => 'Tuesday',
					'wednesday' => 'Wednesday',
					'thursday' => 'Thursday',
					'friday' => 'Friday',
					'saturday' => 'Saturday',
					'publicholidays' => 'Public Holidays',
				),
				'time_by_name' => array(
					'hour' => 3600,
					'day' => 86400,
					'week' => 604800,
					'month' => 18144000,
				),
				// to use in a form select, make sure you declare the array as associative
				'file_cache_hrs' => array(
					0 => 0,
					3600 => 1,
					7200 => 3,
					21600 => 6,
					32400 => 9,
					43200 => 12,
					86400 => 24,
					129600 => 36,
					172800 => 48,
					259200 => 72,
					604800 => 168,
				),
				'qualifiers' => array(
					'default' => '(default)',
					'no_images' => '(no images)',
					'no_videos' => '(no videos)',
					'settings' => '(settings value)',
					'disabled' => '(option disabled)',
				),
				'script_locations' => array(
					'none' => '[None]',
					'header' => 'Header',
					'footer' => 'Footer',
				),
				'caption_types' => array(
					'none' => '[None]',
					'title' => 'Title Only',
					'excerpt' => 'Excerpt Only',
					'both' => 'Title and Excerpt',
				),
				'user_name_fields' => array(
					'none' => '[None]',
					'fullname' => 'First and Last Names',
					'display_name' => 'Display Name',
					'nickname' => 'Nickname',
				),
				'show_options' => array(
					'basic' => 'Basic Options',
					'all' => 'All Options',
				),
				'site_option_use' => array(
					'default' => 'New activation',
					'empty' => 'If value is empty',
					'force' => 'Force this value',
				),
				'position_crop_x' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
				'position_crop_y' => array(
					'top' => 'Top',
					'center' => 'Center',
					'bottom' => 'Bottom',
				),
				// shortener key is also its filename under lib/pro/ext/
				'shorteners' => array(
					'none' => '[None]',
					'bitly' => 'Bitly (suggested)',
					'googl' => 'Google',
					'owly' => 'Ow.ly',
					'tinyurl' => 'TinyURL',
					'yourls' => 'YOURLS',
				),
				// social account keys and labels for organization sameas
				'social_accounts' => array(
					'fb_publisher_url' => 'Facebook Business Page URL',
					'instgram_publisher_url' => 'Instagram Business URL',
					'linkedin_publisher_url' => 'LinkedIn Company Page URL',
					'myspace_publisher_url' => 'MySpace Business Page URL',
					'rp_publisher_url' => 'Pinterest Company Page URL',
					'seo_publisher_url' => 'Google+ Business Page URL',
					'tc_site' => 'Twitter Business @username',
				),
			),
			'head' => array(
				'limit_min' => array(
					'og_desc_len' => 156,
					'og_img_width' => 200,		// https://developers.facebook.com/docs/sharing/best-practices
					'og_img_height' => 200,
					'schema_img_width' => 400,	// https://developers.google.com/+/web/snippet/article-rendering
					'schema_img_height' => 160,
					'article_img_width' => 696,	// https://developers.google.com/search/docs/data-types/articles
					'article_img_height' => 279,	// based on the max image ratio
				),
				'limit_max' => array(
					'og_img_ratio' => 3,
					'schema_img_ratio' => 2.5,	// https://developers.google.com/+/web/snippet/article-rendering
					'article_img_ratio' => 2.5,
				),
				'og_type_ns' => array(		// http://ogp.me/#types
					'article' => 'http://ogp.me/ns/article#',
					'book' => 'http://ogp.me/ns/book#',
					'music.album' => 'http://ogp.me/ns/music#',
					'music.playlist' => 'http://ogp.me/ns/music#',
					'music.radio_station' => 'http://ogp.me/ns/music#',
					'music.song' => 'http://ogp.me/ns/music#',
					'place' => 'http://ogp.me/ns/place#',		// for Facebook and Pinterest
					'product' => 'http://ogp.me/ns/product#',	// for Facebook and Pinterest
					'profile' => 'http://ogp.me/ns/profile#',
					'video.episode' => 'http://ogp.me/ns/video#',
					'video.movie' => 'http://ogp.me/ns/video#',
					'video.other' => 'http://ogp.me/ns/video#',
					'video.tv_show' => 'http://ogp.me/ns/video#',
					'website' => 'http://ogp.me/ns/website#',
				),
				'og_type_mt' => array(
					'article' => array(
						'article:author',
						'article:section',
						'article:tag',
						'article:published_time',
						'article:modified_time',
						'article:expiration_time',
					),
					'book' => array(
						'book:author',
						'book:isbn',
						'book:release_date',
						'book:tag',
					),
					'music.album' => array(
						'music:song',
						'music:song:disc',
						'music:song:track',
						'music:musician',
						'music:release_date',
					),
					'music.playlist' => array(
						'music:creator',
						'music:song',
						'music:song:disc',
						'music:song:track',
					),
					'music.radio_station' => array(
						'music:creator',
					),
					'music.song' => array(
						'music:album',
						'music:album:disc',
						'music:album:track',
						'music:duration',
						'music:musician',
					),
					'place' => array(
						'place:location:latitude',
						'place:location:longitude',
						'place:location:altitude',
						'place:street_address',
						'place:locality',
						'place:region',
						'place:postal_code',
						'place:country_name',
					),
					'product' => array(
						'product:availability',
						'product:price:amount',
						'product:price:currency',
					),
					'profile' => array(
						'profile:first_name',
						'profile:last_name',
						'profile:username',
						'profile:gender',
					),
					'video.episode' => array(
						'video:actor',
						'video:actor:role',
						'video:director',
						'video:writer',
						'video:duration',
						'video:release_date',
						'video:tag',
						'video:series',
					),
					'video.movie' => array(
						'video:actor',
						'video:actor:role',
						'video:director',
						'video:writer',
						'video:duration',
						'video:release_date',
						'video:tag',
					),
					'video.other' => array(
						'video:actor',
						'video:actor:role',
						'video:director',
						'video:writer',
						'video:duration',
						'video:release_date',
						'video:tag',
					),
					'video.tv_show' => array(
						'video:actor',
						'video:actor:role',
						'video:director',
						'video:writer',
						'video:duration',
						'video:release_date',
						'video:tag',
					),
				),
				/*
				 * NgfbSchema::get_schema_types() flattens the array, so AVOID DUPLICATE KEY NAMES.
				 *
				 * https URLs are preferred - for more info, see https://schema.org/docs/faq.html#19:
				 *
				 * Q: Should we write 'https://schema.org' or 'http://schema.org' in our markup?
				 * A: There is a general trend towards using 'https' more widely, and you can
				 *    already write 'https://schema.org' in your structured data. Over time we will
				 *    migrate the schema.org site itself towards using https: as the default
				 *    version of the site and our preferred form in examples.
				 */
				'schema_type' => array(
					'creative.work' => array(
						'article' => array( 
							'article' => 'https://schema.org/Article',
							'article.news' => 'https://schema.org/NewsArticle',
							'article.tech' => 'https://schema.org/TechArticle',
							'article.scholarly' => 'https://schema.org/ScholarlyArticle',
							'blog.posting' => 'https://schema.org/BlogPosting',
							'report' => 'https://schema.org/Report',
							'social.media.posting' => 'https://schema.org/SocialMediaPosting',
						),
						'book' => 'https://schema.org/Book',
						'blog' => 'https://schema.org/Blog',
						'creative.work' => 'https://schema.org/CreativeWork',
						'game' => 'https://schema.org/Game',
						'movie' => 'https://schema.org/Movie',
						'painting' => 'https://schema.org/Painting',
						'photograph' => 'https://schema.org/Photograph',
						'recipe' => 'https://schema.org/Recipe',
						'review' => 'https://schema.org/Review',
						'sculpture' => 'https://schema.org/Sculpture',
						'software.application' => 'https://schema.org/SoftwareApplication',
						'visual.artwork' => 'https://schema.org/VisualArtwork',
						'webpage' => array(
							'webpage' => 'https://schema.org/WebPage',
							'webpage.about' => 'https://schema.org/AboutPage',
							'webpage.checkout' => 'https://schema.org/CheckoutPage',
							'webpage.collection' => 'https://schema.org/CollectionPage',
							'webpage.contact' => 'https://schema.org/ContactPage',
							'webpage.item' => 'https://schema.org/ItemPage',
							'webpage.profile' => 'https://schema.org/ProfilePage',
							'webpage.qa' => 'https://schema.org/QAPage',
							'webpage.search.results' => 'https://schema.org/SearchResultsPage',
						),
						'website' => 'https://schema.org/WebSite',
					),
					'event' => array(
						'event' => 'https://schema.org/Event',
						'event.business' => 'https://schema.org/BusinessEvent',
						'event.childrens' => 'https://schema.org/ChildrensEvent',
						'event.comedy' => 'https://schema.org/ComedyEvent',
						'event.dance' => 'https://schema.org/DanceEvent',
						'event.delivery' => 'https://schema.org/DeliveryEvent',
						'event.education' => 'https://schema.org/EducationEvent',
						'event.exhibition' => 'https://schema.org/ExhibitionEvent',
						'event.festival' => 'https://schema.org/Festival',
						'event.food' => 'https://schema.org/FoodEvent',
						'event.literary' => 'https://schema.org/LiteraryEvent',
						'event.music' => 'https://schema.org/MusicEvent',
						'event.publication' => 'https://schema.org/PublicationEvent',
						'event.sale' => 'https://schema.org/SaleEvent',
						'event.screening' => 'https://schema.org/ScreeningEvent',
						'event.social' => 'https://schema.org/SocialEvent',
						'event.sports' => 'https://schema.org/SportsEvent',
						'event.theater' => 'https://schema.org/TheaterEvent',
						'event.visual.arts' => 'https://schema.org/VisualArtsEvent',
					),
					'organization' => array(
						'airline' => 'https://schema.org/Airline',
						'corporation' => 'https://schema.org/Corporation',
						'educational.organization' => array(
							'college.or.university' => 'https://schema.org/CollegeOrUniversity',
							'educational.organization' => 'https://schema.org/EducationalOrganization',
							'elementary.school' => 'https://schema.org/ElementarySchool',
							'middle.school' => 'https://schema.org/MiddleSchool',
							'preschool' => 'https://schema.org/Preschool',
							'school' => 'https://schema.org/School',
						),
						'government.organization' => 'https://schema.org/GovernmentOrganization',
						'medical.organization' => array(
							'dentist' => 'https://schema.org/Dentist',
							'hospital' => 'https://schema.org/Hospital',
							'medical.organization' => 'https://schema.org/MedicalOrganization',
							'pharmacy' => 'https://schema.org/Pharmacy',
							'physician' => 'https://schema.org/Physician',
						),
						'non-governmental.organization' => 'https://schema.org/NGO',
						'organization' => 'https://schema.org/Organization',
						'performing.group' => array(
							'dance.group' => 'https://schema.org/DanceGroup',
							'music.group' => 'https://schema.org/MusicGroup',
							'performing.group' => 'https://schema.org/PerformingGroup',
							'theater.group' => 'https://schema.org/TheaterGroup',
						),
						'sports.organization' => array(
							'sports.team' => 'https://schema.org/SportsTeam',
							'sports.organization' => 'https://schema.org/SportsOrganization',
						),
					),
					'person' => 'https://schema.org/Person',
					'place' => array(
						'administrative.area' => 'https://schema.org/AdministrativeArea',
						'civic.structure' => 'https://schema.org/CivicStructure',
						'landform' => 'https://schema.org/Landform',
						'landmarks.or.historical.buildings' => 'https://schema.org/LandmarksOrHistoricalBuildings',
						'local.business' => array( 
							'animal.shelter' => 'https://schema.org/AnimalShelter',
							'automotive.business' => array(
								'auto.body.shop' => 'https://schema.org/AutoBodyShop',
								'auto.dealer' => 'https://schema.org/AutoDealer',
								'auto.parts.store' => 'https://schema.org/AutoPartsStore',
								'auto.rental' => 'https://schema.org/AutoRental',
								'auto.repair' => 'https://schema.org/AutoRepair',
								'auto.wash' => 'https://schema.org/AutoWash',
								'automotive.business' => 'https://schema.org/AutomotiveBusiness',
								'gas.station' => 'https://schema.org/GasStation',
								'motorcycle.dealer' => 'https://schema.org/MotorcycleDealer',
								'motorcycle.repair' => 'https://schema.org/MotorcycleRepair ',
							),
							'child.care' => 'https://schema.org/ChildCare',
							'dry.cleaning.or.laundry' => 'https://schema.org/DryCleaningOrLaundry',
							'emergency.service' => 'https://schema.org/EmergencyService',
							'employement.agency' => 'https://schema.org/EmploymentAgency',
							'entertainment.business' => 'https://schema.org/EntertainmentBusiness',
							'financial.service' => 'https://schema.org/FinancialService',
							'food.establishment' => array( 
								'bakery' => 'https://schema.org/Bakery',
								'bar.or.pub' => 'https://schema.org/BarOrPub',
								'brewery' => 'https://schema.org/Brewery',
								'cafe.or.coffee.shop' => 'https://schema.org/CafeOrCoffeeShop',
								'fast.food.restaurant' => 'https://schema.org/FastFoodRestaurant',
								'food.establishment' => 'https://schema.org/FoodEstablishment',
								'ice.cream.shop' => 'https://schema.org/IceCreamShop',
								'restaurant' => 'https://schema.org/Restaurant',
								'winery' => 'https://schema.org/Winery',
							),
							'government.office' => 'https://schema.org/GovernmentOffice',
							'health.and.beauty.business' => 'https://schema.org/HealthAndBeautyBusiness',
							'home.and.construction.business' => 'https://schema.org/HomeAndConstructionBusiness',
							'internet.cafe' => 'https://schema.org/InternetCafe',
							'legal.service' => 'https://schema.org/LegalService',
							'library' => 'https://schema.org/Library',
							'local.business' => 'https://schema.org/LocalBusiness',
							'lodging.business' => 'https://schema.org/LodgingBusiness',
							'medical.organization' => 'https://schema.org/MedicalOrganization',
							'professional.service' => 'https://schema.org/ProfessionalService',
							'radio.station' => 'https://schema.org/RadioStation',
							'real.estate.agent' => 'https://schema.org/RealEstateAgent',
							'recycling.center' => 'https://schema.org/RecyclingCenter',
							'self.storage' => 'https://schema.org/SelfStorage',
							'shopping.center' => 'https://schema.org/ShoppingCenter',
							'sports.activity.location' => 'https://schema.org/SportsActivityLocation',
							'store' => array(
								'auto.parts.store' => 'https://schema.org/AutoPartsStore',
								'bike.store' => 'https://schema.org/BikeStore',
								'book.store' => 'https://schema.org/BookStore',
								'clothing.store' => 'https://schema.org/ClothingStore',
								'computer.store' => 'https://schema.org/ComputerStore',
								'convenience.store' => 'https://schema.org/ConvenienceStore',
								'department.store' => 'https://schema.org/DepartmentStore',
								'electronics.store' => 'https://schema.org/ElectronicsStore',
								'florist' => 'https://schema.org/Florist',
								'flower.shop' => 'https://schema.org/Florist',
								'furniture.store' => 'https://schema.org/FurnitureStore',
								'garden.store' => 'https://schema.org/GardenStore',
								'grocery.store' => 'https://schema.org/GroceryStore',
								'hardware.store' => 'https://schema.org/HardwareStore',
								'hobby.shop' => 'https://schema.org/HobbyShop',
								'home.goods.store' => 'https://schema.org/HomeGoodsStore',
								'jewelry.store' => 'https://schema.org/JewelryStore',
								'liquor.store' => 'https://schema.org/LiquorStore',
								'mens.clothing.store' => 'https://schema.org/MensClothingStore',
								'mobile.phone.store' => 'https://schema.org/MobilePhoneStore',
								'movie.rental.store' => 'https://schema.org/MovieRentalStore',
								'music.store' => 'https://schema.org/MusicStore',
								'office.equipment.store' => 'https://schema.org/OfficeEquipmentStore',
								'outlet.store' => 'https://schema.org/OutletStore',
								'pawn.shop' => 'https://schema.org/PawnShop',
								'pet.store' => 'https://schema.org/PetStore',
								'shoe.store' => 'https://schema.org/ShoeStore',
								'sporting.goods.store' => 'https://schema.org/SportingGoodsStore',
								'store' => 'https://schema.org/Store',
								'tire.shop' => 'https://schema.org/TireShop',
								'toy.store' => 'https://schema.org/ToyStore',
								'wholesale.store' => 'https://schema.org/WholesaleStore',
							),
							'television.station' => 'https://schema.org/TelevisionStation',
							'tourist.information.center' => 'https://schema.org/TouristInformationCenter',
							'travel.agency' => 'https://schema.org/TravelAgency',
						),
						'place' => 'https://schema.org/Place',
						'residence' => 'https://schema.org/Residence',
						'tourist.attraction' => 'https://schema.org/TouristAttraction',
					),
					'product' => 'https://schema.org/Product',
				),
			),
			'cache' => array(
				'file' => false,
				'object' => true,
				'transient' => true,
			),
			'follow' => array(
				'size' => 24,
				'src' => array(
					'images/follow/Wordpress.png' => 'https://profiles.wordpress.org/jsmoriss/',
					'images/follow/Github.png' => 'https://github.com/SurniaUlula',
					'images/follow/Facebook.png' => 'https://www.facebook.com/SurniaUlulaCom',
					'images/follow/GooglePlus.png' => 'https://plus.google.com/+SurniaUlula/',
					//'images/follow/Linkedin.png' => 'https://www.linkedin.com/company/surnia-ulula-ltd',
					'images/follow/Twitter.png' => 'https://twitter.com/surniaululacom',
					//'images/follow/Youtube.png' => 'https://www.youtube.com/user/SurniaUlulaCom',
					'images/follow/Rss.png' => 'https://surniaulula.com/category/application/wordpress/wp-plugins/ngfb/feed/',
				),
			),
			'sharing' => array(
				'show_on' => array( 
					'content' => 'Content', 
					'excerpt' => 'Excerpt', 
					'sidebar' => 'CSS Sidebar', 
					'admin_edit' => 'Admin Edit',
				),
				'styles' => array(
					'sharing' => 'All Buttons',
					'content' => 'Content',
					'excerpt' => 'Excerpt',
					'sidebar' => 'CSS Sidebar',
					'admin_edit' => 'Admin Edit',
					'shortcode' => 'Shortcode',
					'widget' => 'Widget',
				),
				'position' => array(
					'top' => 'Top',
					'bottom' => 'Bottom',
					'both' => 'Top and Bottom',
				),
				'platform' => array(
					'desktop' => 'Desktop Only',
					'mobile' => 'Mobile Only',
					'any' => 'Any Platform',
				),
			),
		);

		public static function get_version() { 
			return self::$cf['plugin']['ngfb']['version'];
		}

		// get_config is called very early, so don't apply filters unless instructed
		public static function get_config( $idx = false, $do_filter = false ) { 

			if ( ! isset( self::$cf['config_filtered'] ) || 
				self::$cf['config_filtered'] !== true ) {

				self::$cf['*'] = array(
					'base' => array(),
					'lib' => array(
						'gpl' => array (),
						'pro' => array (),
					),
					'version' => '',		// -ngfb8.29.0pro-ngfbum1.4.0gpl
				);

				self::$cf['opt']['version'] = '';	// -ngfb416pro

				if ( $do_filter ) {

					self::$cf = apply_filters( self::$cf['lca'].'_get_config', self::$cf, self::get_version() );

					self::$cf['config_filtered'] = true;

					// remove the sharing libs if social sharing features are disabled
					// don't use SucomUtil::get_const() since the SucomUtil class may not be loaded yet
					if ( defined( 'NGFB_SOCIAL_SHARING_DISABLE' ) && NGFB_SOCIAL_SHARING_DISABLE ) {
						foreach ( array_keys( self::$cf['plugin'] ) as $ext ) {
							unset (
								self::$cf['plugin'][$ext]['lib']['website'],
								self::$cf['plugin'][$ext]['lib']['submenu']['buttons'],
								self::$cf['plugin'][$ext]['lib']['submenu']['styles'],
								self::$cf['plugin'][$ext]['lib']['shortcode']['sharing'],
								self::$cf['plugin'][$ext]['lib']['widget']['sharing'],
								self::$cf['plugin'][$ext]['lib']['gpl']['admin']['sharing'],
								self::$cf['plugin'][$ext]['lib']['pro']['admin']['sharing']
							);
						}
					}

					foreach ( self::$cf['plugin'] as $ext => $info ) {

						if ( defined( strtoupper( $ext ).'_PLUGINDIR' ) )
							$pkg = is_dir( constant( strtoupper( $ext ).
								'_PLUGINDIR' ).'lib/pro/' ) ? 'pro' : 'gpl';
						else $pkg = '';

						if ( isset( $info['base'] ) )
							self::$cf['*']['base'][$info['base']] = $ext;

						if ( isset( $info['lib'] ) && is_array( $info['lib'] ) )
							self::$cf['*']['lib'] = SucomUtil::array_merge_recursive_distinct( 
								self::$cf['*']['lib'], $info['lib'] );

						if ( isset( $info['version'] ) )
							self::$cf['*']['version'] .= '-'.$ext.$info['version'].$pkg;

						if ( isset( $info['opt_version'] ) )
							self::$cf['opt']['version'] .= '-'.$ext.$info['opt_version'].$pkg;

						// complete relative paths in the image array
						foreach ( $info['img'] as $id => $url )
							if ( ! empty( $url ) && strpos( $url, '//' ) === false )
								self::$cf['plugin'][$ext]['img'][$id] = trailingslashit( plugins_url( '', $info['base'] ) ).$url;
					}
				}
			}

			if ( $idx !== false ) {
				if ( isset( self::$cf[$idx] ) )
					return self::$cf[$idx];
				else return null;
			} else return self::$cf;
		}

		public static function set_constants( $plugin_filepath ) { 
			define( 'NGFB_FILEPATH', $plugin_filepath );						
			define( 'NGFB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_filepath ) ) ) );
			define( 'NGFB_PLUGINSLUG', self::$cf['plugin']['ngfb']['slug'] );		// nextgen-facebook
			define( 'NGFB_PLUGINBASE', self::$cf['plugin']['ngfb']['base'] );		// nextgen-facebook/nextgen-facebook.php
			define( 'NGFB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );
			define( 'NGFB_NONCE', md5( NGFB_PLUGINDIR.'-'.self::$cf['plugin']['ngfb']['version'].
				( defined( 'NONCE_SALT' ) ? NONCE_SALT : '' ) ) );
			self::set_variable_constants();
		}

		public static function set_variable_constants( $var_const = null ) { 
			if ( $var_const === null )
				$var_const = self::get_variable_constants();
			foreach ( $var_const as $name => $value )
				if ( ! defined( $name ) )
					define( $name, $value );
		}

		public static function get_variable_constants() { 
			$var_const = array();

			if ( defined( 'NGFB_PLUGINDIR' ) ) {
				$var_const['NGFB_CACHEDIR'] = NGFB_PLUGINDIR.'cache/';
				$var_const['NGFB_TOPICS_LIST'] = NGFB_PLUGINDIR.'share/topics.txt';
			}

			if ( defined( 'NGFB_URLPATH' ) )
				$var_const['NGFB_CACHEURL'] = NGFB_URLPATH.'cache/';

			$var_const['NGFB_DEBUG_FILE_EXP'] = 300;
			$var_const['NGFB_MENU_ORDER'] = '99.11';		// position of the NGFB menu item
			$var_const['NGFB_MENU_ICON_HIGHLIGHT'] = true;		// highlight the NGFB menu icon
			$var_const['NGFB_SHARING_SHORTCODE'] = 'ngfb';		// used by social sharing features
			$var_const['NGFB_HIDE_ALL_ERRORS'] = false;		// auto-hide all error notices
			$var_const['NGFB_HIDE_ALL_WARNINGS'] = false;		// auto-hide all warning notices
			$var_const['NGFB_JSON_PRETTY_PRINT'] = true;		// output pretty / human readable json

			/*
			 * NGFB option and meta array names
			 */
			$var_const['NGFB_TS_NAME'] = 'ngfb_timestamps';
			$var_const['NGFB_OPTIONS_NAME'] = 'ngfb_options';
			$var_const['NGFB_SITE_OPTIONS_NAME'] = 'ngfb_site_options';
			$var_const['NGFB_NOTICE_NAME'] = 'ngfb_notices';	// stored notices
			$var_const['NGFB_DISMISS_NAME'] = 'ngfb_dismissed';	// dismissed notices
			$var_const['NGFB_META_NAME'] = '_ngfb_meta';		// post meta
			$var_const['NGFB_PREF_NAME'] = '_ngfb_pref';		// user meta

			/*
			 * NGFB option and meta array alternate names
			 */
			$var_const['NGFB_OPTIONS_NAME_ALT'] = 'wpsso_options';
			$var_const['NGFB_SITE_OPTIONS_NAME_ALT'] = 'wpsso_site_options';
			$var_const['NGFB_META_NAME_ALT'] = '_wpsso_meta';
			$var_const['NGFB_PREF_NAME_ALT'] = '_wpsso_pref';

			/*
			 * NGFB hook priorities
			 */
			$var_const['NGFB_ADD_MENU_PRIORITY'] = -20;
			$var_const['NGFB_ADD_SUBMENU_PRIORITY'] = -10;
			$var_const['NGFB_META_SAVE_PRIORITY'] = 6;
			$var_const['NGFB_META_CACHE_PRIORITY'] = 9;
			$var_const['NGFB_INIT_PRIORITY'] = 14;
			$var_const['NGFB_HEAD_PRIORITY'] = 10;
			$var_const['NGFB_SOCIAL_PRIORITY'] = 100;		// used by social sharing features
			$var_const['NGFB_FOOTER_PRIORITY'] = 100;		// used by social sharing features
			$var_const['NGFB_SEO_FILTERS_PRIORITY'] = 100;

			/*
			 * NGFB curl settings
			 */
			if ( defined( 'NGFB_PLUGINDIR' ) )
				$var_const['NGFB_PHP_CURL_CAINFO'] = NGFB_PLUGINDIR.'share/curl/ca-bundle.crt';
			$var_const['NGFB_PHP_CURL_USERAGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';

			foreach ( $var_const as $name => $value )
				if ( defined( $name ) )
					$var_const[$name] = constant( $name );	// inherit existing values

			return $var_const;
		}

		public static function require_libs( $plugin_filepath ) {

			require_once( NGFB_PLUGINDIR.'lib/com/nodebug.php' );	// always load fallback class
			require_once( NGFB_PLUGINDIR.'lib/com/nonotice.php' );	// always load fallback class
			require_once( NGFB_PLUGINDIR.'lib/com/exception.php' );	// extends Exception
			require_once( NGFB_PLUGINDIR.'lib/com/util.php' );
			require_once( NGFB_PLUGINDIR.'lib/com/cache.php' );
			require_once( NGFB_PLUGINDIR.'lib/com/script.php' );
			require_once( NGFB_PLUGINDIR.'lib/com/style.php' );
			require_once( NGFB_PLUGINDIR.'lib/com/webpage.php' );

			require_once( NGFB_PLUGINDIR.'lib/register.php' );
			require_once( NGFB_PLUGINDIR.'lib/check.php' );
			require_once( NGFB_PLUGINDIR.'lib/util.php' );
			require_once( NGFB_PLUGINDIR.'lib/options.php' );
			require_once( NGFB_PLUGINDIR.'lib/meta.php' );
			require_once( NGFB_PLUGINDIR.'lib/post.php' );		// extends meta.php
			require_once( NGFB_PLUGINDIR.'lib/term.php' );		// extends meta.php
			require_once( NGFB_PLUGINDIR.'lib/user.php' );		// extends meta.php
			require_once( NGFB_PLUGINDIR.'lib/media.php' );
			require_once( NGFB_PLUGINDIR.'lib/head.php' );
			require_once( NGFB_PLUGINDIR.'lib/opengraph.php' );
			require_once( NGFB_PLUGINDIR.'lib/weibo.php' );
			require_once( NGFB_PLUGINDIR.'lib/twittercard.php' );
			require_once( NGFB_PLUGINDIR.'lib/schema.php' );
			require_once( NGFB_PLUGINDIR.'lib/functions.php' );

			if ( is_admin() ) {
				require_once( NGFB_PLUGINDIR.'lib/messages.php' );
				require_once( NGFB_PLUGINDIR.'lib/admin.php' );
				require_once( NGFB_PLUGINDIR.'lib/com/form.php' );
				require_once( NGFB_PLUGINDIR.'lib/ext/parse-readme.php' );
			}

			if ( ! SucomUtil::get_const( 'NGFB_SOCIAL_SHARING_DISABLE' ) &&
				empty( $_SERVER['NGFB_SOCIAL_SHARING_DISABLE'] ) &&
					file_exists( NGFB_PLUGINDIR.'lib/sharing.php' ) )
						require_once( NGFB_PLUGINDIR.'lib/sharing.php' );

			if ( file_exists( NGFB_PLUGINDIR.'lib/loader.php' ) )
				require_once( NGFB_PLUGINDIR.'lib/loader.php' );

			add_filter( 'ngfb_load_lib', array( 'NgfbConfig', 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {
			if ( $ret === false && ! empty( $filespec ) ) {
				$filepath = NGFB_PLUGINDIR.'lib/'.$filespec.'.php';
				if ( file_exists( $filepath ) ) {
					require_once( $filepath );
					if ( empty( $classname ) )
						return SucomUtil::sanitize_classname( 'ngfb'.$filespec, false );	// $underscore = false
					else return $classname;
				}
			}
			return $ret;
		}
	}
}

?>
