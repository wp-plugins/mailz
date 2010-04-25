<?php
$zing_mailz_name = "Zingiri Mailing List";
$zing_mailz_shortname = "zing_mailz";

function zing_mailz_add_admin() {

	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;

	if ( $_GET['page'] == basename(__FILE__) ) {

		if ( 'update' == $_REQUEST['action'] ) {
			zing_mailz_activate();
			foreach ($zing_mailz_options as $value) {
				if( isset( $_REQUEST[ $value['id'] ] ) ) {
					update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
				} else { delete_option( $value['id'] );
				}
			}
			header("Location: options-general.php?page=mailz_cp.php");
			die;
		}

		if ( 'install' == $_REQUEST['action'] ) {
			zing_mailz_activate();
			foreach ($zing_mailz_options as $value) {
				if( isset( $_REQUEST[ $value['id'] ] ) ) {
					update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
				} else { delete_option( $value['id'] );
				}
			}
			header("Location: options-general.php?page=mailz_cp.php&installed=true");
			die;
		}

		if( 'uninstall' == $_REQUEST['action'] ) {
			zing_mailz_uninstall();
			foreach ($zing_mailz_options as $value) {
				delete_option( $value['id'] );
				update_option( $value['id'], $value['std'] );
			}
			header("Location: options-general.php?page=mailz_cp.php&uninstalled=true");
			die;
		}
	}

	add_options_page($zing_mailz_name." Options", "$zing_mailz_name", 8, basename(__FILE__), 'zing_mailz_admin');
}

