<?php
/*  zingiri_mailz.php
 Copyright 2008,2009,2010 EBO
 Support site: http://www.zingiri.com

 This file is part of Zingiri Mailing List.

 Zingiri Mailing List is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Zingiri Mailing List is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Zingiri; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?php
/*
 Plugin Name: Zingiri Mailing List
 Plugin URI: http://www.zingiri.com
 Description: This plugin provides easy to use mailing list functionality to your Wordpress site
 Author: EBO
 Version: 0.8
 Author URI: http://www.zingiri.com/
 */
define("ZING_MAILZ_VERSION","0.8");
define("ZING_MAILZ_PREFIX","zing_");

// Pre-2.6 compatibility for wp-content folder location
if (!defined("WP_CONTENT_URL")) {
	define("WP_CONTENT_URL", get_option("siteurl") . "/wp-content");
}
if (!defined("WP_CONTENT_DIR")) {
	define("WP_CONTENT_DIR", ABSPATH . "wp-content");
}

if (!defined("ZING_MAILZ_PLUGIN")) {

	$zing_mailz_plugin=substr(dirname(__FILE__),strlen(WP_CONTENT_DIR)+9,strlen(dirname(__FILE__))-strlen(WP_CONTENT_DIR)-9);
	define("ZING_MAILZ_PLUGIN", $zing_mailz_plugin);
}

if (!defined("ZING_MAILZ_SUB")) {
	if (get_option("siteurl") == get_option("home"))
	{
		define("ZING_MAILZ_SUB", "wp-content/plugins/".ZING_MAILZ_PLUGIN."/osticket/upload/");
	}
	else {
		define("ZING_MAILZ_SUB", "wordpress/wp-content/plugins/".ZING_MAILZ_PLUGIN."/osticket/upload/");
	}
}
if (!defined("ZING_MAILZ_DIR")) {
	define("ZING_MAILZ_DIR", WP_CONTENT_DIR . "/plugins/".ZING_MAILZ_PLUGIN."/osticket/upload/");
}

if (!defined("ZING_MAILZ_LOC")) {
	define("ZING_MAILZ_LOC", WP_CONTENT_DIR . "/plugins/".ZING_MAILZ_PLUGIN."/");
}

if (!defined("ZING_MAILZ_URL")) {
	define("ZING_MAILZ_URL", WP_CONTENT_URL . "/plugins/".ZING_MAILZ_PLUGIN."/");
}
if (!defined("ZING_MAILZ_LOGIN")) {
	define("ZING_MAILZ_LOGIN", get_option("zing_mailz_login"));
}

define("ZING_OST_URL",ZING_MAILZ_URL.'lists');

$zing_footers[]=array('http://www.osticket.com/','osTicket');
$zing_mailz_version=get_option("zing_mailz_version");
if ($zing_mailz_version) {
	add_action("init","zing_mailz_init");
	add_filter('wp_footer','zing_mailz_footer');
	add_action("plugins_loaded", "zing_mailz_sidebar_init");
	add_filter('the_content', 'zing_mailz_content', 10, 3);
	add_action('wp_head','zing_mailz_header');
}

register_activation_hook(__FILE__,'zing_mailz_activate');
register_deactivation_hook(__FILE__,'zing_mailz_deactivate');
require_once(dirname(__FILE__) . '/includes/shared.inc.php');
require_once(dirname(__FILE__) . '/includes/http.class.php');
require_once(dirname(__FILE__) . '/includes/footer.inc.php');
require_once(dirname(__FILE__) . '/includes/integrator.inc.php');
require_once(dirname(__FILE__) . '/mailz_cp.php');

/**
 * Activation: creation of database tables & set up of pages
 * @return unknown_type
 */
