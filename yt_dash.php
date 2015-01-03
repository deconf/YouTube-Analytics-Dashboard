<?php
/* 
Plugin Name: YouTube Analytics Dashboard
Plugin URI: https://deconf.com
Description: This plugin will display YouTube Analytics data and statistics into Admin Dashboard. 
Author: Alin Marcu
Version: 1.0
Author URI: https://deconf.com
*/  

function yt_dash_admin() {  
    include('yt_dash_admin.php');  
} 
	
function yt_dash_admin_actions() {
	if (current_user_can('manage_options')) {  
		add_options_page(__("YouTube Analytics Dashboard",'yt_dash'), __("YouTube Analytics",'yt_dash'), "manage_options", "YouTube_Analytics_Dashboard", "yt_dash_admin");
	}
}  

$plugin = plugin_basename(__FILE__);

add_action('wp_dashboard_setup', 'yt_dash_setup');
add_action('admin_menu', 'yt_dash_admin_actions'); 
add_action('admin_enqueue_scripts', 'yt_dash_admin_enqueue_scripts');
add_action('plugins_loaded', 'yt_dash_init');
add_filter("plugin_action_links_$plugin", 'yt_dash_settings_link' );

function yt_dash_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=YouTube_Analytics_Dashboard">'.__("Settings",'yt_dash').'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function yt_dash_init() {
  	load_plugin_textdomain( 'yt_dash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function yt_dash_admin_enqueue_scripts() {
	if (get_option('yt_dash_style')=="red"){
		wp_register_style( 'yt_dash', plugins_url('yt_dash.css', __FILE__) );
		wp_enqueue_style( 'yt_dash' );
	} else{
		wp_register_style( 'yt_dash', plugins_url('yt_dash_light.css', __FILE__) );
		wp_enqueue_style( 'yt_dash' );
	}	
}

function yt_dash_setup() {
	if (current_user_can(get_option('yt_dash_access'))) {
		wp_add_dashboard_widget(
			'yt_dash-widget',
			'YouTube Analytics Dashboard',			
			'yt_dash_content',
			$control_callback = null
		);
	}
}

