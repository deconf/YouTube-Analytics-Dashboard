<?php
require_once 'functions.php';

if (! current_user_can('manage_options')) {
    return;
}
if (isset($_REQUEST['Clear'])) {
    yt_dash_clear_cache();
    ?><div class="updated">
	<p>
		<strong><?php _e('Cleared Cache.', 'yt_dash' ); ?></strong>
	</p>
</div>
<?php
}
if (isset($_REQUEST['Reset'])) {
    
    yt_dash_reset_token();
    ?><div class="updated">
	<p>
		<strong><?php _e('Token Reseted.', 'yt_dash'); ?></strong>
	</p>
</div>
<?php
} else 
    if (yt_dash_safe_get('yt_dash_hidden') == 'Y') {
        // Form data sent
        $apikey = yt_dash_safe_get('yt_dash_apikey');
        if ($apikey) {
            update_option('yt_dash_apikey', sanitize_text_field($apikey));
        }
        
        $clientid = yt_dash_safe_get('yt_dash_clientid');
        if ($clientid) {
            update_option('yt_dash_clientid', sanitize_text_field($clientid));
        }
        
        $clientsecret = yt_dash_safe_get('yt_dash_clientsecret');
        if ($clientsecret) {
            update_option('yt_dash_clientsecret', sanitize_text_field($clientsecret));
        }
        
        $dashaccess = yt_dash_safe_get('yt_dash_access');
        update_option('yt_dash_access', $dashaccess);
        
        $yt_dash_additional = yt_dash_safe_get('yt_dash_additional');
        update_option('yt_dash_additional', $yt_dash_additional);
        
        $yt_dash_style = yt_dash_safe_get('yt_dash_style');
        update_option('yt_dash_style', $yt_dash_style);
        
        $yt_dash_cachetime = yt_dash_safe_get('yt_dash_cachetime');
        update_option('yt_dash_cachetime', $yt_dash_cachetime);
        
        $yt_dash_userapi = yt_dash_safe_get('yt_dash_userapi');
        update_option('yt_dash_userapi', $yt_dash_userapi);
        
        if (! isset($_REQUEST['Clear']) and ! isset($_REQUEST['Reset'])) {
            ?>
<div class="updated">
	<p>
		<strong><?php _e('Options saved.', 'yt_dash'); ?></strong>
	</p>
</div>
<?php
        }
    } else 
        if (yt_dash_safe_get('yt_dash_hidden') == 'A') {
            $apikey = yt_dash_safe_get('yt_dash_apikey');
            if ($apikey) {
                update_option('yt_dash_apikey', sanitize_text_field($apikey));
            }
            
            $clientid = yt_dash_safe_get('yt_dash_clientid');
            if ($clientid) {
                update_option('yt_dash_clientid', sanitize_text_field($clientid));
            }
            
            $clientsecret = yt_dash_safe_get('yt_dash_clientsecret');
            if ($clientsecret) {
                update_option('yt_dash_clientsecret', sanitize_text_field($clientsecret));
            }
            
            $yt_dash_userapi = yt_dash_safe_get('yt_dash_userapi');
            update_option('yt_dash_userapi', $yt_dash_userapi);
        }

if (isset($_REQUEST['Authorize'])) {
    $adminurl = admin_url("#yt_dash-widget");
    echo '<script> window.location="' . $adminurl . '"; </script> ';
}

if (! get_option('yt_dash_access')) {
    update_option('yt_dash_access', "manage_options");
}

if (! get_option('yt_dash_style')) {
    update_option('yt_dash_style', "red");
}

$apikey = get_option('yt_dash_apikey');
$clientid = get_option('yt_dash_clientid');
$clientsecret = get_option('yt_dash_clientsecret');
$dashaccess = get_option('yt_dash_access');
$yt_dash_additional = get_option('yt_dash_additional');
$yt_dash_style = get_option('yt_dash_style');
$yt_dash_cachetime = get_option('yt_dash_cachetime');
$yt_dash_userapi = get_option('yt_dash_userapi');

?>
<div class="wrap">
	<?php echo "<h2>" . __( 'YouTube Analytics Settings', 'yt_dash' ) . "</h2>"; ?>  <hr>