function zing_mailz_activate() {
	global $wpdb;
	global $current_user;
	global $zing_mailz_options;

	$wpdb->show_errors();
	$prefix=$wpdb->prefix.ZING_MAILZ_PREFIX;
	$zing_mailz_version=get_option("zing_mailz_version");
	if (!$zing_mailz_version)
	{
		add_option("zing_mailz_version",ZING_MAILZ_VERSION);
	}
	else
	{
		update_option("zing_mailz_version",ZING_MAILZ_VERSION);
	}

	//create standard pages
	if ($zing_mailz_version <= '0.1') {
		$pages=array();
		$pages[]=array("Mailing list","mailz","*",0);

		$ids="";
		foreach ($pages as $i =>$p)
		{
			$my_post = array();
			$my_post['post_title'] = $p['0'];
			$my_post['post_content'] = '';
			$my_post['post_status'] = 'publish';
			$my_post['post_author'] = 1;
			$my_post['post_type'] = 'page';
			$my_post['comment_status'] = 'closed';
			$my_post['menu_order'] = 100+$i;
			$id=wp_insert_post( $my_post );
			if (empty($ids)) { $ids.=$id; } else { $ids.=",".$id; }
			if (!empty($p[1])) add_post_meta($id,'zing_mailz_page',$p[1]);
		}
		if (get_option("zing_mailz_pages"))
		{
			update_option("zing_mailz_pages",$ids);
		}
		else {
			add_option("zing_mailz_pages",$ids);
		}
	}

	//create database tables
	if (!$zing_mailz_version) {
		$http=zing_mailz_http("phplist",'admin/index.php',array('page'=>'initialise','firstintall'=>1));
		$news = new HTTPRequest($http);
		if ($news->live()) {
			$output=$news->DownloadToString(true);
		}
	}

	//set admin password
	$password=md5(time().get_option('home'));
	$query="update ".$prefix."phplist_admin set password='".$password."' where loginname='admin'";
	$wpdb->query($query);
	update_option("zing_mailz_password",$password);

	//set configuration options
	//$query="update ".$prefix."phplist_config set value='".str_replace('http://','',substr(ZING_MAILZ_URL,0,-1))."' where item='website'";
	$query="update ".$prefix."phplist_config set value='".str_replace('http://','',get_option('home'))."' where item='website'";
	$wpdb->query($query);

	//default options
	foreach ($zing_mailz_options as $value) {
		delete_option( $value['id'] );
		if ( !empty($value['id']) && !get_option($value['id']) ) update_option( $value['id'], $value['std'] );
	}
}

/**
 * Deactivation
 * @return void
 */
function zing_mailz_deactivate() {
	wp_clear_scheduled_hook('zing_mailz_cron_hook');
}

/**
 * Uninstallation: removal of database tables
 * @return void
 */
function zing_mailz_uninstall() {
	global $wpdb;

	$prefix=$wpdb->prefix.ZING_MAILZ_PREFIX;
	$rows=$wpdb->get_results("show tables like '".$prefix."%'",ARRAY_N);
	if (count($rows) > 0) {
		foreach ($rows as $id => $row) {
			if (strpos($row[0],'_mybb_')===false && strstr($row[0],'_ost_')===false) {
				$query="drop table ".$row[0];
				$wpdb->query($query);
			}
		}
	}
	$ids=get_option("zing_mailz_pages");
	$ida=explode(",",$ids);
	foreach ($ida as $id) {
		wp_delete_post($id);
	}
	delete_option("zing_mailz_version",ZING_VERSION);
	delete_option("zing_mailz_pages",ZING_VERSION);
}

/**
 * Main function handling content, footer and sidebars
 * @param $process
 * @param $content
 * @return unknown_type
 */
function zing_mailz_main($process,$content="") {
	global $zing_mailz_content;

	if ($zing_mailz_content) $content=$zing_mailz_content;
	return $content;
}

