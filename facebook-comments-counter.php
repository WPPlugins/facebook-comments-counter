<?php
/*
 * Plugin Name: Facebook comments counter
 * Plugin URI: http://www.almogbaku.com
 * Description: Add comments counter from facebook
 * Author: Almog Baku
 * Author URI: http://www.almogbaku.com
 * License: GPL v.3 < http://www.opensource.org/licenses/gpl-3.0.html >
 * Version: 1.0
 */


register_activation_hook(__FILE__, 'facebook_comments_count_check_dependency');
function facebook_comments_count_check_dependency() {
	if(!is_plugin_active('facebook-comments-for-wordpress/facebook-comments.php'))
		die('The plugin "Facebook Comments for WordPress" is require!');
}

add_action('admin_menu', 'facebook_comments_count_add_admin');
function facebook_comments_count_add_admin() {
	add_options_page("Facebook Comments Counter", "Facebook Comments Counter", 'manage_options', basename(__FILE__), 'facebook_comments_count_admin');
	
	register_setting('facebook_comments_count', 'fbComments_secret');
}
function facebook_comments_count_admin() {?>

<div class="wrap">
<h2><?php _e("Facebook comments counter options"); ?></h2>

<form method="post" action="options.php">
    <?php settings_fields('facebook_comments_count'); ?>
    <table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e("Application ID"); ?></th>
		    <td><input type="text" disabled="disabled" name="fbComments_appId" value="<?php echo get_option('fbComments_appId'); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><a href="http://www.facebook.com/developers/apps.php" title="facebook developers"><?php _e("Application secret"); ?></a></th>
		    <td><input type="text" name="fbComments_secret" value="<?php echo get_option('fbComments_secret'); ?>" /></td>
		</tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div><?php

}


function facebook_comments_count_get_comments($value) {
	global $fbComments_settings, $wp_query, $facebook_comments_count;
	
	$xid = $fbComments_settings['fbComments_xid'] . "_post" . $wp_query->post->ID;
	
	try {
	    $result = $facebook_comments_count->api(array(
	        'method' => 'fql.query',
	        'query' => 'SELECT count FROM comments_info WHERE app_id='.$facebook_comments_count->getAppId().' AND xid="'.$xid.'"'
	    ));
	    return $value+$result[0]['count'];
	} catch (FacebookApiException $e) {
	    error_log($e);
	    return $value;
	}
}

if(get_option("fbComments_appId")!='' && get_option("fbComments_secret")!='') {
	require_once('facebook.php');
	$facebook_comments_count = new Facebook(array(
	    'appId' => get_option("fbComments_appId"),
	    'secret' => get_option("fbComments_secret"),
	));
	
	add_filter('get_comments_number','facebook_comments_count_get_comments');
}
	