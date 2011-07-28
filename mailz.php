<?php
/*
 Plugin Name: Mailing List
 Plugin URI: http://www.zingiri.net
 Description: This plugin provides easy to use mailing list functionality to your Wordpress site
 Author: EBO
 Version: 1.2.1
 Author URI: http://www.zingiri.net/
 */
define("ZING_MAILZ_VERSION","1.2.1");
define("ZING_PHPLIST_VERSION","2.10.11");
define("ZING_MAILZ_PREFIX","zing_");

//error_reporting(E_ALL & ~E_NOTICE);
//ini_set('display_errors', '1');

$dbtablesprefix=$wpdb->prefix.ZING_MAILZ_PREFIX;

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

if (!defined("BLOGUPLOADDIR")) {
	$upload=wp_upload_dir();
	define("BLOGUPLOADDIR",$upload['path']);
}

define("ZING_PHPLIST_URL",ZING_MAILZ_URL.'lists');

$zing_footers[]=array('http://www.phplist.com/','PHPlist');
$zing_mailz_version=get_option("zing_mailz_version");
if ($zing_mailz_version) {
	add_action("init","zing_mailz_init");
	add_filter('wp_footer','zing_mailz_footer');
	add_filter('the_content', 'zing_mailz_content', 10, 3);
	add_action('wp_head','zing_mailz_head');
	add_action('wp_head','zing_mailz_header');
}
add_action('admin_notices','zing_mailz_notices');

register_activation_hook(__FILE__,'zing_mailz_activate');
register_deactivation_hook(__FILE__,'zing_mailz_deactivate');
require_once(dirname(__FILE__) . '/includes/index.php');
require_once(dirname(__FILE__) . '/classes/index.php');
require_once(dirname(__FILE__) . '/mailz_cp.php');
require_once(dirname(__FILE__) . '/mailz_import.php');
require_once(dirname(__FILE__) . '/addons/simplehtmldom/simple_html_dom.php');

function zing_mailz_notices() {
	$zing_mailz_version=get_option("zing_mailz_version");
	$warnings=array();

	if (phpversion() < '5')	$errors[]="You are running PHP version ".phpversion().". You require PHP version 5 or higher to install the Web Shop.";
	if (!function_exists('curl_init')) $warnings[]="You need to have cURL installed. Contact your hosting provider to do so.";
	
	$upload=wp_upload_dir();
	if ($upload['error']) $warnings[]=$upload['error'];
	
	if (empty($zing_mailz_version)) $warnings[]='Please proceed with a clean install or deactivate your plugin';
	elseif ($zing_mailz_version != ZING_MAILZ_VERSION) $warnings[]='You downloaded version '.ZING_MAILZ_VERSION.' and need to <a href="admin.php?page=mailz-upgrade">upgrade</a> your database (currently at version '.$zing_mailz_version.').';

	if (count($warnings)>0) {
		echo "<div id='zing-warning' style='clear:both;background-color:greenyellow' class='updated fade'>";
		foreach ($warnings as $message) {
			echo "<p><strong>Mailing list: ".$message."</strong></p>";
		}
		echo "</div>";
	}
}
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
		$news = new zHttpRequest($http,'mailz');
		if ($news->live()) {
			$output=$news->DownloadToString();
		}
	}

	//set admin password
	$password=md5(time().get_option('home'));
	$query="update ".$prefix."phplist_admin set password='".$password."' where loginname='admin'";
	$wpdb->query($query);
	update_option("zing_mailz_password",$password);

	//set configuration options
	//$query="update ".$prefix."phplist_config set value='".str_replace('http://','',substr(ZING_MAILZ_URL,0,-1))."' where item='website'";
	$query="update ".$prefix."phplist_config set value='".str_replace('http://','',get_option('siteurl'))."' where item='website'";
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
 * Main function handling content
 * @param $process
 * @param $content
 * @return unknown_type
 */
