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

require_once("./assessment/classes/class.ilObjTest.php");

/**
* Export class for questionpools
*
* @author Helmut Schottmüller <hschottm@tzi.de>
*
* @version $Id$
*
* @package assessment
*/
class ilQuestionpoolExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $qpl_obj;		// questionpool object
	var $questions; // array with question ids to export
	var $inst_id;		// installation id
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function ilQuestionpoolExport(&$a_qpl_obj, $a_mode = "xml", $array_questions)
	{
		global $ilErr, $ilDB, $ilias;

		$this->qpl_obj =& $a_qpl_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$this->inst_id = IL_INST_ID;
		$this->questions = $array_questions;
		$date = time();
		switch($this->mode)
		{
			default:
				$this->export_dir = $this->qpl_obj->getExportDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					"qpl"."_".$this->qpl_obj->getId();
				$this->filename = $this->subdir.".xml";
				$this->qti_filename = $date."__".$this->inst_id."__".
					"qti"."_".$this->qpl_obj->getId().".xml";
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
			default:
				return $this->buildExportFileXML();
				break;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportFileXML()
	{
		global $ilBench;

		$ilBench->start("QuestionpoolExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Test Questionpool ".
			$this->qpl_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		$this->qpl_obj->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->qpl_obj->getExportDirectory();
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");
		
		// write qti file
		$qti_file = fopen($this->export_dir."/".$this->subdir."/".$this->qti_filename, "w");
		fwrite($qti_file, $this->qpl_obj->to_xml($this->questions));
		fclose($qti_file);

		// get xml content
		$ilBench->start("QuestionpoolExport", "buildExportFile_getXML");
		$this->qpl_obj->exportPagesXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog, $this->questions);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_getXML");

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$ilBench->start("QuestionpoolExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_dumpToFile");

		// zip the file
		$ilBench->start("QuestionpoolExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("QuestionpoolExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("QuestionpoolExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}


}

?>
