<?php
/**
*Name of File: inc.sortdates.php
*Author: Jan Hübbers (jan@huebbers.de)
*Author: Frank Grümmert (jan@huebbers.de)
*Version: 0.5
*
* Description:
*	These functions mediate between the display and the database.
*	The two major parts are the calculations, if a reocurring date (rotation)
*	is in a given timeframe and to check if it is negated by a negative date.
*	The other part is the resolution of colliding dates for the dayview.
*
* Version History:
*
*	0.5	- adjusted getDayList() for dates without length.
*
*	0.4	- adjusted getDayList() for dates without length.
*
*	0.3	- added support for whole day dates, getWholeDayDateList()
*	
*	0.2	- added support for rotation dates, calculateDatesFromRotation(), cmp(), checkIfNotNegDate()
*	
*	0.1	- creation of first code, getDayList() and getDateList()
*		  	
*		
*
*/

/*-----------------------------------------------------------------------------*/


/**
*	boolean function checkIfNotNegDate($neg_startTime, $neg_ID, $neg_dates)
*	
*	@param $neg_startTime, the calculated start timestamp of the rotation date to be checked
*	@param $neg_ID, the ID of the date to be checked
*	@param $neg_dates, an array of negative dates, which will be checked if ID and timestamp are the same
*
*	@return bolean $return, true, if the date is not in $neg_dates. False, if the user has deleted the date.
*	
*	@description This function evaluates, if a calculated rotation date is to be passed as a date to be displayed or
*			to be discarded
*/

function checkIfNotNegDate($neg_startTime, $neg_ID, $neg_dates){
    $notNeg = TRUE;
    $neg_startTime = mktime(0, 0, 0, date("m", $neg_startTime), date ("d", $neg_startTime), date("Y", $neg_startTime)); // truncate to start of day, as the negative date will also by saved in this format
    if($neg_dates){
        foreach ($neg_dates as $key => $ArrayRow)
        {
        if ($ArrayRow[9] == $neg_startTime && $ArrayRow[0]== $neg_ID)  {$notNeg =FALSE; break; } // compare
        }
    }
    return $notNeg;
}
// end func

/*-----------------------------------------------------------------------------*/


/**
*	string function cmp($a, $b)
*	
*	@param string $a first to be compared value
*	@param $b, second to be compared value
*
*	@return (int), a value expressing the result of the comparisson.
*			0: $a==$b
*			1: $a>$b
*			-1:$a<$b
*	@description this function is needed by usort(), usort() is a PHP function. It compares the starting times of dates.
*/



function cmp ($a, $b) {
        if ($a[1] == $b[1]) return 0;
        return ($a[1] < $b[1]) ? -1 : 1;
}

/*-----------------------------------------------------------------------------*/


/**
*	array[][] function getDateList($id, $start, $end, $Keywords)
*	
*	@param $id, the users ID.
*	@param $start, a required timestamp of sometime during the current day.
*	@param $end, a required timestamp of sometime during the current day.
*	@param $DB, a DB class object.
*	@param array[] $Keywords, the selected keywords which the dates have to match to
*			be displayed. This array is just passed to the database
*	
*	@description This function fetches date arrays from the database for a 
*			given time frame, calls for the calculation of rotation dates and sorts these
*			dates into the result array. It returns an array of the following format for the second dimension:
*			0=>date_id; 1=>begin; 2=>end; 3=>group_id or blank ; 4=>user_id or blank; 5=>shorttext; 6=>text; 7=>rotationtype or blank; 8=> rotationend or blank
*/

function getDateList($id, $start, $end, $Keywords, $DB){
    $neg_dates			= $DB->getNegRotationDates ($id, $start, $end);
    $resultRotation		= $DB->getRotationDates($id, $start, $end, $Keywords);
    $resultArray		= $DB->getDates($id, $start, $end, $Keywords);
	if ($resultRotation && $resultArray) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $resultRotation, $neg_dates);
		$result = array_merge($resultArray, $rotationDates) ; // watch out for implementation changes of PHP
		usort($result, "cmp");
	}
	if (!$resultRotation && $resultArray){
		$result = $resultArray;
	}
	if ($resultRotation && !$resultArray) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $resultRotation, $neg_dates);
		$result = $rotationDates;
		usort($result, "cmp");
	}
	if (!$resultRotation && !$resultArray) {
	    $result = FALSE;
	}

    return $result;
}

