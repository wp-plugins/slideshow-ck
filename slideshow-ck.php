<?php
/**
 * Plugin Name: Slideshow CK
 * Plugin URI: http://www.wp-pluginsck.com/plugins-wordpress/slideshow-ck
 * Description: Slideshow CK is a responsive slideshow plugin that show your images with nice effects.
 * Version: 1.0.1
 * Author: Cédric KEIFLIN
 * Author URI: http://www.wp-pluginsck.com/
 * License: GPL2
 */
defined('ABSPATH') or die;

class Slideshowck {

	public $pluginname, $pluginurl, $plugindir, $options, $settings, $settings_field, $ispro, $prourl, $params;
	public $default_settings = array();

	function __construct() {
		$this->pluginname = 'slideshow-ck';
		$this->pluginurl = plugins_url('', __FILE__);
		$this->plugindir = WP_PLUGIN_DIR . '/' . $this->pluginname;
		$this->settings_field = 'slideshow-ck_options';
		$this->options = get_option($this->settings_field);
		$this->prourl = 'http://www.wp-pluginsck.com/en/wordpress-plugins/slideshow-ck';
	}

	function init() {

		if (is_admin()) {
			// load the main admin menu items
			add_action('admin_menu', array($this, 'create_admin_menu'), 20);

			// create the custom post type
			add_action('init', array($this, 'create_post_type'));

			// add the get pro link in the plugins list
//			add_filter('plugin_action_links', array($this, 'show_pro_message_action_links'), 10, 2);

			// manage ajax calls
			add_action('wp_ajax_add_slide', array($this, 'ajax_add_slide'));
		}
		// create the widget
		add_action('widgets_init', array($this, 'create_slideshowck_widget'));
	}

	/**
	 * Set some styles for the admin menu icon
	 */
	function set_admin_menu_image_position() {
		?>
		<style type="text/css">#toplevel_page_slideshowck_general .wp-menu-image > img { padding: 12px 0 0 !important; }</style>
		<?php
	}

	/**
	 * Create and register the slideshow widget
	 */
	public function create_slideshowck_widget() {
		require_once( $this->plugindir . '/includes/widget-slideshowck.php' );
		register_widget('slideshowck_widget');
	}

	/**
	 * Create the slideshowck post type
	 */
	function create_post_type() {
		register_post_type('slideshowck', array(
			'labels' => array(
				'name' => __('Slideshow CK'),
				'singular_name' => __('Slideshow CK'),
				'add_new' => __('Add New Slideshow'),
				'add_new_item' => __('Add New Slideshow'),
				'edit_item' => __('Edit Slideshow'),
				'new_item' => __('Add New Slideshow'),
				'view_item' => __('View Slideshow'),
				'search_items' => __('Search Slideshow'),
				'not_found' => __('No events found'),
				'not_found_in_trash' => __('No events found in trash')
			),
			'public' => true,
			'exclude_from_search' => true,
			'public' => true,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
				)
		);
	}

	/**
	 * Create menu links in the admin
	 */
	function create_admin_menu() {
		$this->pagehook = $page = add_menu_page('Slideshow CK', 'Slideshow CK', 'administrator', 'slideshowck_general', array($this, 'render_general'), $this->pluginurl . '/images/admin_menu.png');
		add_submenu_page('slideshowck_general', __('Slideshow CK'), __('All Slideshows'), 'administrator', 'slideshowck_general', array($this, 'render_general'));
		$editpage = add_submenu_page('slideshowck_general', __('Edit'), __('Add New'), 'administrator', 'slideshowck_edit', array($this, 'render_edit'));
		// for a nice menu icon
		add_action('admin_head', array($this, 'set_admin_menu_image_position'), 20);
	}

