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
		$query = "SELECT * FROM object_reference,object_data WHERE object_reference.ref_id= ".
			$ilDB->quote($this->getRefId())." AND object_reference.obj_id=object_data.obj_id ";
        $result = $this->ilias->db->query($query);

		$objRow = $result->fetchRow(DB_FETCHMODE_ASSOC);

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
		
		$query = "REPLACE INTO dbk_translations ".
			"SET id = ".$ilDB->quote($this->ref_id).", ".
			"tr_id = ".$ilDB->quote($a_ref_id)." ";
		$res = $this->ilias->db->query($query);

		$query = "REPLACE INTO dbk_translations ".
			"SET id = ".$ilDB->quote($a_ref_id).", ".
			"tr_id = ".$ilDB->quote($this->ref_id)." ";
		$res = $this->ilias->db->query($query);

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

		$query = "DELETE FROM dbk_translations ".
			"WHERE id = ".$ilDB->quote($this->ref_id)." ".
			"AND tr_id = ".$ilDB->quote($a_ref_id)." ";

		$res = $this->ilias->db->query($query);

		$query = "DELETE FROM dbk_translations ".
			"WHERE id = ".$ilDB->quote($a_ref_id)." ".
			"AND tr_id = ".$ilDB->quote($this->ref_id)." ";

		$res = $this->ilias->db->query($query);

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
	/**
	 * STATIC METHOD
	 * search for dbk data. This method is called from class.ilSearch
	 * @param	object reference on object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$search_obj,$a_search_in)
	{
		global $ilBench;

		switch($a_search_in)
		{
			case 'meta':
				// FILTER ALL DBK OBJECTS
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("xv.tag_value"));

				/* very slow on mysql < 4.0.18
				$query = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"lm_data AS l,xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ((o.obj_id=l.lm_id AND xm.ns_book_fk=l.obj_id) OR ".
					"(o.obj_id=xm.ns_book_fk AND xm.ns_type IN ('dbk','bib'))) ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'dbk'"; */

				$query1 = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ( ".
					"(o.obj_id=xm.ns_book_fk AND xm.ns_type IN ('dbk','bib'))) ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'dbk'";

				// BEGINNING SELECT WITH SEARCH RESULTS IS MUCH FASTER
				$query1 = "SELECT DISTINCT(r.ref_id) as ref_id FROM xmlvalue AS xv ".
					"LEFT JOIN xmlnestedset AS xm ON xm.ns_tag_fk=xv.tag_fk ".
					"LEFT JOIN object_data AS o ON o.obj_id = xm.ns_book_fk ".
					"LEFT JOIN object_reference AS r ON o.obj_id = r.obj_id ".
					$where.
					$in.
					" AND o.type = 'dbk' AND xm.ns_type IN ('dbk','bib')";

				$query2 = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"lm_data AS l,xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ((o.obj_id=l.lm_id AND xm.ns_book_fk=l.obj_id) ".
					") ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'dbk'";

				$query2 = "SELECT DISTINCT(r.ref_id) as ref_id FROM xmlvalue AS xv ".
					" LEFT JOIN xmlnestedset AS xm ON xm.ns_tag_fk = xv.tag_fk ".
					" LEFT JOIN lm_data AS l ON l.obj_id = xm.ns_book_fk ".
					" LEFT JOIN object_data AS o ON o.obj_id = l.lm_id ".
					" LEFT JOIN object_reference AS r ON r.obj_id = o.obj_id ".
					$where.
					$in.
					"AND o.type = 'dbk'";
					

				/*
				$query = "SELECT DISTINCT(r.ref_id) AS ref_id FROM object_reference AS r ".
					"INNER JOIN object_data AS o ON r.obj_id=o.obj_id ".
					"INNER JOIN lm_data AS l ON l.lm_id = o.obj_id ".
					"INNER JOIN xmlnestedset AS xm ON (xm.ns_book_fk = l.obj_id OR xm.ns_type IN ('dbk','bib')) ".
					"INNER JOIN xmlvalue AS xv ON xm.ns_tag_fk = xv.tag_fk ".
					$where.
					$in.
					"AND o.type = 'dbk'";
				*/

				$ilBench->start("Search", "ilObjDlBook_search_meta");
				$res1 = $search_obj->ilias->db->query($query1);
				$res2 = $search_obj->ilias->db->query($query2);
				$ilBench->stop("Search", "ilObjDlBook_search_meta");

				$counter = 0;
				$ids = array();
				while($row = $res1->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$ids[] = $row->ref_id;
					$result[$counter]["id"]		=  $row->ref_id;

					++$counter;
				}
				while($row = $res2->fetchRow(DB_FETCHMODE_OBJECT))
				{
					if(in_array($row->ref_id,$ids))
					{
						continue;
					}
					$result[$counter]["id"]		=  $row->ref_id;

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

				$ilBench->start("Search", "ilObjDlBook_search_content");
				$res = $search_obj->ilias->db->query($query);
				$ilBench->stop("Search", "ilObjDlBook_search_content");

				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]		= $row->ref_id;
					$result[$counter]["page_id"] = $row->page_id;

					++$counter;
				}
				break;
		}
		return $result ? $result : array();
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
			"WHERE id = ".$ilDB->quote($this->ref_id)." ";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_tr_ids[] = $row->tr_id;
		}
		return $this->tr_ids = $tmp_tr_ids ? $tmp_tr_ids : array();
	}
} // END class.ilObjDlBook

?>
