<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Factory for virus scanner class(es)
*  
* @author	Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @package ilias-core
*/

class ilVirusScannerFactory
{
	/**
	* Constructor
	* @access	public
	* @param	string virus scanner command
	*/
	function &_getInstance()
	{
		// create global virus scanner class instance 
		switch (IL_VIRUS_SCANNER)
		{
			case "Sophos":
				require_once("classes/class.ilVirusScannerSophos.php");
				$vs = new ilVirusScannerSophos(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
				return $vs;
				break;
				
			case "AntiVir":
				require_once("classes/class.ilVirusScannerAntiVir.php");
				$vs = new ilVirusScannerAntiVir(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
				return $vs;
				break;
				
			default:
				return null;
				break;
		}
	}

}
?>