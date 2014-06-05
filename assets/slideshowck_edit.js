function save_slideshow() {
	if ( validate_data() === false ) {
		return;
	}

	if (jQuery('#ckslides').length && jQuery('#slides_sources').val() == 'slidemanager') {
		var slides_list = [];
		jQuery('#ckslides .ckslide').each(function(i, slide) {
			var slide_obj = {};
			slide = jQuery(slide);
			// slide_obj.imageurl = jQuery('.ckslideimgname', slide).val();
			slide_obj.imgname = jQuery('.ckslideimgname', slide).val();
			slide_obj.title = jQuery('.ckslidetitle', slide).val();
			slide_obj.title = slide_obj.title.replace(/"/g, "|dq|");
			slide_obj.description = jQuery('.ckslidedescription', slide).val();
			slide_obj.description = slide_obj.description.replace(/"/g, "|dq|");
//			slide_obj.imgthumb = jQuery('img', slide).src;
			slide_obj.imglink = jQuery('.ckslidelinktext', slide).val().replace(/"/g, "|dq|");
			slide_obj.imgtarget = jQuery('.ckslidetargettext', slide).val();
			slide_obj.imgalignment = jQuery('.ckslidedataalignmenttext', slide).val();
			slide_obj.imgvideo = jQuery('.ckslidevideotext', slide).val();
			slide_obj.slideselect = jQuery('.ckslideselect', slide).val();
			slide_obj.slidearticleid = jQuery('.ckslidearticleid', slide).val();
			slide_obj.slidearticlename = jQuery('.ckslidearticlename', slide).val();
			slide_obj.imgtime = jQuery('.ckslideimgtime', slide).val();
			slides_list.push(slide_obj);
		});
		jQuery('#slideshow-ck-slides').val(JSON.stringify(slides_list).replace(/"/g, '|qq|'));
	}

	var params_obj = {};
	jQuery('input, select', jQuery('.saveparam')).each(function(i, param) {
		param = jQuery(param);
		params_obj[param.attr('id')] = param.val();
	});
	jQuery('#slideshow-ck-params').val(JSON.stringify(params_obj).replace(/"/g, '|qq|'));
	jQuery('#slideshowck-edit').submit();
}

function removeslide(slide) {
	if (confirm('Remove this slide ?')) {
		slide.remove();
	}
}

function open_media_manager(button) {
	button = jQuery(button);
	wp.media.model.settings.post.id = 0;
	var file_frame;

	if (file_frame) {
		// Set the post ID to what we want
		// file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
		// Open frame
		file_frame.open();
		return;
	} else {
		// Set the wp.media post id so the uploader grabs the ID we want when initialised
		// wp.media.model.settings.post.id = set_to_post_id;
	}

	// Create the media frame.
	file_frame = wp.media.frames.file_frame = wp.media({
		title: jQuery(this).data('uploader_title'),
		button: {
			text: jQuery(this).data('uploader_button_text'),
		},
		multiple: false  // Set to true to allow multiple files to be selected
	});

	// When an image is selected, run a callback.
	file_frame.on('select', function() {
		// We set multiple to false so only get one image from the uploader
		attachment = file_frame.state().get('selection').first().toJSON();
		// Do something with attachment.id and/or attachment.url here
		add_image_url_to_slideck(button, attachment.url);
		// Restore the main post ID
		// wp.media.model.settings.post.id = wp_media_post_id;
	});

	// Finally, open the modal
	file_frame.open();
}

function create_tabs_in_slide(slide) {
	jQuery('div.tabck:not(.current)', slide).hide();
	jQuery('.menulinkck', slide).each(function(i, tab) {
		jQuery(tab).click(function() {
			jQuery('div.tabck', slide).hide();
			jQuery('.menulinkck', slide).removeClass('current');
			if (jQuery('#' + jQuery(tab).attr('tab')).length)
				jQuery('#' + jQuery(tab).attr('tab')).show();
			jQuery(this).addClass('current');
		});
	});
}

function show_slides_sources() {
	jQuery('.slides_source').each(function(i, source) {
		if (jQuery('#slides_sources').val() == jQuery(source).attr('data-source')) {
			jQuery(source).show();
		} else {
			jQuery(source).hide();
		}
	});

}

function load_slideshowck_demo_data() {
	if ( validate_data() === false ) {
		return;
	}

	if (!confirm('Warning, this will remove all your settings ! Do you want to continue ?')) {
		return;
	}

	jQuery('#slideshow-ck-slides').val(jQuery('#demo-slides').val());
	jQuery('#slideshow-ck-params').val(jQuery('#demo-params').val());
	jQuery('#slideshowck-edit').submit();
}

function validate_data() {
	if (jQuery('#post_title').val() == '') {
		alert('Please give a name to your slideshow');
		return false;
	}
	return true;
}