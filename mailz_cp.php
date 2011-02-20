<?php
$zing_mailz_name = "Mailing List";
$zing_mailz_shortname = "zing_mailz";
$zing_mailz_options=array();

function zing_mailz_upgrade() {
	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;

	zing_mailz_activate();
	foreach ($zing_mailz_options as $value) {
		if( isset( $_REQUEST[ $value['id'] ] ) ) {
			update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
		} else delete_option( $value['id'] );
	}
	header("Location: admin.php?page=mailz_cp");
	die();
}

function zing_mailz_install() {
	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;

	if ($_GET['action']=='install') {
		zing_mailz_activate();
		foreach ($zing_mailz_options as $value) {
			if( isset( $_REQUEST[ $value['id'] ] ) ) {
				update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
			} else { delete_option( $value['id'] );
			}
		}
		//die('here now');
		header("Location: admin.php?page=mailz_cp&installed=true");
		die();
	} else {
		$message='<p>Ready to install this plugin? Simply click on the button below and wait a few seconds.</p><br />';
		$message.='<a href="admin.php?page=mailz_cp&action=install" class="button">Install</a><br />';
		zing_mailz_cp($message);
	}
}

function zing_mailz_remove() {
	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;

	if ($_GET['action']!='remove') {
		$message='<p>Are you sure you want to uninstall this plugin? If so click, please confirm by clicking the button below.</p><br />';
		$message.='<a href="admin.php?page=mailz-uninstall&action=remove" class="button">Uninstall</a><br />';
		zing_mailz_cp($message);
	} else {
		zing_mailz_uninstall();
		foreach ($zing_mailz_options as $value) {
			delete_option( $value['id'] );
			update_option( $value['id'], $value['std'] );
		}
		header("Location: admin.php?page=mailz_cp&uninstalled=true");
		exit();
		//zing_mailz_cp();
	}
}

function zing_mailz_admin_menu() {
	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;
	global $zing_mailz_content;
	global $zing_mailz_menu;

	$zing_mailz_version=get_option("zing_mailz_version");

	if ($_GET['action']=='remove' && $_GET['page']=='mailz_cp') zing_mailz_remove();
	if ($_GET['action']=='install' && $_GET['page']=='mailz_cp') zing_mailz_install();
	
	if (empty($_GET['zlist'])) $_GET['zlist']='admin/index';
	if (!empty($_REQUEST['page']) && $_REQUEST['page'] != 'mailz_cp') {
		$_GET['zlistpage']=str_replace('mailz-','',$_REQUEST['page']);
		$_GET['zlist']='index';
	}

	if (get_option("zing_mailz_version")) {
		add_menu_page($zing_mailz_name, $zing_mailz_name, 'administrator', 'mailz_cp','zing_mailz_admin');
		//add_submenu_page('mailz_cp', $zing_mailz_name.'- Administration', 'Administration', 'administrator', 'mailz_cp', 'zing_mailz_admin');
		zing_mailz_header();
		$html=str_get_html($zing_mailz_menu);
		$first=true;
		foreach($html->find('a') as $e) {
			$link=str_replace("admin.php?page=mailz_cp&zlist=index&zlistpage=","",$e->href);
			$label=ucfirst($e->innertext);
			if ($first) add_submenu_page('mailz_cp', $zing_mailz_name.'- '.$label, $label, 'administrator', 'mailz_cp', 'zing_mailz_admin');
			elseif (substr($link,0,3)!='div') add_submenu_page('mailz_cp', $zing_mailz_name.'- '.$label, $label, 'administrator', 'mailz-'.$link, 'zing_mailz_admin');
			$first=false;
		}
		add_submenu_page('mailz_cp', $zing_mailz_name.'- Import', 'Import', 'administrator', 'mailz-import', 'zing_mailz_import');
		if ($zing_mailz_version != ZING_MAILZ_VERSION) add_submenu_page('mailz_cp', $zing_mailz_name.'- Upgrade', 'Upgrade', 'administrator', 'mailz-upgrade', 'zing_mailz_upgrade');
		if ($zing_mailz_version) add_submenu_page('mailz_cp', $zing_mailz_name.'- Uninstall', 'Uninstall', 'administrator', 'mailz-uninstall', 'zing_mailz_remove');
	} else {
		add_menu_page($zing_mailz_name, $zing_mailz_name, 'administrator', 'mailz_cp','zing_mailz_install');
		add_submenu_page('mailz_cp', $zing_mailz_name.'- Install', 'Install', 'administrator', 'mailz_cp', 'zing_mailz_install');
	}
}

function zing_mailz_admin() {

	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options, $wpdb;

	if ( $_REQUEST['installed'] ) echo '<div id="message" class="updated fade"><p><strong>'.$zing_mailz_name.' installed.</strong></p></div>';
	if ( $_REQUEST['uninstalled'] ) echo '<div id="message" class="updated fade"><p><strong>'.$zing_mailz_name.' uninstalled.</strong></p></div>';

	$zing_mailz_version=get_option("zing_mailz_version");
	?>
<?php
//if ($zing_mailz_version) {
	zing_mailz_cp();
//}
?>
<?php
}

function zing_mailz_cp($message='') {
	global $zing_mailz_content,$zing_mailz_name,$zing_mailz_menu;

	//	if (empty($_GET['zlist'])) $_GET['zlist']='admin/index';
	$zing_mailz_version=get_option("zing_mailz_version");
	
	zing_mailz_head();
	
	echo '<div class="wrap">';
	echo '<div id="zing-mailz-cp-content">';
	if ($message) {
		echo '<h2><b>'.$zing_mailz_name.' - '.$_GET['zlistpage'].'</b></h2>';
		echo $message;
	} elseif ($zing_mailz_version) {
		if ($_GET['zlistpage']=='admin') {
			echo 'Please use the <a href="users.php">Wordpress Users menu</a> to change <strong>admin</strong> user details';
		} else {
			echo '<div id="phplist">'.$zing_mailz_content.'</div>';
		}
	}
	echo '</div>';
	require(dirname(__FILE__).'/support-us.inc.php');
	echo '</div>';
?><div style="clear: both"></div>
<hr />
<p>For more info and support, contact us at <a href="http://www.choppedcode.com/">ChoppedCode</a> or
check out our <a href="http://choppedcode.com/forums/">support forums</a>.</p>
<hr />
<?php
}

add_action('admin_menu', 'zing_mailz_admin_menu'); ?>