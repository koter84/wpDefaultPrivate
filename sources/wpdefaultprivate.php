<?php
/**
 * @package wpDefaultPrivate
 * @version 1.1.1
 */
/*
Plugin Name: wpDefaultPrivate
Plugin URI: http://wordpress.org/plugins/wpDefaultPrivate/
Description: This plugin makes all posts default to private instead of public
Author: Dennis Koot
Version: 1.1.1
Author URI: http://denniskoot.nl/
Text Domain: wpDefaultPrivate
*/


// just a small grapical-fix so it looks as if the visibility is private (but really stays on public until save (otherwise drafts don't work))
function wpdefaultprivate_frontend_visibility()
{
	global $post;

	if($post->post_status == 'auto-draft' OR $post->post_status == 'draft' OR $post->post_status == 'pending' OR $post->post_status == 'future')
	{
		?>
		<script type="text/javascript">
			(function($){
				try {
					$('#post-visibility-display').text($("<div>").html("<?php echo __('Private', wpdefaultprivate); ?>").text());
					$('.edit-visibility').hide();
					$('#post-visibility-select').hide();
				} catch(err){}
			}) (jQuery);
		</script>
		<?php
	}
}
add_action('post_submitbox_misc_actions', 'wpdefaultprivate_frontend_visibility');


// when a post is published make it private
function wpdefaultprivate_status_change($post)
{
	wp_update_post(array(
		'ID'		=> $post->ID,
		'post_status'	=> 'private'
	));
}
add_action('auto-draft_to_publish', 'wpdefaultprivate_status_change', 10, 1);
add_action('draft_to_publish', 'wpdefaultprivate_status_change', 10, 1);
add_action('pending_to_publish', 'wpdefaultprivate_status_change', 10, 1);
add_action('future_to_publish', 'wpdefaultprivate_status_change', 10, 1);


// Init wpdefaultprivate-plugin
function wpdefaultprivate_init()
{
	// Translation-support (i18n)
	load_plugin_textdomain('wpdefaultprivate', false, 'wpdefaultprivate/languages');

	// Geef extra links in de plugin-overzichtspagina
//	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'wpsol_plugin_action_links');
}
add_action('plugins_loaded', 'wpdefaultprivate_init');


// Setup defaults during installation
register_activation_hook( __FILE__, 'wpdefaultprivate_install');

// Install function
function wpdefaultprivate_install()
{
	// set default option-values
	update_option('wpdefaultprivate_allusers', 1);
	// set capabilities to default value
	$wp_roles->add_cap('author', 'read_private_posts');
	$wp_roles->add_cap('contributor', 'read_private_posts');
	$wp_roles->add_cap('subscriber', 'read_private_posts');
}


// init the configuration page
function wpdefaultprivate_admin_menu()
{
	add_options_page('wpDefaultPrivate', 'wpDefaultPrivate', 'manage_options', 'wpdefaultprivate_settings', 'wpdefaultprivate_admin_options');
}
add_action('admin_menu', 'wpdefaultprivate_admin_menu');


// Admin Settings Pagina
function wpdefaultprivate_admin_options()
{
	if ( !current_user_can( 'manage_options' ) )
	{
		wp_die( __('You do not have sufficient permissions to access this page.', wpdefaultprivate) );
	}

	// See if the user has posted us some information
	$key = 'wpdefaultprivate_allusers';
	$hidden_field_name = 'wpdefaultprivate_hidden';
	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' )
	{
		global $wp_roles;

		if(isset($_POST[$key]))
		{
			update_option( $key, $_POST[$key] );
			// add capability to read private posts
			$wp_roles->add_cap('author', 'read_private_posts');
			$wp_roles->add_cap('contributor', 'read_private_posts');
			$wp_roles->add_cap('subscriber', 'read_private_posts');
		}
		else
		{
			update_option( $key, 0 );
			// remove capability to read private posts
			$wp_roles->remove_cap('author', 'read_private_posts');
			$wp_roles->remove_cap('contributor', 'read_private_posts');
			$wp_roles->remove_cap('subscriber', 'read_private_posts');
		}
		// Put an settings updated message on the screen
		echo "<div class=\"updated\"><p><strong>".__('Settings Saved', 'wpdefaultprivate')."</strong></p></div>";
    }

	// get current value for key
	if(get_option($key) == 1)
		$sel = "CHECKED";
	else
		$sel = "";

    // Now display the settings editing screen
    ?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo __('wpDefaultPrivate Settings', wpdefaultprivate); ?></h2>
	<form name="wpdefaultprivate_settings_form" method="post" action="">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<table class="form-table">

		<tr valign="top">
			<th scope="row"><label for="wpdefaultprivate_allusers"><?php echo __('Private Posts visible for all users', wpdefaultprivate); ?></label></th>
			<td>
				<input type="checkbox" name="wpdefaultprivate_allusers" value="1" <?php echo $sel; ?>/>
				<p class="description"><?php echo __('', wpdefaultprivate); ?></p>
			</td>
		</tr>

	</table>
	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>

	</form>
</div>
<?php
}
