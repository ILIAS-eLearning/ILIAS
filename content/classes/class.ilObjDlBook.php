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
* Class ilObjDlBook
*
* @author Databay AG <ay@databay.de>
* @version $Id$
*
* @package content
*/
class ilObjDlBook extends ilObjContentObject
{
	var $bib_obj;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjDlBook($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "dbk";
		parent::ilObjContentObject($a_id, $a_call_by_reference);
	}

	
	/**
	*	init bib object (contains all bib item data)
	*/
	function initBibItemObject()
	{
		include_once("content/classes/class.ilBibItem.php");

		$this->bib_obj =& new ilBibItem($this);
		$this->bib_obj->read();

		return true;
	}


	/**
	*	exports the digi-lib-object into a xml structure
	*/
	function export($ref_id) 
	{

		$export_dir = $this->getExportDirectory();
		if ($export_dir==false) 
		{
			$this->createExportDirectory();
			
			$export_dir = $this->getExportDirectory();
			if ($export_dir==false) 
			{
				$this->ilias->raiseError("Creation of Export-Directory failed.",$this->ilias->error_obj->FATAL);
			}
		}
		
		include_once("./classes/class.ilNestedSetXML.php");
		
		// anhand der ref_id die obj_id ermitteln.
		$query = "SELECT * FROM object_reference,object_data WHERE object_reference.ref_id='".$ref_id."' AND object_reference.obj_id=object_data.obj_id ";
        $result = $this->ilias->db->query($query);

		$objRow = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$obj_id = $objRow["obj_id"];

		// Jetzt alle lm_data anhand der obj_id auslesen.
		$query = "SELECT * FROM lm_data WHERE lm_id='".$obj_id."' ";
        $result = $this->ilias->db->query($query);

		$xml = "<?xml version=\"1.0\"?>\n<!DOCTYPE ContentObject SYSTEM \"ilias_co.dtd\">\n<ContentObject Type=\"LibObject\">\n";
		
		$nested = new ilNestedSetXML();
		$co = $nested->export($obj_id,"dbk");
		$xml .= $co."\n";

		$inStruture = false;
		while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) ) 
		{
			// vd($row);
			
			// StructureObject
			if ($row["type"] == "st") 
			{
				
				if ($inStructure) 
				{
					$xml .= "</StructureObject>\n";
				}
				
				$xml .= "<StructureObject>\n";
				$inStructure = true;
				
				$nested = new ilNestedSetXML();
				$xml .= $nested->export($row["obj_id"],"st");
				$xml .= "\n";
				
				
			}
			
			//PageObject
			if ($row["type"] == "pg") 
			{
				
				$query = "SELECT * FROM page_object WHERE page_id='".$row["obj_id"]."' ";
				$result2 = $this->ilias->db->query($query);
		
				$row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
				
				$PO = $row2["content"]."\n";
				
				$nested = new ilNestedSetXML();
				$mdxml = $nested->export($row["obj_id"],"pg");

				$PO = str_replace("<PageObject>","<PageObject>\n$mdxml\n",$PO);
				
				$xml .= $PO;
				
			}
			
			
		}
		
		if ($inStructure) 
		{
			$xml .= "\n</StructureObject>\n";
		}
	
		$nested = new ilNestedSetXML();
		$bib = $nested->export($obj_id,"bib");
		
		$xml .= $bib."\n";
	
		$xml .= "</ContentObject>";		
		
		// TODO: Handle file-output
		
		
		
		/*
		echo "<pre>";
		echo htmlspecialchars($xml);
		echo "</pre>";
		*/
		$fileName = $objRow["title"];
		$fileName = str_replace(" ","_",$fileName);
		
		if (!file_exists($export_dir."/".$fileName)) 
		{
			@mkdir($export_dir."/".$fileName);
			@chmod($export_dir."/".$fileName,0755);
		}
		
		
		$fp = fopen($export_dir."/".$fileName."/".$fileName.".xml","wb");
		fwrite($fp,$xml);
		fclose($fp);
		
		ilUtil::zip($export_dir."/".$fileName, $export_dir."/".$fileName.".zip");
		
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
		header ("Content-length:".(string)(strlen ($xml)) );
		
		readfile( $export_dir."/".$fileName.".zip" );
		
	}
	/**
	 * STATIC METHOD
	 * search for dbk data. This method is called from class.ilSearch
	 * @param	object reference on object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$search_obj,$a_search_in)
	{
		switch($a_search_in)
		{
			case 'meta':
				// FILTER ALL DBK OBJECTS
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("like",array("xv.tag_value"));


				$query = "SELECT DISTINCT(r.ref_id) AS ref_id FROM object_reference AS r ".
					"INNER JOIN object_data AS o ON r.obj_id=o.obj_id ".
					"INNER JOIN lm_data AS l ON l.lm_id = o.obj_id ".
					"INNER JOIN xmlnestedset AS xm ON (xm.ns_book_fk = l.obj_id OR xm.ns_type IN ('dbk','bib')) ".
					"INNER JOIN xmlvalue AS xv ON xm.ns_tag_fk = xv.tag_fk ".
					$where.
					$in.
					"AND o.type = 'dbk'";
				$res = $search_obj->ilias->db->query($query);
				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]		=  $row->ref_id;
					$result[$counter]["link"]	=  "content/lm_presentation.php?ref_id=".$row->ref_id;
					$result[$counter]["target"]	=  "_top";
					
					++$counter;
				}
				break;

			case 'content':
				$in		= $search_obj->getInStatement("ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("pg.content"));

				$query = "SELECT DISTINCT(r.ref_id) AS ref_id ,pg.page_id AS page_id FROM page_object AS pg ".
					"INNER JOIN object_reference AS r ON pg.parent_id = r.obj_id ".
					$where.
					$in.
					"AND pg.parent_type = 'dbk' ";

				$res = $search_obj->ilias->db->query($query);
				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]		= $row->ref_id;
					$result[$counter]["page_id"] = $row->page_id;
					$result[$counter]["link"]	= "content/lm_presentation.php?ref_id=".$row->ref_id;
					$result[$counter]["target"]	= "_top";
					
					++$counter;
				}
				break;
		}
		return $result ? $result : array();
	}

	
	
} // END class.ilObjDlBook

?>