function yt_dash_content() {
	
	require_once 'functions.php';
	
	if(!get_option('yt_dash_cachetime')){
		update_option('yt_dash_cachetime', 60*60*24);	
	}

	// If at least PHP 5.3.2 use the autoloader, if not try to edit the include_path
	if (version_compare(PHP_VERSION, '5.3.2') >= 0) {
	    require 'vendor/autoload.php';
	} else {
	    set_include_path($GADASH_Config->plugin_path . '/src/' . PATH_SEPARATOR . get_include_path());
	    // Include GAPI client
	    if (! class_exists('Google_Client')) {
	        require_once 'Google/Client.php';
	    }
	    
	    // Include GAPI YouTube Service
	    if (! class_exists('Google_Service_YouTube')) {
	        require_once 'Google/Service/YouTube.php';
	    }
	    	    
	    // Include GAPI YouTubeAnalytics Service
	    if (! class_exists('Google_Service_YouTubeAnalytics')) {
	        require_once 'Google/Service/YouTubeAnalytics.php';
	    }
	}	
	
	
	$client = new Google_Client();
	$client->setAccessType('offline');
	$client->setApplicationName('YouTube Analytics Dashboard');
	$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
	
	if (get_option('yt_dash_userapi')){		
			$client->setClientId(get_option('yt_dash_clientid'));
			$client->setClientSecret(get_option('yt_dash_clientsecret'));
			$client->setDeveloperKey(get_option('yt_dash_apikey'));
	}else{
			$client->setClientId('77574247852.apps.googleusercontent.com');
			$client->setClientSecret('1UkZ--iYRC0qNYiZToPQp-bb');
			$client->setDeveloperKey('AIzaSyClgZFJ-yPZ_iQDoomg6OghJneCOP5Gaxo');
		}
	
	$client->setScopes(array('https://www.googleapis.com/auth/yt-analytics.readonly','https://www.googleapis.com/auth/yt-analytics-monetary.readonly',"https://www.googleapis.com/auth/youtube", "https://www.googleapis.com/auth/youtube.readonly", "https://www.googleapis.com/auth/youtubepartner"));
	
	$service = new Google_Service_YouTubeAnalytics($client);		
	
	if (yt_dash_get_token()) { 
		$token = yt_dash_get_token();
		$client->setAccessToken($token);
	}
	
	if (!$client->getAccessToken()) {
		
		$authUrl = $client->createAuthUrl();
		
		if (!isset($_REQUEST['yt_dash_authorize'])){
			if (!current_user_can('manage_options')){
				_e("Ask an admin to authorize this Application", 'yt_dash');
				return;
			}
			echo '<div style="padding:20px;">'.__("Use this link to get your access code:", 'yt_dash').' <a href="'.$authUrl.'" target="_blank">'.__("Get Access Code", 'yt_dash').'</a>';
			echo '<form name="input" action="#" method="get">
						<p><b>'.__("Access Code:", 'yt_dash').' </b><input type="text" name="yt_dash_code" value="" size="45"></p>
						<input type="submit" class="button button-secondary" name="yt_dash_authorize" value="'.__("Save Access Code", 'yt_dash').'"/>
					</form>
				</div>';
			return;
		}		
		else{
			if ($_REQUEST['yt_dash_code']){
				$client->authenticate($_REQUEST['yt_dash_code']);
				yt_dash_store_token($client->getAccessToken());
			} else{
			
				$adminurl = admin_url("#yt_dash-widget");
				echo '<script> window.location="'.$adminurl.'"; </script> ';
			
			}	
		}

	}
	
	if (!isset($_REQUEST['yt_dash_code'])){
		$userid=yt_dash_getuserid($client);
	}else{
		echo "<br />".__("Getting your YouTube unique User ID ...",'yt-dash')."<br /><br />";
		$adminurl = admin_url("#yt_dash-widget");
		echo '<script> window.location="'.$adminurl.'"; </script> ';	
	}
	
	
	if(isset($_REQUEST['yt_query']))
		$yt_query = $_REQUEST['yt_query'];
	else	
		$yt_query = "views";
		
	if(isset($_REQUEST['yt_period']))	
		$yt_period = $_REQUEST['yt_period'];
	else
		$yt_period = "last30days"; 	

	switch ($yt_period){

		case 'thisyear'	:	$from = date('Y-01-01');
								$to = date('Y-m-d', time());
							break;

		case 'thismonth'	:	$from = date('Y-m-01');
								$to = date('Y-m-d', time());
								break;
		
		case 'last7days'	:	$from = date('Y-m-d', time()-7*24*60*60);
							$to = date('Y-m-d');
							break;	

		case 'last14days'	:	$from = date('Y-m-d', time()-14*24*60*60);
							$to = date('Y-m-d');
							break;	
							
		default	:	$from = date('Y-m-d', time()-30*24*60*60);
					$to = date('Y-m-d');
					break;

	}

	switch ($yt_query){

		case 'comments'	:	$title=__("Coments",'yt_dash'); break;

		case 'averageViewDuration'	:	$title=__("Average View Duration (seconds)",'yt_dash'); break;
		
		case 'likes'	:	$title=__("Likes",'yt_dash'); break;	

		case 'dislikes'	:	$title=__("Dislikes",'yt_dash'); break;
		
		case 'estimatedMinutesWatched'	:	$title=__("Minutes Watched (minutes)",'yt_dash'); break;
		
		default	:	$title=__("Views",'yt_dash');

	}

	$projectId="channel==".$userid;
	
	$metrics = $yt_query;
	$dimensions = 'day';

	try{
		$serial='ytdash_qr2'.str_replace(array(',','-',date('Y')),"",$from.$to.$metrics);
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
			$data = $service->reports->query($projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
			set_transient( $serial, $data, get_option('yt_dash_cachetime') );
		}else{
			$data = $transient;
			//echo "HIT0";			
		}	
	} catch (Google_Service_Exception $e) {
			echo yt_dash_pretty_error($e);
			return;
	}
	$yt_dash_statsdata="";

	foreach ($data->getRows() as $row){
		$yt_dash_statsdata.="['".$row[0]."',".$row[1]."],";
	}

	$metrics = 'views,estimatedMinutesWatched,averageViewDuration,likes,dislikes,comments';
	if (get_option('yt_dash_additional')){
		$metrics .= ',favoritesAdded,favoritesRemoved,shares,annotationClickThroughRate,annotationCloseRate,subscribersGained,subscribersLost';
	}
	
	$dimensions = '';
	try{
		$serial='ytdash_qr3'.str_replace(array(',','-',date('Y')),"",$from.$to);
		if (get_option('yt_dash_additional')){
			$serial.="additstats";
		}
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
			$data = $service->reports->query($projectId, $from, $to, $metrics);
			set_transient( $serial, $data, get_option('yt_dash_cachetime') );
		}else{
			$data = $transient;
		}	
	} catch (Google_Service_Exception $e) {
		echo yt_dash_pretty_error($e);
		return;
	}
	
	$rows=$data->getRows();
	
	if (get_option('yt_dash_style')=="light"){ 
		$css="colors:['gray','darkgray'],";
		$colors="black";
	} else{
		$css="colors:['#C7312B','#AF2B26'],";;
		$colors="#AF2B26";
	}
	
    $code='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(yt_dash_callback);

	  function yt_dash_callback(){
			yt_dash_drawstats();
			if(typeof yt_dash_additional == "function"){
				yt_dash_additional();
			}
	  }	

      function yt_dash_drawstats() {
        var data = google.visualization.arrayToDataTable(['."
          ['".__("Date", 'yt_dash')."', '".$title."'],"
		  .$yt_dash_statsdata.
		"  
        ]);

        var options = {
		  legend: {position: 'none'},	
		  pointSize: 3,".$css."
          title: '".$title."',
       	  chartArea: {width: '99%',height: '90%'},
	      vAxis: { textPosition: 'in', minValue: 0},
	      hAxis: { textPosition: 'none' }
		};

        var chart = new google.visualization.AreaChart(document.getElementById('yt_dash_statsdata'));
		chart.draw(data, options);
		
      }";

	if (get_option('yt_dash_additional')){
	
		$yt_dash_additional="['".__("Favorites Added",'yt_dash')."',".(int)$rows[0][6]."],";
		$yt_dash_additional.="['".__("Favorites Removed",'yt_dash')."',".(int)$rows[0][7]."],";
		$yt_dash_additional.="['".__("Subscribers Gained",'yt_dash')."',".(int)$rows[0][11]."],";
		$yt_dash_additional.="['".__("Subscribers Lost",'yt_dash')."',".(int)$rows[0][12]."],";	
		$yt_dash_additional.="['".__("Shares",'yt_dash')."',".(int)$rows[0][8]."],";
		$yt_dash_additional.="['".__("Annotation Click ThroughRate",'yt_dash')."',".round($rows[0][9],2)."],";
		$yt_dash_additional.="['".__("Annotation Close Rate",'yt_dash')."',".round($rows[0][10],2)."],";
 
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function yt_dash_additional() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Statistics",'yt_dash')."', '".__("Value",'yt_dash')."'],"
			  .$yt_dash_additional.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 7,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('yt_dash_additionaldata'));
			chart.draw(data, options);
			
		  }";

	}
	
    $code.="</script>";

	$yt_button_style=get_option('yt_dash_style')=='light'?'button':'ytbutton';
	$code.='<div id="yt_dash">
	<center>
		<div id="yt_buttons_div">
		
			<input class="'.$yt_button_style.'" type="button" value="'.__("7 days",'yt_dash').'" onClick="window.location=\'?yt_period=last7days&yt_query='.$yt_query.'\'" />
			<input class="'.$yt_button_style.'" type="button" value="'.__("14 days",'yt_dash').'" onClick="window.location=\'?yt_period=last14days&yt_query='.$yt_query.'\'" />
			<input class="'.$yt_button_style.'" type="button" value="'.__("30 days",'yt_dash').'" onClick="window.location=\'?yt_period=last30days&yt_query='.$yt_query.'\'" />
			<input class="'.$yt_button_style.'" type="button" value="'.__("This Month",'yt_dash').'" onClick="window.location=\'?yt_period=thismonth&yt_query='.$yt_query.'\'" />
			<input class="'.$yt_button_style.'" type="button" value="'.__("This Year",'yt_dash').'" onClick="window.location=\'?yt_period=thisyear&yt_query='.$yt_query.'\'" />		
		</div>
		
		<div id="yt_dash_statsdata"></div>
		<div id="ytdetails_div">
			
			<table class="yttable" cellpadding="4">
			<tr>
			<td width="24%">'.__("Views:",'yt_dash').'</td>
			<td width="12%" class="ytvalue"><a href="?yt_query=views&yt_period='.$yt_period.'" class="yttable">'.(int)$rows[0][0].'</td>
			<td width="24%">'.__("Watched:",'yt_dash').'</td>
			<td  width="12%" class="ytvalue"><a href="?yt_query=estimatedMinutesWatched&yt_period='.$yt_period.'" class="yttable">'.round(($rows[0][1]/60),2).'h</a></td>			
			<td width="24%">'.__("Duration:",'yt_dash').'</td>
			<td width="12%" class="ytvalue"><a href="?yt_query=averageViewDuration&yt_period='.$yt_period.'" class="yttable">'.(int)$rows[0][2].'s</a></td>
			</tr>
			<tr>
			<td>'.__("Likes:",'yt_dash').'</td>
			<td class="ytvalue"><a href="?yt_query=likes&yt_period='.$yt_period.'" class="yttable">'.(int)$rows[0][3].'</a></td>
			<td>'.__("Dislikes:",'yt_dash').'</td>
			<td class="ytvalue"><a href="?yt_query=dislikes&yt_period='.$yt_period.'" class="yttable">'.(int)$rows[0][4].'</a></td>
			<td>'.__("Comments:",'yt_dash').'</td>
			<td class="ytvalue"><a href="?yt_query=comments&yt_period='.$yt_period.'" class="yttable">'.(int)$rows[0][5].'</a></td>
			</tr>
			</table>
					
		</div>';
		
	$code.='</center>		
	</div>';

	if (get_option('yt_dash_additional'))
		$code .= '<br /><div id="yt_dash_additionaldata"></div>';
	
	echo $code; 
}	
?>