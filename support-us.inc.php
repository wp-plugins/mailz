<?php 
if (!function_exists('zing_support_us')) {
	function zing_support_us($plugin,$action='check') {
		$option=$plugin.'-support-us';
		if ($action == 'activate' || get_option($option) == '') {
			update_option($option,time());
		} elseif ($_REQUEST['support-us'] == 'hide') {
			update_option($option,time()+7776000);
		} elseif ($action == 'check') {
			if ((time() - get_option($option)) > 1209600) { //14 days 
				return "<div id='zing-warning' style='background-color:red;color:white;font-size:large;margin:20px;padding:10px;'>Looks like you've been using this plugin for quite a while now. Have you thought about showing your appreciation through a small donation?<br /><br /><a href='http://www.zingiri.net/donations'><img src='https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif' /></a><br /><br />If you already made a donation, you can <a href='?page=".$plugin."&support-us=hide'>hide</a> this message.</div>";
			}
		}
	}
}
?>
<div style="width:20%;float:right;position:relative">
	<div class="cc-support-us">
		<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
		<h3>Support Us</h3>
		<p>If you like this plugin, please share it with your friends</p>
		<div style="align:center;margin-bottom:15px;text-align:center">
			<a style="margin-bottom:15px" href="http://www.twitter.com/zingiri"><img align="middle" src="http://twitter-badges.s3.amazonaws.com/follow_us-a.png" alt="Follow Zingiri on Twitter"/></a>
		</div>
		<div style="margin-bottom:15px;text-align:center">
			<fb:share-button href="http://www.zingiri.net/plugins-and-addons/mailing-list/" type="button" >
		</div>
		<p>Rate our plugin on Wordpress</p>
		<a href="http://wordpress.org/extend/plugins/mailz" alt="Rate our plugin"><img height="35px" src="<?php echo ZING_MAILZ_URL;?>stars.png"><img height="35px" src="<?php echo ZING_MAILZ_URL;?>stars.png"><img height="35px" src="<?php echo ZING_MAILZ_URL;?>stars.png"><img height="35px" src="<?php echo ZING_MAILZ_URL;?>stars.png"><img height="35px" src="<?php echo ZING_MAILZ_URL;?>stars.png"></img></a>
		<?php echo zing_support_us('mailz_cp');?>
	</div>
	<div class="cc-support-us">
		<h3>Discover Mailing List Pro</h3>
		<p>Mailing List Pro is an extension to the Mailings List plugin taking the integration with Wordpress to a new level.<br />
		With Mailing List Pro you can
			upload your WP users to Mailing List and attach them to your chosen Mailing List,
			auto signup new blog users to a chosen Mailing List and
			auto create a Mailing List message when creating a new post.
		</p>   
		<a href="http://www.clientcentral.info/cart.php?a=add&pid=113" target="_blank"><img src="<?php echo ZING_MAILZ_URL;?>images/buy_now.png" /></a>		
		<p><strong style="color:green;font-size:large">$19.95</strong>/year.</p>
	</div>
	<div class="cc-support-us">
		<h3>Unlimited Hosting</h3>
		<p>Every month we offer a handful of unlimited hosting packages at an exceptional low price to our plugin users.<br />The packages are made available on a first come, first served basis, so don't wait too long beforing ordering yours!</p>   
		<a href="http://www.clientcentral.info/cart.php?a=add&pid=114" target="_blank"><img src="<?php echo ZING_MAILZ_URL;?>images/home.png" /></a>		
		<p><strong style="color:red;font-size:large">$2.95</strong>/month.</p>
	</div>

	<div style="text-align:center;margin-top:40px">
		<a href="http://www.zingiri.net" target="_blank"><img src="http://www.zingiri.net/logo.png" /></a>
	</div>
</div>