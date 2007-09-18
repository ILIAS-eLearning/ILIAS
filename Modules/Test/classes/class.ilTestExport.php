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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Export class for tests
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilTestExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $test_obj;		// test object
	var $inst_id;		// installation id
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function ilTestExport(&$a_test_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias;

		$this->test_obj =& $a_test_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$this->inst_id = IL_INST_ID;

		$date = time();
		$this->export_dir = $this->test_obj->getExportDirectory();
		switch($this->mode)
		{
			case "results":
				$this->subdir = $date."__".$this->inst_id."__".
					"test__results__".$this->test_obj->getId();
				break;
			default:
				$this->subdir = $date."__".$this->inst_id."__".
					"test"."__".$this->test_obj->getId();
				$this->filename = $this->subdir.".xml";
				$this->qti_filename = $date."__".$this->inst_id."__".
					"qti"."__".$this->test_obj->getId().".xml";
				break;
		}
		$this->filename = $this->subdir.".".$this->getExtension();
	}

	function getExtension () {
		switch ($this->mode) {
			case "results":
				return "csv"; break;
			default:
			 	return "xml"; break;
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
			case "results":
				return $this->buildExportResultFile();
				break;
			default:
				return $this->buildExportFileXML();
				break;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportResultFile()
	{
		global $ilBench;
		global $log;

		//get data
		$participants = $this->test_obj->getTestParticipants();
		if (!count($participants)) 
		{
			return;
		}
		//get Log File
		$expDir = $this->test_obj->getExportDirectory();
		$expLog = &$log;
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Of Results");

		// make_directories
		$this->test_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir);

		$data =  $this->test_obj->getAllTestResults($participants);

		$file = fopen($this->export_dir."/".$this->filename, "w");
		foreach ($data as $row) {
			fwrite($file, join (";",$row)."\n");
		}
		fclose($file);

		// end
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export of Results");

		return $this->export_dir."/".$this->filename;
	}


	/**
	* build xml export file
	*/
	function buildExportFileXML()
	{
		global $ilBench;

		$ilBench->start("TestExport", "buildExportFile");

		include_once("./classes/class.ilXmlWriter.php");
		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Test ".
			$this->test_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		$this->test_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->test_obj->getExportDirectory();
		include_once "./Services/Logging/classes/class.ilLog.php";
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// write qti file
		$qti_file = fopen($this->export_dir."/".$this->subdir."/".$this->qti_filename, "w");
		fwrite($qti_file, $this->test_obj->toXML());
		fclose($qti_file);

		// get xml content
		$ilBench->start("TestExport", "buildExportFile_getXML");
		$this->test_obj->exportPagesXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("TestExport", "buildExportFile_getXML");

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$ilBench->start("TestExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("TestExport", "buildExportFile_dumpToFile");

			// add media objects which were added with tiny mce
		$ilBench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
		$this->exportXHTMLMediaObjects($this->export_dir."/".$this->subdir);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

		// zip the file
		$ilBench->start("TestExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("TestExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("TestExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	function exportXHTMLMediaObjects($a_export_dir)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->test_obj->getId());
		foreach ($mobs as $mob)
		{
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->exportFiles($a_export_dir);
			unset($mob_obj);
		}
		foreach ($this->test_obj->questions as $question_id)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
			foreach ($mobs as $mob)
			{
				$mob_obj =& new ilObjMediaObject($mob);
				$mob_obj->exportFiles($a_export_dir);
				unset($mob_obj);
			}
		}
	}

}

?>
