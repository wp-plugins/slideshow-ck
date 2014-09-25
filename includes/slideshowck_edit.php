<?php
defined('ABSPATH') or die;

// check if the user has the rights to access this page
if (!current_user_can('manage_options')) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
}

// load scripts
wp_enqueue_media();
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core ');
wp_enqueue_script('jquery-ui-dialog');
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('wp-jquery-ui-dialog');
wp_enqueue_style('wp-color-picker');
wp_enqueue_style('ckradio2', $this->pluginurl . '/cklibrary/ckfields/ckradio2/ckradio2.css');
wp_enqueue_script('ckradio2', $this->pluginurl . '/cklibrary/ckfields/ckradio2/ckradio2.js', array('jquery'));
wp_enqueue_script('ckcolor', $this->pluginurl . '/cklibrary/ckfields/ckcolor/ckcolor.js', array('jquery', 'jquery-ui-button', 'wp-color-picker'));

// init variables
$post = $post_type = $post_type_object = null;

// check if the post exists
if (isset($_GET['id'])) {
	$post_id = (int) $_GET['id'];
} elseif (isset($_POST['post_ID'])) {
	$post_id = (int) $_POST['post_ID'];
} else {
	$post_id = 0;
}

// get the existing post
if ($post_id) {
	$post = get_post($post_id);
}

if ($post) {
	$post_type = $post->post_type;
}

// check if we get a slideshow object
if (0 !== $post_id && 'slideshowck' !== $post->post_type) {
	wp_die(__('Invalid post type'));
}