function zing_mailz_main($process,$content="") {
	global $zing_mailz_content;

	if ($zing_mailz_content) {
		$content='<div id="phplist">'.$zing_mailz_content.'</div>';
	}
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
				if ($_GET['page']=='mailz_cp') $to_include='admin/index';
				elseif (isset($_GET['page'])) $to_include='admin/index';
				else $to_include=$_GET['zlist'];
				$zing_mailz_mode="client";
			}
			elseif (isset($_GET['zscp']))
			{
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
				$to_include="index";
			}
			elseif (isset($cf['zing_mailz_page']) && ($cf['zing_mailz_page'][0]=='admin'))
			{
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
		default:
			return $content;
			break;
	}
	if (zing_mailz_login()) {
		$http=zing_mailz_http("phplist",$to_include.'.php');
		$news = new zHttpRequest($http,'mailz');
		if ($news->live()) {
			$output=stripslashes($news->DownloadToString());
			$content.=zing_mailz_ob($output);
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
		$buffer=str_replace('./?','admin.php?'.'page=mailz_cp&zlist=index&',$buffer);
		$buffer=str_replace('<form method=post >','<form method=post action="'.$admin.'admin.php?page=mailz_cp&zlist=index&zlistpage='.$_GET['zlistpage'].'">',$buffer);
		$buffer=str_replace('name="page"','name="zlistpage"',$buffer);
		$buffer=str_replace('<form method=get>','<form method=get><input type="hidden" name="page" value="mailz_cp" /><input type="hidden" name="zlist" value="index" /><input type="hidden" name="zlistpage" value="'.$_GET['zlistpage'].'" />',$buffer);
		$buffer=str_replace('<form method="post" action="">','<form method=post action="'.$admin.'admin.php?page=mailz_cp&zlist=index&zlistpage='.$_GET['zlistpage'].'">',$buffer);
		$buffer=str_replace(ZING_PHPLIST_URL.'/?',$admin.'admin.php?page=mailz_cp&zlist=index&',$buffer);
		$buffer=str_replace('./FCKeditor',ZING_PHPLIST_URL.'/admin/FCKeditor',$buffer);
		$buffer=str_replace('src="images/','src="'.ZING_PHPLIST_URL.'/admin/images/',$buffer);
		$buffer=str_replace('src="js/jslib.js"','src="'.ZING_PHPLIST_URL.'/js/jslib.js"',$buffer);
	} else {
		$buffer=str_replace('/lists/admin',$admin.'admin.php?page=mailz_cp&zlist=index&',$buffer); //go to admin page
		$buffer=str_replace('./?',$home.'/?page_id='.$pid.'&zlist=index&',$buffer);
		$buffer=str_replace(ZING_PHPLIST_URL.'/?',$home.'/?page_id='.$pid.'&zlist=index&',$buffer);
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
	$http=ZING_PHPLIST_URL.'/';
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
	$menu1=zing_integrator_cut($output,'<div class="menutableright">','</div>');
	if ($menu1) {
		$menu1=str_replace('<span','<li><span',$menu1);
		$menu1=str_replace('</span>','</span></li>',$menu1);
		$menu1='<ul>'.$menu1.'</ul>';
		$menu1=str_replace('menulinkleft','xmenulinkleft',$menu1);
		$menu1=str_replace('<hr>','',$menu1);
	}
	$zing_mailz_menu=$menu1;

	$body=zing_integrator_cut($output,'<body','</body>',true);
	$body=strchr($body,'>');
	$zing_mailz_content=trim(substr($body,1));
}

function zing_mailz_head() {
	if (is_admin()) {
		echo '<link rel="stylesheet" type="text/css" href="' . ZING_MAILZ_URL . 'lists/admin/styles/phplist.css" media="screen" />';
	} else {
		echo '<link rel="stylesheet" type="text/css" href="' . ZING_MAILZ_URL . 'lists/styles/phplist.css" media="screen" />';
	}
	echo '<link rel="stylesheet" type="text/css" href="' . ZING_MAILZ_URL . 'zing.css" media="screen" />';
}

/**
 * Initialization of page, action & page_id arrays
 * @return unknown_type
 */
function zing_mailz_init()
{
	ob_start();
	session_start();
}

function zing_mailz_login() {
	global $current_user,$wpdb;

	$loggedin=false;

	if (!isset($_SESSION['zing']['mailz']['loggedin'])) $_SESSION['zing']['mailz']['loggedin']=0;
	
	if (!current_user_can('edit_plugins') && $_SESSION['zing']['mailz']['loggedin'] > 0) {
		zing_mailz_logout();
	}
	if (!is_admin()) {
		$loggedin=true;
	} elseif (is_admin() && current_user_can('edit_plugins') && time()-$_SESSION['zing']['mailz']['loggedin'] > 60) { //We relogin every minute to avoid time outs
		$post['do']='scplogin';
		$post['login']='admin';//$current_user->data->user_login;
		$post['password']=get_option('zing_mailz_password');
		$post['submit']='Enter';
		$http=zing_mailz_http('osticket','admin/index.php');
		$news = new zHttpRequest($http,'mailz');
		$news->post=$post;
		if ($news->live()) {
			$output=stripslashes($news->DownloadToString());
			if (strpos($output,"invalid password")===false && strpos($output,"Default login is admin")===false) {
				$loggedin=true;
				$_SESSION['zing']['mailz']['loggedin']=time();
			} else echo '<br /><strong style="color:red">Couldn\'t log in to PHPlist</strong><br />';
		}
	}
	elseif (isset($_SESSION['zing']['mailz']['loggedin'])) $loggedin=true;
	return $loggedin;
}

function zing_mailz_logout() {
	$_GET['zlistpage']='logout';
	$http=zing_mailz_http('osticket','admin/index.php');
	$news = new zHttpRequest($http);
	if ($news->live()) {
		$output=$news->DownloadToString(true);
		unset($_SESSION['zing']['mailz']['loggedin']);
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

	$news = new zHttpRequest($http,'mailz');
	$news->post=$post;

	if ($news->live()) {
		$output=$news->DownloadToString();
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
?>