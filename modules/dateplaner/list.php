<?
//session_start() ;


// CSCW HEader includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.list.php');
require_once	('./includes/inc.output.php');
		
$DB					= new Database();
$Gui				= new Gui();
$PAGETITLE			= "Date - List";


if ($action=="print") {

	if($CSCW_fromtime_ts) $fromtime_ts	= $CSCW_fromtime_ts; 
	if($CSCW_totime_ts) $totime_ts		= $CSCW_totime_ts;
	$list_print_float = printDateList($fromtime_ts,$totime_ts);
	eval("doOutput(\"".$Gui->getTemplate("list_print")."\");"); 
}
else 
{
	
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year, $CSCW_Lang);
$keywords_float	= showKeywords($S_Keywords);
	
eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//
if ($action=="list") {
	if($outdata==True) {
		$fromtime_ts	= $timestamp; 
		$totime_ts		= $timestamp2 ;
		$Valid[0]		= "TRUE" ;
	}
	else 
	{
		$Start_date		= explode ("/",$date2);
		$timestamp		= mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2]);
		$End_date		= explode ("/",$date4);
		$timestamp2		= mktime(23,59,59,$End_date[1],$End_date[0],$End_date[2]);
		$fromtime_ts	= $timestamp; 
		$totime_ts		= $timestamp2 ;
		$Valid = parseData ($fromtime_ts, $totime_ts , $Start_date, $End_date);
	}
}

if($Valid[0] == "TRUE" or !$Valid) {
	if ($timestamp and $timestamp != "-1") {
		if ($timestamp2) {

			$fromtime_ts		= $timestamp; 
			$totime_ts			= $timestamp2 ;
			$CSCW_fromtime_ts	= $fromtime_ts;
			$CSCW_totime_ts		= $totime_ts;
			$Return			= setDateList($fromtime_ts,$totime_ts);
		}else {
			$fromtime_ts		= $timestamp; 
			$totime_ts			= $timestamp;
			$CSCW_fromtime_ts	= $fromtime_ts;
			$CSCW_totime_ts		= $totime_ts;
			$Return			= setDateList($fromtime_ts,$totime_ts);
		}
	}
	else
	{

		if($CSCW_fromtime_ts) $fromtime_ts	= $CSCW_fromtime_ts; 
		if($CSCW_totime_ts) $totime_ts		= $CSCW_totime_ts;
		
		if (!$fromtime_ts or $fromtime_ts == "-1") 
		{
			$fromtime_ts	= mktime(0,0,0);
			session_unregister ("CSCW_totime_ts");
		} 
		if(!$totime_ts or $totime_ts == "-1") 
		{
			$totime_ts		= $fromtime_ts;
		}

		$CSCW_fromtime_ts	= $fromtime_ts;
		$CSCW_totime_ts		= $totime_ts;
		$Return			= setDateList($fromtime_ts,$totime_ts);
	}
}
else 
{
	
	for($i=0; $i<(count($Valid)-1); $i++) {
		$list_navigation = $list_navigation.$Valid[$i]."<br>";
	}
	$list_navigation = $list_navigation.'
			<a href="list.php?action=list&outdata=1&timestamp='.$timestamp.'&timestamp2='.$timestamp2.'">'.$CSCW_language[back].'</a><br>
			';
}

$list_navigation	= $Return[0];
$list_float			= $Return[1];

eval ("\$centertxt = \"".$Gui->getTemplate("list_main")."\";");

// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//

exit;
}

?>