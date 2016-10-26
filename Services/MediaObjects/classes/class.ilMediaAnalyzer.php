<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

use \GetId3\GetId3Core as GetId3;

/**
* Analyzes media files. Wrapper for getid3 library.
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilMediaAnalyzer
{
	var $file;

	function __construct()
	{
		//include_once("./Services/MediaObjects/lib/getID3-1.9.10/getid3/getid3.php");
		//include_once("./libs/composer/vendor/phansys/getid3/GetId3/GetId3.php");
		//include_once("./Services/MediaObjects/getid3/getid3/getid3.php");
		//$this->getid3 = new GetId3_GetId3();
		$this->getid3 = new GetId3();
	}

	/**
	* Set Full File Path.
	*
	* @param	string	$a_file	Full File Path
	*/
	function setFile($a_file)
	{
		$this->file = $a_file;
	}

	/**
	* Get Full File Path.
	*
	* @return	string	Full File Path
	*/
	function getFile()
	{
		return $this->file;
	}
	
	/**
	* Get PlaytimeString.
	*
	* @return	string	PlaytimeString
	*/
	function getPlaytimeString()
	{
		return $this->file_info["playtime_string"];
	}

	/**
	* Get PlaytimeSeconds.
	*
	* @return	double	PlaytimeSeconds
	*/
	function getPlaytimeSeconds()
	{
		return $this->file_info["playtime_seconds"];
	}

	/**
	* Analyze current file.
	*/
	function analyzeFile()
	{
		$this->file_info = $this->getid3->analyze($this->getFile());
	}

}
?>
