<?php
$zing_mailz_options[]=array(  "name" => "General settings",
            "type" => "heading",
			"desc" => "This section manages the Mailing List settings.");
$zing_mailz_options[]=array(	"name" => "Mode",
			"desc" => "The plugin can operate with a local installation, using your own database ('local') or can be set up to use our web services ('remote'). Choose your setting here. Note that you can't switch from one setting to another unless you reinstall the plugin.",
			"id" => "zing_mailz_mode",
			"options" => array("remote"=>'Remote: using our web services - sleep tight!',"local"=>'Local: using your own database - remember to back up!'),
			"type" => "selectwithkey");
