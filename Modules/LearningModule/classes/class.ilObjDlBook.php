<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

/**
* Class ilObjDlBook
*
* @author Databay AG <ay@databay.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
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

		if($a_id)
		{
			$this->readAssignedTranslations();
		}
	}

	
	/**
	*	init bib object (contains all bib item data)
	*/
	function initBibItemObject()
	{
		include_once("./Modules/LearningModule/classes/class.ilBibItem.php");

		$this->bib_obj =& new ilBibItem($this);
		$this->bib_obj->read();

		return true;
	}

    /**
    *   export lm_data-table to xml-structure
    *
    *   @param  integer obj_id
    *   @param  integer depth
    *   @param  integer left    left border of nested-set-structure
    *   @param  integer right   right border of nested-set-structure   
    *   @access public
    *   @return string  xml
    */
    function exportRekursiv($obj_id, $depth, $left, $right)
	{
		global $ilDB;
		
		// Jetzt alle lm_data anhand der obj_id auslesen.
		$query = "SELECT  *
                  FROM lm_tree, lm_data
                  WHERE lm_tree.lm_id = ".$ilDB->quote($obj_id)." 
                  AND   lm_tree.child = lm_data.obj_id 
                  AND   ( lm_data.type =  'st' OR lm_data.type =  'pg' )
                  AND lm_tree.depth = ".$ilDB->quote($depth)."
                  AND lm_tree.lft > ".$ilDB->quote($left)." and lm_tree.rgt < ".$ilDB->quote($right)."
                  ORDER BY lm_tree.lft";
        $result = $this->ilias->db->query($query);
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) ) 
		{
			if ($row["type"] == "st") 
			{
				$xml .= "<StructureObject>";
                
				$nested = new ilNestedSetXML();
				$xml .= $nested->export($row["obj_id"],"st");
				$xml .= "\n";

                $xml .= $this->exportRekursiv($obj_id, $depth+1, $row["lft"], $row["rgt"]);
                
				$xml .= "</StructureObject>";
			}            

            if ($row["type"] == "pg") 
            {
                
                $query = "SELECT * FROM page_object WHERE page_id= ".$ilDB->quote($row["obj_id"]);
				$result2 = $this->ilias->db->query($query);
		
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
	function export($a_deliver = true)
	{
		global $ilDB;
		
		include_once("./classes/class.ilNestedSetXML.php");
		// ------------------------------------------------------
		// anhand der ref_id die obj_id ermitteln.
		// ------------------------------------------------------
		$objRow["obj_id"] = ilOject::_lookupObjId();
		$objRow["title"] = ilOject::_lookupTitle($objRow["obj_id"]);
		$obj_id = $objRow["obj_id"];

        $this->mob_ids = array();

		// ------------------------------------------------------
        // start xml-String
		// ------------------------------------------------------
		$xml = "<?xml version=\"1.0\"?>\n<!DOCTYPE ContentObject SYSTEM \"ilias_co.dtd\">\n<ContentObject Type=\"LibObject\">\n";

		// ------------------------------------------------------
        // get global meta-data
		// ------------------------------------------------------
		$nested = new ilNestedSetXML();
		$xml .= $nested->export($obj_id,"dbk")."\n";

		// ------------------------------------------------------
        // get all book-xml-data recursiv
		// ------------------------------------------------------

		$query = "SELECT  *
                  FROM lm_tree, lm_data
                  WHERE lm_tree.lm_id = ".$ilDB->quote($obj_id)."
                  AND   lm_tree.child = lm_data.obj_id
                  AND   ( lm_data.type =  'du' )
                  AND lm_tree.depth = 1
                  ORDER BY lm_tree.lft";
        $result = $this->ilias->db->query($query);
        $treeData = $result->fetchRow(DB_FETCHMODE_ASSOC);

        $xml .= $this->exportRekursiv($obj_id,2, $treeData["lft"], $treeData["rgt"]);

		// ------------------------------------------------------
        // get or create export-directory
		// ------------------------------------------------------
		$this->createExportDirectory();
		$export_dir = $this->getExportDirectory();

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

                $query = "SELECT * FROM media_item WHERE mob_id= ".$ilDB->quote($key)." ";
                //vd($query);
                $first = true;
                $result = $this->ilias->db->query($query);
                while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) )
                {
                    if($first) 
                    {
                        //vd($row[purpose]);
                        $nested = new ilNestedSetXML();
                        $metaxml = $nested->export($key,"mob");
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
		$bib = $nested->export($obj_id,"bib");

		$xml .= $bib."\n";

		// ------------------------------------------------------
        // xml-ending
		// ------------------------------------------------------
		$xml .= "</ContentObject>";

		// ------------------------------------------------------
        // filename and directory-creation
		// ------------------------------------------------------
		$fileName = $objRow["title"];
		$fileName = str_replace(" ","_",$fileName);

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

		if($a_deliver)
		{
			ilUtil::deliverFile($export_dir."/".$fileName.".zip",$fileName);
		}
		else
		{
			return $export_dir."/".$fileName.".zip";
		}
		/*
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
		*/

	}


	function addTranslation($a_ref_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM dbk_translations ".
			" WHERE id = ".$ilDB->quote($this->ref_id, "integer")." ".
			" AND tr_id = ".$ilDB->quote($a_ref_id, "integer"));
		
		$ilDB->manipulate("INSERT INTO dbk_translations (id, tr_id) VALUES ".
			"(".$ilDB->quote($this->ref_id, "integer").", ".
			"".$ilDB->quote($a_ref_id, "integer").")");

		$ilDB->manipulate("DELETE FROM dbk_translations ".
			" WHERE id = ".$ilDB->quote($a_ref_id, "integer")." ".
			" AND tr_id = ".$ilDB->quote($this->ref_id, "integer"));

		$ilDB->manipulate("INSERT INTO dbk_translations (id, tr_id) VALUES ".
			"(".$ilDB->quote($a_ref_id, "integer").", ".
			"".$ilDB->quote($this->ref_id, "integer").")");

		// UPDATE MEMBER VARIABLE
		$this->readAssignedTranslations();

		return true;
	}

	function addTranslations($a_arr_ref_id)
	{
		if(!is_array($a_arr_ref_id))
		{
			return false;
		}
		foreach($a_arr_ref_id as $ref_id)
		{
			$this->addTranslation($ref_id);
		}
		return true;
	}
	function deleteTranslation($a_ref_id)
	{
		global $ilDB;
		
		if(!$a_ref_id)
		{
			return false;
		}

		$ilDB->manipulate("DELETE FROM dbk_translations ".
			"WHERE id = ".$ilDB->quote($this->ref_id, "integer")." ".
			"AND tr_id = ".$ilDB->quote($a_ref_id, "integer"));

		$ilDB->manipulate("DELETE FROM dbk_translations ".
			"WHERE id = ".$ilDB->quote($a_ref_id, "integer")." ".
			"AND tr_id = ".$ilDB->quote($this->ref_id, "integer"));

		// UPDATE MEMBER VARIABLE
		$this->readAssignedTranslations();

		return true;
	}

	function deleteTranslations($a_arr_ref_id)
	{
		if(!is_array($a_arr_ref_id))
		{
			return false;
		}
		foreach($a_arr_ref_id as $ref_id)
		{
			$this->deleteTranslation($ref_id);
		}
		return true;
	}
	function getTranslations()
	{
		return $this->tr_ids;
	}

	function getXMLZip()
	{
		return $this->export(false);
	}


	// PRIVATE METHODS
	function readAssignedTranslations()
	{
		global $ilDB;
		
		$query = "SELECT tr_id FROM dbk_translations ".
			"WHERE id = ".$ilDB->quote($this->ref_id, "integer");

		$res = $ilDB->query($query);
		while ($row = $ilDB->fetchObject($res))
		{
			$tmp_tr_ids[] = $row->tr_id;
		}
		return $this->tr_ids = $tmp_tr_ids ? $tmp_tr_ids : array();
	}
} // END class.ilObjDlBook

?>
