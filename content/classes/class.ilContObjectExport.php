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

require_once("content/classes/class.ilObjContentObject.php");

/**
* Export class for content objects
*
* @author Databay AG <ay@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @package content
*/
class ilContObjectExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $cont_obj;		// content object (learning module | digilib book)
	var $inst_id;		// installation id

	/**
	* Constructor
	* @access	public
	*/
	function ilContObjectExport(&$a_cont_obj)
	{
		global $ilErr, $ilDB, $ilias;

		$this->cont_obj =& $a_cont_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;

		$settings = $this->ilias->getAllSettings();
		$this->inst_id = $settings["inst_id"];

	}

	function getInstId()
	{
		return $this->inst_id;
	}

    function exportRekursiv($depth, $left, $right)
	{
		// Jetzt alle lm_data anhand der obj_id auslesen.
		$query = "SELECT  *
			FROM lm_tree, lm_data
			WHERE lm_tree.lm_id = ".$this->cont_obj->getId()."
			AND   lm_tree.child = lm_data.obj_id
			AND   ( lm_data.type =  'st' OR lm_data.type =  'pg' )
			AND lm_tree.depth = $depth
			AND lm_tree.lft>$left and lm_tree.rgt<$right
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


	/**
	*	exports the digi-lib-object into a xml structure
	*/
	function export()
	{
		include_once("./classes/class.ilNestedSetXML.php");

        $this->mob_ids = array();

		// ------------------------------------------------------
        // start xml-String
		// ------------------------------------------------------
		$xml = "<?xml version=\"1.0\"?><!DOCTYPE ContentObject SYSTEM \"ilias_co.dtd\">\n<ContentObject Type=\"LibObject\">";

		// ------------------------------------------------------
        // get global meta-data
		// ------------------------------------------------------
		$nested = new ilNestedSetXML();
		$xml .= $nested->export($this->cont_obj->getId(),
			$this->cont_obj->getType());

		// ------------------------------------------------------
        // get all book-xml-data recursiv
		// ------------------------------------------------------

		$query = "SELECT  *
			FROM lm_tree, lm_data
			WHERE lm_tree.lm_id = ".$this->cont_obj->getId()."
			AND   lm_tree.child = lm_data.obj_id
			AND   ( lm_data.type =  'du' )
			AND lm_tree.depth = 1
			ORDER BY lm_tree.lft";
		$result = $this->db->query($query);
		$treeData = $result->fetchRow(DB_FETCHMODE_ASSOC);

		$xml .= $this->exportRekursiv(2, $treeData["lft"], $treeData["rgt"]);

        // get/create export-directory
		$export_dir = $this->cont_obj->getExportDirectory();

		if ($export_dir == false)
		{
			$this->cont_obj->createExportDirectory();

			$export_dir = $this->cont_obj->getExportDirectory();
			if ($export_dir == false)
			{
				$this->ilias->raiseError("Creation of Export-Directory failed.", $this->err->FATAL);
			}
		}

		// ------------------------------------------------------
        // get mediaobject-xml-data
		// ------------------------------------------------------
		$mob_ids = $this->mob_ids;
		if (is_array($mob_ids) && count($mob_ids)>0)
		{
			reset ($mob_ids);
			while (list ($key, $val) = each ($mob_ids))
			{
				$xml .= "<MediaObject>";

				$query = "SELECT * FROM media_item WHERE mob_id='".$key."' ";
				//vd($query);
				$first = true;
				$result = $this->db->query($query);
				while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) )
				{
					if($first)
					{
						//vd($row[purpose]);
						$nested = new ilNestedSetXML();
						$metaxml = $nested->export($key, "mob");
						$metaxml = preg_replace("/Entry=\"(.*?)\"/","Entry=\"il__mob_".$key."\"",$metaxml);

						$metaxml2 = "<Technical>";
						$metaxml2 .= "<Format>".$row["format"]."</Format>";
						$metaxml2 .= "<Size>14559</Size>";
						$metaxml2 .= "<Location Type=\"".$row["location_type"]."\">".$row["location"]."</Location>";
						$metaxml2 .= "</Technical>";

						$metaxml = str_replace("</MetaData>",$metaxml2."</MetaData>",$metaxml);

						$xml .= $metaxml;

						$first = false;
					}

					$xml .= "<MediaItem Purpose=\"".$row["purpose"]."\">";
					$xml .= "<Location Type=\"".$row["location_type"]."\">".$row["location"]."</Location>";
					$xml .= "<Format>".$row["format"]."</Format>";
					$xml .= "<Layout Width=\"".$row["width"]."\" Height=\"".$row["height"]."\"/>";
					$xml .= "</MediaItem>";

				}
				$xml .= "</MediaObject>";
			}
		}


		// ------------------------------------------------------
		// get bib-xml-data
		// ------------------------------------------------------
		$nested = new ilNestedSetXML();
		$bib = $nested->export($this->cont_obj->getId(), "bib");

		$xml .= $bib;

		// ------------------------------------------------------
        // xml-ending
		// ------------------------------------------------------
		$xml .= "</ContentObject>";

		// ------------------------------------------------------
        // filename and directory-creation
		// ------------------------------------------------------
		// get timestamp for dir and file names
		$date = time();

		// set dir and file names (format: <timestamp>__<inst>__le_<id>__lm/)
		//$this->dir = $this->targetDir.$date."__".$this->luInst."__le_".$this->luId."__lm/";
		//$this->file = $this->dir.$date."__".$this->luInst."__".
		//	$this->cont_obj->getType()."_".$this->cont_obj->getId().xml";

		$fileName = $date."__".$this->inst_id."__".
			$this->cont_obj->getType()."_".$this->cont_obj->getId();

		if (!file_exists($export_dir."/".$fileName))
		{
			@mkdir($export_dir."/".$fileName);
			@chmod($export_dir."/".$fileName,0755);
		}
		if (!file_exists($export_dir."/".$fileName."/objects"))
		{
			@mkdir($export_dir."/".$fileName."/objects");
			@chmod($export_dir."/".$fileName."/objects",0755);
		}

		// ------------------------------------------------------
        // copy mob-files
		// ------------------------------------------------------
		$mob_ids = $this->mob_ids;
		if (is_array($mob_ids) && count($mob_ids)>0)
		{
			reset ($mob_ids);
			while (list ($key, $val) = each ($mob_ids))
			{

				if (!file_exists($export_dir."/".$fileName."/objects/mm".$key))
				{
					@mkdir($export_dir."/".$fileName."/objects/mm".$key);
					@chmod($export_dir."/".$fileName."/objects/mm".$key,0755);
				}

				$mobdir = "./data/mobs/mm_".$key;
				ilUtil::rCopy($mobdir, $export_dir."/".$fileName."/objects/mm".$key);
			}
		}

		// ------------------------------------------------------
        // save xml-file
		// ------------------------------------------------------
		$fp = fopen($export_dir."/".$fileName."/".$fileName.".xml","wb");
		fwrite($fp,$xml);
		fclose($fp);

		// ------------------------------------------------------
        // zip all files
		// ------------------------------------------------------
		ilUtil::zip($export_dir."/".$fileName, $export_dir."/".$fileName.".zip");

		// ------------------------------------------------------
        // deliver files
		// ------------------------------------------------------
		header("Expires: Mon, 1 Jan 1990 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-type: application/octet-stream");
		if (stristr(" ".$GLOBALS["HTTP_SERVER_VARS"]["HTTP_USER_AGENT"],"MSIE") )
		{
			header ("Content-Disposition: attachment; filename=" . $fileName.".zip");
		}
		else
		{
			header ("Content-Disposition: inline; filename=".$fileName.".zip" );
		}
		header ("Content-length:".(string)( filesize($export_dir."/".$fileName.".zip")) );

		readfile( $export_dir."/".$fileName.".zip" );

	}

}


function buildExportFile()
{
	require_once("classes/class.ilXmlWriter.php");

	$this->xml = new ilXmlWriter;

	// set dtd definition
	$this->xml->xmlSetDtdDef("<!DOCTYPE LearningModule SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

	// set generated comment
	$this->xml->xmlSetGenCmt("Export of ILIAS Content Module ".
		$this->cont_obj->getId()." of installation ".$this->inst.".");

	// set xml header
	$this->xml->xmlHeader();

	//
	$this->cont_obj->getXML($this->xml);

	// dump xml document to screen (only for debugging reasons)
	/*
	echo "<PRE>";
	echo htmlentities($this->xml->xmlDumpMem($format));
	echo "</PRE>";
	*/

	// dump xml document to file
	$this->xml->xmlDumpFile($this->file, $format);

	// destroy writer object
	$this->xml->_XmlWriter;
}


?>
