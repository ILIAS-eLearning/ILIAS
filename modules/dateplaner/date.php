<?
//session_start() ;

// CSCW Header includieren
require			('./includes/inc.cscw.header.php');
require_once	('./includes/inc.dates.php');
require_once	('./includes/inc.output.php');

$DB				= new Database();
$Gui			= new Gui();


//Übergabe parameter Prüfen

if ($date_id){
	$DateArray		= $DB->getDate ($date_id, $CSCW_UId);
// debug 
//	echo("DATEARRAY <br>");
//	print_r($DateArray);
	$PAGETITLE		= "Date : ".$DateArray[8];			// Page Titel setzten
} else {
	$PAGETITLE		= "Date :".@$DateValues[shorttext];	// Page Titel setzten
}


// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
// linker Frame wird nicht benötigt
$left	= '';
// --------------------------------------  ende Fest -------------------------------//

// kein Timpestamp vorhanden !

$DateValues[date2]		= $date2 ;
$DateValues[date4]		= $date4 ;
$DateValues[date6]		= $date6 ;
$DateValues[group_id]	= $DateValuesGroup_id;
$DateValues[rotation]	= $DateValuesRotation;
$DateValues[whole_day]	= $DateValuesWhole_day;

// aktionen
if($dateaction) {
	switch($dateaction) {
		case 'insert':
			$msg = setInsertAction($date2, $date4, $date6, $DateValues);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			break; 
		case $CSCW_language[dv_button_update]:
			$msg = setUpdateAction($date2, $date4, $date6, $DateValues);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			
			break;
		case $CSCW_language[dv_button_delete]:
			$msg = setDeleteAction($DateValues);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			break;
	}
}else {
	if ((!$date_id and !$DateValues[date_id]) or $dateview == "insert"  )
	// neuer Termin soll eingetragen werden
	{
		if($dateview == "freetime") {
			if($timestamp != "") {
				$ttd					= new TimestampToDate;
				$ttd->ttd($timestamp);
				$DateValues[date2] 		= $DateValues[date4]	= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
				$DateValues[begin_h]	= $DateValues[end_h]	= $ttd->hour_long ;
				$DateValues[begin_min]	= $DateValues[end_min] 	= $ttd->minutes ;
			}
		}
		if (!$timestamp ) $timestamp = (int)mktime(0,0,0);
		$dateContent = setInsertDate($timestamp, $DateValues);
		$jscriptboddy = "onLoad=\"HideElements('textOne','textTwo','textThree', 'textFour'); HideThingsRotation(); HideThingsGroup()\"";
	}else 
	{
		if (!$timestamp ) $timestamp = (int)mktime(0,0,0);
		$DateArray[old_keyword_id] = $DateArray[keyword_id];
		$dateContent = setUpdateDeleteDate($timestamp, $date_id, $DateArray, $DateValues );

		if($js != "ro") 
		{
			$jscriptboddy = "onLoad=\"HideElements('textOne','textTwo','textThree', 'textFour'); HideThingsRotation()\"";
		}
		else 
		{
			$jscriptboddy = "";
		}


	}
}

// -----------------------------------------  FEST ---------------------------------//
eval ("\$main = \"".$Gui->getTemplate("date_main")."\";");
// Frameset
// nicht benötigt
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//

echo ("<noscript>".$CSCW_language[ERROR_JAVASCRIPT]." </noscript>");
 
exit;


?>