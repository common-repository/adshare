<?php
/*
Plugin Name: AdShare
Plugin URI: http://tinsology.net/plugins/adshare/
Description: Allows you to display adsense ads based on the current post author. Version 0.2Beta
Author: Mathew Tinsley
Version: 0.2
Author URI: http://tinsology.net/
*/
/*  Copyright 2009  Mathew Tinsley  (email : tinsley@tinsology.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//register hooks
register_activation_hook(__FILE__, 'as_install');
register_deactivation_hook(__FILE__, 'as_uninstall');
add_action('admin_menu', 'as_adminMenu');
add_action('wp' , 'as_definePub');
add_filter('widget_text' , 'as_filter');
add_filter('the_content' , 'as_filter');

function as_definePub()
{
	global $pubid;
	$pubid = as_fetchPub();
}


function as_adminMenu()
{
	add_submenu_page('options-general.php', 'AdShare' , 'AdShare' , 8 , 'adshare' , 'as_display');
}

function as_display()
{
	global $wpdb;
	$meta = $wpdb->prefix . 'usermeta';
	@include(dirname(__FILE__) . '/style.css');
	as_header();
	//page content
	$update = false;
	if(isset($_POST['submit_button']))
	{
		$update = true;
		update_option('as_defaultAdsense' , $_POST['as_defaultAdsense']);
		
		update_option('as_support' , $_POST['as_support']);
		
		$userid = $_POST['as_user'];
		$upubid = $_POST['as_upub'];
		
		
		$query;
		if($userid != -1)
		{
			if($umeta_id = $wpdb->get_var("SELECT umeta_id FROM $meta WHERE user_id = '$userid' AND meta_key = 'as_adsense';"))
			{
				$query = "UPDATE $meta SET meta_value = '$upubid' WHERE umeta_id = $umeta_id;";
			}
			else
			{
				$query = "INSERT INTO $meta (umeta_id , user_id , meta_key , meta_value)
							VALUES(NULL , $userid , 'as_adsense' , '$upubid');";
			}
		
		$wpdb->query($query);
		}
	}
	
	$dropdown = '<select name="as_user" onchange="updatedPubField(this.value);"><option value="-1" selected="selected">Select User...</option>';
	$szSort = 'user_nicename';

	$aUsersID = $wpdb->get_col( $wpdb->prepare(
		"SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC"
		, $szSort ));
		
		?>
	<script type='text/javascript'>
	function updatedPubField(x)
	{
		var upub = document.getElementById('as_upub');

		switch(x)
		{
		
	<?php

	foreach($aUsersID as $iUserID)
	{
		$user = get_userdata( $iUserID );
		$pubid = $wpdb->get_var("SELECT meta_value FROM $meta WHERE user_id = $iUserID AND meta_key = 'as_adsense';");
		
		echo "case '$iUserID':
				upub.value = '$pubid';
				break;";

		$username = $user->user_nicename;
		$dropdown .= "<option value=\"$iUserID\">$username</option>";
	}
	$dropdown .= '</select>';
					?>
			default:
				upub.value = '';
		}
	}
	</script>
	<?php if($update) : ?>
	<div id='update-nag'>Options Updated</div>
	<?php endif; ?>
	<form id="as_form" name="as_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<table class="form-table">
	
	<tr valign="top">
	<th scope="row">Default Adsense ID</th>
	<td><input type="text" size="25" name="as_defaultAdsense" value="<?php echo get_option('as_defaultAdsense'); ?>" /></td>
	<td>This will be the Publisher ID used when no other is available.
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">Add Adsense ID</th>
	<td><?php echo $dropdown; ?> <input type="text" size="25" name="as_upub" id="as_upub" /></td>
	<td>Associate the given user with the given Adsense ID
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">Support This Plugin?</th>
	<td><input type="radio" name="as_support" value="1" <?php if(get_option('as_support') == 1){echo 'checked="checked"';} ?>/> Yes! 
        <input type="radio" name="as_support" value="0" <?php if(get_option('as_support') == 0){echo 'checked="checked"';} ?> /> No thanks</td>
	<td>
	Checking this option will cause your publisher id to be replaced <em>2%</em> of the time. 98% of the time your publisher id will be used.
	</td>
	</tr>
	</table>
	<p class="submit">
	<input name="submit_button" type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
	</p>


	</form>
	<h3>Usage</h3>
	<p>
	For ads coded directly into your template, replace
	</p>
	google_ad_client = "pub-#########"; <br />
	<p>with</p>
	google_ad_client = "&lt;?php global $pubid; echo $pubid; ?&gt;";

	<p>
	For ads in a widget or post, replace
	</p>
	google_ad_client = "pub-#########"; <br />
	<p>with</p>
	google_ad_client = "[pubid]";

	<?php
	as_footer();
}

function as_install()
{
	add_option('as_version');
	update_option('as_version' , '0.2');
	
	add_option('as_defaultAdsense');
	add_option('as_support' , 0);
}

function as_uninstall()
{
	delete_option('as_version');
}

function as_filter($content)
{
	global $pubid;
	return str_replace('[pubid]' , $pubid , $content);
}

function as_fetchPub()
{
	if(get_option('as_support') == 1)
	{
		if(rand(0 , 99) < 2)
			return 'pub-5524816422457717';
	}
	if(is_single() )
	{
		//look for user's adsense id
		global $wp_query;
		$authorID = $wp_query->post->post_author;
		global $wpdb;
		$table = $wpdb->prefix . 'usermeta';
		$query = "SELECT meta_value FROM $table WHERE user_id = $authorID AND meta_key = 'as_adsense';";
		if($pub = $wpdb->get_var($query))
			return $pub;
		else
			return get_option('as_defaultAdsense');
	}
	//else use default
	return get_option('as_defaultAdsense');
}

function as_header()
{
	echo '<div class="wrap">';
	echo '<h2>AdShare</h2>';
}

function as_footer()
{
	$as_version = get_option('as_version');
	echo '<br /><a href="http://tinsology.net/plugins/adshare/" target="_blank">' . _('Feedback Needed!') . '</a>
		  <br /><h6 align="right">Version ' . $as_version . '</h6></div>';
}

?>