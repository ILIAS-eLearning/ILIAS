<?
//session_start() ;

// CSCW HEader includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.parse.php');
require_once	('./includes/inc.output.php');

// Objects
$db = new Database();
$Gui = new Gui($db);

$PAGETITLE	= "Properties";
$tableBorder = 0;
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year, $CSCW_Lang);
//$keywords_show = showKeywords();
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//
//*******************************************************************************************************
	
if ( $btn_accept == "OK" )
{
	if ($newKeyword != "")
	{
		$db->addKeyword( $CSCW_UId, $newKeyword );
	}

	if ($keyword != "" AND $changedKeyword != "")
	{	
		$db->updateKeyword( $keyword, $changedKeyword );
	}
	
}

if ( $btn_delete == "$CSCW_language[delete]" )
{
		$db->delKeyword( $keyword );
}

if ( $btn_time == "OK" )
{
	$startEnd = $db->getStartEnd( $CSCW_UId );
	if ( $startEnd[0] != "" )
	{	// Es ist schon ein Eintrag vorhanden
		$db->updateStartEnd( $startEnd[0], $starttime, $endtime );
		
	}
	else
	{
		// Noch kein Eintrag vorhanden
		$db->addStartEnd( $CSCW_UId, $starttime, $endtime );
		
	}
	$CSCW_Starttime		= $starttime;
	$CSCW_Endtime		= $endtime;		
}



$keywords = $db->getKeywords($CSCW_UId);
$x="";
for ($i = 0; $i < count($keywords); $i++)
{
	$keywordText = $keywords[$i][1];
	$keywordID = $keywords[$i][0];
	$options ="<option value='$keywordID'>$keywordText</option>";
	$x = $x.$options;
}
$optionBox = $x;


//*******************************************************************************************************
if($_FILES) {

$parsedata = parse($db,$_FILES);

}

eval ("\$centertxt = \"".$Gui->getTemplate("properties_main")."\";");


// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>