function zing_mailz_output($process) {

	global $post;
	global $wpdb;
	global $cfg;
	global $thisuser;
	global $nav;
	global $zing_mailz_loaded,$zing_mailz_mode;

	$content="";

	switch ($process)
	{
		case "content":
			if (isset($_POST['zname'])) {
				$_POST['name']=$_POST['zname'];
				unset($_POST['zname']);
			}
			$cf=get_post_custom($post->ID);
			if (isset($_GET['zlist']))
			{
				if ($_GET['page']=='mailz_cp.php') $to_include='admin/index';
				elseif (isset($_GET['page'])) $to_include='admin/index';
				else $to_include=$_GET['zlist'];
				$zing_mailz_mode="client";
			}
			elseif (isset($_GET['zscp']))
			{
				//$to_include="scp/".$_GET['zscp'];
				$to_include="index";

				$zing_mailz_mode="admin";
			}
			elseif (isset($_GET['zsetup']))
			{
				$to_include="setup/".$_GET['zscp'];
				$zing_mailz_mode="setup";
			}
			elseif (isset($cf['zing_mailz_page']) && ($cf['zing_mailz_page'][0]=='mailz'))
			{
				//$zing_mailz_mode="client";
				$to_include="index";
			}
			elseif (isset($cf['zing_mailz_page']) && ($cf['zing_mailz_page'][0]=='admin'))
			{
				//$to_include="scp/".$_GET['zscp'];
				$to_include="index.php";
				$zing_mailz_mode="admin";
			}
			else
			{
				return $content;
			}
			if (isset($cf['cat'])) {
				$_GET['cat']=$cf['cat'][0];
			}
			break;
			/*
			 case "footer":
			 $to_include="footer.php";
			 break;
			 case "sidebar":
			 $to_include="menu_".$content.".php";
			 break;
			 */
		default:
			return $content;
			break;
	}
	//error_reporting(E_ALL & ~E_NOTICE);
	//ini_set('display_errors', '1');

	/*
	 if (get_option("zing_mailz_subscribers") == "Subscribers" && !is_user_logged_in()) {
		$content="Access not allowed, please register or login first";
		return $content;
		}
		*/

	if (zing_mailz_login()) {
		$http=zing_mailz_http("phplist",$to_include.'.php');
		//echo $http;
		//print_r($_POST);
		$news = new HTTPRequest($http);
		if ($news->live()) {
			$output=$news->DownloadToString(true);
			if ($news->redirect) {
				$redirect=str_replace(ZING_OST_URL.'/admin/?page=',get_option('siteurl').'/wp-admin/'.'options-general.php?page=mailz_cp.php&zlist=index&zlistpage=',$output);
				//echo $redirectPage;
				//$redirect=get_option('siteurl').'/wp-admin/'.'options-general.php?page=mailz_cp.php&zlist=index&zlistpage='.$_GET['zlistpage'];
				//echo $output;
				header($redirect);
				//echo 'should redirect to '.$output;
				//print_r($_GET);
				//print_r($_POST);
				die();
			}
			$content.=zing_mailz_ob($output);
			//$content.=$output;
			return $content;
		}
	}
}

function zing_mailz_mainpage() {
	$ids=get_option("zing_mailz_pages");
	$ida=explode(",",$ids);
	return $ida[0];
}

