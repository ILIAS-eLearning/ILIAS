<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Functions for freetime.php
*
* this file should manages freetime actions
*
* @author		Matthias Pohl <m.pohl@gmx.net> 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
*/

/**
* 	void function str2date($in)
*
* 	@param string $in
* 	@return date $date 
*/
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
}// end func

/**
* 	void function setDateInTblHead($timestamp)
* 	set the Strings in the top of the table in freetime.htm 
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