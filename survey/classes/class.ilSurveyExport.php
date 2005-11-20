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

/**
* Export class for surveys
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
* @package survey
*/
class ilSurveyExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $survey_obj;		// survey object
	var $inst_id;		// installation id
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function ilSurveyExport(&$a_survey_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias;

		$this->survey_obj =& $a_survey_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$this->inst_id = IL_INST_ID;

		$date = time();
		switch($this->mode)
		{
			default:
				$this->export_dir = $this->survey_obj->getExportDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					"survey"."__".$this->survey_obj->getId();
				$this->filename = $this->subdir.".xml";
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

		$ilBench->start("SurveyExport", "buildExportFile");

		// create directories
		$this->survey_obj->createExportDirectory();

		// get Log File
		$expDir = $this->survey_obj->getExportDirectory();
		include_once "./classes/class.ilLog.php";
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");
		// write qti file
		$qti_file = fopen($expDir . "/" . $this->filename, "w");
		fwrite($qti_file, $this->survey_obj->to_xml());
		fclose($qti_file);

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("SurveyExport", "buildExportFile");

		return $this->filename;
	}


}

?>