function zing_mailz_ob($buffer) {
	global $current_user,$zing_mailz_mode,$wpdb;

	$prefix=$wpdb->prefix.ZING_MAILZ_PREFIX;
	$query="select uniqid from ".$prefix."phplist_user where email='".$current_user->data->user_email."'";
	$uid=$wpdb->get_var($query);
	$home=get_option('home');
	$admin=get_option('siteurl').'/wp-admin/';
	$pid=zing_mailz_mainpage();

	$buffer=str_replace('page=','zlistpage=',$buffer);
	if (is_admin()) {
		$buffer=str_replace('<span class="menulinkleft"><a href="./?zlistpage=logout">logout</a><br /></span>','',$buffer);
		$buffer=str_replace('<a href="./?zlistpage=logout">logout</a>','',$buffer);
		$buffer=str_replace('./?','options-general.php?'.'page=mailz_cp.php&zlist=index&',$buffer);
		$buffer=str_replace('<form method=post >','<form method=post action="'.$admin.'options-general.php?page=mailz_cp.php&zlist=index&zlistpage='.$_GET['zlistpage'].'">',$buffer);
		$buffer=str_replace('<form method="post" action="">','<form method=post action="'.$admin.'options-general.php?page=mailz_cp.php&zlist=index&zlistpage='.$_GET['zlistpage'].'">',$buffer);
		$buffer=str_replace(ZING_OST_URL.'/?',$admin.'options-general.php?page=mailz_cp.php&zlist=index&',$buffer);
		$buffer=str_replace('./FCKeditor',ZING_OST_URL.'/admin/FCKeditor',$buffer);
		$buffer=str_replace('src="images/','src="'.ZING_OST_URL.'/admin/images/',$buffer);
		$buffer=str_replace('src="js/jslib.js"','src="'.ZING_OST_URL.'/js/jslib.js"',$buffer);
	} else {
		$buffer=str_replace('/lists/admin',$admin.'options-general.php?page=mailz_cp.php&zlist=index&',$buffer); //go to admin page
		$buffer=str_replace('./?','index?page_id='.$pid.'&zlist=index&',$buffer);
		$buffer=str_replace(ZING_OST_URL.'/?',$home.'/?page_id='.$pid.'&zlist=index&',$buffer);
		if ($_GET['p']=='subscribe' && isset($current_user->data->user_email)) {
			$buffer=str_replace('name=email value=""','name=email value="'.$current_user->data->user_email.'"',$buffer);
			$buffer=str_replace('name=emailconfirm value=""','name=emailconfirm value="'.$current_user->data->user_email.'"',$buffer);
		}
		if ($_GET['p']=='unsubscribe' && isset($current_user->data->user_email)) {
			$buffer=str_replace('name="unsubscribeemail" value=""','name="unsubscribeemail" value="'.$current_user->data->user_email.'"',$buffer);
			$buffer=str_replace('uid="','uid='.$uid.'"',$buffer);
		}
		if ($_GET['p']=='preferences' && isset($current_user->data->user_email)) {
			$buffer=str_replace('name=email value=""','name=email value="'.$current_user->data->user_email.'"',$buffer);
			$buffer=str_replace('name=emailconfirm value=""','name=emailconfirm value="'.$current_user->data->user_email.'"',$buffer);
		}
	}

	return '<!--buffer:start-->'.$buffer.'<!--buffer:end-->';
}

function zing_mailz_http($module,$to_include="index",$get=array()) {
	$vars="";
	if (!$to_include || $to_include==".php") $to_include="index";
	$http=ZING_OST_URL.'/';
	$http.= $to_include;
	$and="";
	if (count($_GET) > 0) {
		foreach ($_GET as $n => $v) {
			if ($n!="zpage" && $n!="page_id" && $n!="zscp" && $n!="zlistpage" && $n!="page") {
				$vars.= $and.$n.'='.zing_urlencode($v);
				$and="&";
			} elseif ($n=="zlistpage") {
				$vars.= $and.'page'.'='.zing_urlencode($v);
				$and="&";
			}
		}
	}
	if (count($get) > 0) {
		foreach ($get as $n => $v) {
			$vars.= $and.$n.'='.zing_urlencode($v);
			$and="&";
		}
	}

	$vars.=$and.'wpabspath='.urlencode(ABSPATH);
	$vars.='&wppageid='.zing_mailz_mainpage();
	$vars.='&wpsiteurl='.get_option('siteurl');
	if ($vars) $http.='?'.$vars;
	return $http;
}

/**
 * Page content filter
 * @param $content
 * @return unknown_type
 */
