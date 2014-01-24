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

require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");

/**
* Export class for content objects
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilGlossaryExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $glo_obj;		// glossary
	var $inst_id;		// installation id

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryExport(&$a_glo_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias;

		$this->glo_obj =& $a_glo_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();
		// The default '0' is required for the directory structure (smeyer)
		$this->inst_id = $settings["inst_id"] ? $settings['inst_id'] : 0;

		$date = time();
		switch($this->mode)
		{
			case "xml":
				$this->export_dir = $this->glo_obj->getExportDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					$this->glo_obj->getType()."_".$this->glo_obj->getId();
				$this->filename = $this->subdir.".xml";
				break;
		
			case "html":
				$this->export_dir = $this->glo_obj->getExportDirectory("html");
				$this->subdir = $this->glo_obj->getType()."_".$this->glo_obj->getId();
				$this->filename = $this->subdir.".zip";
				break;

		}

	}

	function getInstId()
	{
		return $this->inst_id;
	}
	
	/**
	*   build export file (complete zip file)
	*
	*   @access public
	*   @return
	*/
	function buildExportFile()
	{
		switch ($this->mode)
		{
			case "html":
				return $this->buildExportFileHTML();
				break;

			default:
				return $this->buildExportFileXML();
				break;
		}
	}

	/**
	* build export file (complete zip file)
	*/
	function buildExportFileXML()
	{
		global $ilBench;

		$ilBench->start("GlossaryExport", "buildExportFile");

		require_once("./Services/Xml/classes/class.ilXmlWriter.php");

		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co_3_7.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Glossary ".
			$this->glo_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		$this->glo_obj->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->glo_obj->getExportDirectory();
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
//echo "ContObjExport:".$this->inst_id.":<br>";
		$ilBench->start("GlossaryExport", "buildExportFile_getXML");
		$this->glo_obj->exportXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("GlossaryExport", "buildExportFile_getXML");

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/


		// dump xml document to file
		$ilBench->start("GlossaryExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("GlossaryExport", "buildExportFile_dumpToFile");

		// zip the file
		$ilBench->start("GlossaryExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("GlossaryExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("GlossaryExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	/**
	* build html export file
	*/
	function buildExportFileHTML()
	{
		global $ilBench;

		// create directories
		$this->glo_obj->createExportDirectory("html");

		// get Log File
		$expDir = $this->glo_obj->getExportDirectory();

		// get xml content
		$this->glo_obj->exportHTML($this->export_dir."/".$this->subdir, $expLog);
	}

}

?>
