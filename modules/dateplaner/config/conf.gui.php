<?php
//	if(!$actualIliasDir) {
//		require_once	('./classes/class.interface.php');
//		$interface = new Interface(nb);
//		$actualIliasDir = $interface->getactualIliasDir();
//	}

	// Standardeienstellungen 

	$templatefolder = '/templates';
	$actualtemplate = "default";
	
	//--------------------------------------------------------------
	// Weiterfühernde Einstellungen für die Oberfläche wie zb css und Tabellen Farben ..

	
	// lokale einstellungen für cscw
	// ilias stylesheets 
	$DP_CSS[tblrow1]		= 'class="tblrow1"';
	$DP_CSS[tblrow2]		= 'class="tblrow2"';
	$DP_CSS[tblrow1_s]	= 'tblrow1';
	$DP_CSS[tblrow2_s]	= 'tblrow2';
	$DP_CSS[tblheader]	= 'class="tblheader"';
	$DP_CSS[small]		= 'class="small"';
	$DP_CSS[fullwidth]	= 'class="fullwidth"';

	// cscw stylesheets 
	$DP_CSS[tblrow3]		= 'class="tblrow3"';
	$DP_CSS[tblrow4]		= 'class="tblrow4"';
	$DP_CSS[navi_new]		= 'class="navi_new"';
	$DP_CSS[navi_open]	= 'class="navi_open"';
	$DP_CSS[tblrow01]		= 'class="tblrow01"';
	$DP_CSS[tblrow02]		= 'class="tblrow02"';
	$DP_CSS[tblrow03]		= 'class="tblrow03"';

?>
