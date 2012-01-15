<?php 
//v2.01.09
if (!function_exists('zing_support_us')) {
	function zing_support_us($shareName,$wpPluginName,$adminLink,$version,$donations=true) {
?>
		<div style="width:20%;float:right;position:relative">
				<div style="margin:5px 15px;">
					<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
					<div style="float:left;">
						<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.zingiri.com" data-text="Zingiri">Tweet</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>				
					</div>
					<div style="float:left;">
						<fb:share-button href="http://www.zingiri.com/bookings/<?php echo $shareName;?>/" type="button" >
					</div>
				</div>
				<div style="clear:both"></div>
			<div class="cc-support-us">
				<p>Rate our plugin on Wordpress</p>
				<a href="http://wordpress.org/extend/plugins/<?php echo $wpPluginName;?>" alt="Rate our plugin">
				<img src="http://www.zingiri.com/wordpress/wp-content/uploads/5-stars-125pxw.png" />
				</a>
				<?php 
				$option=$wpPluginName.'-support-us';
				if (get_option($option) == '') {
					update_option($option,time());
				} elseif (isset($_REQUEST['support-us']) && ($_REQUEST['support-us'] == 'hide')) {
					update_option($option,time()+7776000);
				} else {
					if ((time() - get_option($option)) > 1209600) { //14 days 
						if ($donations) echo "<div id='zing-warning' style='background-color:red;color:white;font-size:large;margin:20px;padding:10px;'>Looks like you've been using this plugin for quite a while now. Have you thought about showing your appreciation through a small donation?<br /><br /><a href='http://www.zingiri.com/donations'><img src='https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif' /></a><br /><br />If you already made a donation, you can <a href='?page=".$adminLink."&support-us=hide'>hide</a> this message.</div>";
					}
				}
				?>
			</div>
			<?php 	
			if ((time()-get_option($wpPluginName.'_news_time')) > 7200) {
				global $current_user;
			
				$url='http://www.zingiri.com/index.php?zlistpro=register&e='.urlencode($current_user->data->user_email).'&f='.urlencode(isset($current_user->data->first_name) ? $current_user->data->first_name : '').'&l='.urlencode(isset($current_user->data->last_name) ? $current_user->data->last_name : '').'&w='.urlencode(get_option('home')).'&p='.$wpPluginName.'&v='.urlencode($version);			
				$news = new zHttpRequest($url);
				if ($news->live()) {
					update_option($wpPluginName.'_news',$news->DownloadToString());
				}
				update_option($wpPluginName.'_news_time',time());
			}
			?>
			<?php
			$data=json_decode(get_option($wpPluginName.'_news'));
			if (count($data) > 0) {
				foreach ($data as $rec) { ?>
					<div class="cc-support-us">
					<h3><?php echo $rec->title;?></h3>
					<?php echo $rec->content;?>
					</div>
				<?php 
				}
			}?>

			<div style="text-align:center;margin-top:15px">
				<a href="http://www.zingiri.com" target="_blank"><img width="150px" src="http://www.zingiri.com/logo.png" /></a>
			</div>
		</div>
<?php 
	}
}
?>