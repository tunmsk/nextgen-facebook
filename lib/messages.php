<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbMessages' ) ) {

	class NgfbMessages {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		public function get( $idx = false, $info = array() ) {

			if ( is_string( $info ) ) {
				$text = $info;
				$info = array( 'text' => $text );
			} else $text = isset( $info['text'] ) ?
				$info['text'] : '';

			$idx = sanitize_title_with_dashes( $idx );

			/*
			 * Define some basic values that can be used in any message text.
			 */
			$info['lca'] = $lca = isset( $info['lca'] ) ?
				$info['lca'] : $this->p->cf['lca'];

			foreach ( array( 'short', 'name' ) as $key ) {
				$info[$key] = isset( $info[$key] ) ?
					$info[$key] : $this->p->cf['plugin'][$lca][$key];
				$info[$key.'_pro'] = $info[$key].' Pro';
			}

			// an array of plugin urls (download, purchase, etc.)
			$url = isset( $this->p->cf['plugin'][$lca]['url'] ) ?
				$this->p->cf['plugin'][$lca]['url'] : array();

			$fb_recommends = __( 'Facebook has published a preference for Open Graph image dimensions of 1200x630px cropped (for retina and high-PPI displays), 600x315px cropped as a minimum (the default settings value), and ignores images smaller than 200x200px.', 'nextgen-facebook' );

			/*
			 * All tooltips
			 */
			if ( strpos( $idx, 'tooltip-' ) === 0 ) {
				if ( strpos( $idx, 'tooltip-meta-' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-meta-sharing_url':
							$text = 'A custom sharing URL used in the Facebook / Open Graph, Pinterest Rich Pin meta tags and social sharing buttons. The default sharing URL may be influenced by settings from supported SEO plugins. Please make sure any custom URL you enter here is functional and redirects correctly.';
						 	break;
						case 'tooltip-meta-schema_is_main':
							$text = 'Select if this Schema markup describes the <em>main entity</em> for this webpage.';
						 	break;
						case 'tooltip-meta-schema_type':
							$text = 'The Schema type is used to declare the item type for Schema JSON-LD and/or meta tags in webpage headers.';
						 	break;
						case 'tooltip-meta-schema_title':
							$text = 'A custom name / title for the Schema item type "name" JSON-LD property.';
						 	break;
						case 'tooltip-meta-schema_headline':
							$text = 'A custom headline for the Schema Article "headline" JSON-LD property. The custom headline field is disabled for all non-Article item types.';
						 	break;
						case 'tooltip-meta-schema_desc':
							$text = 'A custom description for the Schema meta tag and item type "description" JSON-LD property.';
						 	break;
						case 'tooltip-meta-og_title':
							$text = __( 'A custom title for the Facebook / Open Graph, Pinterest Rich Pin, and Twitter Card meta tags (all Twitter Card formats).', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-og_desc':
							$text = 'A custom description for the Facebook / Open Graph, Pinterest Rich Pin, and fallback description for other meta tags. The default description value is based on the category / tag description, or user biographical info. Update and save this description to change the default value of all other description fields.';
						 	break;
						case 'tooltip-meta-seo_desc':
							$text = 'A custom description for the Google Search / SEO description meta tag.';
						 	break;
						case 'tooltip-meta-tc_desc':
							$text = 'A custom description for the Twitter Card description meta tag (all Twitter Card formats).';
						 	break;
						case 'tooltip-meta-og_img_id':
							$text = __( 'A custom image ID to include first, before any featured, attached, or content images.', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-og_img_url':
							$text = __( 'A custom image URL (instead of an image ID) to include first, before any featured, attached, or content images.', 'nextgen-facebook' ).' '.__( 'Please make sure your custom image is large enough, or it may be ignored by social website(s).', 'nextgen-facebook' ).' '.$fb_recommends.' <em>'.__( 'This field is disabled if a custom image ID has been selected.', 'nextgen-facebook' ).'</em>';
							break;
						case 'tooltip-meta-og_img_max':
							$text = __( 'The maximum number of images to include in the Facebook / Open Graph meta tags.', 'nextgen-facebook' ).' '.__( 'There is no advantage in selecting a maximum value greater than 1.', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-og_vid_embed':
							$text = 'Custom Video Embed HTML to use for the first in the Facebook / Open Graph, Pinterest Rich Pin, and \'Player\' Twitter Card meta tags. If the URL is from Youtube, Vimeo or Wistia, an API connection will be made to retrieve the preferred sharing URL, video dimensions, and video preview image. The '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_social', 'Video Embed HTML Custom Field' ).' advanced option also allows a 3rd-party theme or plugin to provide custom Video Embed HTML for this option.';
						 	break;
						case 'tooltip-meta-og_vid_url':
							$text = 'A custom Video URL to include first in the Facebook / Open Graph, Pinterest Rich Pin, and \'Player\' Twitter Card meta tags. If the URL is from Youtube, Vimeo or Wistia, an API connection will be made to retrieve the preferred sharing URL, video dimensions, and video preview image. The '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_social', 'Video URL Custom Field' ).' advanced option allows a 3rd-party theme or plugin to provide a custom Video URL value for this option.';
						 	break;
						case 'tooltip-meta-og_vid_title':
						case 'tooltip-meta-og_vid_desc':
							$text = sprintf( __( 'The %1$s video API modules include the video name / title and description <em>when available</em>.', 'nextgen-facebook' ), $info['short_pro'] ).' '.__( 'The video name / title and description text is used for Schema JSON-LD markup (extension plugin required), which can be read by both Google and Pinterest.', 'nextgen-facebook' );
							break;
						case 'tooltip-meta-og_vid_max':
							$text = __( 'The maximum number of embedded videos to include in the Facebook / Open Graph meta tags.', 'nextgen-facebook' ).' '.__( 'There is no advantage in selecting a maximum value greater than 1.', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-og_vid_prev_img':
							$text = 'When video preview images are enabled and available, they are included in webpage meta tags before any custom, featured, attached, etc. images.';
						 	break;
						case 'tooltip-meta-rp_img_id':
							$text = __( 'A custom image ID to include first when the Pinterest crawler is detected, before any featured, attached, or content images.', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-rp_img_url':
							$text = __( 'A custom image URL (instead of an image ID) to include first when the Pinterest crawler is detected.', 'nextgen-facebook' ).' <em>'.__( 'This field is disabled if a custom image ID has been selected.', 'nextgen-facebook' ).'</em>';
						 	break;
						case 'tooltip-meta-schema_img_id':
							$text = __( 'A custom image ID to include first in the Google / Schema meta tags and JSON-LD markup, before any featured, attached, or content images.', 'nextgen-facebook' );
						 	break;
						case 'tooltip-meta-schema_img_url':
							$text = __( 'A custom image URL (instead of an image ID) to include first in the Google / Schema meta tags and JSON-LD markup.', 'nextgen-facebook' ).' <em>'.__( 'This field is disabled if a custom image ID has been selected.', 'nextgen-facebook' ).'</em>';
						 	break;
						case 'tooltip-meta-schema_img_max':
							$text = __( 'The maximum number of images to include in the Google / Schema meta tags and JSON-LD markup.', 'nextgen-facebook' );
						 	break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_user', $text, $idx, $info );
							break;
					}	// end of tooltip-user switch
				/*
				 * Post Meta settings
				 */
				} elseif ( strpos( $idx, 'tooltip-post-' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-post-og_art_section':
							$text = 'A custom topic, different from the default Article Topic selected in the General Settings. The Facebook / Open Graph \'og:type\' meta tag must be an \'article\' to enable this option. The value will be used in the \'article:section\' Facebook / Open Graph and Pinterest Rich Pin meta tags. Select \'[None]\' if you prefer to exclude the \'article:section\' meta tag.';
						 	break;
						case 'tooltip-post-og_desc':
							$text = 'A custom description for the Facebook / Open Graph, Pinterest Rich Pin, and fallback description for other meta tags. The default description value is based on the content, or excerpt if one is available. Update and save the Facebook / Open Graph description to change the default value of all other description fields.';
						 	break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_post', $text, $idx, $info );
							break;
					}	// end of tooltip-post switch
				/*
				 * Open Graph settings
				 */
				} elseif ( strpos( $idx, 'tooltip-og_' ) === 0 ) {
					switch ( $idx ) {
						/*
						 * 'Priority Media' settings
						 */
						case 'tooltip-og_img_dimensions':
							$def_dimensions = $this->p->opt->get_defaults( 'og_img_width' ).'x'.
								$this->p->opt->get_defaults( 'og_img_height' ).' '.
								( $this->p->opt->get_defaults( 'og_img_crop' ) == 0 ? 'uncropped' : 'cropped' );
							$text = 'The image dimensions used in the Facebook / Open Graph meta tags (the default dimensions are '.$def_dimensions.'). '.$fb_recommends.' Note that images in the WordPress Media Library and/or NextGEN Gallery must be larger than your chosen image dimensions.';
							break;
						case 'tooltip-og_def_img_id':
							$text = 'An image ID and media library for your default / fallback website image. The default image ID will be used for index / archive pages, and as a fallback for Posts / Pages that do not have a suitable image featured, attached, or in their content.';
							break;
						case 'tooltip-og_def_img_url':
							$text = 'You can enter a default image URL (including the http:// prefix) instead of choosing a default image ID &mdash; if a default image ID is specified, the default image URL option is disabled. The default image URL option allows you to <strong>use an image outside of a managed collection (WordPress Media Library or NextGEN Gallery), and/or a smaller logo style image</strong>. The image should be at least '.$this->p->cf['head']['min']['og_img_width'].'x'.$this->p->cf['head']['min']['og_img_height'].' or more in width and height. The default image ID or URL is used for index / archive pages, and as a fallback for Posts and Pages that do not have a suitable image featured, attached, or in their content.';
							break;
						case 'tooltip-og_def_img_on_index':
							$text = 'Check this option to force the default image on index webpages (<strong>non-static</strong> homepage, archives, categories). If this option is <em>checked</em>, but a Default Image ID or URL has not been defined, then <strong>no image will be included in the meta tags</strong>. If the option is <em>unchecked</em>, then '.$info['short'].' will use image(s) from the first entry on the webpage (default is checked).';
							break;
						case 'tooltip-og_def_img_on_search':
							$text = 'Check this option to force the default image on search results. If this option is <em>checked</em>, but a Default Image ID or URL has not been defined, then <strong>no image will be included in the meta tags</strong>. If the option is <em>unchecked</em>, then '.$info['short'].' will use image(s) returned in the search results (default is unchecked).';
							break;
						case 'tooltip-og_def_vid_url':
							$text = 'The Default Video URL is used as a <strong>fallback value for Posts and Pages that do not have any videos</strong> in their content. Do not specify a Default Video URL <strong>unless you want to include video information in all your Posts and Pages</strong>.';
							break;
						case 'tooltip-og_def_vid_on_index':
							$text = 'Check this option to force the default video on index webpages (<strong>non-static</strong> homepage, archives, categories). If this option is <em>checked</em>, but a Default Video URL has not been defined, then <strong>no video will be included in the meta tags</strong> (this is usually preferred). If the option is <em>unchecked</em>, then '.$info['short'].' will use video(s) from the first entry on the webpage (default is checked).';
							break;
						case 'tooltip-og_def_vid_on_search':
							$text = 'Check this option to force the default video on search results. If this option is <em>checked</em>, but a Default Video URL has not been defined, then <strong>no video will be included in the meta tags</strong>. If the option is <em>unchecked</em>, then '.$info['short'].' will use video(s) returned in the search results (default is unchecked).';
							break;
						case 'tooltip-og_ngg_tags':
							$text = 'If the <em>featured</em> image in a Post or Page is from a NextGEN Gallery, then add that image\'s tags to the Facebook / Open Graph and Pinterest Rich Pin tag list (default is unchecked).';
							break;
						case 'tooltip-og_img_max':
							$text = 'The maximum number of images to include in the Facebook / Open Graph meta tags -- this includes the <em>featured</em> image, <em>attached</em> images, and any images found in the content. If you select "0", then no images will be listed in the Facebook / Open Graph meta tags (<strong>not recommended</strong>). If no images are listed in your meta tags, social websites may choose an unsuitable image from your webpage (including headers, sidebars, etc.). There is no advantage in selecting a maximum value greater than 1.';
							break;
						case 'tooltip-og_vid_max':
							$text = 'The maximum number of videos, found in the Post or Page content, to include in the Facebook / Open Graph and Pinterest Rich Pin meta tags. If you select "0", then no videos will be listed in the Facebook / Open Graph and Pinterest Rich Pin meta tags. There is no advantage in selecting a maximum value greater than 1.';
							break;
						case 'tooltip-og_vid_https':
							$text = 'Use an HTTPS connection whenever possible to retrieve information about videos from YouTube, Vimeo, Wistia, etc. (default is checked).';
							break;
						case 'tooltip-og_vid_autoplay':
							$text = 'When possible, add or modify the "autoplay" argument of video URLs in webpage meta tags (default is checked).';
							break;
						case 'tooltip-og_vid_prev_img':
							$text = 'Include video preview images in the webpage meta tags (default is unchecked). When video preview images are enabled and available, they are included before any custom, featured, attached, etc. images.';
							break;
						case 'tooltip-og_vid_html_type':
							$text = 'Include additional Open Graph meta tags for the embed video URL as a text/html video type (default is checked).';
							break;
						/*
						 * 'Description' settings
						 */
						case 'tooltip-og_art_section':
							$text = 'The topic that best describes the Posts and Pages on your website. This value will be used in the \'article:section\' Facebook / Open Graph and Pinterest Rich Pin meta tags. Select \'[None]\' if you prefer to exclude the \'article:section\' meta tag. The Pro version also allows you to select a custom Topic for each individual Post and Page.';
							break;
						case 'tooltip-og_site_name':
							$text = sprintf( __( 'The WordPress Site Name is used for the Facebook / Open Graph and Pinterest Rich Pin \'og:site_name\' meta tag. You may override <a href="%s">the default WordPress Site Title value</a>.', 'nextgen-facebook' ), get_admin_url( null, 'options-general.php' ) );
							break;
						case 'tooltip-og_site_description':
							$text = 'The WordPress Tagline is used as a description for the <em>index</em> (non-static) home page, and as a fallback for the Facebook / Open Graph and Pinterest Rich Pin \'og:description\' meta tag. You may override <a href="'.get_admin_url( null, 'options-general.php' ).'">the default WordPress Tagline value</a> here, to provide a longer and more complete description of your website.';
							break;
						case 'tooltip-og_title_sep':
							$text = 'One or more characters used to separate values (category parent names, page numbers, etc.) within the Facebook / Open Graph and Pinterest Rich Pin title string (the default is the hyphen \''.$this->p->opt->get_defaults( 'og_title_sep' ).'\' character).';
							break;
						case 'tooltip-og_title_len':
							$text = 'The maximum length of text used in the Facebook / Open Graph and Rich Pin title tag (default is '.$this->p->opt->get_defaults( 'og_title_len' ).' characters).';
							break;
						case 'tooltip-og_desc_len':
							$text = 'The maximum length of text used in the Facebook / Open Graph and Rich Pin description tag. The length should be at least '.$this->p->cf['head']['min']['og_desc_len'].' characters or more, and the default is '.$this->p->opt->get_defaults( 'og_desc_len' ).' characters.';
							break;
						case 'tooltip-og_page_title_tag':
							$text = 'Add the title of the <em>Page</em> to the Facebook / Open Graph and Pinterest Rich Pin article tag and Hashtag list (default is unchecked). If the Add Page Ancestor Tags option is checked, all the titles of the ancestor Pages will be added as well. This option works well if the title of your Pages are short (one or two words) and subject-oriented.';
							break;
						case 'tooltip-og_page_parent_tags':
							$text = 'Add the WordPress tags from the <em>Page</em> ancestors (parent, parent of parent, etc.) to the Facebook / Open Graph and Pinterest Rich Pin article tags and Hashtag list (default is unchecked).';
							break;
						case 'tooltip-og_desc_hashtags':
							$text = 'The maximum number of tag names (converted to hashtags) to include in the Facebook / Open Graph and Pinterest Rich Pin description, tweet text, and social captions. Each tag name is converted to lowercase with whitespaces removed.  Select \'0\' to disable the addition of hashtags.';
							break;
						/*
						 * 'Authorship' settings
						 */
						case 'tooltip-og_author_field':
							$text = __( 'Select which contact field to use from the author\'s WordPress profile page for the Facebook / Open Graph <code>article:author</code> meta tag. The preferred setting is the Facebook URL field (default value).', 'nextgen-facebook' );
							break;
						case 'tooltip-og_author_fallback':
							$text = sprintf( __( 'If the \'%1$s\' (and the \'%2$s\' in the Google settings below) is not a valid URL, then %3$s can fallback to using the author index / archive page on this website (for example, \'%4$s\').', 'nextgen-facebook' ), _x( 'Author Profile URL Field', 'option label', 'nextgen-facebook' ), _x( 'Author Link URL Field', 'option label', 'nextgen-facebook' ), $info['short'], trailingslashit( site_url() ).'author/username' ).' '.__( 'Uncheck this option to disable the fallback feature (default is unchecked).', 'nextgen-facebook' );
							break;
						case 'tooltip-og_def_author_id':
							$text = 'A default author for webpages <em>missing authorship information</em> (for example, an index webpage without posts). If you have several authors on your website, you should probably leave this option set to <em>[None]</em> (the default).';
							break;
						case 'tooltip-og_def_author_on_index':
							$text = 'Check this option if you would like to force the Default Author on index webpages (<strong>non-static</strong> homepage, archives, categories, author, etc.). If this option is checked, index webpages will be labeled as a an \'article\' with authorship attributed to the Default Author (default is unchecked). If the Default Author is <em>[None]</em>, then the index webpages will be labeled as a \'website\'.';
							break;
						case 'tooltip-og_def_author_on_search':
							$text = 'Check this option if you would like to force the Default Author on search result webpages as well.  If this option is checked, search results will be labeled as a an \'article\' with authorship attributed to the Default Author (default is unchecked).';
							break;
						case 'tooltip-og_author_gravatar':
							$text = 'Check this option to include the author\'s Gravatar image in meta tags for author index / archive webpages. If the "<strong>Use Default Image on <em>Author</em> Index</strong>" option is also checked under the <em>Images</em> tab (unchecked by default), then the default image will be used instead for author index / archive webpages.';
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_og', $text, $idx, $info );
							break;
					}	// end of tooltip-og switch
				/*
				 * Advanced plugin settings
				 */
				} elseif ( strpos( $idx, 'tooltip-plugin_' ) === 0 ) {
					switch ( $idx ) {
						/*
						 * 'Plugin Settings' settings
						 */
						case 'tooltip-plugin_preserve':	// Preserve Settings on Uninstall
							$text = 'Check this option if you would like to preserve all '.$info['short'].' settings when you <em>uninstall</em> the plugin (default is unchecked).';
							break;
						case 'tooltip-plugin_debug':	// Add Hidden Debug Messages
							$text = 'Add hidden debug messages to the HTML of webpages (default is unchecked).';
							break;
						case 'tooltip-plugin_clear_on_save':	// Clear All Cache(s) on Save Settings
							$text = 'Automatically clear all cache(s) when saving the plugin settings (default is checked).';
							break;
						case 'tooltip-plugin_show_opts':	// Options to Show by Default
							$text = 'Select the default number of options to display on the '.$info['short'].' settings pages by default. The basic view shows only the essential options that are most commonly used.';
							break;
						/*
						 * 'Content and Filters' settings
						 */
						case 'tooltip-plugin_filter_title':
							$text = 'By default, '.$info['short'].' uses the title values provided by WordPress, which may include modifications by themes and/or SEO plugins (appending the blog name to all titles, for example, is a fairly common practice). Uncheck this option to use the original title value without modifications.';
							break;
						case 'tooltip-plugin_filter_content':
							$text = 'Apply the standard WordPress \'the_content\' filter to render content text (default is unchecked). This renders all shortcodes, and allows '.$info['short'].' to detect images and embedded videos that may be provided by these.';
							break;
						case 'tooltip-plugin_filter_excerpt':
							$text = 'Apply the standard WordPress \'get_the_excerpt\' filter to render the excerpt text (default is unchecked). Check this option if you use shortcodes in your excerpt, for example.';
							break;
						case 'tooltip-plugin_p_strip':
							$text = 'If a Page or Post does <em>not</em> have an excerpt, and this option is checked, the plugin will ignore all text until the first html paragraph tag in the content. If an excerpt exists, then this option is ignored and the complete text of the excerpt is used.';
							break;
						case 'tooltip-plugin_use_img_alt':
							$text = 'If the content is empty, or comprised entirely of HTML tags (that must be stripped to create a description text), '.$info['short'].' can extract and use text from the image <em>alt=""</em> attributes instead of returning an empty description.';
							break;
						case 'tooltip-plugin_img_alt_prefix':
							$text = 'When use of the image <em>alt=""</em> text is enabled, '.$info['short'].' can prefix that text with an optional string. Leave this option empty to prevent image alt text from being prefixed.';
							break;
						case 'tooltip-plugin_p_cap_prefix':
							$text = $info['short'].' can add a custom text prefix to paragraphs assigned the "wp-caption-text" class. Leave this option empty to prevent caption paragraphs from being prefixed.';
							break;
						case 'tooltip-plugin_embedded_media':
							$text = 'Check the Post and Page content, along with the custom Social Settings, for embedded media URLs from supported media providers (Youtube, Wistia, etc.). If a supported URL is found, an API connection to the provider will be made to retrieve information about the media (preview image, flash player url, oembed player url, video width / height, etc.).';
							break;
						/*
						 * 'Social Settings' settings
						 */
						case 'tooltip-plugin_social_columns':
							$text = '\'Social Image\' and \'Social Description\' columns are added to the Posts, Pages, Taxonomy / Terms, and Users list pages by default. You can exclude the columns individually from the <em>Screen Options</em> tab on the list pages, or disable the columns globally by unchecking these options.';
							break;
						case 'tooltip-plugin_add_to':
							$text = 'The Social Settings metabox, which allows you to enter custom Facebook / Open Graph values (among other options), is available on the User, Posts, Pages, Media, and Product admin pages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include the Social Settings metabox on their admin pages, check the appropriate option(s) here.';
							break;
						case 'tooltip-plugin_add_tab':
							$text = 'Include and exclude specific tabs in the Social Settings metabox.';
							break;
						case 'tooltip-plugin_cf_img_url':
							$text = 'If your theme or another plugin provides a custom field for image URLs, you may enter its custom field name here. If a custom field matching that name is found, its value will be used for the "<strong>Image URL</strong>" option in the Social Settings metabox. The default value is "'.$this->p->opt->get_defaults( 'plugin_cf_img_url' ).'".';
							break;
						case 'tooltip-plugin_cf_vid_url':
							$text = 'If your theme or another plugin provides a custom field for video URLs (not embed HTML code), you may enter its custom field name here. If a custom field matching that name is found, its value will be used for the "<strong>Video URL</strong>" option in the Social Settings metabox. The default value is "'.$this->p->opt->get_defaults( 'plugin_cf_vid_url' ).'".';
							break;
						case 'tooltip-plugin_cf_vid_embed':
							$text = 'If your theme or another plugin provides a custom field for video embed HTML code (not simply a URL), you may enter its custom field name here. If a custom field matching that name is found, its value will be used for the "<strong>Video Embed HTML</strong>" option in the Social Settings metabox. The default value is "'.$this->p->opt->get_defaults( 'plugin_cf_vid_embed' ).'".';
							break;
						/*
						 * 'WP / Theme Integration' settings
						 */
						case 'tooltip-plugin_check_head':
							$text = $info['short'].' can check the front-end webpage head section for duplicate HTML tags when editing Posts and Pages. You may uncheck this option if you\'ve edited a few Posts and Pages without seeing any warning messages about duplicate HTML tags.';
							break;
						case 'tooltip-plugin_html_attr_filter':
							$text = $info['short'].' hooks the "language_attributes" filter by default to add / modify required Open Graph namespace prefix values. The "language_attributes" WordPress function and filter are used by most themes &mdash; if the namespace prefix values are missing from your &amp;lt;html&amp;gt; element, make sure your header template(s) use the language_attributes() function. Leaving this option blank disables the addition of Open Graph namespace values. Example template code: <pre><code>&amp;lt;html &amp;lt;?php language_attributes(); ?&amp;gt;&amp;gt;</code></pre>';
							break;
						case 'tooltip-plugin_head_attr_filter':
							$text = $info['short'].' hooks the "head_attributes" filter by default to add / modify the <code>&amp;lt;head&amp;gt;</code> element attributes for the Schema itemscope / itemtype markup. If your theme offers a filter for <code>&amp;lt;head&amp;gt;</code> element attributes, enter its name here (most themes do not). Alternatively, you can add an action manually in your header templates to call the "head_attributes" filter. Example code: <pre><code>&amp;lt;head &amp;lt;?php do_action( \'add_head_attributes\' ); ?&amp;gt;&amp;gt;</code></pre>';
							break;
						case 'tooltip-plugin_filter_lang':
							$text = $info['short_pro'].' can use the WordPress locale to select the correct language for the Facebook / Open Graph and Pinterest Rich Pin meta tags'.( empty( $this->p->is_avail['ssb'] ) ? '' : ', along with the Google, Facebook, and Twitter social sharing buttons' ).'. If your website is available in multiple languages, this can be a useful feature. Uncheck this option to ignore the WordPress locale and always use the configured language.'; 
							break;
						case 'tooltip-plugin_auto_img_resize':
							$text = 'Automatically generate missing or incorrect image sizes for previously uploaded images in the WordPress Media Library (default is checked).';
							break;
						case 'tooltip-plugin_ignore_small_img':
							$text = 'Full size images selected by '.$info['short'].' must be equal to (or larger) than the '.$this->p->util->get_admin_url( 'image-dimensions', 'Social Image Dimensions' ).' you\'ve defined. Uncheck this option to disable the minimum image dimensions check. <em>Disabling this option is not advised</em> &mdash; if you uncheck this option, images that are too small for some social websites may be included in your meta tags.';
							break;
						case 'tooltip-plugin_upscale_images':
							$text = 'WordPress does not upscale (enlarge) images &mdash; WordPress only creates smaller images from larger full-size originals. Upscaled images do not look as sharp or clean when upscaled, and if enlarged too much, images will look fuzzy and unappealing &mdash; not something you want to promote on social sites. '.$info['short_pro'].' includes an optional module that allows upscaling of WordPress Media Library images for '.$info['short'].' image sizes (up to a maximum upscale percentage). <strong>Do not enable this option unless you want to publish lower quality images on social sites</strong>.';
							break;
						case 'tooltip-plugin_upscale_img_max':
							$text = 'When upscaling of '.$info['short'].' image sizes is allowed, '.$info['short_pro'].' can make sure smaller / thumbnail images are not upscaled beyond reason, which could publish very low quality / fuzzy images on social sites (the default maximum is 50%). If an image needs to be upscaled beyond this maximum &ndash; <em>in either width or height</em> &ndash; the image will not be upscaled.';
							break;
						case 'tooltip-plugin_shortcodes':
							$text = 'Enable the '.$info['short'].' shortcode features (default is checked).';
							break;
						case 'tooltip-plugin_widgets':
							$text = 'Enable the '.$info['short'].' widget features (default is checked).';
							break;
						case 'tooltip-plugin_page_excerpt':
							$text = 'Enable the excerpt editing metabox for Pages. Excerpts are optional hand-crafted summaries of your content that '.$info['short'].' can use as a default description value.';
							break;
						case 'tooltip-plugin_page_tags':
							$text = 'Enable the tags editing metabox for Pages. Tags are optional keywords that highlight the content subject(s), often used for searches and "tag clouds". '.$info['short'].' converts tags into hashtags for some social websites (Twitter, Facebook, Google+, etc.).';
							break;
						/*
						 * 'File and Object Cache' settings
						 */
						case 'tooltip-plugin_object_cache_exp':
							// use the original un-filtered value
							$exp_sec = NgfbConfig::$cf['opt']['defaults']['plugin_object_cache_exp'];
							$exp_hrs = sprintf( '%0.2d', $exp_sec / 60 / 60 );
							$text = '<p>'.$info['short'].' saves filtered and rendered content to a non-persistant cache (aka <a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache" target="_blank">WP Object Cache</a>), and the meta tag HTMLs to a persistant (aka <a href="https://codex.wordpress.org/Transients_API" target="_blank">Transient</a>) cache. The default is '.$exp_sec.' seconds ('.$exp_hrs.' hrs), and the minimum value is 1 second (values bellow 3600 seconds are not recommended). If you have database performance issues, or don’t use an object / transient cache (like Memcache, Xcache, etc.), you may want to disable the transient caching feature completely by setting the NGFB_TRANSIENT_CACHE_DISABLE constant to true.</p>';
							break;
						case 'tooltip-plugin_verify_certs':
							$text = 'Enable verification of peer SSL certificates when fetching content to be cached using HTTPS. The PHP \'curl\' function will use the '.NGFB_CURL_CAINFO.' certificate file by default. You can define a NGFB_CURL_CAINFO constant in your wp-config.php file to use an alternate certificate file.';
							break;
						case 'tooltip-plugin_cache_info':
							$text = 'Report the number of objects removed from the cache when updating Posts and Pages.';
							break;
						case 'tooltip-plugin_file_cache_exp':
							$text = $info['short_pro'].' can save most social sharing JavaScript and images to a cache folder, providing URLs to these cached files instead of the originals. A value of 0 hours (the default) disables the file caching feature. If your hosting infrastructure performs reasonably well, this option can improve page load times significantly. All social sharing images and javascripts will be cached, except for the Facebook JavaScript SDK, which does not work correctly when cached.';
							break;
						/*
						 * 'Service API Keys' (URL Shortening) settings
						 */
						case 'tooltip-plugin_shortener':
							$text = sprintf( __( 'A preferred URL shortening service for %s plugin filters and/or extensions that may need to shorten URLs &mdash; don\'t forget to define the Service API Keys for the URL shortening service of your choice.', 'nextgen-facebook' ), $info['short'] );
							break;
						case 'tooltip-plugin_shortlink':
							$text = __( 'The <em>Get Shortlink</em> button on Posts / Pages admin editing pages provides the shortened sharing URL instead of the default WordPress shortlink URL.', 'nextgen-facebook' );
							break;
						case 'tooltip-plugin_min_shorten':
							$text = sprintf( __( 'URLs shorter than this length will not be shortened (the default suggested by Twitter is %d characters).', 'nextgen-facebook' ), $this->p->opt->get_defaults( 'plugin_min_shorten' ) );
							break;
						case 'tooltip-plugin_bitly_login':
							$text = sprintf( __( 'The username for your Bitly API key (see <a href="%s" target="_blank">Your Bitly API Key</a> for details).', 'nextgen-facebook' ), 'https://bitly.com/a/your_api_key' );
							break;
						case 'tooltip-plugin_bitly_api_key':
							$text = sprintf( __( 'To use Bitly as your preferred shortening service, you must provide the Bitly API key for this website (see <a href="%s" target="_blank">Your Bitly API Key</a> for details).', 'nextgen-facebook' ), 'https://bitly.com/a/your_api_key' );
							break;
						case 'tooltip-plugin_google_api_key':
							$text = sprintf( __( 'The Google BrowserKey value for this website (project). If you don\'t already have a Google project, visit <a href="%s" target="_blank">Google\'s Cloud Console</a> and create a new project for your website (use the \'Select a project\' drop-down).', 'nextgen-facebook' ), 'https://console.developers.google.com/start' );
							break;
						case 'tooltip-plugin_google_shorten':
							$text = sprintf( __( 'In order to use Google\'s URL Shortener API service, you must <em>Enable</em> the URL Shortener API from <a href="%s" target="_blank">Google\'s Cloud Console</a> (under the project\'s <em>API &amp; auth / APIs / URL Shortener API</em> settings page).', 'nextgen-facebook' ), 'https://console.developers.google.com/start' ).' '.__( 'Confirm that you have enabled Google\'s URL Shortener API service by checking the \'Yes\' option value.', 'nextgen-facebook' );
							break;
						case 'tooltip-plugin_owly_api_key':
							$text = sprintf( __( 'To use Ow.ly as your preferred shortening service, you must provide the Ow.ly API key for this website (complete this form to <a href="%s" target="_blank">Request Ow.ly API Access</a>).', 'nextgen-facebook' ), 'https://docs.google.com/forms/d/1Fn8E-XlJvZwlN4uSRNrAIWaY-nN_QA3xAHUJ7aEF7NU/viewform' );
							break;
						case 'tooltip-plugin_yourls_api_url':
							$text = sprintf( __( 'The URL to <a href="%1$s" target="_blank">Your Own URL Shortener</a> (YOURLS) shortening service.', 'nextgen-facebook' ), 'http://yourls.org/' );
							break;
						case 'tooltip-plugin_yourls_username':
							$text = sprintf( __( 'If <a href="%1$s" target="_blank">Your Own URL Shortener</a> (YOURLS) shortening service is private, enter a configured username (see YOURLS Token for an alternative to the username / password options).', 'nextgen-facebook' ), 'http://yourls.org/' );
							break;
						case 'tooltip-plugin_yourls_password':
							$text = sprintf( __( 'If <a href="%1$s" target="_blank">Your Own URL Shortener</a> (YOURLS) shortening service is private, enter a configured user password (see YOURLS Token for an alternative to the username / password options).', 'nextgen-facebook' ), 'http://yourls.org/' );
							break;
						case 'tooltip-plugin_yourls_token':
							$text = sprintf( __( 'If <a href="%1$s" target="_blank">Your Own URL Shortener</a> (YOURLS) shortening service is private, you can use a token string for authentication instead of a username / password combination.', 'nextgen-facebook' ), 'http://yourls.org/' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_plugin', $text, $idx, $info );
							break;
					}	// end of tooltip-plugin switch
				/*
				 * Publisher 'Facebook' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-fb_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-fb_publisher_url':
							$text = sprintf( __( 'If you have a <a href="%1$s" target="_blank">Facebook Business Page for your website / business</a>, you may enter its URL here (for example, the Facebook Business Page URL for %2$s is <a href="%3$s" target="_blank">%4$s</a>).', 'nextgen-facebook' ), 'https://www.facebook.com/business', 'Surnia Ulula', 'https://www.facebook.com/SurniaUlulaCom', 'https://www.facebook.com/SurniaUlulaCom' ).' '.__( 'The Facebook Business Page URL will be used in Open Graph <em>article</em> type webpages (not index or archive webpages) and schema publisher (Organization) social JSON.', 'nextgen-facebook' ).' '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						case 'tooltip-fb_admins':
							$text = sprintf( __( 'The \'%1$s\' are used by Facebook to allow access to <a href="%2$s" target="_blank">Facebook Insight</a> data for your website. Note that these are <strong>user account names, not Facebook Page names</strong>. Enter one or more Facebook user names, separated with commas. When viewing your own Facebook wall, your user name is located in the URL (for example, https://www.facebook.com/<strong>user_name</strong>). Enter only the user names, not the URLs.', 'nextgen-facebook' ), _x( 'Facebook Admin Username(s)', 'option label', 'nextgen-facebook' ), 'https://developers.facebook.com/docs/insights/' ).' '.sprintf( __( 'You may update your Facebook user name in the <a href="%1$s" target="_blank">Facebook General Account Settings</a>.', 'nextgen-facebook' ), 'https://www.facebook.com/settings?tab=account&section=username&view' );
							break;
						case 'tooltip-fb_app_id':
							$text = sprintf( __( 'If you have a <a href="%1$s" target="_blank">Facebook Application ID for your website</a>, enter it here. The Facebook Application ID will appear in webpage meta tags and is used by Facebook to allow access to <a href="%2$s" target="_blank">Facebook Insight</a> data for accounts associated with that Application ID.', 'nextgen-facebook' ), 'https://developers.facebook.com/apps', 'https://developers.facebook.com/docs/insights/' );
							break;
						case 'tooltip-fb_author_name':
							$text = sprintf( __( '%1$s uses the Facebook contact field value in the author\'s WordPress profile for <code>article:author</code> Open Graph meta tags. This allows Facebook to credit an author on shares, and link their Facebook page URL.', 'nextgen-facebook' ), $info['short'] ).' '.sprintf( __( 'If an author does not have a Facebook page URL, %1$s can fallback and use the <em>%2$s</em> instead (the recommended value is \'Display Name\').', 'nextgen-facebook' ), $info['short'], _x( 'Author Name Format', 'option label', 'nextgen-facebook' ) );
							break;
						case 'tooltip-fb_lang':
							$text = __( 'The default language of your website content, used in the Facebook / Open Graph and Pinterest Rich Pin meta tags. The Pro version can also use the WordPress locale to adjust the language value dynamically (useful for websites with multilingual content).', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_fb', $text, $idx, $info );
							break;
					}	// end of tooltip-fb switch
				/*
				 * Publisher 'Google' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-google_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-google_publisher_url':
							$text = 'If you have a <a href="http://www.google.com/+/business/" target="_blank">Google+ Business Page for your website / business</a>, you may enter its URL here (for example, the Google+ Business Page URL for Surnia Ulula is <a href="https://plus.google.com/+SurniaUlula/" target="_blank">https://plus.google.com/+SurniaUlula/</a>). The Google+ Business Page URL will be used in a link relation header tag, and the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						case 'tooltip-google_desc_len':
							$text = 'The maximum length of text used for the Google Search / SEO description meta tag. The length should be at least '.$this->p->cf['head']['min']['og_desc_len'].' characters or more (the default is '.$this->p->opt->get_defaults( 'seo_desc_len' ).' characters).';
							break;
						case 'tooltip-google_author_field':
							$text = $info['short'].' can include an <em>author</em> and <em>publisher</em> link in your webpage headers. These are not Facebook / Open Graph and Pinterest Rich Pin meta property tags &mdash; they are used primarily by Google\'s search engine to associate Google+ profiles with search results. Select which field to use from the author\'s profile for the <em>author</em> link tag.';
							break;
						case 'tooltip-google_def_author_id':
							$text = 'A default author for webpages missing authorship information (for example, an index webpage without posts). If you have several authors on your website, you should probably leave this option set to <em>[None]</em> (the default). This option is similar to the Facebook / Open Graph and Pinterest Rich Pin Default Author, except that it\'s applied to the Link meta tag instead.';
							break;
						case 'tooltip-google_def_author_on_index':
							$text = 'Check this option if you would like to force the Default Author on index webpages (<strong>non-static</strong> homepage, archives, categories, author, etc.).';
							break;
						case 'tooltip-google_def_author_on_search':
							$text = 'Check this option if you would like to force the Default Author on search result webpages as well.';
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_google', $text, $idx, $info );
							break;
					}	// end of tooltip-google switch
				/*
				 * Publisher 'Schema' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-schema_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-schema_add_noscript':
							$text = 'When additional schema properties are available (product ratings, for example), one or more "noscript" containers can be included in webpage headers. The "noscript" container is read correctly by the Google Structured Data Testing Tool, but the W3C Validator will show errors for the included meta tags (these errors can be safely ignored).';
							break;
						case 'tooltip-schema_social_json':
							$text = 'Include Website, Organization, and/or Person schema markup in the home page for Google. The Website markup includes the site name, alternate site name, site URL and search query URL. Developers can hook the \''.$lca.'_json_ld_search_url\' filter to modify the site search URL (or disable its addition by returning false). The Organization markup includes all URLs entered on the '.$this->p->util->get_admin_url( 'social-accounts', 'Website Social Pages and Accounts' ).' settings page. The Person markup includes all contact method URLs from the user\'s profile page.';
							break;
						case 'tooltip-schema_alt_name':
							$text = 'An alternate name for your Website that you want Google to consider (optional).';
							break;
						case 'tooltip-schema_logo_url':
							$text = 'An image of your organization\'s logo that Google can use in search results and <em>Knowledge Graph</em>.';
							break;
						case 'tooltip-schema_banner_url':
							$text = 'A 600x60px banner of your organization\'s logo that Google can use in Articles and other Schema item types.';
							break;
						case 'tooltip-schema_img_max':
							$text = 'The maximum number of images to include in the Google / Schema markup -- this includes the <em>featured</em> or <em>attached</em> images, and any images found in the Post or Page content. If you select \'0\', then no images will be listed in the Google / Schema meta tags (<strong>not recommended</strong>).';
							break;
						case 'tooltip-schema_img_dimensions':
							$def_dimensions = $this->p->opt->get_defaults( 'schema_img_width' ).'x'.
								$this->p->opt->get_defaults( 'schema_img_height' ).' '.
								( $this->p->opt->get_defaults( 'schema_img_crop' ) == 0 ? 'uncropped' : 'cropped' );
							$text = 'The image dimensions used in the Google / Schema meta tags and JSON-LD markup (the default dimensions are '.$def_dimensions.'). The minimum image width required by Google is 696px for the resulting resized image. If you do not choose to crop this image size, make sure the height value is large enough for portrait / vertical images.';
							break;
						case 'tooltip-schema_desc_len':
							$text = 'The maximum length of text used for the Google+ / Schema description meta tag. The length should be at least '.$this->p->cf['head']['min']['og_desc_len'].' characters or more (the default is '.$this->p->opt->get_defaults( 'schema_desc_len' ).' characters).';
							break;
						case 'tooltip-schema_author_name':
							$text = sprintf( __( 'Select an <em>%1$s</em> for the author / Person markup, or \'[None]\' to disable this feature (the recommended value is \'Display Name\').', 'nextgen-facebook' ), _x( 'Author Name Format', 'option label', 'nextgen-facebook' ) );
							break;
						case 'tooltip-schema_home_page':
							$text = 'Select the Schema type for the site home page. The Schema type is used to define the item type for Schema JSON-LD and/or meta tags in webpage headers. The default Schema type for the home page is http://schema.org/WebSite.';
							break;
						case 'tooltip-schema_type_for_ptn':
							$text = 'Select the Schema type for each WordPress post type. The Schema type is used to define the item type for Schema JSON-LD and/or meta tags in webpage headers.';
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_google', $text, $idx, $info );
							break;
					}	// end of tooltip-google switch
				/*
				 * Publisher 'Twitter Card' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-tc_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-tc_site':
							$text = 'The <a href="https://business.twitter.com/" target="_blank">Twitter @username for your website and/or business</a> (not your personal Twitter @username). As an example, the Twitter @username for Surnia Ulula is <a href="https://twitter.com/surniaululacom" target="_blank">@surniaululacom</a>. The website / business @username is also used for the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						case 'tooltip-tc_desc_len':
							$text = 'The maximum length of text used for the Twitter Card description. The length should be at least '.$this->p->cf['head']['min']['og_desc_len'].' characters or more (the default is '.$this->p->opt->get_defaults( 'tc_desc_len' ).' characters).';
							break;
						case 'tooltip-tc_sum_dimensions':
							$card = 'sum';
							$text = 'The dimension of content images provided for the <a href="https://dev.twitter.com/docs/cards/types/summary-card" target="_blank">Summary Card</a> (should be at least 120x120, larger than 60x60, and less than 1MB). The default image dimensions are '.$this->p->opt->get_defaults( 'tc_'.$card.'_width' ).'x'.$this->p->opt->get_defaults( 'tc_'.$card.'_height' ).', '.( $this->p->opt->get_defaults( 'tc_'.$card.'_crop' ) ? '' : 'un' ).'cropped.';
							break;
						case 'tooltip-tc_lrgimg_dimensions':
							$card = 'lrgimg';
							$text = 'The dimension of Post Meta, Featured or Attached images provided for the <a href="https://dev.twitter.com/docs/cards/large-image-summary-card" target="_blank">Large Image Summary Card</a> (must be larger than 280x150 and less than 1MB). The default image dimensions are '.$this->p->opt->get_defaults( 'tc_'.$card.'_width' ).'x'.$this->p->opt->get_defaults( 'tc_'.$card.'_height' ).', '.( $this->p->opt->get_defaults( 'tc_'.$card.'_crop' ) ? '' : 'un' ).'cropped.';
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_tc', $text, $idx, $info );
							break;
					}	// end of tooltip-tc switch
				/*
				 * Publisher 'Pinterest' (Rich Pin) settings
				 */
				} elseif ( strpos( $idx, 'tooltip-rp_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-rp_publisher_url':
							$text = 'If you have a <a href="https://business.pinterest.com/" target="_blank">Pinterest Business Page for your website / business</a>, you may enter its URL here. The Publisher Business Page URL will be used in the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						case 'tooltip-rp_img_dimensions':
							$def_dimensions = $this->p->opt->get_defaults( 'rp_img_width' ).'x'.
								$this->p->opt->get_defaults( 'rp_img_height' ).' '.
								( $this->p->opt->get_defaults( 'rp_img_crop' ) == 0 ? 'uncropped' : 'cropped' );
							$text = 'The image dimensions specifically for Rich Pin meta tags when the Pinterest crawler is detected (the default dimensions are '.$def_dimensions.'). Images in the Facebook / Open Graph meta tags are usually cropped square, where-as images on Pinterest often look better in their original aspect ratio (uncropped) and/or cropped using portrait photo dimensions. Note that original images in the WordPress Media Library and/or NextGEN Gallery must be larger than your chosen image dimensions.';
							break;
						case 'tooltip-rp_author_name':
							$text = __( 'Pinterest ignores Facebook-style Author Profile URLs in the <code>article:author</code> Open Graph meta tags.', 'nextgen-facebook' ).' '.__( 'A different meta tag value can be used when the Pinterest crawler is detected.', 'nextgen-facebook' ).' '.sprintf( __( 'Select an <em>%1$s</em> for the <code>%2$s</code> meta tag or \'[None]\' to disable this feature (the recommended value is \'Display Name\').', 'nextgen-facebook' ), _x( 'Author Name Format', 'option label', 'nextgen-facebook' ), 'article:author' );
							break;
						case 'tooltip-rp_dom_verify':
							$text = sprintf( __( 'To <a href="%s" target="_blank">verify your website</a> with Pinterest, edit your business account profile on Pinterest and click the "Verify Website" button.', 'nextgen-facebook' ), 'https://help.pinterest.com/en/articles/verify-your-website#meta_tag' ).' '.__( 'Enter the supplied \'p:domain_verify\' meta tag <em>content</em> value here.', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_rp', $text, $idx, $info );
							break;
					}	// end of tooltip-rp switch
				/*
				 * Publisher 'Instagram' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-instgram_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-instgram_publisher_url':
							$text = 'If you have an <a href="http://blog.business.instagram.com/" target="_blank">Instagram account for your website / business</a>, you may enter its URL here. The Instagram Business URL will be used in the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_instgram', $text, $idx, $info );
							break;
					}	// end of tooltip-instgram switch

				/*
				 * Publisher 'LinkedIn' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-linkedin_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-linkedin_publisher_url':
							$text = 'If you have a <a href="https://business.linkedin.com/marketing-solutions/company-pages/get-started" target="_blank">LinkedIn Company Page for your website / business</a>, you may enter its URL here (for example, the LinkedIn Company Page URL for Surnia Ulula is <a href="https://www.linkedin.com/company/surnia-ulula-ltd" target="_blank">https://www.linkedin.com/company/surnia-ulula-ltd</a>). The LinkedIn Company Page URL will be included in the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_linkedin', $text, $idx, $info );
							break;
					}	// end of tooltip-linkedin switch
				/*
				 * Publisher 'MySpace' settings
				 */
				} elseif ( strpos( $idx, 'tooltip-myspace_' ) === 0 ) {
					switch ( $idx ) {
						case 'tooltip-myspace_publisher_url':
							$text = 'If you have a <a href="http://myspace.com/" target="_blank">MySpace account for your website / business</a>, you may enter its URL here. The MySpace Business (Brand) URL will be used in the schema publisher (Organization) social JSON. '.__( 'Google Search may use this information to display additional publisher / business details in its search results.', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip_myspace', $text, $idx, $info );
							break;
						}	// end of tooltip-myspace switch
				/*
				 * All other settings
				 */
				} else {
					switch ( $idx ) {
						case 'tooltip-custom-cm-field-name':
							$text = '<strong>You should not modify the contact field names unless you have a specific reason to do so.</strong> As an example, to match the contact field name of a theme or other plugin, you might change \'gplus\' to \'googleplus\'. If you change the Facebook or Google+ field names, please make sure to update the Open Graph <em>Author Profile URL</em> and <em>Google Author Link URL</em> options in the '.$this->p->util->get_admin_url( 'general', 'General Settings' ).' as well.';
							break;
						case 'tooltip-wp-cm-field-name':
							$text = __( 'The built-in WordPress contact field names cannot be modified.', 'nextgen-facebook' );
							break;
						case 'tooltip-site-use':
							$text = __( 'Individual sites/blogs may use this value as a default (when the plugin is first activated), if the current site/blog option value is blank, or force every site/blog to use this specific value.', 'nextgen-facebook' );
							break;
						default:
							$text = apply_filters( $lca.'_messages_tooltip', $text, $idx, $info );
							break;
					} 	// end of all other settings switch
				}	// end of tooltips
			/*
			 * Misc informational messages
			 */
			} elseif ( strpos( $idx, 'info-' ) === 0 ) {
				switch ( $idx ) {
					case 'info-meta-social-preview':
					 	$text = '<p style="text-align:right;">'.__( 'The Open Graph social preview shows an <em>example</em> of a typical share on a social website. Images are displayed using Facebooks suggested minimum image dimensions of 600x315px. Actual shares on Facebook and other social websites may look significantly different than this example (depending on the client platform, resolution, orientation, etc.).', 'nextgen-facebook' ).'</p>';
					 	break;
					case 'info-plugin-tid':
						$um_info = $this->p->cf['plugin']['ngfbum'];
						$text = '<blockquote class="top-info"><p>'.__( 'After purchasing one or more Pro version license(s), an email is sent to you with an Authentication ID and installation / activation instructions.', 'nextgen-facebook' ).' '.__( 'Enter the unique Authentication ID on this settings page to check for Pro version updates immediately, and every 24 hours thereafter.', 'nextgen-facebook' ).'</p><p><strong>'.sprintf( __( 'The %s extension must be active in order to check for Pro version updates.', 'nextgen-facebook' ), $um_info['name'] ).'</strong> '.sprintf( __( 'If you accidentally de-activate the %1$s extension, update information will be provided by the WordPress.org plugin repository, and any update notices will be for the Free versions &mdash; always update the Pro version when the %2$s extension is active.', 'nextgen-facebook' ), $um_info['short'], $um_info['short'] ).' '.__( 'If you accidentally re-install the Free version from WordPress.org &ndash; don\'t worry &ndash; your Authentication ID will always allow you update back to the Pro version.', 'nextgen-facebook' ).' ;-)</p></blockquote>';
						break;
					case 'info-plugin-tid-network':
						$um_info = $this->p->cf['plugin']['ngfbum'];
						$text = '<blockquote class="top-info"><p>'.__( 'After purchasing one or more Pro version license(s), an email is sent to you with an Authentication ID and installation / activation instructions.', 'nextgen-facebook' ).' '.__( 'You may enter the unique Authentication ID on this page <em>to define a value for all sites within the network</em> &mdash; or enter the Authentication ID individually on each site\'s Pro Licenses settings page.', 'nextgen-facebook' ).'</p><p>'.__( 'If you enter an Authentication ID here, <em>please make sure you have purchased enough licenses to license all sites within the network</em> (for example, if you have 10 sites, you will need 10 or more licenses).', 'nextgen-facebook' ).' <strong>'.__( 'To license one or more sites individually, enter the Authentication ID in each site\'s Pro Licenses settings page.', 'nextgen-facebook' ).'</strong></p><p>'.sprintf( __( 'Please note that <em>the default site / blog must be licensed</em> and the %1$s extension active, in order to install %2$s version updates from the network admin interface.', 'nextgen-facebook' ), $um_info['name'], $info['short_pro'] ).'</p></blockquote>';
						break;
					case 'info-pub-pinterest':
						$text = '<blockquote class="top-info"><p>'.__( 'These options allow you to customize some Open Graph meta tag and Schema markup values for the Pinterest crawler.', 'nextgen-facebook' ).' '.__( 'If you use a caching plugin (or front-end caching service), it should detect the Pinterest user-agent and bypass its cache (for example, look for a <em>User-Agent Exclusion Pattern</em> setting and add "Pinterest/" to that list).', 'nextgen-facebook' ).'</p></blockquote>';
						break;
					case 'info-cm':
						$text = '<blockquote class="top-info"><p>'.sprintf( __( 'The following options allow you to customize the contact fields shown in <a href="%s">the user profile page</a> under the <strong>Contact Info</strong> header.', 'nextgen-facebook' ), get_admin_url( null, 'profile.php' ) ).' '.sprintf( __( '%s uses the Facebook, Google+, and Twitter contact values for Facebook / Open Graph, Google / Schema, and Twitter Card meta tags.', 'nextgen-facebook' ), $info['short'] ).'</p><p><strong>'.sprintf( __( 'You should not modify the <em>%s</em> unless you have a <em>very</em> good reason to do so.', 'nextgen-facebook' ), _x( 'Contact Field Name', 'column title', 'nextgen-facebook' ) ).'</strong> '.sprintf( __( 'The <em>%s</em> on the other hand is for display purposes only and it can be changed as you wish.', 'nextgen-facebook' ), _x( 'Profile Contact Label', 'column title', 'nextgen-facebook' ) ).' ;-)</p><p>'.sprintf( __( 'Enabled contact methods are included on user profile editing pages automatically. Your theme is responsible for using their values in its templates (see the WordPress <a href="%s" target="_blank">get_the_author_meta()</a> documentation for examples).', 'nextgen-facebook' ), 'https://codex.wordpress.org/Function_Reference/get_the_author_meta' ).'</p><p><center><strong>'.__( 'DO NOT ENTER YOUR CONTACT INFORMATION HERE &ndash; THESE ARE CONTACT FIELD LABELS ONLY.', 'nextgen-facebook' ).'</strong><br/>'.sprintf( __( 'Enter your personal contact information on <a href="%1$s">the user profile page</a>.', 'nextgen-facebook' ), get_admin_url( null, 'profile.php' ) ).'</center></p></blockquote>';
						break;
					case 'info-taglist':
						$text = '<blockquote class="top-info"><p>'.sprintf( __( '%s adds the following Google / SEO, Facebook, Open Graph, Rich Pin, Schema, and Twitter Card HTML tags to the <code>&lt;head&gt;</code> section of your webpages.', 'nextgen-facebook' ), $info['short'] ).' '.__( 'If your theme or another plugin already creates one or more of these HTML tags, you can uncheck them here to prevent duplicates from being added.', 'nextgen-facebook' ).' '.__( 'As an example, the "meta name description" HTML tag is automatically unchecked if a <em>known</em> SEO plugin is detected.', 'nextgen-facebook' ).' '.__( 'The "meta name canonical" HTML tag is unchecked by default since themes often include this meta tag in their header template(s).', 'nextgen-facebook' ).'</p></blockquote>';
						break;
					case 'info-social-accounts':
						$text = '<blockquote class="top-info"><p>'.__( 'The website / business social account values are used for SEO, Schema, Open Graph, and other social meta tags &ndash; including publisher (Organization) social markup for Google Search.', 'nextgen-facebook' ).'</p><p>'.sprintf( __( 'See the <a href="%s">Google / Schema settings tab</a> to define a website / business logo for Google Search results, and enable / disable the addition of publisher (Organization) and/or author (Person) JSON-LD markup.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_google' ) ).'</p></blockquote>';
						break;
					default:
						$text = apply_filters( $lca.'_messages_info', $text, $idx, $info );
						break;
				}	// end of info switch
			/*
			 * Misc pro messages
			 */
			} elseif ( strpos( $idx, 'pro-' ) === 0 ) {
				switch ( $idx ) {
					case 'pro-feature-msg':
						if ( $this->p->check->aop( $lca, false ) )
							$text = '<p class="pro-feature-msg"><a href="'.$url['purchase'].'" target="_blank">'.sprintf( __( 'Purchase %s licence(s) to install / enable Pro modules and modify the following options.', 'nextgen-facebook' ), $info['short_pro'] ).'</a></p>';
						else $text = '<p class="pro-feature-msg"><a href="'.$url['purchase'].'" target="_blank">'.sprintf( __( 'Purchase the %s plugin to install / enable Pro modules and modify the following options.', 'nextgen-facebook' ), $info['short_pro'] ).'</a></p>';
						break;
					case 'pro-option-msg':
						$text = '<p class="pro-option-msg"><a href="'.$url['purchase'].'" target="_blank">'.sprintf( _x( '%s required to use this option', 'option comment', 'nextgen-facebook' ), $info['short_pro'] ).'</a></p>';
						break;
					case 'pro-about-msg-post':
						// additional text for the following pro-about-msg paragraph
						$info['text'] = __( 'You can modify the description values by updating the content or excerpt, and change the social image by selecting a featured image, attaching one or more images, or including images in the content.', 'nextgen-facebook' );
						// no break
					case 'pro-about-msg':
						$text = '<p class="pro-about-msg">'.sprintf( __( 'The Free / Basic version of %1$s does not include the modules required to manage custom post, term, or user meta &mdash; these options are included for display purposes only.', 'nextgen-facebook' ), $info['short'] ).( empty( $info['text'] ) ? '' : ' '.$info['text'] ).'</p>';
						break;
					default:
						$text = apply_filters( $lca.'_messages_pro', $text, $idx, $info );
						break;
				}
			/*
			 * Misc notice messages
			 */
			} elseif ( strpos( $idx, 'notice-' ) === 0 ) {
				switch ( $idx ) {
					case 'notice-image-rejected':
						$hide_const_name = strtoupper( $lca ).'_HIDE_ALL_WARNINGS';
						$hide_warnings = SucomUtil::get_const( $hide_const_name );

						$text = __( 'The <em>Select Media</em> tab in the Social Settings metabox can be used to select a larger image specifically for social / SEO purposes.', 'nextgen-facebook' );
						if ( current_user_can( 'manage_options' ) ) {
							$text .= '<p><em>'.__( 'Additional information shown only to users with Administrative privileges:', 'nextgen-facebook' ).'</em></p>';
							$text .= '<ul>';
							$text .= '<li>'.sprintf( __( 'You can also adjust the <b>%2$s</b> option in the <a href="%1$s">Social and SEO Image Dimensions</a> settings.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'image-dimensions' ), $info['size_label'] ).'</li>';
							$text .= '<li>'.sprintf( __( 'Enable or increase the <a href="%1$s">WP / Theme Integration</a> <em>image upscaling percentage</em> feature.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_integration' ) ).'</li>';
							$text .= '<li>'.sprintf( __( 'Disable the <a href="%1$s">WP / Theme Integration</a> <em>image dimensions check</em> option (not recommended).', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_integration' ) ).'</li>';
							if ( ! $hide_warnings )
								$text .= '<li>'.sprintf( __( 'Define the %1$s constant as <em>true</em> to auto-hide all dismissable warnings.', 'nextgen-facebook' ), $hide_const_name ).'</li>';
							$text .= '</ul>';
						}
						break;
					case 'notice-missing-og-image':
						$text = __( 'An Open Graph image meta tag could not be created from this webpage content. Facebook and other social websites <em>require</em> at least one image meta tag to render shared content correctly.', 'nextgen-facebook' );
						break;
					case 'notice-missing-schema-image':
						$text = __( 'Google / Schema image markup could not be created from this webpage content. Google <em>requires</em> at least one image object for this Schema item type.', 'nextgen-facebook' );
						break;
					case 'notice-missing-schema_logo_url':
						$text = __( 'A Business Logo Image is missing for the Schema Organization markup.', 'nextgen-facebook' ).' '.
						sprintf( __( 'Please entrer a Business Logo Image URL in the %1$s settings.', 'nextgen-facebook' ),
							( $this->p->is_avail['json'] ? '<a href="'.$this->p->util->get_admin_url( 'schema-json-ld' ).'">Schema JSON-LD</a>' :
								'<a href="'.$this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_google' ).'">Google / Schema</a>' ) );
						break;
					case 'notice-missing-schema_banner_url':
						$text = __( 'A Business Banner Image is missing for the Schema Organization markup.', 'nextgen-facebook' ).' '.
						sprintf( __( 'Please enter a Business Banner (600x60) Image URL in the %1$s settings.', 'nextgen-facebook' ),
							( $this->p->is_avail['json'] ? '<a href="'.$this->p->util->get_admin_url( 'schema-json-ld' ).'">Schema JSON-LD</a>' :
								'<a href="'.$this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_google' ).'">Google / Schema</a>' ) );
						break;
					case 'notice-object-cache-exp':
						$text = sprintf( __( 'Please note that the <a href="%1$s">%2$s</a> advanced option is currently set at %3$d seconds &mdash; this is lower than the recommended default value of %4$d seconds.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_cache' ), _x( 'Object Cache Expiry', 'option label', 'nextgen-facebook' ), $this->p->options['plugin_object_cache_exp'], $this->p->opt->get_defaults( 'plugin_object_cache_exp' ) );
						break;
					case 'notice-content-filters-disabled':
						$text = '<p><b>'.sprintf( __( 'The <a href="%1$s">%2$s</a> advanced option is currently disabled.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content' ), _x( 'Apply WordPress Content Filters', 'option label', 'nextgen-facebook' ) ).'</b> '.sprintf( __( 'The use of WordPress content filters allows %s to fully render your content text for meta tag descriptions, and detect additional images / embedded videos provided by shortcodes.', 'nextgen-facebook' ), $info['short'] ).'</p><p><b>'.__( 'Some theme / plugins have badly coded content filters, so this option is disabled by default.', 'nextgen-facebook' ).'</b> '.sprintf( __( '<a href="%s">If you use any shortcodes in your content text, this option should be enabled</a> (Pro version required) &mdash; if you experience display issues after enabling this option, determine which theme / plugin content filter is at fault, and report the problem to its author(s).', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_content' ) ).'</p>';
						break;
					case 'notice-header-tmpl-no-head-attr':
						$action_url = wp_nonce_url( $this->p->util->get_admin_url( '?'.$this->p->cf['lca'].'-action=modify_tmpl_head_elements' ),
							NgfbAdmin::get_nonce(), NGFB_NONCE );
						$text = '<p><b>'.__( 'At least one of your theme header templates does not support Schema markup of the webpage head section.', 'nextgen-facebook' ).'</b> '.sprintf( __( 'The %s element in your theme\'s header templates should include a function / action / filter call for its attributes.', 'nextgen-facebook' ), '<code>&lt;head&gt;</code>' ).' '.sprintf( __( '%1$s can update your theme header templates automatically to change the default %2$s element to:', 'nextgen-facebook' ), $info['short'], '<code>&lt;head&gt;</code>' ).'</p><pre><code>&lt;head &lt;?php do_action( \'add_head_attributes\' ); ?&gt;&gt;</code></pre><p>'.sprintf( __( '<b><a href="%1$s">Click here to update theme header templates automatically</a></b> or update the theme templates yourself manually.', 'nextgen-facebook' ), $action_url ).'</p>';
						break;
					case 'notice-pro-tid-missing':
						if ( ! is_multisite() )
							$text = '<p><b>'.sprintf( __( 'The %1$s plugin %2$s option is empty.', 'nextgen-facebook' ), $info['name'], _x( 'Pro Authentication ID', 'option label', 'nextgen-facebook' ) ).'</b> '.sprintf( __( 'To enable Pro version features and allow the plugin to authenticate itself for updates, please enter the unique Authentication ID you received by email on the <a href="%s">Pro Licenses and Extension Plugins</a> settings page.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'licenses' ) ).'</p>';
						break;
					case 'notice-pro-not-installed':
						$text = sprintf( __( 'An Authentication ID has been entered for %s, but the Pro version is not yet installed &ndash; don\'t forget to update the plugin to install the latest Pro version. ;-)', 'nextgen-facebook' ), $info['name'] );
						break;
					case 'notice-um-extension-required':
					case 'notice-um-activate-extension':
						$um_info = $this->p->cf['plugin']['ngfbum'];
						$wp_upload_url = get_admin_url( null, 'plugin-install.php?tab=upload' );
						$text = '<p><b>'.sprintf( __( 'At least one Authentication ID has been entered on the <a href="%1$s">Pro Licenses and Extension Plugins</a> settings page, but the %2$s plugin is not active.', 'nextgen-facebook' ), $this->p->util->get_admin_url( 'licenses' ), $um_info['name'] ).'</b> ';

						if ( $idx === 'notice-um-extension-required' ) {
							$text .= sprintf( __( 'This Free plugin is required to update and enable the %s plugin and its Pro extensions.', 'nextgen-facebook' ), $info['name_pro'] ).'</p><ol><li><b>'.sprintf( __( 'Download the Free <a href="%1$s">%2$s plugin archive</a> (ZIP).', 'nextgen-facebook' ), $um_info['url']['latest_zip'], $um_info['name'] ).'</b></li><li><b>'.sprintf( __( 'Then <a href="%s">upload and activate the plugin</a> on the WordPress plugin upload page.', 'nextgen-facebook' ), $wp_upload_url ).'</b></li></ol>';
						} else $text .= '</p>';

						$text .= '<p>'.sprintf( __( 'Once the %s extension has been activated, one or more Pro version updates may be available for your licensed plugin(s).', 'nextgen-facebook' ), $um_info['name'] ).' '.sprintf( __( 'Read more <a href="%1$s" target="_blank">about the %2$s extension plugin</a>.', 'nextgen-facebook' ), $um_info['url']['download'], $um_info['name'] ).'</p>';
						break;
					case 'notice-um-version-required':
						$um_info = $this->p->cf['plugin']['ngfbum'];
						$um_version = isset( $um_info['version'] ) ? $um_info['version'] : 'unknown';
						$text = sprintf( __( '%1$s version %2$s requires the use of %3$s version %4$s or newer (version %5$s is currently installed).', 'nextgen-facebook' ), $info['name_pro'], $this->p->cf['plugin']['ngfb']['version'], $um_info['short'], $info['um_min_version'], $um_version ).' '.sprintf( __( 'Use the <em>%1$s</em> button from any %2$s settings page to retrieve the latest update information, or <a href="%3$s" target="_blank">download the latest %4$s extension version</a> and install the ZIP file manually.', 'nextgen-facebook' ), _x( 'Check for Pro Update(s)', 'submit button', 'nextgen-facebook' ), $this->p->cf['menu'], $um_info['url']['download'], $um_info['short'] );
						break;
					default:
						$text = apply_filters( $lca.'_messages_notice', $text, $idx, $info );
						break;
			}
			/*
			 * Misc sidebox messages
			 */
			} elseif ( strpos( $idx, 'side-' ) === 0 ) {
				switch ( $idx ) {
					case 'side-purchase':
						$text = '<p>';
						if ( $this->p->is_avail['aop'] )
							$text .= sprintf( __( '%s can be purchased quickly and easily via Paypal &ndash; allowing you to license and enable Pro version features within seconds of your purchase.', 'nextgen-facebook' ), $info['short_pro'] );
						else $text .= sprintf( __( '%s can be purchased quickly and easily via Paypal &ndash; allowing you to update the plugin within seconds of your purchase.', 'nextgen-facebook' ), $info['short_pro'] );
						$text .= ' '.__( 'Pro version licenses do not expire &ndash; there are no yearly or recurring fees for updates and support.', 'nextgen-facebook' );
						$text .= '<p>';
						break;
					case 'side-help':
						$submit_text = _x( 'Save All Plugin Settings', 'submit button', 'nextgen-facebook' );
						$text = '<p>'.sprintf( __( 'Metaboxes (like this one) can be opened / closed by clicking on their title bar, moved and re-ordered by dragging them, or removed / added from the <em>Screen Options</em> tab (top-right of page).', 'nextgen-facebook' ).' '.__( 'Option values in multiple tabs can be modified before clicking the \'%s\' button.', 'nextgen-facebook' ), $submit_text ).'</p>';
						break;
					default:
						$text = apply_filters( $lca.'_messages_side', $text, $idx, $info );
						break;
				}
			} else $text = apply_filters( $lca.'_messages', $text, $idx, $info );

			if ( is_array( $info ) && 
				! empty( $info['is_locale'] ) )
					$text .= ' '.sprintf( __( 'This option is localized &mdash; you may change the WordPress admin locale with <a href="%1$s" target="_blank">Polylang</a>, <a href="%2$s" target="_blank">WP Native Dashboard</a>, etc., to define alternate option values for different languages.', 'nextgen-facebook' ), 'https://wordpress.org/plugins/polylang/', 'https://wordpress.org/plugins/wp-native-dashboard/' );

			if ( strpos( $idx, 'tooltip-' ) === 0 && ! empty( $text ) ) {
				$text = '<img src="'.NGFB_URLPATH.'images/question-mark.png" width="14" height="14" class="'.
					( isset( $info['class'] ) ? $info['class'] : $this->p->cf['form']['tooltip_class'] ).
						'" alt="'.esc_attr( $text ).'" />';
			}

			return $text;
		}
	}
}

?>