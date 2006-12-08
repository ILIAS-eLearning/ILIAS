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
* Gui Class
*
* this class should manage the Gui function
* Die Klasse erm�glicht es Code und Statisches HTML mittels Templates auszugeben. 
* Da die Ausgabe letztlich eine Ausgabe erzeugen soll wird eine ausf�hrende Funktion ben�tigt. 
* Als Ausf�hrende Datei nutzt darum diese Klasse die Funktion "inc.output.php" im Includes Ordner.   
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
*/
include('.'.DATEPLANER_ROOT_DIR.'/config/conf.gui.php');

class Gui
{

	/**
	* constructor
	*/

	function Gui() {
 	}

	/**
	* function gettemplate($template,$endung="htm")
	* @description : get Inforamtion from a template File 
	* @param string template
	* @param string extension ( means the extension of the Template files, eg. .htm )
	* @global string templatefolder
	* @global string actualtemplate ( means the number of Template set eg. "default" )
	* @return Sting
	*/
	function getTemplate($template, $extension="htm") {
        global $templatefolder, $actualtemplate;

        if(!$templatefolder) $templatefolder = "templates";
        return str_replace("\"","\\\"",implode("",file(".".DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/".$template.".".$extension)));
	}

	/**
	* function gettemplate($template,$endung="htm")
	* @description : get Inforamtion from a template File 
	* @param string template
	* @param string extension ( means the extension of the Template files, eg. .htm )
	* @return Sting
	*/
	function getLangArray($DP_Lang) {
		$array_tmp = file('.'.DATEPLANER_ROOT_DIR.'/lang/dp_'.$DP_Lang.'.lang');
		foreach($array_tmp as $v)
		{
			if ((substr(trim($v),0,13)=='dateplaner#:#') && (substr_count($v,'#:#')>=2))
			{//Line mustn't start with a ';' and must contain at least one '=' symbol.
				$pos		= strpos($v, '#:#', '13');
				$offset1	= strpos($v, '#:#', '13')-13;
				$offset2	= strpos($v, '###', '13')-$offset1-16;
				if($offset2 != (-$offset1-16)) {
					$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3,$offset2));
				}
				else {
					$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3));
				}
			}
		}
		unset($array_tmp);

		return $DP_language;
	}

	/**
	* function setToolTip($starttime, $endtime, $shortext, $text, $id)
	* @description : set a mouse over tooltip to dates 
	* @param string starttime
	* @param string endtime 
	* @param string shortext
	* @param string text 
	* @param int id 
	* @return Sting float
	*/
	function setToolTip($starttime, $endtime, $shortext, $text, $id ) {
		$text = str_replace("\r\n","<br>" , $text);
		$headerText = $starttime.' -  '.$endtime.' ['.$shortext.']';
		$float= "<div id='$id' style='position:absolute; top:400px; left:0px; width:250; visibility:hidden; z-index:1; background-color:white'>";
		$float.= '
<table border="0" cellpadding="2" cellspacing="2" style="border-collapse: collapse"  width="100%" height="100%">
  <tr>
    <td width="100%" bgcolor="#000080"><b><font color="#FFFFFF">'.$headerText.'</font></b> </td>
  </tr>
  <tr>
    <td width="100%" height="100%" valign="top" bgcolor="#FFFFFF" ><font color="#000000">'.$text.'</font></td>
  </tr>
</table>
						</div>';
		Return $float;

	}


} // end class
?>