function zing_mailz_admin() {

	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options, $wpdb;

	if ( $_REQUEST['installed'] ) echo '<div id="message" class="updated fade"><p><strong>'.$zing_mailz_name.' installed.</strong></p></div>';
	if ( $_REQUEST['uninstalled'] ) echo '<div id="message" class="updated fade"><p><strong>'.$zing_mailz_name.' uninstalled.</strong></p></div>';

	$zing_mailz_version=get_option("zing_mailz_version");
	?>
<div class="wrap">
<h2 class="zing-left"><b><?php echo $zing_mailz_name; ?></b></h2>


<div style="clear:both"></div>

<hr />
<!-- 
<p>The following Wordpress users are active osTicket users</p>
 -->
<?php if ($zing_mailz_version) {
	//$wpdb->show_errors();
	/*
	$prefix=$wpdb->prefix."zing_ost_";
	
	$query="select * from `##users`,`##usermeta` where `##users`.`ID`=`##usermeta`.`user_id` and `##usermeta`.`meta_key`='wp_user_level'";
	$query=str_replace("##",$wpdb->base_prefix,$query);
	$sql = mysql_query($query) or die(mysql_error());
	while ($row = mysql_fetch_array($sql)) {
		if ($row['meta_value'] >= 8) { //administrator role
			if (!isset($row['first_name'])) $row['first_name']=$row['display_name'];
			$query2="INSERT INTO `".$prefix."staff` (`staff_id`, `group_id`, `dept_id`, `username`, `firstname`, `lastname`, `passwd`, `email`, `phone`, `phone_ext`, `mobile`, `signature`, `isactive`, `isadmin`, `isvisible`, `onvacation`, `daylight_saving`, `append_signature`, `change_passwd`, `timezone_offset`, `max_page_size`, `created`, `lastlogin`, `updated`) VALUES";
			$query2.="('".$row['ID']."', 1, 1, '".$row['user_login']."', '".$row['first_name']."', '".$row['last_name']."', '".md5($row['user_pass'])."', '".$row['user_email']."', '', '', '', '', 1, 1, 1, 0, 0, 0, 0, 0.0, 0, '".date("Y-m-d")."', NULL, '".date("Y-m-d")."')";
			$wpdb->query($query2);
			$query2=sprintf("UPDATE `".$prefix."staff` SET `passwd`='%s', `isadmin`=1, `change_passwd`=0 WHERE `username`='%s'",md5($row['user_pass']),$row['user_login']);
			$wpdb->query($query2);
		} elseif ($row['meta_value'] >= 5) { //editor role
			if (!isset($row['first_name'])) $row['first_name']=$row['display_name'];
			$query2="INSERT INTO `".$prefix."staff` (`staff_id`, `group_id`, `dept_id`, `username`, `firstname`, `lastname`, `passwd`, `email`, `phone`, `phone_ext`, `mobile`, `signature`, `isactive`, `isadmin`, `isvisible`, `onvacation`, `daylight_saving`, `append_signature`, `change_passwd`, `timezone_offset`, `max_page_size`, `created`, `lastlogin`, `updated`) VALUES";
			$query2.="('".$row['ID']."', 1, 1, '".$row['user_login']."', '".$row['first_name']."', '".$row['last_name']."', '".md5($row['user_pass'])."', '".$row['user_email']."', '', '', '', '', 1, 0, 1, 0, 0, 0, 0, 0.0, 0, '".date("Y-m-d")."', NULL, '".date("Y-m-d")."')";
			$wpdb->query($query2);
			$query2=sprintf("UPDATE `".$prefix."staff` SET `passwd`='%s', `isadmin`=0, `change_passwd`=0 WHERE `username`='%s'",md5($row['user_pass']),$row['user_login']);
			$wpdb->query($query2);
		} else {
			$query2=sprintf("DELETE FROM `".$prefix."staff` WHERE `username`='%s'",$row['user_login']);
			$wpdb->query($query2);
		}
		$level[$row['user_login']]=$row['meta_value'];
	}
	$query="select * from `##users`,`##zing_ost_staff` where `##users`.`user_login`=`##zing_ost_staff`.`username`";
	$query=str_replace("##",$wpdb->base_prefix,$query);
	$sql = mysql_query($query) or die(mysql_error());
	while ($row = mysql_fetch_array($sql)) {
		echo $row['user_login'].' - '.$row['firstname'].' - '.$row['user_email'];
		if ($level[$row['user_login']] >= 8) echo ' - admin';
		elseif ($level[$row['user_login']] >= 5) echo ' - staff';
		
		if (md5($row['user_pass']) != $row['passwd']) echo '!Password not synchronised';
		echo '<br />';
	}
	*/
		zing_mailz_cp();
}
	?>
	<br />
<div id="zing-mailz-cp-menu">
<form method="post">
<p>
	<?php
	if (empty($zing_mailz_version))
	echo 'Please proceed with a clean install or deactivate your plugin';
	elseif ($zing_mailz_version != ZING_MAILZ_VERSION)
	echo 'You downloaded version '.ZING_MAILZ_VERSION.' and need to upgrade your database (currently at version '.$zing_mailz_version.').';
	elseif ($zing_mailz_version == ZING_MAILZ_VERSION)
	echo 'Your version is up to date!';

	?>
	</p>

<?php if (!$zing_mailz_version) { ?>
<p class="submit"><input name="install" type="submit" value="Install" /> <input type="hidden"
	name="action" value="install"
/></p>

<?php } elseif ($zing_mailz_version != ZING_MAILZ_VERSION) { ?>
<p class="submit"><input name="install" type="submit" value="Upgrade" /> <input type="hidden"
	name="action" value="install"
/></p>

<?php } else { ?>

<p class="submit"><input name="install" type="submit" value="Update" /> <input type="hidden"
	name="action" value="update"
/></p>

<?php } ?>
</form>
<form method="post">
<p class="submit"><input name="uninstall" type="submit" value="Uninstall" /> <input type="hidden"
	name="action" value="uninstall"
/></p>
</form>
</div>
<div style="clear:both"></div>
<hr />
<p>For more info and support, contact us at <a href="http://www.zingiri.com/">Zingiri</a> or
check out our <a href="http://forums.zingiri.com/">support forums</a>.</p>
<hr />
	<?php
}

function zing_mailz_cp() {
	global $zing_mailz_content;
	global $zing_mailz_menu;
//	global $zing_mailz_post;
	
	if (empty($_GET['zlist'])) $_GET['zlist']='admin/index';
	
	zing_mailz_header();
//echo '<div width="100%">';
	echo '<div id="zing-mailz-cp-content">';
	if ($_GET['zlistpage']=='admin') {
		echo 'Please use the <a href="users.php">Wordpress Users menu</a> to change <strong>admin</strong> user details';
	} else {
		echo $zing_mailz_content;
	}
	echo '</div>';
	echo '<div id="zing-mailz-cp-menu">';
	echo $zing_mailz_menu;
	echo '</div>';
//echo '</div>';
	
}

add_action('admin_menu', 'zing_mailz_add_admin'); ?>