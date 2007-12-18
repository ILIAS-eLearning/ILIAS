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
* Include file Output
*
* this file gerates an output
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
*/



/**
*	void function dooutput($template)
*	retuns Output
*	@param int template
*	
*/
function doOutput($template) {
		global $templatefolder, $actualtemplate ,$PAGETITLE, $DP_StyleFname, $DP_Style, $DP_language, $_SESSION;
			
		if($_SESSION[DP_JSscript] != "1" ) {
			$jsscriptIn			= "<!-- ";
			$jsscriptOut		= " -->";
			$template	= str_replace("{jsscriptIn}","$jsscriptIn",$template);
			$template	= str_replace("{jsscriptOut}","$jsscriptOut",$template);
		}else {
			$template	= str_replace("{jsscriptIn}","",$template);
			$template	= str_replace("{jsscriptOut}","",$template);
		}

		$template	= str_replace("{TITLE}","$DP_language[title]",$template);
		$template	= str_replace("{PAGETITLE}","$PAGETITLE",$template);
		
		$template	= str_replace("{DATEPLANER_ROOT_DIR}",DATEPLANER_ROOT_DIR,$template);
		$template	= str_replace("{ILIAS_HTTP_PATH}",ILIAS_HTTP_PATH,$template);

		$css_ilias	= '<LINK href="'.$DP_StyleFname.'" type="text/css" rel="stylesheet" />';
        $template	= str_replace("{css_ilias}","$css_ilias",$template);
		
		$template	= str_replace("{css_ilias_cont}",
			'<LINK href="'.ilUtil::getNewContentStyleSheetLocation().'" type="text/css" rel="stylesheet" />',
			$template);

		//$template	= str_replace("{p3p_link}",
		//	'<LINK href="'.ilUtil::getP3PLocation().'" rel="P3Pv1" />',
		//	$template);
		if(@!file_exists('.'.DATEPLANER_ROOT_DIR.$templatefolder.'/'.$actualtemplate.'/'.$DP_Style.'.css'))
		{
			$css		= '<LINK href=".'.DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/".$DP_Style.'.css" type="text/css" rel="stylesheet" />';
			$template	= str_replace("{css}","$css",$template);
		}
		else
		{
			$css		= '<LINK href=".'.DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate.'/delos.css" type="text/css" rel="stylesheet" />';
			$template	= str_replace("{css}","$css",$template);
		}

	    echo $template;
	}
?>