</div>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
					<form name="yt_dash_form" method="post"
						action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <?php echo "<i>".__("You should watch this", 'yt_dash')." <a href='http://deconf.com/youtube-analytics-dashboard-wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=ytdwp' target='_blank'>". __("video tutorial")."</a> ".__("before proceeding with authorization.", 'yt_dash')."</i>";?>
		<p>
							<input name="yt_dash_userapi" type="checkbox"
								id="yt_dash_userapi" onchange="this.form.submit()" value="1"
								<?php if (get_option('yt_dash_userapi')) echo " checked='checked'"; ?> /><?php echo "<b>".__(" use your own API Project credentials", 'yt_dash' )."</b>"; ?></p>
		<?php
if (get_option('yt_dash_userapi')) {
    ?>
			<p><?php echo "<b>".__("API Key:", 'yt_dash')." </b>"; ?><input
								type="text" name="yt_dash_apikey"
								value="<?php echo esc_attr($apikey); ?>" size="61">
						</p>
						<p><?php echo "<b>".__("Client ID:", 'yt_dash')." </b>"; ?><input
								type="text" name="yt_dash_clientid"
								value="<?php echo esc_attr($clientid); ?>" size="60">
						</p>
						<p><?php echo "<b>".__("Client Secret:", 'yt_dash')." </b>"; ?><input
								type="text" name="yt_dash_clientsecret"
								value="<?php echo esc_attr($clientsecret); ?>" size="55">
						</p>  
		<?php }?>
		<p><?php
if (get_option('yt_dash_token')) {
    echo "<input type=\"submit\" name=\"Reset\" class=\"button button-secondary\" value=\"" . __("Clear Authorization", 'yt_dash') . "\" />";
    ?> <input type="submit" name="Clear" class="button button-secondary"
								value="<?php _e('Clear Cache', 'yt_dash' ) ?>" /><?php
    echo '<input type="hidden" name="yt_dash_hidden" value="Y">';
} else {
    echo "<input type=\"submit\" name=\"Authorize\" class=\"button button-secondary\" value=\"" . __("Authorize Application", 'yt_dash') . "\" />";
    ?> <input type="submit" name="Clear" class="button button-secondary"
								value="<?php _e('Clear Cache', 'yt_dash' ) ?>" /><?php
    echo '<input type="hidden" name="yt_dash_hidden" value="A">';
    echo "</form>";
    _e("(the rest of the settings will show up after completing the authorization process)", 'yt_dash');
    echo "</div>";
    ytdashsidebar();
    return;
}
?>
		</p>  
		<?php echo "<h3>" . __( 'Access Level', 'yt_dash' ). "</h3>";?>
		<p><?php _e("View Access Level: ", 'yt_dash' ); ?>
		<select id="yt_dash_access" name="yt_dash_access">
								<option value="manage_options"
									<?php if (($dashaccess=="manage_options") OR (!$dashaccess)) echo "selected='yes'"; echo ">".__("Administrators", 'yt_dash');?></option>
								<option value="edit_pages"
									<?php if ($dashaccess=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'yt_dash');?></option>
								<option value="publish_posts"
									<?php if ($dashaccess=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'yt_dash');?></option>
								<option value="edit_posts"
									<?php if ($dashaccess=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'yt_dash');?></option>
							</select>
						</p>

		<?php echo "<h3>" . __( 'Additional Stats', 'yt_dash' ). "</h3>";?>
		<p>
							<input name="yt_dash_additional" type="checkbox"
								id="yt_dash_additional" value="1"
								<?php if (get_option('yt_dash_additional')) echo " checked='checked'"; ?> /><?php _e(" show additional stats like engagement and annotations performance", 'yt_dash' ); ?></p>
						<p><?php _e("CSS Settings: ", 'yt_dash' ); ?>
		<select id="yt_dash_style" name="yt_dash_style">
								<option value="red"
									<?php if (($yt_dash_style=="red") OR (!$yt_dash_style)) echo "selected='yes'"; echo ">".__("Red Theme", 'yt_dash');?></option>
								<option value="light"
									<?php if ($yt_dash_style=="light") echo "selected='yes'"; echo ">".__("Light Theme", 'yt_dash');?></option>
							</select>
						</p>
		
		<?php echo "<h3>" . __( 'Cache Settings', 'yt_dash' ). "</h3>";?>
		<p><?php _e("Cache Time: ", 'yt_dash' ); ?>
		<select id="yt_dash_cachetime" name="yt_dash_cachetime">
								<option value="18000"
									<?php if ($yt_dash_cachetime=="18000") echo "selected='yes'"; echo ">".__("5 hours", 'yt_dash');?></option>
								<option value="36000"
									<?php if ($yt_dash_cachetime=="36000") echo "selected='yes'"; echo ">".__("10 hours", 'yt_dash');?></option>
								<option value="86400"
									<?php if (($yt_dash_cachetime=="86400") OR (!$yt_dash_cachetime)) echo "selected='yes'"; echo ">".__("1 day", 'yt_dash');?></option>
							</select>
						</p>

						<p class="submit">
							<input type="submit" name="Submit" class="button button-primary"
								value="<?php _e('Save Changes', 'yt_dash' ) ?>" />
						</p>
					</form>
				</div>
