<?
//session_start() ;

// CSCW HEader includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.month.php');
require_once	('./includes/inc.output.php');
		
$DB					= new Database();
$Gui				= new Gui();
$PAGETITLE			= "Month View";
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year, $CSCW_Lang);
$keywords_float	= showKeywords($S_Keywords);

eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//

if ($timestamp ) {
	 $Return = setMonthView($timestamp, $week_s, False);
}else {
	 $first_change = true;
	 $Return = setMonthView(mktime(0,0,0), $week_s, $first_change);
}
$month_navigation	= $Return[0];
$month_float		= $Return[1];
$month_string		= $Return[2];
eval ("\$centertxt = \"".$Gui->getTemplate("month_main")."\";");

// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>