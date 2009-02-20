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
* Interface to the AntiVir virus protector
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilVirusScanner
*/

require_once "class.ilVirusScanner.php";

class ilVirusScannerAntiVir extends ilVirusScanner
{
	/**
	* Constructor
	* @access	public
	* @param	string virus scanner command
	*/
	function ilVirusScannerAntivir($a_scancommand, $a_cleancommand)
	{
		$this->ilVirusScanner($a_scancommand, $a_cleancommand);
		$this->type = "antivir";
		$this->scanZipFiles = true;
	}

	/**
	* scan a file for viruses
	*
	* @param	string	path of file to check
	* @param	string	original name of the file to ckeck
	* @return   string  virus message (empty if not infected)
	* @access	public
	*/
	function scanFile($a_filepath, $a_origname = "")
	{
		// This function should:
		// - call the external scanner for a_filepath
		// - set scanFilePath to a_filepath
		// - set scanFileOrigName to a_origname
		// - set scanFileIsInfected according the scan result
		// - set scanResult to the scanner output message
		// - call logScanResult() if file is infected
		// - return the scanResult, if file is infected
		// - return an empty string, if file is not infected

		$this->scanFilePath = $a_filepath;
		$this->scanFileOrigName = $a_origname;

		// Call of antivir command
		$cmd = $this->scanCommand . " " . $a_filepath. " ";
		exec($cmd, $out, $ret);
		$this->scanResult = implode("\n", $out);

		// sophie could be called
		if (ereg("ALERT:", $this->scanResult))
		{
			$this->scanFileIsInfected = true;
			$this->logScanResult();
			return $this->scanResult;
		}
		else
		{
			$this->scanFileIsInfected = false;
			return "";
		}

		// antivir has failed (todo)
		$this->log->write("ERROR (Virus Scanner failed): "
						. $this->scanResult
						. "; COMMAMD=" . $cmd);

	}


}
?>