// do an action
if (!empty($_REQUEST['action']) && isset($post_id)) {
	if ($_REQUEST['action'] == 'save' && wp_verify_nonce($_REQUEST['_wpnonce'], 'slideshowck_save')) {
		$ck_post = array(
			'ID' => (int) $post_id,
			'post_title' => sanitize_text_field($_POST['post_title']),
			'post_content' => '',
			'post_type' => 'slideshowck',
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);

		// save the post into the database
		$ck_post_id = wp_insert_post($ck_post);

		// Update the meta field for the slideshow settings
		update_post_meta($ck_post_id, 'slideshow-ck-params', $_POST['slideshow-ck-params']);
		update_post_meta($ck_post_id, 'slideshow-ck-slides', $_POST['slideshow-ck-slides']);
		// TODO : ajouter notice en haut de page
		wp_redirect(home_url() . '/wp-admin/admin.php?page=slideshowck_edit&action=updated&id=' . (int) $ck_post_id);
		exit;
	}
}
// get the settings for the post
$this->params = json_decode(str_replace('|qq|', '"', get_post_meta((int) $post_id, 'slideshow-ck-params', TRUE)));
if ($this->ispro) $this->pro_class->params = $this->params;
$post_title = isset( $post->post_title ) ? $post->post_title : '';
?>
<?php //echo $CK_Notices; // TODO : creer notices pour dire "bien enregistre"  ?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->pluginurl ?>/assets/slideshowck_edit.css" media="all" />
<script type="text/javascript" src="<?php echo $this->pluginurl ?>/assets/slideshowck_edit.js" ></script>
<input type="hidden" id="demo-params" name="demo-params" value="{|qq|slides_sources|qq|:|qq|slidemanager|qq|,|qq|ckaddslide|qq|:|qq|Add a Slide|qq|,|qq|ckslideimgname0|qq|:|qq|wp-content/plugins/slideshow-ck/slides/bridge.jpg|qq|,|qq|undefined|qq|:|qq|Clear|qq|,|qq|ckslidedataalignmenttext0|qq|:|qq|default|qq|,|qq|ckslidetargettext0|qq|:|qq|default|qq|,|qq|ckslideimgname1|qq|:|qq|/wp-content/plugins/slideshow-ck/slides/road.jpg|qq|,|qq|ckslidedataalignmenttext1|qq|:|qq|default|qq|,|qq|ckslidetargettext1|qq|:|qq|default|qq|,|qq|ckslideimgname2|qq|:|qq|/wp-content/plugins/slideshow-ck/slides/big_bunny_fake.jpg|qq|,|qq|ckslidedataalignmenttext2|qq|:|qq|default|qq|,|qq|ckslidetargettext2|qq|:|qq|default|qq|,|qq|ckaddslide1|qq|:|qq|Add a Slide|qq|,|qq|height|qq|:|qq|62%|qq|,|qq|width|qq|:|qq|auto|qq|,|qq|loader|qq|:|qq|1|qq|,|qq|navigation|qq|:|qq|1|qq|,|qq|thumbnails|qq|:|qq|1|qq|,|qq|pagination|qq|:|qq|1|qq|,|qq|theme|qq|:|qq|default|qq|,|qq|skin|qq|:|qq|camera_amber_skin|qq|,|qq|imgalignment|qq|:|qq|default|qq|,|qq|thumbnailheight|qq|:|qq|100|qq|,|qq|captiontitle_color|qq|:|qq|#000000|qq|,|qq|captiontitle_fontsize|qq|:|qq|18|qq|,|qq|captiontitle_fontfamily|qq|:|qq||qq|,|qq|captiontitle_fontweight|qq|:|qq|normal|qq|,|qq|captiontitle_fontweightnormal|qq|:|qq|normal|qq|,|qq|captiontitle_fontweightbold|qq|:|qq|bold|qq|,|qq|captiondesc_color|qq|:|qq|#6b6b6b|qq|,|qq|captiondesc_fontsize|qq|:|qq|12|qq|,|qq|captiondesc_fontfamily|qq|:|qq||qq|,|qq|captiondesc_fontweight|qq|:|qq|normal|qq|,|qq|captiondesc_fontweightnormal|qq|:|qq|normal|qq|,|qq|captiondesc_fontweightbold|qq|:|qq|bold|qq|,|qq|caption_bgcolor1|qq|:|qq|#000000|qq|,|qq|caption_bgcolor2|qq|:|qq||qq|,|qq|caption_opacity|qq|:|qq|0.8|qq|,|qq|caption_margintop|qq|:|qq||qq|,|qq|caption_marginright|qq|:|qq||qq|,|qq|caption_marginbottom|qq|:|qq||qq|,|qq|caption_marginleft|qq|:|qq||qq|,|qq|caption_paddingtop|qq|:|qq||qq|,|qq|caption_paddingright|qq|:|qq||qq|,|qq|caption_paddingbottom|qq|:|qq||qq|,|qq|caption_paddingleft|qq|:|qq||qq|,|qq|caption_roundedcornerstl|qq|:|qq||qq|,|qq|caption_roundedcornerstr|qq|:|qq||qq|,|qq|caption_roundedcornersbr|qq|:|qq||qq|,|qq|caption_roundedcornersbl|qq|:|qq||qq|,|qq|caption_bordercolor|qq|:|qq||qq|,|qq|caption_borderwidth|qq|:|qq||qq|,|qq|caption_shadowcolor|qq|:|qq||qq|,|qq|caption_shadowblur|qq|:|qq||qq|,|qq|caption_shadowspread|qq|:|qq||qq|,|qq|caption_shadowoffsetx|qq|:|qq||qq|,|qq|caption_shadowoffsety|qq|:|qq||qq|,|qq|caption_shadowinset|qq|:|qq||qq|,|qq|caption_shadowinset0|qq|:|qq|0|qq|,|qq|caption_shadowinset1|qq|:|qq|1|qq|,|qq|effect|qq|:[|qq|random|qq|],|qq|captioneffect|qq|:|qq|moveFromLeft|qq|,|qq|time|qq|:|qq|7000|qq|,|qq|transperiod|qq|:|qq|1500|qq|,|qq|portrait|qq|:|qq|0|qq|,|qq|portrait1|qq|:|qq|1|qq|,|qq|portrait0|qq|:|qq|0|qq|,|qq|autoAdvance|qq|:|qq|1|qq|,|qq|autoAdvance1|qq|:|qq|1|qq|,|qq|autoAdvance0|qq|:|qq|0|qq|,|qq|hover|qq|:|qq|1|qq|,|qq|hover1|qq|:|qq|1|qq|,|qq|hover0|qq|:|qq|0|qq|,|qq|displayorder|qq|:|qq|normal|qq|,|qq|fullpage|qq|:|qq|0|qq|,|qq|fullpage1|qq|:|qq|1|qq|,|qq|fullpage0|qq|:|qq|0|qq|,|qq|imagetarget|qq|:|qq|_parent|qq|}" />
<input type="hidden" id="demo-slides" name="demo-slides" value="[{|qq|imgname|qq|:|qq|wp-content/plugins/slideshow-ck/slides/bridge.jpg|qq|,|qq|title|qq|:|qq|This is a bridge|qq|,|qq|description|qq|:|qq|You can get more information about Slideshow CK for Wordpress on <a href='http://www.wp-pluginsck.com'>WP Plugins CK</a>|qq|,|qq|imglink|qq|:|qq||qq|,|qq|imgtarget|qq|:|qq|default|qq|,|qq|imgalignment|qq|:|qq|default|qq|,|qq|imgvideo|qq|:|qq||qq|,|qq|imgtime|qq|:|qq||qq|},{|qq|imgname|qq|:|qq|wp-content/plugins/slideshow-ck/slides/road.jpg|qq|,|qq|title|qq|:|qq|On the road again|qq|,|qq|description|qq|:|qq|When the sky is blue, the rain will come.|qq|,|qq|imglink|qq|:|qq||qq|,|qq|imgtarget|qq|:|qq|default|qq|,|qq|imgalignment|qq|:|qq|default|qq|,|qq|imgvideo|qq|:|qq||qq|,|qq|imgtime|qq|:|qq||qq|},{|qq|imgname|qq|:|qq|wp-content/plugins/slideshow-ck/slides/big_bunny_fake.jpg|qq|,|qq|title|qq|:|qq||qq|,|qq|description|qq|:|qq||qq|,|qq|imglink|qq|:|qq||qq|,|qq|imgtarget|qq|:|qq|default|qq|,|qq|imgalignment|qq|:|qq|default|qq|,|qq|imgvideo|qq|:|qq|http://player.vimeo.com/video/2203727|qq|,|qq|imgtime|qq|:|qq||qq|}]" />
<form id="slideshowck-edit" method="post" action="">
	<h2>Slideshow CK - <?php echo __('Edit') . ' <span class="small">[ ' . $post_title . ' ]</span>'; ?>
		<input type="button" class="button button-primary" name="save_slideshowck" value="<?php esc_attr_e('Save'); ?>" onclick="save_slideshow()" />
	</h2>
	<input type="hidden" name="action" value="save"/>
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('slideshowck_save'); ?>" />
	<input type="hidden" name="ID" value="<?php echo $post_id ?>" />
	<input type="hidden" name="post_content" id="post_content" value="" />
	<label for="post_title"><?php _e('Name'); ?></label>
	<input type="text" name="post_title" id="post_title" value="<?php echo $post_title ?>" />
	<a class="button" href="javascript:void(0)" onclick="load_slideshowck_demo_data()"><img src="<?php echo $this->pluginurl ?>/images/magic.png" style="margin: -1px 3px 0 0;vertical-align: middle;" /><?php _e('Load demo data'); ?></a>
	<input type="hidden" name="slideshow-ck-params" id="slideshow-ck-params" value="<?php echo get_post_meta($post_id, 'slideshow-ck-params', TRUE); ?>" />
	<input type="hidden" name="slideshow-ck-slides" id="slideshow-ck-slides" value="<?php echo get_post_meta($post_id, 'slideshow-ck-slides', TRUE); ?>" />
