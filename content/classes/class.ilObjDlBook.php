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
	*	exports the digi-lib-object into a xml structure
	*/
	function export($ref_id) 
	{

		include_once("./classes/class.ilNestedSetXML.php");
		
		// anhand der ref_id die obj_id ermitteln.
		$query = "SELECT * FROM object_reference WHERE ref_id='".$ref_id."' ";
        $result = $this->ilias->db->query($query);

		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$obj_id = $row["obj_id"];

		// Jetzt alle lm_data anhand der obj_id auslesen.
		$query = "SELECT * FROM lm_data WHERE lm_id='".$obj_id."' ";
        $result = $this->ilias->db->query($query);

		$xml = "<?xml version=\"1.0\"?>\n<!DOCTYPE ContentObject SYSTEM \"ilias_co.dtd\">\n<ContentObject Type=\"LibObject\">\n";
		
		$nested = new ilNestedSetXML();
		$co = $nested->export($obj_id,"dbk");
		$xml .= $co."\n";

		$inStruture = false;
		while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) ) {
			// vd($row);
			
			// StructureObject
			if ($row["type"] == "st") {
				
				if ($inStructure) {
					$xml .= "</StructureObject>\n";
				}
				
				$xml .= "<StructureObject>\n";
				$inStructure = true;
				
				$nested = new ilNestedSetXML();
				$xml .= $nested->export($row["obj_id"],"st");
				$xml .= "\n";
				
				
			}
			
			//PageObject
			if ($row["type"] == "pg") {
				
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
		
		if ($inStructure) {
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
		
	}

	
	
} // END class.ilObjDlBook

?>