	/**
	 * Load JS / CSS files and codes in the admin
	 */
	function load_admin_assets() {
		?>
		<script type="text/javascript">
			function ckdoajax(func, id) {
				var data = {
					action: func,
					id: id
				};
				jQuery('#slideshowck_admin').html('<div class="ckwait_overlay"></div>');
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery('.ckwait_overlay').remove();
					jQuery('#slideshowck_admin').html(response);
				});
			}
		</script>
		<?php
	}

	/**
	 * Return the HTML code of the field
	 * 
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 * @param string $classname
	 * @param mixed $optionsgroup - can be array or string
	 * @param boolean $isfiles
	 * @param string $attribs
	 * @return string - the field html code
	 */
	function get_field($type, $name, $value, $classname = '', $optionsgroup = '', $isfiles = false, $attribs = '') {
		return $this->ckfields->get($type, $name, $value, $classname, $optionsgroup, $isfiles, $attribs);
	}

	/**
	 * Return the field name
	 * 
	 * @param string $name
	 * @return string
	 */
	function get_field_name($name) {
		return sprintf('%s[%s]', $this->settings_field, $name);
	}

	/**
	 * Return the field value
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function get_field_value($key, $default = null) {
		if (isset($this->options[$key])) {
			return $this->options[$key];
		} else {
			if ($default == null && isset($this->default_settings[$key]))
				return $this->default_settings[$key];
		}
		return $default;
	}

	function get_option($name) {
		if (isset($this->options[$name])) {
			return $this->options[$name];
		} else if (isset($this->default_settings[$name])) {
			return $this->default_settings[$name];
		}
		return null;
	}

	function show_pro_message_action_links($links, $file) {
		if ($file == plugin_basename(__FILE__)) {
			array_push($links, '<a href="options-general.php?page=' . $this->pluginname . '">' . __('Settings') . '</a>');
			if (!$this->ispro) {
				array_push($links, '<br /><img class="iconck" src="' . $this->pluginurl . '/images/star.png" /><a target="_blank" href="' . $this->prourl . '">' . __('Get the PRO Version') . '</a>');
			} else {
				array_push($links, '<br /><img class="iconck" src="' . $this->pluginurl . '/images/tick.png" /><span style="color: green;">' . __('You are using the PRO Version. Thank you !') . '</span>');
			}
		}
		return $links;
	}

	function show_pro_message_settings_page() {
		?>
		<div class="ckcheckproversion">
			<?php if (!file_exists($this->plugindir . '/' . $this->pluginname . '-pro.php')) : ?>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/star.png" />
				<a target="_blank" href="<?php echo $this->prourl ?>"><?php _e('Get the PRO Version'); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}

	function render_general() {
		?>
		<div id="slideshowck_admin">
			<?php
			require_once($this->plugindir . '/includes/slideshowck_general.php');
			?>
		</div>
		<?php
	}

	/**
	 * Load the edition page
	 */
	function render_edit() {
		require_once($this->plugindir . '/includes/slideshowck_edit.php');
	}

	/**
	 * Create an empty slide
	 */
	function ajax_add_slide() {
		$number = (int) $_POST['number'];
		$this->add_slide($number);
		die;
	}

	/**
	 * Render the HTML code of a slide
	 * 
	 * @param integer $i		the slide number
	 * @param object $options	the slide options
	 */
	function add_slide($i = false, $options = null) {
		$options = $this->clean_options($options);
		?>
		<div id="ckslide<?php echo $i; ?>" class="ckslide">
			<div class="ckslidehandle">
				<div class="ckslidenumber"><?php echo $i; ?></div>
			</div>
			<div class="ckslidedelete" onclick="javascript:removeslide(jQuery(this).parents('.ckslide')[0]);" name="ckslidedelete<?php echo $i; ?>">X</div>
			<div class="ckslidecontainer">
				<div class="cksliderow">
					<div class="ckslideimgcontainer">
						<img class="ckslideimgthumb" width="64" height="64" src="<?php echo get_site_url() . '/' . $options->imgname; ?>">
					</div>
					<input id="ckslideimgname<?php echo $i; ?>" class="ckslideimgname" type="text" onchange="javascript:add_image_url_to_slideck(this, this.value);" value="<?php echo trim(str_replace(get_site_url(), '', $options->imgname), '/'); ?>" title="" name="ckslideimgname<?php echo $i; ?>">
					<a class="button button-secondary" onclick="open_media_manager(this);"><?php _e('Select') ?></a>
					<br />
					<span class="ckslidelabel"><?php _e('Title') ?></span>
					<input class="ckslidetitle" type="text" value="<?php echo $this->get_param('title', '', $options); ?>" name="ckslidetitle<?php echo $i; ?>">
					<br />
					<span class="ckslidelabel"><?php _e('Description') ?></span>
					<input class="ckslidedescription" type="text" value="<?php echo $this->get_param('description', '', $options); ?>" name="ckslidedescription<?php echo $i; ?>">

				</div>
					 <div class="ckslideoptionstoggler" onclick="jQuery('+ .ckslideoptions', jQuery(this)).toggle('fast');
								jQuery(this).toggleClass('open');"><?php _e('Options') ?></div>
				<div class="cksliderow ckslideoptions">
					<div id="ckslideaccordion<?php echo $i; ?>">
						<span class="menulinkck current" tab="tab_slideoptions_duration<?php echo $i; ?>"><?php _e('Duration') ?></span>
						<span class="menulinkck" tab="tab_slideoptions_alignment<?php echo $i; ?>"><?php _e('Alignment') ?></span>
						<span class="menulinkck" tab="tab_slideoptions_link<?php echo $i; ?>"><?php _e('Link') ?></span>
						<span class="menulinkck" tab="tab_slideoptions_video<?php echo $i; ?>"><?php _e('Video') ?></span>
						<div style="clear:both;"></div>
						<div class="tabck menustyles current" id="tab_slideoptions_duration<?php echo $i; ?>">
							<div class="cksliderow">
								<span>
									<span class="ckslidelabel"><?php _e('Slide duration') ?></span>
									<img align="top" title="" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/hourglass.png">
									<input class="ckslideimgtime" type="text" style="width:65px;" value="<?php echo $this->get_param('imgtime', '', $options); ?>" name="ckslideimgtime<?php echo $i; ?>">
								</span>
								<span>ms</span>
								<span class="ckslidelabeldesc"><?php _e('Leave it blank to use the global setting'); ?></span>
							</div>
						</div>
						<div class="tabck menustyles" id="tab_slideoptions_alignment<?php echo $i; ?>" >
							<div class="cksliderow">
								<span class="ckslidelabel"><?php _e('Image alignment'); ?></span>
								<img align="top" title="" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/image_alignment.png">
								<?php
								$options_ckslidedataalignmenttext = array(
									'default' => __('default')
									, 'topLeft' => __('top left')
									, 'topCenter' => __('top center')
									, 'topRight' => __('top right')
									, 'centerLeft' => __('center left')
									, 'center' => __('center')
									, 'centerRight' => __('center right')
									, 'bottomLeft' => __('bottom left')
									, 'bottomCenter' => __('bottom center')
									, 'bottomRight' => __('bottom right')
								);
								echo $this->get_field('select', 'ckslidedataalignmenttext' . $i, $this->get_param('imgalignment', '', $options), 'ckslidedataalignmenttext', $options_ckslidedataalignmenttext);
								?>
							</div>
						</div>
						<div class="tabck menustyles" id="tab_slideoptions_link<?php echo $i; ?>" >
							<div class="cksliderow">
								<span class="ckslidelabel"><?php _e('Link url'); ?></span>
								<img align="top" title="" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/link.png">
								<input class="ckslidelinktext" type="text" value="<?php echo $this->get_param('imglink', '', $options); ?>" name="ckslidelinktext<?php echo $i; ?>">
							</div>
							<div class="cksliderow">
								<span class="ckslidelabel"><?php _e('Target'); ?></span>
								<img align="top" title="" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/link_go.png">
								<?php
								$options_ckslidetargettext = array(
									'default' => __('Default')
									, '_parent' => __('Open in the same window')
									, '_blank' => __('Open in a new window')
										// , 'lightbox'=>__('Open in a Lightbox')
								);
								echo $this->get_field('select', 'ckslidetargettext' . $i, $this->get_param('imgtarget', '', $options), 'ckslidetargettext', $options_ckslidetargettext);
								?>
							</div>
						</div>
						<div class="tabck menustyles" id="tab_slideoptions_video<?php echo $i; ?>">
							<div class="cksliderow">
								<span class="ckslidelabel"><?php _e('Video url'); ?></span>
								<img align="top" title="" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/film.png">
								<input class="ckslidevideotext" type="text" value="<?php echo $this->get_param('imgvideo', '', $options); ?>" name="ckslidevideotext<?php echo $i; ?>">
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div> 
		<?php
		// fin ckslide
	}

	/**
	 * Do some work with the slide options
	 * 
	 * @param object $options		the slide options
	 * @return object				the slide options modified
	 */
	function clean_options($options) {
		$options->imgname = isset($options->imgname) ? $options->imgname : str_replace(get_site_url(), '', $this->pluginurl) . '/images/unknown.png';

		return $options;
	}

	/**
	 * Get the value of a params from a params object list
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @param object $params
	 * @return mixed the param value
	 */
	function get_param($key, $default = null, $params = null) {
		if ( $params === null ) {
			$params = $this->params;
		}
		if (isset($params->$key)) {
			return $params->$key;
		} else {
			if ($default == null && isset($this->default_settings[$key]))
				return $this->default_settings[$key];
		}
		return $default;
	}

	/**
	 * Test if there is already a unit, else add the px
	 *
	 * @param string $value
	 * @return string
	 */
	private function test_unit($value) {
		if ((stristr($value, 'px')) OR ( stristr($value, 'em')) OR ( stristr($value, '%')))
			return $value;

		return $value . 'px';
	}

	/**
	 * Create the css array from the params
	 * 
	 * @param string $prefix
	 * @return array of styles
	 */
	function create_css($prefix = '') {
		$css = Array();
		// $css['paddingtop'] = ($this->get_param($prefix.'paddingtop')) ? 'padding-top: ' . $this->get_param($prefix.'paddingtop', '0').'px;' : '';
		// $css['paddingright'] = ($this->get_param($prefix.'paddingright')) ? 'padding-right: ' . $this->get_param($prefix.'paddingright', '0').'px;' : '';
		// $css['paddingbottom'] = ($this->get_param($prefix.'paddingbottom') ) ? 'padding-bottom: ' . $this->get_param($prefix.'paddingbottom', '0').'px;' : '';
		// $css['paddingleft'] = ($this->get_param($prefix.'paddingleft')) ? 'padding-left: ' . $this->get_param($prefix.'paddingleft', '0').'px;' : '';
		// $css['margintop'] = ($this->get_param($prefix.'margintop')) ? 'margin-top: ' . $this->get_param($prefix.'margintop', '0').'px;' : '';
		// $css['marginright'] = ($this->get_param($prefix.'marginright')) ? 'margin-right: ' . $this->get_param($prefix.'marginright', '0').'px;' : '';
		// $css['marginbottom'] = ($this->get_param($prefix.'marginbottom')) ? 'margin-bottom: ' . $this->get_param($prefix.'marginbottom', '0').'px;' : '';
		// $css['marginleft'] = ($this->get_param($prefix.'marginleft')) ? 'margin-left: ' . $this->get_param($prefix.'marginleft', '0').'px;' : '';
		// $css['background'] = ($this->get_param($prefix.'bgcolor1')) ? 'background-color: ' . $this->get_param($prefix.'bgcolor1').';' : '';
		// $css['background'] .= ($this->get_param($prefix.'bgimage')) ? 'background-image: url("' . JURI::ROOT() . $this->get_param($prefix.'bgimage').'");' : '';
		// $css['background'] .= ($this->get_param($prefix.'bgimage')) ? 'background-repeat: ' . $this->get_param($prefix.'bgimagerepeat').';' : '';
		// $css['background'] .= ($this->get_param($prefix.'bgimage')) ? 'background-position: ' . $this->get_param($prefix.'bgpositionx').' ' . $this->get_param($prefix.'bgpositiony').';' : '';
		$csspaddingtop = ($this->get_param($prefix . 'paddingtop') ) ? 'padding-top: ' . $this->test_unit($this->get_param($prefix . 'paddingtop', '0')) . ';' : '';
		$csspaddingright = ($this->get_param($prefix . 'paddingright') ) ? 'padding-right: ' . $this->test_unit($this->get_param($prefix . 'paddingright', '0')) . ';' : '';
		$csspaddingbottom = ($this->get_param($prefix . 'paddingbottom') ) ? 'padding-bottom: ' . $this->test_unit($this->get_param($prefix . 'paddingbottom', '0')) . ';' : '';
		$csspaddingleft = ($this->get_param($prefix . 'paddingleft') ) ? 'padding-left: ' . $this->test_unit($this->get_param($prefix . 'paddingleft', '0')) . ';' : '';
		$css['padding'] = $csspaddingtop . $csspaddingright . $csspaddingbottom . $csspaddingleft;
		$cssmargintop = ($this->get_param($prefix . 'margintop') ) ? 'margin-top: ' . $this->test_unit($this->get_param($prefix . 'margintop', '0')) . ';' : '';
		$cssmarginright = ($this->get_param($prefix . 'marginright') ) ? 'margin-right: ' . $this->test_unit($this->get_param($prefix . 'marginright', '0')) . ';' : '';
		$cssmarginbottom = ($this->get_param($prefix . 'marginbottom') ) ? 'margin-bottom: ' . $this->test_unit($this->get_param($prefix . 'marginbottom', '0')) . ';' : '';
		$cssmarginleft = ($this->get_param($prefix . 'marginleft') ) ? 'margin-left: ' . $this->test_unit($this->get_param($prefix . 'marginleft', '0')) . ';' : '';
		$css['margin'] = $cssmargintop . $cssmarginright . $cssmarginbottom . $cssmarginleft;
		$bgcolor1 = ($this->get_param($prefix . 'bgcolor1') && $this->get_param($prefix . 'bgopacity')) ? $this->hex2RGB($this->get_param($prefix . 'bgcolor1'), $this->get_param($prefix . 'bgopacity')) : $this->get_param($prefix . 'bgcolor1');
		$css['background'] = ($this->get_param($prefix . 'bgcolor1') ) ? 'background: ' . $bgcolor1 . ';' : '';
		$css['background'] .= ( $this->get_param($prefix . 'bgimage') ) ? 'background-image: url("' . get_site_url() . $this->get_param($prefix . 'bgimage') . '");' : '';
		$css['background'] .= ( $this->get_param($prefix . 'bgimage') ) ? 'background-repeat: ' . $this->get_param($prefix . 'bgimagerepeat') . ';' : '';
		$css['background'] .= ( $this->get_param($prefix . 'bgimage') ) ? 'background-position: ' . $this->get_param($prefix . 'bgpositionx') . ' ' . $this->get_param($prefix . 'bgpositiony') . ';' : '';
		$css['gradient'] = ($css['background'] AND $this->get_param($prefix . 'bgcolor2') ) ?
				"background: -moz-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%, " . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%," . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . "), color-stop(100%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . ")); "
				. "background: -webkit-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -o-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -ms-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%); "
				. "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='" . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . "', endColorstr='" . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . "',GradientType=0 );" : '';
		$css['gradient'] = ($css['background'] AND $this->get_param($prefix . 'bgcolor2')) ?
				"background: -moz-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%, " . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%," . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . "), color-stop(100%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . ")); "
				. "background: -webkit-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -o-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: -ms-linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%);"
				. "background: linear-gradient(top,  " . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . " 0%," . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . " 100%); "
				. "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='" . $this->get_param($prefix . 'bgcolor1', '#f0f0f0') . "', endColorstr='" . $this->get_param($prefix . 'bgcolor2', '#e3e3e3') . "',GradientType=0 );" : '';
		$css['borderradius'] = ($this->get_param($prefix . 'roundedcornerstl', '0') && $this->get_param($prefix . 'roundedcornerstr', '0') && $this->get_param($prefix . 'roundedcornersbr', '0') && $this->get_param($prefix . 'roundedcornersbl', '0')) ?
				'-moz-border-radius: ' . $this->get_param($prefix . 'roundedcornerstl', '0') . 'px ' . $this->get_param($prefix . 'roundedcornerstr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbl', '0') . 'px;'
				. '-webkit-border-radius: ' . $this->get_param($prefix . 'roundedcornerstl', '0') . 'px ' . $this->get_param($prefix . 'roundedcornerstr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbl', '0') . 'px;'
				. 'border-radius: ' . $this->get_param($prefix . 'roundedcornerstl', '0') . 'px ' . $this->get_param($prefix . 'roundedcornerstr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbr', '0') . 'px ' . $this->get_param($prefix . 'roundedcornersbl', '0') . 'px;' : '';
		$shadowinset = $this->get_param($prefix . 'shadowinset', 0) ? 'inset ' : '';
		$css['shadow'] = ($this->get_param($prefix . 'shadowcolor') AND $this->get_param($prefix . 'shadowblur')) ?
				'-moz-box-shadow: ' . $shadowinset . ($this->get_param($prefix . 'shadowoffsetx', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsetx', '0')) : '0') . ' ' . ($this->get_param($prefix . 'shadowoffsety', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsety', '0')) : '0') . ' ' . $this->test_unit($this->get_param($prefix . 'shadowblur', '')) . ' ' . ($this->get_param($prefix . 'shadowspread', '0') ? $this->test_unit($this->get_param($prefix . 'shadowspread', '0')) : '0') . ' ' . $this->get_param($prefix . 'shadowcolor', '') . ';'
				. '-webkit-box-shadow: ' . $shadowinset . ($this->get_param($prefix . 'shadowoffsetx', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsetx', '0')) : '0') . ' ' . ($this->get_param($prefix . 'shadowoffsety', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsety', '0')) : '0') . ' ' . $this->test_unit($this->get_param($prefix . 'shadowblur', '')) . ' ' . ($this->get_param($prefix . 'shadowspread', '0') ? $this->test_unit($this->get_param($prefix . 'shadowspread', '0')) : '0') . ' ' . $this->get_param($prefix . 'shadowcolor', '') . ';'
				. 'box-shadow: ' . $shadowinset . ($this->get_param($prefix . 'shadowoffsetx', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsetx', '0')) : '0') . ' ' . ($this->get_param($prefix . 'shadowoffsety', '0') ? $this->test_unit($this->get_param($prefix . 'shadowoffsety', '0')) : '0') . ' ' . $this->test_unit($this->get_param($prefix . 'shadowblur', '')) . ' ' . ($this->get_param($prefix . 'shadowspread', '0') ? $this->test_unit($this->get_param($prefix . 'shadowspread', '0')) : '0') . ' ' . $this->get_param($prefix . 'shadowcolor', '') . ';' : '';
		$css['border'] = ($this->get_param($prefix . 'bordercolor') AND $this->get_param($prefix . 'borderwidth')) ?
				'border: ' . $this->get_param($prefix . 'bordercolor', '#efefef') . ' ' . $this->test_unit($this->get_param($prefix . 'borderwidth', '1')) . ' solid;' : '';
		$css['fontsize'] = ($this->get_param($prefix . 'fontsize')) ?
				'font-size: ' . $this->test_unit($this->get_param($prefix . 'fontsize')) . ';'
				. 'line-height: ' . $this->test_unit($this->get_param($prefix . 'fontsize')) . ';' : '';
		$css['fontcolor'] = ($this->get_param($prefix . 'fontcolor')) ?
				'color: ' . $this->get_param($prefix . 'fontcolor') . ';' : '';
		$css['fontweight'] = ($this->get_param($prefix . 'fontweight')) ?
				'font-weight: ' . $this->get_param($prefix . 'fontweight') . ';' : '';
		$css['fontfamily'] = ($this->get_param($prefix . 'fontfamily')) ?
				'font-family: ' . $this->get_param($prefix . 'fontfamily') . ';' : '';
		return $css;
	}

	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */
	static function hex2RGB($hexStr, $opacity) {
		if (!stristr($opacity, '.'))
			$opacity = $opacity / 100;
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			return false; //Invalid hex color code
		}
		$rgbacolor = "rgba(" . $rgbArray['red'] . "," . $rgbArray['green'] . "," . $rgbArray['blue'] . "," . $opacity . ")";

		return $rgbacolor;
	}

	function render_slideshow($id) {
		$items = $this->get_items($id);
		if ($this->get_param('displayorder', 'normal') == 'shuffle') {
			shuffle($items);
		}
		$params = json_decode(str_replace('|qq|', '"', get_post_meta($id, 'slideshow-ck-params', TRUE)));
		$this->params = $params;
		$width = ($this->get_param('width') AND $this->get_param('width') != 'auto') ? ' style="width:' . $this->test_unit($this->get_param('width')) . ';"' : '';
		$this->load_slideshow_assets($id);
		?>
		<div class="slideshowck camera_wrap <?php echo $this->get_param('skin'); ?>" id="camera_wrap_<?php echo $id; ?>"<?php echo $width; ?>>
			<?php
			for ($i = 0; $i < count($items); ++$i) {
				if ($this->get_param('displayorder', 'normal') == 'shuffle' && $this->get_param('limitslides', '') && $i >= $this->get_param('limitslides', ''))
					break;
				$item = $items[$i];
				// set the variables for each item
				$this->get_item_data($item);
				if ($item->imgalignment != 'default') {
					$dataalignment = ' data-alignment="' . $item->imgalignment . '"';
				} else {
					$dataalignment = '';
				}
				$imgtarget = ($item->imgtarget == 'default') ? $this->get_param('imagetarget') : $item->imgtarget;
				$datatitle = ($this->get_param('lightboxcaption', 'caption') != 'caption') ? 'data-title="' . htmlspecialchars(str_replace("\"", "&quot;", str_replace(">", "&gt;", str_replace("<", "&lt;", $datacaption)))) . '" ' : '';
				$dataalbum = ($this->get_param('lightboxgroupalbum', '0')) ? '[albumslideshowck' . $module->id . ']' : '';
				$datarel = ($imgtarget == 'lightbox') ? 'data-rel="lightbox' . $dataalbum . '" ' : '';
				$datatime = ($item->imgtime) ? ' data-time="' . $item->imgtime . '"' : '';
				?>
				<div <?php echo $datarel . $datatitle; ?>data-thumb="<?php echo get_site_url() . '/' . trim($item->imgthumb, '/'); ?>" data-src="<?php echo get_site_url() . '/' . trim($item->imgname, '/'); ?>" <?php
				if ($item->imglink)
					echo 'data-link="' . $item->imglink . '" data-target="' . $imgtarget . '"';
				echo $dataalignment . $datatime;
				?>>
						<?php if ($item->imgvideo) { ?>
						<iframe src="<?php echo $item->imgvideo; ?>" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
						<?php
					}
					if (($item->title || $item->description || $item->article) && (($this->get_param('lightboxcaption', 'caption') != 'title' || $imgtarget != 'lightbox') || !$item->imglink)) {
						?>
						<div class="camera_caption <?php echo $this->get_param('captioneffect', 'moveFromBottom') ?>">
							<div class="camera_caption_title">
								<?php echo str_replace("|dq|", "\"", $item->title); ?>
								<?php
								if ($item->article && $this->get_param('showarticletitle', '1') == '1') {
									if ($this->get_param('articlelink', 'readmore') == 'title')
										echo '<a href="' . $item->article->link . '">';
									echo $item->article->title;
									if ($this->get_param('articlelink', 'readmore') == 'title')
										echo '</a>';
								}
								?>
							</div>
							<div class="camera_caption_desc">
								<?php echo str_replace("|dq|", "\"", $item->description); ?>
								<?php
								if ($item->article) {
									echo $item->article->text;
									if ($this->get_param('articlelink', 'readmore') == 'readmore')
										echo '<a href="' . $item->article->link . '">' . JText::_('COM_CONTENT_READ_MORE_TITLE') . '</a>';
								}
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			<?php } ?>
		</div>
		<div style="clear:both;"></div>
		<?php
	}

	function get_item_data($item) {
		// set the variables
		if (!isset($item->video))
			$item->video = null;
		if (!isset($item->title))
			$item->title = null;
		if (!isset($item->description))
			$item->description = null;
		if (!isset($item->article))
			$item->article = null;
		if (!isset($item->imgalignment))
			$item->imgalignment = null;
		if (!isset($item->imgtarget))
			$item->imgtarget = null;
		if (!isset($item->imgtime))
			$item->imgtime = null;
		if (!isset($item->imglink))
			$item->imglink = null;
		if (!isset($item->imgthumb))
			$item->imgthumb = $item->imgname;
	}

	function load_slideshow_assets($id) {
		// set the navigation variables
		switch ($this->get_param('navigation', '2')) {
			case 0:
				// aucune
				$navigation = "navigationHover: false,
						navigation: false,
						playPause: false,
						";
				break;
			case 1:
				// toujours
				$navigation = "navigationHover: false,
						navigation: true,
						playPause: true,
						";
				break;
			case 2:
			default:
				// on mouseover
				$navigation = "navigationHover: true,
						navigation: true,
						playPause: true,
						";
				break;
		}
		$theme = $this->get_param('theme', 'default');
		// load the caption styles
		$title_css = $this->create_css('captiontitle_');
		$desc_css = $this->create_css('captiondesc_');
		$caption_css = $this->create_css('caption_');
		/*
		  $fontfamily = ($this->get_param('captionstylesusefont', '0') && $this->get_param('captionstylestextgfont', '0')) ? "font-family:'" . $this->get_param('captionstylestextgfont', 'Droid Sans') . "';" : '';
		  if ($fontfamily) {
		  $gfonturl = str_replace(" ", "+", $this->get_param('captionstylestextgfont', 'Droid Sans'));
		  $document->addStylesheet('https://fonts.googleapis.com/css?family=' . $gfonturl);
		  } */
		wp_enqueue_script('jquery-easing', $this->pluginurl . '/assets/jquery.easing.1.3.js');
		wp_enqueue_script('jquery-mobile', $this->pluginurl . '/assets/jquery.mobile.customized.min.js');
		wp_enqueue_script('camerack', $this->pluginurl . '/assets/camera_1.3.8.js');
		?>
		<script type="text/javascript"> <!--
			jQuery(function() {
				jQuery('#camera_wrap_<?php echo $id; ?>').camera({
					height: '<?php echo $this->get_param('height', '400') ?>',
					minHeight: '',
					pauseOnClick: false,
					hover: <?php echo $this->get_param('hover', '1') ?>,
					fx: '<?php echo implode(",", $this->get_param('effect', array('random'))) ?>',
					loader: '<?php echo $this->get_param('loader', 'pie') ?>',
					pagination: <?php echo $this->get_param('pagination', '1') ?>,
					thumbnails: <?php echo $this->get_param('thumbnails', '1') ?>,
					thumbheight: <?php echo $this->get_param('thumbnailheight', '100') ?>,
					thumbwidth: <?php echo $this->get_param('thumbnailwidth', '75') ?>,
					time: <?php echo $this->get_param('time', '7000') ?>,
					transPeriod: <?php echo $this->get_param('transperiod', '1500') ?>,
					alignment: '<?php echo $this->get_param('alignment', 'center') ?>',
					autoAdvance: <?php echo $this->get_param('autoAdvance', '1') ?>,
					mobileAutoAdvance: <?php echo $this->get_param('autoAdvance', '1') ?>,
					portrait: <?php echo $this->get_param('portrait', '0') ?>,
					barDirection: '<?php echo $this->get_param('barDirection', 'leftToRight') ?>',
					imagePath: '<?php echo $this->pluginurl ?>/images/',
					lightbox: '<?php echo $this->get_param('lightboxtype', 'mediaboxck') ?>',
					fullpage: <?php echo $this->get_param('fullpage', '0') ?>,
					//mobileimageresolution: '<?php echo ($this->get_param('usemobileimage', '0') ? $this->get_param('mobileimageresolution', '640') : '0') ?>',
					<?php echo $navigation ?>
					barPosition: '<?php echo $this->get_param('barPosition', 'bottom') ?>'
					});
				}); //--> </script>

		<link href="<?php echo $this->pluginurl ?>/themes/<?php echo $theme ?>/css/camera.css" rel="stylesheet" type="text/css" />
		<style type="text/css">
			#camera_wrap_<?php echo $id; ?> .camera_pag_ul li img {
				height:<?php echo $this->test_unit($this->get_param('thumbnailheight', '75')); ?>;
				width: auto;
			}
			#camera_wrap_<?php echo $id; ?> .camera_caption {
				display: block;
				position: absolute;
			}
			#camera_wrap_<?php echo $id; ?> .camera_caption > div {
				<?php echo $caption_css['padding'] . $caption_css['margin'] . $caption_css['background'] . $caption_css['gradient'] . $caption_css['borderradius'] . $caption_css['shadow'] . $caption_css['border'] ?>
			}
			#camera_wrap_<?php echo $id; ?> .camera_caption div.camera_caption_title {
				<?php echo $title_css['fontcolor'] . $title_css['fontsize'] . $title_css['fontweight'] . $title_css['fontfamily'] ?>
			}
			#camera_wrap_<?php echo $id; ?> .camera_caption div.camera_caption_desc {
				<?php echo $desc_css['fontcolor'] . $desc_css['fontsize'] . $desc_css['fontweight'] . $desc_css['fontfamily'] ?>
			}
		</style>
		<?php
	}

	function get_items($id) {
		switch ( $this->get_param('slides_sources') ) {
			case 'slidesmanager':
			default:
				$items = json_decode(str_replace('|qq|', '"', get_post_meta($id, 'slideshow-ck-slides', TRUE)));
				break;
		}

		return $items;
	}

}

// load the process
$slideshowckClass = new Slideshowck();
$slideshowckClass->init();

if (!is_admin()) {
	/**
	 * Render the slideshow in the page
	 * 
	 * @param integer $id the slideshow ID
	 */
	function do_slideshowck($id) {
		$slideshowckClass = new Slideshowck();
		$slideshowckClass->render_slideshow($id);
	}
} else {

	/**
	 * Empty funtion to avoir to load a slideshow in the admin and avoid an error
	 * 
	 * @param integer $id the slideshow ID
	 * @return type
	 */
	function do_slideshowck($id) {
		return;
	}
}

// register the shortcode to call the slideshow
add_shortcode( 'slideshowck', 'shortcode_slideshowck' );

/**
 * Render the slideshow using the shortcode
 * 
 * @param type $attr
 * @return the slideshow or null
 */
function shortcode_slideshowck($attr) {
	if ( isset($attr['id']) ) {
		return do_slideshowck( (int) $attr['id'] );
	}
	return null;
}