<?php

ytdashsidebar();

function ytdashsidebar()
{
    ?>
				
			</div>
		</div>

		<div id="postbox-container-1" class="postbox-container">
			<div class="meta-box-sortables">
				<div class="postbox">
					<h3>
						<span><?php _e("Setup Tutorial & Demo",'yt_dash') ?></span>
					</h3>
					<div class="inside">
						<a
							href="https://deconf.com/youtube-analytics-dashboard-wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=video&utm_campaign=ytdwp"
							target="_blank"><img
							src="<?php echo plugins_url( 'images/youtube-analytics.png' , __FILE__ );?>"
							width="100%" alt="" /></a>
					</div>
				</div>
				<div class="postbox">
					<h3>
						<span><?php _e("Support & Reviews",'yt_dash')?></span>
					</h3>
					<div class="inside">
						<div class="deconf-title">
							<a
								href="https://deconf.com/youtube-analytics-dashboard-wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=support&utm_campaign=ytdwp"><img
								src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  __('Plugin documentation and support on', 'yt_dash') . ' <a href="https://deconf.com/youtube-analytics-dashboard-wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=support&utm_campaign=ytdwp">deconf.com</a>.'; ?></div>
						<br />
						<div class="deconf-title">
							<a
								href="https://wordpress.org/support/view/plugin-reviews/youtube-analytics#plugin-info"><img
								src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  __('Your feedback and review are both important,', 'yt_dash').' <a href="https://wordpress.org/support/view/plugin-reviews/youtube-analytics#plugin-info">'.__('rate this plugin', 'yt_dash').'</a>!'; ?></div>
					</div>
				</div>
				<div class="postbox">
					<h3>
						<span><?php _e("Further Reading",'yt_dash')?></span>
					</h3>
					<div class="inside">
						<div class="deconf-title">
							<a
								href="https://deconf.com/move-website-https-ssl/?utm_source=ytdwp_config&utm_medium=link&utm_content=ssl&utm_campaign=ytdwp"><img
								src="<?php echo plugins_url( 'images/ssl.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  '<a href="https://deconf.com/move-website-https-ssl/?utm_source=ytdwp_config&utm_medium=link&utm_content=ssl&utm_campaign=ytdwp">'.__('Improve search rankings', 'yt_dash').'</a> '.__('by moving your website to HTTPS/SSL.', 'yt_dash'); ?></div>
						<br />
						<div class="deconf-title">
							<a
								href="https://deconf.com/wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=plugins&utm_campaign=ytdwp"><img
								src="<?php echo plugins_url( 'images/wp.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  __('Other', 'yt_dash').' <a href="https://deconf.com/wordpress/?utm_source=ytdwp_config&utm_medium=link&utm_content=plugins&utm_campaign=ytdwp">'.__('WordPress Plugins', 'yt_dash').'</a> '.__('written by the same author', 'yt_dash').'.'; ?></div>
					</div>
				</div>
				<div class="postbox">
					<h3>
						<span><?php _e("Other Services",'yt_dash')?></span>
					</h3>
					<div class="inside">
						<div class="deconf-title">
							<a href="http://tracking.maxcdn.com/c/94142/36539/378"><img
								src="<?php echo plugins_url( 'images/mcdn.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  __('Speed up your website and plug into a whole', 'yt_dash').' <a href="http://tracking.maxcdn.com/c/94142/36539/378">'.__('new level of site speed', 'yt_dash').'</a>.'; ?></div>
						<br />
						<div class="deconf-title">
							<a
								href="https://deconf.com/clicky-web-analytics-review/?utm_source=ytdwp_config&utm_medium=link&utm_content=clicky&utm_campaign=ytdwp"><img
								src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="deconf-desc"><?php echo  '<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=ytdwp_config&utm_medium=link&utm_content=clicky&utm_campaign=ytdwp">'.__('Web Analytics', 'yt_dash').'</a> '.__('service with users tracking at IP level.', 'yt_dash'); ?></div>
					</div>
				</div>
			</div>
		</div>

	</div>
	
<?php }?>	