/*-----------------------------------------------------------------------------*/


/**
*	array[][] function getWholeDayDateList($id, $start, $end, $Keywords)
*	
*	@param $id, the users ID.
*	@param $start, a required timestamp of sometime during the current day.
*	@param $end, a required timestamp of sometime during the current day.
*	@param $DB, a DB class object.
*	@param array[] $Keywords, the selected keywords which the dates have to match to
*			be displayed. This array is just passed to the database
*	
*	@description This function fetches date arrays for whole day dates from the database for a 
*			given time frame, calls for the calculation of rotation dates and sorts these
*			dates into the result array. It returns an array of the following format for the second dimension:
*			0=>date_id; 1=>begin; 2=>end; 3=>group_id or blank ; 4=>user_id or blank; 5=>shorttext; 6=>text; 7=>rotationtype or blank; 8=> rotationend or blank
*			
*/
function getWholeDayDateList($id, $start, $end, $Keywords, $DB){
    $neg_dates			= $DB->getNegRotationDates ($id, $start, $end);
    $resultRotation		= $DB->getFullDayRotationDates($id, $start, $end, $Keywords);
    $resultArray		= $DB->getFullDayDates($id, $start, $end, $Keywords);
	if ($resultRotation && $resultArray) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $resultRotation, $neg_dates);
		$result = array_merge($resultArray, $rotationDates) ;
		usort($result, "cmp");
	}
	if (!$resultRotation && $resultArray){
		$result = $resultArray;
	}
	if ($resultRotation && !$resultArray) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $resultRotation, $neg_dates);
		$result = $rotationDates;
		usort($result, "cmp");
	}
	if (!$resultRotation && !$resultArray) {
	    $result = FALSE;
	}
    return $result;
}


/*-----------------------------------------------------------------------------*/


/**
*	array[][][] function getDayList($id, $start, $end, $Keywords)
*	
*	@param $id, the users ID.
*	@param $start, a required timestamp of sometime during the current day.
*	@param $end, a required timestamp of sometime during the current day.
*	@param $DB, a DB class object.
*	@param array[] $Keywords, the selected keywords which the dates have to match to
*			be displayed. This array is just passed to the database
*	
*	@description This function uses the dates provided by getDateList(). It compares start and end times of dates
*			and determines, if dates are colliding. to avoid collisions in one array a 3-dimensional array
*			is created, the first dimension holds 2-dimensional arrays of not colliding dates. 
*/

function getDayList($id, $start, $end, $Keywords, $DB){
    $unstreamedDates= getDateList($id, $start, $end, $Keywords, $DB);
    //echo $unstreamedDates;
    

    if($unstreamedDates){

	// first element exists, so take it
        $streamedDates[0][0][0]= $unstreamedDates[0][0];
        $streamedDates[0][0][1]= $unstreamedDates[0][1];
        $streamedDates[0][0][2]= $unstreamedDates[0][2];
        $streamedDates[0][0][3]= $unstreamedDates[0][5];
        $streamedDates[0][0][4]= $unstreamedDates[0][6];
	
	// check other dates
        for($i=1;$i<count($unstreamedDates);$i++){
            $newstart = $unstreamedDates[$i][1];
            $newend   = $unstreamedDates[$i][2];
            $stream=0;
            while (($newstart < $streamedDates[$stream][(count($streamedDates[$stream])-1)][2])// collission in this stream?
            		|| ($newstart == $streamedDates[$stream][(count($streamedDates[$stream])-1)][1])  ){
                $stream++; // check next stream
            }
            $row =count($streamedDates[$stream]);
            $streamedDates[$stream][$row][0] = $unstreamedDates[$i][0]; //ID
            $streamedDates[$stream][$row][1] = $unstreamedDates[$i][1]; //start timestamp
            $streamedDates[$stream][$row][2] = $unstreamedDates[$i][2]; //end timestamp
            $streamedDates[$stream][$row][3] = $unstreamedDates[$i][5]; //short text
            $streamedDates[$stream][$row][4] = $unstreamedDates[$i][6]; //long text
	}
    }
    else{
        $streamedDates = false;

    }
    return $streamedDates;

}

