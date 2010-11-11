<?php
$zing_mailz_name = "ccMails";
$zing_mailz_shortname = "zing_mailz";
$zing_mailz_options=array();

function zing_mailz_add_admin() {

	global $zing_mailz_name, $zing_mailz_shortname, $zing_mailz_options;

	//echo 'mc='.get_magic_quotes_gpc().'/'.get_magic_quotes_runtime();
	
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
		zing_mailz_cp();
}
	?>
	<br />
<div id="zing-mailz-cp-menu">
<form method="post">
<p>
	<?php
	if ($zing_mailz_version == ZING_MAILZ_VERSION)
	echo 'Your version ('.$zing_mailz_version.') is up to date!';
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
<p>For more info and support, contact us at <a href="http://www.choppedcode.com/">ChoppedCode</a> or
check out our <a href="http://choppedcode.com/forums/">support forums</a>.</p>
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
		echo '<div id="phplist">'.$zing_mailz_content.'</div>';
	}
	echo '</div>';
	echo '<div id="zing-mailz-cp-menu">';
	echo $zing_mailz_menu;
	echo '</div>';
//echo '</div>';
	
}

add_action('admin_menu', 'zing_mailz_add_admin'); ?>