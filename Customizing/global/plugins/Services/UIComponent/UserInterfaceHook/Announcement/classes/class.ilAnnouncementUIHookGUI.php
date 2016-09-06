<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
require_once("./Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");

/**
 * Create an announcement in an overlay.
 */
class ilAnnouncementUIHookGUI extends ilUIHookPluginGUI {
	public function __construct() {
		global $ilUser, $lng;
		$this->gLng = $lng;
		$this->gUser = $ilUser;
	}

	/**
	 * @inheritdoc
	 */
	function getHTML($a_comp, $a_part, $a_par = array()) {
		if ( 	$a_part != "template_get"
			|| 	$a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html"
			||	$_COOKIE["gev_announcement"][$this->gUser->getId()] === "announcement"
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		setcookie("gev_announcement[".$this->gUser->getId()."]", "announcement", time()+31*24*3600);

		// TODO: This should totally go to a template:
		$ann = <<<HTML

<div class="gev_ann" style="position: fixed; background-color: #000000; left:0; top:0; height:100%; width:100%; opacity: 0.5; visibility: visible; display: block;">

</div>
<div  class="gev_ann" style="z-index: 1000; background-color: #CECECE; opacity: 1; margin: -350px 0 0 -400px; width: 800px; height: 700px; position: absolute; top:50%; left: 50%; overflow: hidden;" >
	<div style="float: right; margin-top: 15px; margin-bottom: 5px; margin-right: 15px;">
			<a id="gev_ann_close" href="#">Schließen (X)</a>
	</div>
	<div class="ilClearFloat"></div>
	<div class="catTitle" style="background-color: #FFFFFF; padding: 10px;">
		<div class="catTitleHeader">Bald für Sie im neuen Look!</div>
	</div>
	<div style="background-color: #FFFFFF; padding: 10px;">
		<img  style="border: 1px solid #CECECE;" src="Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcement/new_design.png">
	</div>
</div>
<script>
	$(document).ready(function() {
		$("#gev_ann_close").click(function(ev) {
			$(".gev_ann").css("display", "none");
			ev.preventDefault();
		});	
	});
</script>

HTML;

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $ann
			);
	}
}
