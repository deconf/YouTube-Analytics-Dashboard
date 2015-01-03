<?php
	
	function yt_dash_pretty_error($e){
		return "<center><table><tr><td colspan='2' style='word-break:break-all;'>".$e->getMessage()."<br /><br /></td></tr><tr><td width='50%'><a href='http://wordpress.org/support/plugin/youtube' target='_blank'>".__("Help on Wordpress Forum",'yt_dash')."</a><td width='50%'><a href='http://forum.deconf.com/wordpress-plugins-f182/' target='_blank'>".__("Support on Deconf Forum",'yt_dash')."</a></td></tr></table></center>";	
	}

	function yt_dash_clear_cache(){
		global $wpdb;
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ytdash%%'");
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ytdash%%'");
	}
	
	function yt_dash_safe_get($key) {
		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}
		return false;
	}
	
	function yt_dash_store_token ($token){
		update_option('yt_dash_token', $token);
	}		
	
	function yt_dash_get_token (){

		if (get_option('yt_dash_token')){
			return get_option('yt_dash_token');
		}
		else{
			return;
		}
	
	}
	
	function yt_dash_reset_token (){
		update_option('yt_dash_token', "");
		update_option('yt_dash_access', "");
		update_option('yt_dash_userid', ""); 		 		
	}

	function yt_dash_getuserid ($client){

		$service = new Google_Service_YouTube($client);
		try{
			$serial='ytdash_qr1userid';
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->channels->listChannels('snippet', array('mine' => 'true',));
				set_transient( $serial, $data, get_option('yt_dash_cachetime') );
			}else{
				$data = $transient;
			}	
		} catch (Google_Service_Exception $e) {
				echo yt_dash_pretty_error($e);
				return;
		}

		$item=$data->items[0]->id;
		
		return substr($item,2);	
		
	}	
	
?>