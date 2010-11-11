<?php

if (!function_exists('zing_footers')) {
	function zing_footers() {
		global $zing_footer,$zing_footers;

		$bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
		if ( $bail_out ) return $footer;

		//Please contact us if you wish to remove the ChoppedCode logo in the footer
		if (!$zing_footer) {
			echo '<center style="margin-top:0px;font-size:x-small">';
			echo 'Powered by <a href="http://www.choppedcode.com">ChoppedCode</a>';
			foreach ($zing_footers as $foot) {
				echo ', <a href="'.$foot[0].'">'.$foot[1].'</a>';
			}
			echo '</center>';
			$zing_footer=true;
		}

	}
}
?>