function zing_mailz_content($content) {
	return zing_mailz_main("content",$content);
}


/**
 * Header hook: loads FWS addons and css files
 * @return unknown_type
 */
function zing_mailz_header()
{
	global $zing_mailz_content;
	global $zing_mailz_menu;
	global $zing_mailz_post;

	if (isset($_POST) && isset($zing_mailz_post)) {
		$_POST=array_merge($_POST,$zing_mailz_post);
	}

	$output=zing_mailz_output("content");
	/*
	 echo '<script type="text/javascript" language="javascript">';
	 echo "var ajaxurl='".ZING_MAILZ_URL."osticket/upload/scp/';";
	 echo '</script>';
	 */

	$menu1=zing_integrator_cut($output,'<div class="menutableright">','</div>');
	if ($menu1) {
		//$menu1=strchr($menu1,'<span');
		$menu1=str_replace('<span','<li><span',$menu1);
		$menu1=str_replace('</span>','</span></li>',$menu1);
		$menu1='<ul>'.$menu1.'</ul>';
		$menu1=str_replace('menulinkleft','xmenulinkleft',$menu1);
		//$menu1=str_replace('<br />','',$menu1);
		$menu1=str_replace('<hr>','',$menu1);
		//$menu1=str_replace('|','',$menu1).'<br />';
	}
	$zing_mailz_menu=$menu1;

	$body=zing_integrator_cut($output,'<body','</body>',true);
	$body=strchr($body,'>');
	$zing_mailz_content='<div id="phplist">'.substr($body,1).'</div>';
	/*
	 zing_integrator_cut($output,'<div id="footer">','</div>');
	 $head=zing_integrator_cut($output,'<div id="osthead">','</div>',true);
	 */
	/*
	 $menu=zing_integrator_cut($output,'<div id="nav">','</div>');
	 zing_integrator_cut($output,'<div id="header"','</div>');

	 $menu=str_replace('<ul id="main_nav" >','<ul>',$menu);
	 $menu=str_replace('<ul id="sub_nav">','<br /><ul>',$menu);
	 $zing_mailz_menu=$menu1.$menu;
	 $zing_mailz_content=$output;

	 //stylesheets and javascripts
	 echo $head;
	 */
	echo '<link rel="stylesheet" type="text/css" href="' . ZING_MAILZ_URL . 'lists/admin/styles/phplist.css" media="screen" />';
	//echo '<script language="javascript" type="text/javascript" src="' . ZING_MAILZ_URL . 'lists/admin/js/select_style.js">';
	//echo '<script language="javascript" type="text/javascript" src="' . ZING_MAILZ_URL . 'lists/admin/js/jslib.js">';
	echo '<link rel="stylesheet" type="text/css" href="' . ZING_MAILZ_URL . 'zing.css" media="screen" />';
}

/**
 * Sidebar menu widget
 * @param $args
 * @return unknown_type
 */
/*
 function widget_zingiri_mailz_menu($args) {
 global $nav, $thisuser;
 global $zing_mailz_menu;

 extract($args);
 echo $before_widget;
 echo $before_title;
 echo "Tickets";
 echo $after_title;
 if ($zing_mailz_menu!='') {
 echo $zing_mailz_menu;
 echo '<ul>';
 echo '<li><a href="?page_id='.zing_mailz_mainpage().'&zlist=index">User panel</a></li>';
 echo '</ul>';
 } else {
 echo '<ul>';
 if (current_user_can('edit_plugins')) echo '<li><a href="?page_id='.zing_mailz_mainpage().'&zlist=admin/index">Admin Panel</a></li>';
 echo '</ul>';
 }
 echo $after_widget;
 }
 */
/**
 * Register sidebar widgets
 * @return unknown_type
 */
function zing_mailz_sidebar_init()
{
	if (current_user_can('edit_plugins') || current_user_can('edit_pages')) {
		//	register_sidebar_widget(__('Zingiri Mailing List Menu'), 'widget_zingiri_mailz_menu');
	}
}

