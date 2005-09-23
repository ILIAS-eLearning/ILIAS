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
* CSCW JS Function file 
*
* this file should manage the startup sequence
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
* @module       dpFunctions.js                           
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/


/**
 * DP_date_Funktion -------------------- Start 

 */

// Open a popup window. 
function popup(url,name,einstellungen)
{
	window.open(url,name,einstellungen);
}

// check Format of date values
function start_date_check()
{
	if (document.forms[0].date2.value.lastIndexOf("/")==document.forms[0].date2.value.indexOf("/") || document.forms[0].date2.value.indexOf("/")=="-1" || document.forms[0].date2.value.lastIndexOf("/")=="-1") alert("ERROR: Das Format des Termin-Starts stimmt nicht!"); 
}

// check Format of date values
function end_date_check()
{
	if (document.forms[0].date4.value.lastIndexOf("/")==document.forms[0].date4.value.indexOf("/") || document.forms[0].date4.value.indexOf("/")=="-1" || document.forms[0].date4.value.lastIndexOf("/")=="-1") alert("ERROR: Das Format des Termin-Endes stimmt nicht!"); 
}

// close window
function on_cancel()
{
	top.close()
}

// hide some elments if WholeDay Button checked
function HideElements(el1,el2,el3,el4) {
	if (document.Formular.DateValuesWhole_day.checked)
	{
		document.getElementById(el1).style.visibility="hidden";
		document.getElementById(el2).style.visibility="hidden";
		document.getElementById(el3).style.visibility="hidden";
		document.getElementById(el4).style.visibility="hidden";
	}else {
		document.getElementById(el1).style.visibility="visible";
		document.getElementById(el2).style.visibility="visible";
		document.getElementById(el3).style.visibility="visible";
		document.getElementById(el4).style.visibility="visible";
	}
}

// hide elment if selected Group = 0 (no group)
function CheckSelectGroup(el) {
	if(document.Formular.DateValuesGroup_id.options[0].selected == true ){
		document.getElementById(el).style.visibility="hidden";
	}
	else{
		document.getElementById(el).style.visibility="visible";
	}
}

// hide static freetime elment  if selected Group = 0 (no group)
function HideThingsGroup() {
	if (document.Formular.DateValuesGroup_id.options[0].selected == true){
		document.getElementById("freetime").style.visibility="hidden";
	}else {
		document.getElementById("freetime").style.visibility="visible";
	}
}
// hide elment if selected Rotation = 0 (no rotation)
function CheckSelectRotation(el) {
	if(document.Formular.DateValuesRotation.options[0].selected == true ){
		document.getElementById(el).style.visibility="hidden";
	}
	else{
		document.getElementById(el).style.visibility="visible";
	}
}

// hide static rotationtime elment  if selected Rotation = 0 (no rotation)
function HideThingsRotation() {
	if (document.Formular.DateValuesRotation.options[0].selected == true){
		document.getElementById("rotationtime").style.visibility="hidden";
	}else {
		document.getElementById("rotationtime").style.visibility="visible";
	}
}

/**
 * Stefan

 */
function setKeywordEdit()
{
	document.propertiesForm.changedKeyword.value = 
	document.propertiesForm.keyword.options[document.propertiesForm.keyword.options.selectedIndex].text;
}

// Detect if the browser is IE or not.
// If it is not IE, we assume that the browser is NS.
var IE = document.all?true:false

// If NS -- that is, !IE -- then set up for mouse capture
if (!IE) document.captureEvents(Event.MOUSEMOVE)

// Set-up to use getMouseXY function onMouseMove
document.onmousemove = getMouseXY;

// Temporary variables to hold mouse x-y pos.s
var tempX = 0
var tempY = 0

// Main function to retrieve mouse x-y pos.s

function show(e){

document.getElementById(e).style.top=tempY;
document.getElementById(e).style.left=tempX;
document.getElementById(e).style.visibility="visible";
		

}

function show_left(e){

document.getElementById(e).style.top=tempY;
document.getElementById(e).style.left=tempX-300;
document.getElementById(e).style.visibility="visible";
		

} 

function hide(e){
document.getElementById(e).style.visibility="hidden";
}

function getMouseXY(e) {
  if (IE) { // grab the x-y pos.s if browser is IE
    tempX = event.clientX + document.body.scrollLeft
    tempY = event.clientY + document.body.scrollTop
  } else {  // grab the x-y pos.s if browser is NS
    tempX = e.pageX
    tempY = e.pageY
  }  
  // catch possible negative values in NS4
  if (tempX < 0){tempX = 0}
  if (tempY < 0){tempY = 0}  
  // show the position values in the form named Show
  // in the text fields named MouseX and MouseY
  //return true
}