</form>
<div id="slideshowedition">
	<div class="menulinkck current" tab="tab_images"><?php _e('Images'); ?></div>
	<div class="menulinkck" tab="tab_styles"><?php _e('Styles'); ?></div>
	<div class="menulinkck" tab="tab_effects"><?php echo _e('Effects'); ?></div>
	<div class="clr"></div>
	<div class="tabck menustyles current" id="tab_images">
		<!--<input type="hidden" name="slides_sources" id="slides_sources" value="slidemanager" />-->
		<div class="saveparam">
		<?php
		if ($this->ispro) {
			$options_slides_sources = array('slidemanager' => __('Slides Manager'), 'autoloadfolder' => __('Autoload from a folder'));
			echo $this->get_field('select', 'slides_sources', $this->get_param('slides_sources', 'slidemanager'), 'slideshowckparams', $options_slides_sources, false, 'onchange="show_slides_sources();"');
		} else { ?>
			<input type="hidden" name="slides_sources" id="slides_sources" value="slidemanager" />
		<?php
		}
		?>
		</div>
		<div class="slides_source" data-source="slidemanager">
			<div id="ckslides">
				<input name="ckaddslide" id="ckaddslide" type="button" value="<?php _e('Add a Slide') ?>" class="button button-secondary" onclick="addslideck();"/>
				<span id="addslide_waiticon"></span>
				<?php
				if (get_post_meta($post_id, 'slideshow-ck-slides', TRUE)) {
					$slides = json_decode(str_replace('|qq|', '"', get_post_meta((int) $post_id, 'slideshow-ck-slides', TRUE)));
					if ($slides && count($slides)) {
						foreach ($slides as $i => $slide) {
							$this->add_slide($i, $slide);
						}
					}
				}
				?>
			</div>
			<input name="ckaddslide" id="ckaddslide1" type="button" value="<?php _e('Add a Slide') ?>" class="button button-secondary" onclick="addslideck();"/>
		</div>
		<div class="slides_source saveparam" data-source="autoloadfolder">
			<?php 
			if ($this->ispro) {
				$this->pro_class->render_autoload_from_folder_option();
			}
			?>
		</div>
	</div>
	<div class="tabck menustyles saveparam" id="tab_styles">
		<div style="background: #fff;border:1px solid #ddd;">
			<div style="background: url(<?php echo $this->pluginurl; ?>/images/slideshowck_styles.png) 100px 50px no-repeat; width:550px;height:360px;position:relative;margin:0px auto 10px auto;">
				<div style="position:absolute;left:10px;top:150px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Height') ?></div>
					<input id="height" type="text" value="<?php echo $this->get_param('height'); ?>" name="height" style="">
				</div>
				<div style="position:absolute;left:220px;top:40px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Width') ?></div>
					<input id="width" type="text" value="<?php echo $this->get_param('width'); ?>" name="width" style="">
				</div>
				<?php
				$options_yes_no = array(
					'1' => __('Yes')
					, '0' => __('No')
				);
				?>
				<div style="position:absolute;left:420px;top:20px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Loader icon') ?></div>
					<?php
					$options_loader = array('pie' => 'Pie'
						, 'bar' => 'Bar'
						, 'none' => __('None')
						);
					echo $this->get_field('select', 'loader', $this->get_param('loader'), '', $options_loader);
					?>
				</div>
				<div style="position:absolute;left:85px;top:320px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Navigation') ?></div>
					<?php
					echo $this->get_field('select', 'navigation', $this->get_param('navigation'), '', $options_yes_no);
					?>
				</div>
				<div style="position:absolute;left:220px;top:320px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Thumbnails') ?></div>
					<?php
					echo $this->get_field('select', 'thumbnails', $this->get_param('thumbnails'), '', $options_yes_no);
					?>
				</div>
				<div style="position:absolute;left:420px;top:320px;width:105px;">
					<div style="position:absolute;left:5px;top:-18px;"><?php _e('Pagination') ?></div>
					<?php
					echo $this->get_field('select', 'pagination', $this->get_param('pagination'), '', $options_yes_no);
					?>
				</div>
			</div>
		</div>
		<div>
			<label for="theme"><?php _e('Theme'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/photo.png" />
			<?php echo $this->get_field('select', 'theme', $this->get_param('theme'), 'theme', CKfolder::folders($this->plugindir . '/themes'), true); ?>
		</div>
		<div>
			<label for="skin"><?php _e('Skin'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/palette.png" />
			<?php
			$options_skin = array('camera_amber_skin' => 'camera_amber_skin',
				'camera_ash_skin' => 'camera_ash_skin',
				'camera_azure_skin' => 'camera_azure_skin',
				'camera_beige_skin' => 'camera_beige_skin',
				'camera_black_skin' => 'camera_black_skin',
				'camera_blue_skin' => 'camera_blue_skin',
				'camera_brown_skin' => 'camera_brown_skin',
				'camera_burgundy_skin' => 'camera_burgundy_skin',
				'camera_charcoal_skin' => 'camera_charcoal_skin',
				'camera_chocolate_skin' => 'camera_chocolate_skin',
				'camera_coffee_skin' => 'camera_coffee_skin',
				'camera_cyan_skin' => 'camera_cyan_skin',
				'camera_fuchsia_skin' => 'camera_fuchsia_skin',
				'camera_gold_skin' => 'camera_gold_skin',
				'camera_green_skin' => 'camera_green_skin',
				'camera_grey_skin' => 'camera_grey_skin',
				'camera_indigo_skin' => 'camera_indigo_skin',
				'camera_khaki_skin' => 'camera_khaki_skin',
				'camera_lime_skin' => 'camera_lime_skin',
				'camera_magenta_skin' => 'camera_magenta_skin',
				'camera_maroon_skin' => 'camera_maroon_skin',
				'camera_orange_skin' => 'camera_orange_skin',
				'camera_olive_skin' => 'camera_olive_skin',
				'camera_pink_skin' => 'camera_pink_skin',
				'camera_pistachio_skin' => 'camera_pistachio_skin',
				'camera_pink_skin' => 'camera_pink_skin',
				'camera_red_skin' => 'camera_red_skin',
				'camera_tangerine_skin' => 'camera_tangerine_skin',
				'camera_turquoise_skin' => 'camera_turquoise_skin',
				'camera_violet_skin' => 'camera_violet_skin',
				'camera_white_skin' => 'camera_white_skin',
				'camera_yellow_skin' => 'camera_yellow_skin');
			echo $this->get_field('select', 'skin', $this->get_param('skin'), 'skin', $options_skin);
			?>
		</div>
		<div>
			<label for="imgalignment"><?php _e('Image alignment'); ?></label>
			<img class="iconck" style="float: none;" src="<?php echo $this->pluginurl; ?>/images/image_alignment.png">
			<?php
			$options_imgalignment = array(
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
			echo $this->get_field('select', 'imgalignment', $this->get_param('imgalignment'), 'imgalignment', $options_imgalignment);
			?>
		</div>
		<div>
			<label for="thumbnailheight"><?php _e('Thumbnail height') ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/height.png" />
			<input id="thumbnailheight" name="thumbnailheight" type="text" value="<?php echo $this->get_param('thumbnailheight') ?>" >
		</div>
			<div class="ckheading"><?php _e('Caption Title'); ?></div>
			<div>
				<label for="captiontitle_color"><?php _e('Title Color'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
				<?php echo $this->get_field('color', 'captiontitle_fontcolor', $this->get_param('captiontitle_fontcolor')) ?>
			</div>
			<div>
				<label for="captiontitle_fontsize"><?php _e('Font Size'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/text_fontsize.png" />
				<?php echo $this->get_field('text', 'captiontitle_fontsize', $this->get_param('captiontitle_fontsize')) ?>
			</div>
			<div>
				<label for="captiontitle_fontfamily"><?php _e('Font Family'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/style.png" />
				<?php echo $this->get_field('text', 'captiontitle_fontfamily', $this->get_param('captiontitle_fontfamily')) ?>
				<?php if ($this->ispro) : ?>
					<br />
					<label for="title_googlefont"><?php _e('Google Font'); ?></label>
					<img class="iconck" src="<?php echo $this->pluginurl ?>/images/google.png" />
					<?php echo $this->get_field('text', 'captiontitle_googlefont', $this->get_param('captiontitle_googlefont')) ?>
					<a class="button btn-primary btn" href="javascript:void(0)" onclick="ck_load_googlefont();" title="Example: <link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>"><?php _e('Import'); ?></a>
					<span id="captiontitle_googlefont_wait" style="height:16px;width:16px;"></span>
				<?php endif; ?>
			</div>
			<div>
				<label for="captiontitle_fontweight"><?php _e('Font Weight'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/text_bold.png" />
				<?php
				$options_fontweight = array('normal' => __('Normal'), 'bold' => __('Bold'));
				echo $this->get_field('radio', 'captiontitle_fontweight', $this->get_param('captiontitle_fontweight'), '', $options_fontweight);
				?>
			</div>
			<div class="ckheading"><?php _e('Caption Description'); ?></div>
			<div>
				<label for="captiondesc_color"><?php _e('Description Color'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
<?php echo $this->get_field('color', 'captiondesc_fontcolor', $this->get_param('captiondesc_fontcolor')) ?>
			</div>
			<div>
				<label for="captiondesc_fontsize"><?php _e('Font Size'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/text_fontsize.png" />
<?php echo $this->get_field('text', 'captiondesc_fontsize', $this->get_param('captiondesc_fontsize')) ?>
			</div>
			<div>
				<label for="captiondesc_fontfamily"><?php _e('Font Family'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/style.png" />
				<?php echo $this->get_field('text', 'captiondesc_fontfamily', $this->get_param('captiondesc_fontfamily')) ?>
<?php if ($this->ispro) : ?>
					<br />
					<label for="title_googlefont"><?php _e('Google Font'); ?></label>
					<img class="iconck" src="<?php echo $this->pluginurl ?>/images/google.png" />
	<?php echo $this->get_field('text', 'captiondesc_googlefont', $this->get_param('captiondesc_googlefont')) ?>
					<a class="button btn-primary btn" href="javascript:void(0)" onclick="ck_load_googlefont();" title="Example: <link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>"><?php _e('Import'); ?></a>
					<span id="captiondesc_googlefont_wait" style="height:16px;width:16px;"></span>
<?php endif; ?>
			</div>
			<div>
				<label for="captiondesc_fontweight"><?php _e('Font Weight'); ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/text_bold.png" />
				<?php
				$options_fontweight = array('normal' => __('Normal'), 'bold' => __('Bold'));
				echo $this->get_field('radio', 'captiondesc_fontweight', $this->get_param('captiondesc_fontweight'), '', $options_fontweight);
				?>
			</div>
			<div class="ckheading"><?php _e('Caption Styles'); ?></div>
			<div>
				<label for="caption_bgcolor1"><?php _e('Background Color') ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" />
<?php echo $this->get_field('color', 'caption_bgcolor1', $this->get_param('caption_bgcolor1')) ?>
<?php echo $this->get_field('color', 'caption_bgcolor2', $this->get_param('caption_bgcolor2')) ?>
			</div>
			<div>
				<label for="caption_opacity"><?php _e('Opacity') ?></label>
				<img class="iconck" src="<?php echo $this->pluginurl ?>/images/layers.png" />
<?php echo $this->get_field('text', 'caption_bgopacity', $this->get_param('caption_bgopacity')) ?>
			</div>
			<div>
				<label for="caption_margintop"><?php _e('Margin'); ?></label>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/margin_top.png" /></span>
				<span style="width:35px;" caption="<?php _e('Top'); ?>"><?php echo $this->get_field('text', 'caption_margintop', $this->get_param('caption_margintop')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/margin_right.png" /></span>
				<span style="width:35px;" caption="<?php _e('Right'); ?>"><?php echo $this->get_field('text', 'caption_marginright', $this->get_param('caption_marginright')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/margin_bottom.png" /></span>
				<span style="width:35px;" caption="<?php _e('Bottom'); ?>"><?php echo $this->get_field('text', 'caption_marginbottom', $this->get_param('caption_marginbottom')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/margin_left.png" /></span>
				<span style="width:35px;" caption="<?php _e('Left'); ?>"><?php echo $this->get_field('text', 'caption_marginleft', $this->get_param('caption_marginleft')) ?></span>
			</div>
			<div>
				<label for="caption_paddingtop"><?php _e('Padding'); ?></label>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/padding_top.png" /></span>
				<span style="width:35px;" caption="<?php _e('Top'); ?>"><?php echo $this->get_field('text', 'caption_paddingtop', $this->get_param('caption_paddingtop')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/padding_right.png" /></span>
				<span style="width:35px;" caption="<?php _e('Right'); ?>"><?php echo $this->get_field('text', 'caption_paddingright', $this->get_param('caption_paddingright')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/padding_bottom.png" /></span>
				<span style="width:35px;" caption="<?php _e('Bottom'); ?>"><?php echo $this->get_field('text', 'caption_paddingbottom', $this->get_param('caption_paddingbottom')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/padding_left.png" /></span>
				<span style="width:35px;" caption="<?php _e('Left'); ?>"><?php echo $this->get_field('text', 'caption_paddingleft', $this->get_param('caption_paddingleft')) ?></span>
			</div>
			<div>
				<label for="caption_roundedcornerstl"><?php _e('Border Radius'); ?></label>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/border_radius_tl.png" /></span>
				<span style="width:35px;" title="<?php _e('Top Left'); ?>"><?php echo $this->get_field('text', 'caption_roundedcornerstl', $this->get_param('caption_roundedcornerstl')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/border_radius_tr.png" /></span>
				<span style="width:35px;" title="<?php _e('Top Right'); ?>"><?php echo $this->get_field('text', 'caption_roundedcornerstr', $this->get_param('caption_roundedcornerstr')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/border_radius_br.png" /></span>
				<span style="width:35px;" title="<?php _e('Bottom Right'); ?>"><?php echo $this->get_field('text', 'caption_roundedcornersbr', $this->get_param('caption_roundedcornersbr')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/border_radius_bl.png" /></span>
				<span style="width:35px;" title="<?php _e('Bottom Left'); ?>"><?php echo $this->get_field('text', 'caption_roundedcornersbl', $this->get_param('caption_roundedcornersbl')) ?></span>
			</div>
			<div>
				<label for="caption_bordercolor"><?php _e('Border'); ?></label>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" /></span>
				<span><?php echo $this->get_field('color', 'caption_bordercolor', $this->get_param('caption_bordercolor')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/shape_square.png" /></span>
				<span style="width:35px;"><?php echo $this->get_field('text', 'caption_borderwidth', $this->get_param('caption_borderwidth')) ?></span>
			</div>
			<div>
				<label for="caption_shadowcolor"><?php _e('Shadow'); ?></label>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/color.png" /></span>
				<span><?php echo $this->get_field('color', 'caption_shadowcolor', $this->get_param('caption_shadowcolor')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/shadow_blur.png" /></span>
				<span style="width:35px;"><?php echo $this->get_field('text', 'caption_shadowblur', $this->get_param('caption_shadowblur')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/shadow_spread.png" /></span>
				<span style="width:35px;"><?php echo $this->get_field('text', 'caption_shadowspread', $this->get_param('caption_shadowspread')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/offsetx.png" /></span>
				<span style="width:35px;"><?php echo $this->get_field('text', 'caption_shadowoffsetx', $this->get_param('caption_shadowoffsetx')) ?></span>
				<span><img class="iconck" src="<?php echo $this->pluginurl ?>/images/offsety.png" /></span>
				<span style="width:35px;"><?php echo $this->get_field('text', 'caption_shadowoffsety', $this->get_param('caption_shadowoffsety')) ?></span>
				<?php
				$optionsboxshadowinset = array('0' => __('Out'), '1' => __('In'));
				echo $this->get_field('radio', 'caption_shadowinset', $this->get_param('caption_shadowinset'), '', $optionsboxshadowinset);
				?>
			</div>
	</div>
	<div class="tabck menustyles saveparam" id="tab_effects">
		<div>
			<label for="effect"><?php _e('Animation'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/application_view_gallery.png" />
			<?php
			$options_effect = '<option value="random">random</option>
					<option value="simpleFade">simpleFade</option>
					<option value="curtainTopLeft">curtainTopLeft</option>
					<option value="curtainTopRight">curtainTopRight</option>
					<option value="curtainBottomLeft">curtainBottomLeft</option>
					<option value="curtainBottomRight">curtainBottomRight</option>
					<option value="curtainSliceLeft">curtainSliceLeft</option>
					<option value="curtainSliceRight">curtainSliceRight</option>
					<option value="blindCurtainTopLeft">blindCurtainTopLeft</option>
					<option value="blindCurtainTopRight">blindCurtainTopRight</option>
					<option value="blindCurtainBottomLeft">blindCurtainBottomLeft</option>
					<option value="blindCurtainBottomRight">blindCurtainBottomRight</option>
					<option value="blindCurtainSliceBottom">blindCurtainSliceBottom</option>
					<option value="blindCurtainSliceTop">blindCurtainSliceTop</option>
					<option value="stampede">stampede</option>
					<option value="mosaic">mosaic</option>
					<option value="mosaicReverse">mosaicReverse</option>
					<option value="mosaicRandom">mosaicRandom</option>
					<option value="mosaicSpiral">mosaicSpiral</option>
					<option value="mosaicSpiralReverse">mosaicSpiralReverse</option>
					<option value="topLeftBottomRight">topLeftBottomRight</option>
					<option value="bottomRightTopLeft">bottomRightTopLeft</option>
					<option value="bottomLeftTopRight">bottomLeftTopRight</option>
					<option value="bottomLeftTopRight">bottomLeftTopRight</option>
					<option value="scrollLeft">scrollLeft</option>
					<option value="scrollRight">scrollRight</option>
					<option value="scrollHorz">scrollHorz</option>
					<option value="scrollBottom">scrollBottom</option>
					<option value="scrollTop">scrollTop</option>';
			echo $this->get_field('select', 'effect', $this->get_param('effect'), '', $options_effect, false, ' multiple="true"')
			?>
		</div>
		<div>
			<label for="captioneffect"><?php _e('Caption animation'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/application_view_gallery.png" />
			<?php
			$options_captioneffect = '<option value="moveFromLeft">moveFromLeft</option>
					<option value="moveFromRight">moveFromRight</option>
					<option value="moveFromTop">moveFromTop</option>
					<option value="moveFromBottom">moveFromBottom</option>
					<option value="fadeIn">fadeIn</option>
					<option value="fadeFromLeft">fadeFromLeft</option>
					<option value="fadeFromRight">fadeFromRight</option>
					<option value="fadeFromTop">fadeFromTop</option>
					<option value="fadeFromBottom">fadeFromBottom</option>';
			echo $this->get_field('select', 'captioneffect', $this->get_param('captioneffect'), '', $options_captioneffect)
			?>
		</div>
		<div>
			<label for="time"><?php _e('Slide duration'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/hourglass.png" />
			<?php echo $this->get_field('text', 'time', $this->get_param('time')) ?> ms
		</div>
		<div>
			<label for="transperiod"><?php _e('Transition duration'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/hourglass.png" />
			<?php echo $this->get_field('text', 'transperiod', $this->get_param('transperiod')) ?> ms
		</div>
		<div>
			<label for="portrait"><?php _e('Crop images'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/shape_handles.png" />
<?php
echo $this->get_field('radio', 'portrait', $this->get_param('portrait'), '', $options_yes_no);
?>
		</div>
		<div>
			<label for="autoAdvance"><?php _e('Autoplay'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/control_play.png" />
<?php
echo $this->get_field('radio', 'autoAdvance', $this->get_param('autoAdvance'), '', $options_yes_no);
?>
		</div>
		<div>
			<label for="hover"><?php _e('Pause on mouseover'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/control_pause.png" />
<?php
echo $this->get_field('radio', 'hover', $this->get_param('hover'), '', $options_yes_no);
?>
		</div>
		<div>
			<label for="displayorder"><?php _e('Display order'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/control_repeat.png" />
<?php
$options_displayorder = '<option value="normal">' . __('Normal') . '</option>
					<option value="shuffle">' . __('Random') . '</option>';
echo $this->get_field('select', 'displayorder', $this->get_param('displayorder'), '', $options_displayorder)
?>
		</div>
		<div>
			<label for="fullpage"><?php _e('Full page background'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/arrow_out.png" />
<?php
echo $this->get_field('radio', 'fullpage', $this->get_param('fullpage'), '', $options_yes_no);
?>
		</div>
		<div>
			<label for="imagetarget"><?php _e('Image link target'); ?></label>
			<img class="iconck" src="<?php echo $this->pluginurl ?>/images/link_go.png" />
<?php
$options_imagetarget = '<option value="_parent">' . __('Parent window') . '</option>
					<option value="_blank">' . __('New window') . '</option>';
echo $this->get_field('select', 'imagetarget', $this->get_param('imagetarget'), '', $options_imagetarget)
?>
		</div>
	</div>

</div>

<script type="text/javascript">
	jQuery('#slideshowedition > div.tabck:not(.current)').hide();
	jQuery('#slideshowedition > .menulinkck').each(function(i, tab) {
		jQuery(tab).click(function() {
			jQuery('#slideshowedition > div.tabck').hide();
			jQuery('#slideshowedition > .menulinkck').removeClass('current');
			if (jQuery('#' + jQuery(tab).attr('tab')).length)
				jQuery('#' + jQuery(tab).attr('tab')).show();
			jQuery(this).addClass('current');
		});
	});

	function addslideck() {
		var data = {
			action: 'add_slide',
			number: jQuery('.ckslide').length
		};
		jQuery('#addslide_waiticon').addClass('ckwait_mini');
		jQuery.post(ajaxurl, data, function(response) {
			response = jQuery(response);
			jQuery('#ckslides').append(response);
			jQuery('#addslide_waiticon').removeClass('ckwait_mini');
			create_tabs_in_slide(response);
		});
	}

	function add_image_url_to_slideck(button, url) {
		button = jQuery(button);
		url_relative = url.replace('<?php echo get_site_url(); ?>/', '');
		var ckslide = jQuery(button.parents('.ckslide')[0]);
		ckslide.find('.ckslideimgname').val(url_relative);
		ckslide.find('.ckslideimgthumb').attr('src', '<?php echo get_site_url(); ?>/' + url_relative);
	}

	function renumber_slides() {
		var index = 0;
		jQuery('#ckslides .ckslide').each(function(i, slide) {
			jQuery('.ckslidenumber', jQuery(slide)).html(index);
			index++;
		});
	}

	jQuery(document).ready(function($) {
		show_slides_sources();

		jQuery("#ckslides").sortable({
			placeholder: "ui-state-highlight",
			handle: ".ckslidehandle",
			items: ".ckslide",
			axis: "y",
			forcePlaceholderSize: true,
			forceHelperSize: true,
			dropOnEmpty: true,
			tolerance: "pointer",
			placeholder: "placeholder",
					zIndex: 9999,
			update: function(event, ui) {
				renumber_slides();
			}
		});

		jQuery('#ckslides .ckslide').each(function(i, slide) {
			slide = jQuery(slide);
			create_tabs_in_slide(slide);
		});
	});
</script>
