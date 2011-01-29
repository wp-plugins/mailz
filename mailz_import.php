<?php
//error_reporting(E_ALL & ~E_NOTICE);
//ini_set('display_errors', '1');

function zing_mailz_import() {
	global $wpdb;
	$addedUsers=0;
	$db=new db();
	if ($_POST['importlist'] && $_POST['mailzlistid']) {
		$listId=$_POST['mailzlistid'];
		$authors = $wpdb->get_results("SELECT * from $wpdb->users");
		foreach ( (array) $authors as $author ) {
			if (!$db->select("select id from ##phplist_user where email='".$author->user_email."'")) {
				$id=$db->insertRecord('phplist_user','',array('email' => $author->user_email,'confirmed' => '1','htmlemail' => '1'));
				if (!$db->readRecord('phplist_listuser',array('userid' => $id,'listid' => $listId))) {
					$addedUsers++;
					$db->insertRecord('phplist_listuser','',array('userid' => $id,'listid' => $listId));
				}
			}
		}
		if ($addedUsers) echo '<div id="message" class="updated fade"><p><strong>'.$addedUsers.' users imported.</strong></p></div>';
		else echo '<div id="message" class="updated fade"><p><strong>No users to import.</strong></p></div>';
	}
	echo '<div class="wrap">';
	echo '<h2>Import users from Wordpress</h2>';
	echo '<form action="admin.php?page=mailz-import" method="post">';
	echo '<table class="form-table">';
	echo '<tr valign="top">';
	echo '<th scope="row">';
	echo 'Add Wordpress users to list';
	echo '</th>';
	echo '<td>';
	if ($db->select("select * from ##phplist_list")) {
		echo '<select name="mailzlistid">';
		while ($db->next()) {
			echo '<option value="'.$db->get('id').'">'.$db->get('name').'-'.$db->get('description').'</option>';
		}
		echo '</select>';
	}
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<input type="hidden" name="importlist" value="1"/>';
	echo '<p class="submit">';
	echo '<input type="submit" class="button-primary" value="Import" name="Submit" />';
	echo '</p>';
	echo '<form>';
	echo '</div>';
}