/*-----------------------------------------------------------------------------*/


/**
*	void function calculateDatesFromRotatation($startResultSlot, $endResultSlot, $resultRotation, $neg_dates)
*	
*	@param $startResultSlot, timestamp of the start of the time frame
*	@param $endResultSlot, timestamp of the start of the time frame
*	@param $resultRotation, an array of rotation dates
*	@param $neg_dates[][], an array of negative dates.
*	
*	@description This function calculates rotation dates and compares them to a negative list (i.e. neg_dates).
*			It is then determined, if the calculated date is to be passed on or to be discarded.
*
*/


function calculateDatesFromRotatation($startResultSlot, $endResultSlot, $resultRotation, $neg_dates){
    $resultRotationCalculation = array();
    $counter = 0;
    for($i=0; $i< count($resultRotation); $i++){ // for all dates in $resultRotation ...
        $rotArg = $resultRotation[$i][7];	// ... get ferquency...
        switch($rotArg){
            case 1: $offset="+1 Day"; break;
            case 2: $offset="+1 week"; break;
            case 3: $offset="+2 week"; break;
            case 4: $offset="+4 week"; break;
            case 5: $offset="+1 month"; break;
            case 6: $offset="+6 month"; break;
            case 7: $offset="+1 year"; break;
        }
        $id		= $resultRotation[$i][0];	//....and other attributes....
        $start	= $resultRotation[$i][1];
        $end	= $resultRotation[$i][2];
        $short	= $resultRotation[$i][5];
        $text	= $resultRotation[$i][6];
        $endrotation = $resultRotation[$i][8];


        while($startResultSlot>$end){ // ... while rotation date is not in time frame
           $start = strtotime($offset, $start); // increase timestamps by offset.
           $end   = strtotime($offset, $end);	
        }
        while($endResultSlot>=$start && ($endrotation>= $start)){ // while rotation date is in time frame...
            $isNoNegDate = checkIfNotNegDate($start, $id, $neg_dates); // ...check if not deleted by user..
            if($isNoNegDate){
                $resultRotationCalculation[$counter][0] = $id; // ...insert if not deleted...
                $resultRotationCalculation[$counter][1] = $start;
                $resultRotationCalculation[$counter][2] = $end;
				$resultRotationCalculation[$counter][5] = $short;
                $resultRotationCalculation[$counter][6] = $text;
                $counter++;
            }
            $start = strtotime($offset, $start); // ... increase and back to while _^
            $end   = strtotime($offset, $end);
        }
    }
return $resultRotationCalculation;

}


/**
*	void function getGroupDatesForDisplay($groupID, $start, $end)
*	
*	@param $groupID, the Group ID.
*	@param $start, a required timestamp of sometime during the current day.
*	@param $end, a required timestamp of sometime during the current day.
*	@param $DB, a DB class object.
*	
*	@description This function fetches date arrays from the database for a 
*			given time frame, calls for the calculation of rotation dates and sorts these
*			dates into the result array. It returns an array of the following format for the second dimension:
*			0=>date_id; 1=>begin; 2=>end; 3=>group_id or blank ; 4=>user_id or blank; 5=>shorttext; 6=>text; 7=>rotationtype or blank; 8=> rotationend or blank
*
*/
function getGroupDatesForDisplay($groupID, $start, $end, $DB) {
	  $GroupDates				= $DB->getGroupDates($groupID, $start, $end); //get dates
	  $GroupRotationDates		= $DB->getGroupRotationDates ($groupID, $start, $end);



	if ($GroupRotationDates && $GroupDates) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $GroupRotationDates, $neg_dates);
		$result = array_merge($GroupDates, $rotationDates) ;
		usort($result, "cmp");

	}
	if (!$GroupRotationDates && $GroupDates){
		$result = $GroupDates;
	}
	if ($GroupRotationDates && !$GroupDates) {
	    $rotationDates	= calculateDatesFromRotatation($start, $end, $GroupRotationDates, $neg_dates);
		$result = $rotationDates;
		usort($result, "cmp");
	}
	if (!$GroupRotationDates && !$GroupDates) {
	    $result = FALSE;
	}

	return $result;

}// end func


/*-----------------------------------------------------------------------------*/
?>