/**
 * Initialization of page, action & page_id arrays
 * @return unknown_type
 */
function zing_mailz_init()
{
	/*
	 global $zing_mailz_post;
	 if (isset($_POST['name']) && (isset($_POST['submit_x']) || isset($_POST['submit']))) {
		$zing_mailz_post['name']=$_POST['name'];
		unset($_POST['name']);
		}
		*/
	ob_start();
	session_start();
}

function zing_mailz_login() {
	global $current_user,$wpdb;

	$loggedin=false;

	if (!current_user_can('edit_plugins') && isset($_SESSION['zing']['mailz']['loggedin'])) {
		zing_mailz_logout();
	}
	//unset($_SESSION['zing']['mailz']['loggedin']);
	if (!is_admin()) {
		$loggedin=true;
	}
	elseif (is_admin() && current_user_can('edit_plugins') && !isset($_SESSION['zing']['mailz']['loggedin'])) {
		$post['do']='scplogin';
		$post['login']='admin';//$current_user->data->user_login;
		$post['password']=get_option('zing_mailz_password');
		$post['submit']='Enter';
		//print_r($post);
		$http=zing_mailz_http('osticket','admin/index.php');
		//echo '<br />'.$http;
		$news = new HTTPRequest($http);
		$news->post=$post;
		if ($news->live()) {
			$output=$news->DownloadToString(true);
			//echo '<br />'.$output;
			if (strpos($output,"invalid password")===false && strpos($output,"Default login is admin")===false) {
				$loggedin=true;
				$_SESSION['zing']['mailz']['loggedin']=1;
			}
			else echo '<br /><strong style="color:red">Couldn\'t log in to PHPlist</strong><br />';
		}
	}
	elseif (isset($_SESSION['zing']['mailz']['loggedin'])) $loggedin=true;
	return $loggedin;
}

function zing_mailz_logout() {
	if (isset($_SESSION['zing']['mailz']['loggedin'])) {

		$_GET['zlistpage']='logout';
		$http=zing_mailz_http('osticket','admin/index.php');
		$news = new HTTPRequest($http);
		if ($news->live()) {
			$output=$news->DownloadToString(true);
			unset($_SESSION['zing']['mailz']['loggedin']);
		}

	}
}
/**
 * Display common Zingiri footer
 * @param $page_id
 * @return unknown_type
 */
function zing_mailz_footer() {
	zing_footers();
}

/*
function zing_mailz_more_reccurences() {
	return array(
'minute' => array('interval' => 60, 'display' => 'Every minute'),
'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
'fortnightly' => array('interval' => 1209600, 'display' => 'Once Fortnightly'),
	);
}
add_filter('cron_schedules', 'zing_mailz_more_reccurences');
*/

function zing_mailz_cron() {

	$msg=time();
	
	$post['login']='admin';
	$post['password']=get_option('zing_mailz_password');
	
	$http=zing_mailz_http("phplist",'admin/index.php',array('page'=>'processqueue','user'=>'admin','password'=>get_option('zing_mailz_password')));

	$news = new HTTPRequest($http);
	$news->post=$post;
	
	if ($news->live()) {
		$output=$news->DownloadToString(true);
		$msg.='ok';
	} else {
		$msg.='failed';
	}
	update_option('zing_mailz_cron',$msg);
}
if (!wp_next_scheduled('zing_mailz_cron_hook')) {
	wp_schedule_event( time(), 'hourly', 'zing_mailz_cron_hook' );
}
add_action('zing_mailz_cron_hook','zing_mailz_cron');
//echo wp_next_scheduled('zing_mailz_cron_hook')-time();
//echo '<br />'.wp_get_schedule('zing_mailz_cron_hook');
//print_r(wp_get_schedules());
//echo '<br />last run='.get_option('zing_mailz_cron');
?>