<?php
defined('ABSPATH') or die;
//require_once( ABSPATH . 'wp-admin/admin.php' );

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('CK_List_Table')) {
	require_once( $this->plugindir . '/cklibrary/class-ck-list-table.php' );
}

if (!class_exists('CK_Slideshow_List_Table')) {
	require_once( $this->plugindir . '/includes/class-ck-slideshow-table.php' );
}

//Prepare Table of elements
$wp_list_table = new CK_Slideshow_List_Table();
$wp_list_table->prepare_items();
?>

<div class="wrap">
	<img src="<?php echo $this->pluginurl ?>/images/logo_slideshowck_64.png" style="float:left; margin: 0px 5px;" />
	<h2>Slideshow CK
<?php
if (current_user_can('edit_post', 'slideshowck'))
	echo ' <a href="admin.php?page=slideshowck_edit&id=0" class="add-new-h2">' . __('Add New') . '</a>';
?></h2>
	<div style="clear:both;"></div>
	<form id="movies-filter" method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo!empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
		<input type="hidden" name="post_type" class="post_type_page" value="slideshowck" />
<?php
$wp_list_table->display()
?>
	</form>

</div>