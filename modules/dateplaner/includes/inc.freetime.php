<?php

function str2date($in){

$t = split("/",$in);
if (count($t)!=3) $t = split("-",$in);
if (count($t)!=3) $t = split(" ",$in);

if (count($t)!=3) return -4;

if (!is_numeric($t[0])) return -1;
if (!is_numeric($t[1])) return -2;
if (!is_numeric($t[2])) return -3;
if ($t[2]<1902 || $t[2]>2037) return -3;
if (!checkdate( $t[1], $t[0], $t[2] )) return -5;

return mktime (0,0,0, $t[1], $t[0], $t[2]);
}

/**
* 	void function setDateInTblHead($timestamp)
* 	@description : set the Strings in the top of the table in freetime.htm 
*					the parameter is the startpoint of a given week in timestamp-format
* 	@param int timestamp
* 	@return string S_Datum 
*/
function setDateInTblHead($timestamp)
{

	$ttd = new TimestampToDate;

	$ttd->ttd($timestamp);
	$S_Datum[week]				= $ttd->weeknumber;
	$ttd->ttd($timestamp);
	$S_Datum[monday_full]		= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+1 day" , $timestamp));
	$S_Datum[tuesday_full]		= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+2 day" , $timestamp));
	$S_Datum[wednesday_full]	= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+3 day" , $timestamp));
	$S_Datum[thursday_full]		= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+4 day" , $timestamp));
	$S_Datum[friday_full]		= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+5 day" , $timestamp));
	$S_Datum[saturday_full]		= $ttd->extrashorttime ;

	$ttd->ttd(strtotime ("+6 day" , $timestamp));
	$S_Datum[sunday_full]		= $ttd->extrashorttime ;

	Return $S_Datum;
} // end func


?>