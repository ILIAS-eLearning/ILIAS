<?php
function getCSV($file){
	$handle = fopen ($file,"r"); 
	while ($data = fgetcsv ($handle, 1000, ",")) { // Daten werden aus der Datei
	    $array[] = $data;                           // in ein Array $data gelesen
	}
	fclose ($handle);
	return $array;
}

function showArray($array){
	global $CSCW_language;
	$format = $CSCW_language[date_format];
	$parsedata.= "<b>$CSCW_language[insertImportDates]</b> <br><hr>";
	foreach($array as $date){
		$parsedata.= "<table><tr><td><b>$CSCW_language[timeslice]</b></td><td>".date($format, $date[begin])." - ".date($format, $date[end])."</td></tr>";
		$parsedata.= "<tr><td valign='top'><b>$CSCW_language[shorttext]:</b></td><td>".$date[short]."</td></tr>";
		$parsedata.= "<tr><td valign='top'><b>$CSCW_language[Text]:</b></td><td>".$date[text]."</td></tr></table>";
		
		$parsedata.= "<hr>";
	}
	return $parsedata;
}


function convertToDateFormat($a){
	global $CSCW_UId;
	for($i=1; $i<count($a); $i++){
		$j = $i-1;
		if($a[$i][5]=="Aus"){//ganztagestermin?
			$dates[$j][begin] 	= makeTimestamp($a[$i][1], $a[$i][2]);
			$dates[$j][end]		= makeTimestamp($a[$i][3], $a[$i][4]);
			$dates[$j][user_ID]	= $CSCW_UId;
			$dates[$j][short]	= $a[$i][0];
			if($a[$i][16]!="") {$dates[$j][short].= " (".$a[$i][16].")";}//Ort?
			$dates[$j][text]	= $a[$i][14];
		}else{
			$dates[$j][begin] 	= makeTimestamp($a[$i][1], "00:00:00");
			$dates[$j][end]		= makeTimestamp($a[$i][3], "23:59:59");
			$dates[$j][user_ID]	= $CSCW_UId;
			$dates[$j][short]	= $a[$i][0];
			if($a[$i][16]!="") {$dates[$j][short].= " (".$a[$i][16].")";}//Ort?
			$dates[$j][text]	= $a[$i][14];
		}
		
	}
	return $dates;		
}

function makeTimestamp($day, $time){
	$d = explode(".", $day );
	$t = explode(":", $time);
	$timestamp = mktime($t[0],$t[1],$t[2],$d[1],$d[0],$d[2]);
	return	$timestamp;
}

function parse($db, $_FILES){
	global $CSCW_language;
$file = $_FILES['Datei'];
	if($file[tmp_name]){		
		$array = getCSV($file[tmp_name]);
		$dates = convertToDateFormat($array);
		for($j=0; $j<count($dates);$j++){
			$return = $db->addDate (	$dates[$j][begin],
										$dates[$j][end], 
										0, 
										$dates[$j][user_ID], 
										mktime(),
										0, 
										0, 
										$dates[$j][short], 
										$dates[$j][text], 
										0, 
										$dates[$j][user_ID]);
		}
		return showArray($dates);
	}
	else{
		return $CSCW_language[ERROR_FILE_CSV_MSG];
	}

}
?>
