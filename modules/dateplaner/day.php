<?

//session_start() ;


// CSCW HEader includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.day.php');
require_once	('./includes/inc.output.php');
		
$DB					= new Database();
$Gui				= new Gui();
$PAGETITLE			= "Day View";
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year, $CSCW_Lang);
$keywords_float	= showKeywords($S_Keywords);

eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//



if ($timestamp) {
	 $showdate = getDateForDay($timestamp);
	 $day_navigation = navigation($timestamp);
	 $wholeDayDayDates = getWholeDay($timestamp);
	 $dayString = generateDay($timestamp);
}else {	 $timestamp=(int)mktime(0,0,0);
	 $showdate = getDateForDay($timestamp);
	 $day_navigation = navigation($timestamp);
	 $wholeDayDayDates = getWholeDay($timestamp);
	 $dayString = generateDay($timestamp);
	 
}


eval ("\$centertxt = \"".$Gui->getTemplate("day_main")."\";");

// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>