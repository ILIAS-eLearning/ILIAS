<?
// CSCW HEader includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.week.php');
require_once	('./includes/inc.output.php');
		
$DB					= new Database();
$Gui				= new Gui();
$PAGETITLE			= "Week View";
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year, $CSCW_Lang);
$keywords_float	= showKeywords($S_Keywords);
eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//

if ($timestamp) {
	$Return = setWeekView($timestamp);
}else {
	$Return = setWeekView(mktime(0,0,0));
}

$week_navigation	= $Return[0];
$week_float			= $Return[1];
$S_Datum 			= $Return[2];
eval ("\$centertxt = \"".$Gui->getTemplate("week_main")."\";");

// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>