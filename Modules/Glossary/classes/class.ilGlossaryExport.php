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
		$this->inst_id = $settings["inst_id"];

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

/*
    function exportRekursiv($depth, $left, $right)
	{
		global $ilDB;
		
		// Jetzt alle lm_data anhand der obj_id auslesen.
		$query = "SELECT  *
			FROM lm_tree, lm_data
			WHERE lm_tree.lm_id = ".$ilDB->quote($this->glo_obj->getId())."
			AND   lm_tree.child = lm_data.obj_id
			AND   ( lm_data.type =  'st' OR lm_data.type =  'pg' )
			AND lm_tree.depth = ".$ilDB->quote($depth)."
			AND lm_tree.lft>".$ilDB->quote($left)." and lm_tree.rgt<".$ilDB->quote($right)."
			ORDER BY lm_tree.lft";

        $result = $this->db->query($query);

        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) )
		{
			if ($row["type"] == "st")
			{
				$xml .= "<StructureObject>";

				$nested = new ilNestedSetXML();
				$xml .= $nested->export($row["obj_id"],"st");
				$xml .= "\n";

				$xml .= $this->exportRekursiv($depth+1, $row["lft"], $row["rgt"]);

				$xml .= "</StructureObject>";
			}

			if ($row["type"] == "pg")
			{

                $query = "SELECT * FROM page_object WHERE page_id='".$row["obj_id"]."' ";
				$result2 = $this->db->query($query);

				$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);

				$PO = $row2["content"]."\n";

				if (stristr($PO,"MediaObject"))
				{

					$dom = domxml_open_mem($PO);
					$xpc = xpath_new_context($dom);
					$path = "//MediaObject/MediaAlias";
					$res =& xpath_eval($xpc, $path);
					for($i = 0; $i < count($res->nodeset); $i++)
					{
						$id_arr = explode("_", $res->nodeset[$i]->get_attribute("OriginId"));
						$mob_id = $id_arr[count($id_arr) - 1];
						$this->mob_ids[$mob_id] = true;
					}
				}

				$nested = new ilNestedSetXML();
				$mdxml = $nested->export($row["obj_id"],"pg");

				$PO = str_replace("<PageObject>","<PageObject>\n$mdxml\n",$PO);

				$xml .= $PO;

			}
		}
		return($xml);
	}
*/
	
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

		require_once("classes/class.ilXmlWriter.php");

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

		$ilBench->start("GlossaryExport", "buildHTMLPackage");

		// create directories
		$this->glo_obj->createExportDirectory("html");

		// get Log File
		$expDir = $this->glo_obj->getExportDirectory();
		/*
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");*/

		// get xml content
		$ilBench->start("GlossaryExport", "buildExportFile_getHTML");
		$this->glo_obj->exportHTML($this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("GlossaryExport", "buildExportFile_getHTML");

		$ilBench->stop("GlossaryExport", "buildHTMLPackage");
	}

}

?>
