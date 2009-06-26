<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilSCORM2004Page
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004Page extends ilPageObject
{
	/**
	* Constructor
	* @access	public
	* @param	scorm 2004 page id
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct("sahs", $a_id, $a_old_nr);
		$this->mobs_contained = array();
		$this->files_contained = array();
	}

	/**
	* Set Scorm LM ID.
	*
	* @param	int	$a_scormlmid	Scorm LM ID
	*/
	function setScormLmId($a_scormlmid)
	{
		$this->scormlmid = $a_scormlmid;
	}

	/**
	* Get Scorm LM ID.
	*
	* @return	int	Scorm LM ID
	*/
	function getScormLmId()
	{
		return $this->scormlmid;
	}

	/**
	* Create new scorm 2004
	*/
	function create()
	{
		global $ilDB;
		
		// maybe we need an additional table here?
		
		// create page object
		parent::create();
	}
	
	
	/**
	* Create new scorm 2004 with page-layout
	*/
	function createWithLayoutId($a_layout_id)
	{

		include_once("./Services/Style/classes/class.ilPageLayout.php");

		//get XML Data for Layout		
		$layout_obj = new ilPageLayout($a_layout_id);
		
		parent::setXMLContent($layout_obj->getXMLContent());
		// create page object
		parent::create();
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update($a_validate = true, $a_no_history = false)
	{
		global $ilDB;

		// maybe we need an additional table here?
		
		parent::update($a_validate, $a_no_history);

		return true;
	}
	
	/**
	* Read wiki data
	*/
	function read()
	{
		global $ilDB;
		
		// maybe we need an additional table here?
		
		// get co page
		parent::read();
	}


	/**
	* delete page and al related data	
	*
	* @access	public
	*/
	function delete()
	{
		global $ilDB;
		
		// maybe we need an additional table here?
		
		// delete co page
		parent::delete();
		
		return true;
	}

/**
	* save internal links of page. this method overwrites the 
	* ilpageobject method and adds information on all questions
	* to the db
	*
	* @param	string		xml page code
	*/
	function saveInternalLinks($a_xml)
	{
		global $ilDB;
		
		// *** STEP 1: Standard Processing ***
		
		parent::saveInternalLinks($a_xml);
		
		// *** STEP 2: Save question references of page ***
		
		// delete all reference records
		$stmt = $ilDB->prepareManip("DELETE FROM page_question WHERE page_parent_type = ? ".
			" AND page_id = ?", array("text", "integer"));
		$ilDB->execute($stmt, array($this->getParentType(), $this->getId()));
		
		// save question references of page
		$doc = domxml_open_mem($a_xml);
		$xpc = xpath_new_context($doc);
		$path = "//Question";
		$res = xpath_eval($xpc, $path);
		$q_ids = array();
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$q_ref = $res->nodeset[$i]->get_attribute("QRef");

			$inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
			if (!($inst_id > 0))
			{
				$q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
				if ($q_id > 0)
				{
					$q_ids[$q_id] = $q_id;
				}
			}
		}
		foreach($q_ids as $qid)
		{
			$stmt = $ilDB->prepareManip("INSERT INTO page_question (page_parent_type, page_id, question_id)".
				" VALUES (?,?,?)",
				array("text", "integer", "integer"));
			$ilDB->execute($stmt, array($this->getParentType(), $this->getId(), $qid));
		}
	}
	
	/**
	* Get all questions of a page
	*/
	static function _getQuestionIdsForPage($a_parent_type, $a_page_id)
	{
		global $ilDB;
		
		$stmt = $ilDB->prepare("SELECT * FROM page_question WHERE page_parent_type = ? ".
			" AND page_id = ?",
			array("text", "integer"));
		$res = $ilDB->execute($stmt, array($a_parent_type, $a_page_id));
		$q_ids = array();
		while ($rec = $ilDB->fetchAssoc($res))
		{
			$q_ids[] = $rec["question_id"];
		}
		
		return $q_ids;
	}
	
/**
	 * export page object to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */

	function exportXML(&$a_xml_writer, $a_mode = "normal", $a_inst = 0)
	{
		global $ilBench;

		$attrs = array();
		$a_xml_writer->xmlStartTag("PageObject", $attrs);

		switch ($a_mode)
		{
			case "normal":
				// MetaData
				$ilBench->start("ContentObjectExport", "exportPageObject_XML_Meta");
				$this->exportXMLMetaData($a_xml_writer);
				$ilBench->stop("ContentObjectExport", "exportPageObject_XML_Meta");

				// PageContent
				$ilBench->start("ContentObjectExport", "exportPageObject_XML_PageContent");
				$this->exportXMLPageContent($a_xml_writer, $a_inst);
				$ilBench->stop("ContentObjectExport", "exportPageObject_XML_PageContent");
				break;

			case "alias":
				$attrs = array();
				$attrs["OriginId"] = "il_".$a_inst.
					"_pg_".$this->getId();
				$a_xml_writer->xmlElement("PageAlias", $attrs);
				break;
		}

		$a_xml_writer->xmlEndTag("PageObject");
	}


	/**
	 * export page alias to xml
	 */
	/* todo: this needs to be adopted
	 function _exportXMLAlias(&$a_xml_writer, $a_id, $a_inst = 0)
	 {
		$attrs = array();
		$a_xml_writer->xmlStartTag("PageObject", $attrs);

		$attrs = array();
		$attrs["OriginId"] = "il_".$a_inst.
		"_pg_".$a_id;
		$a_xml_writer->xmlElement("PageAlias", $attrs);

		$a_xml_writer->xmlEndTag("PageObject");
		}
		*/

	/**
	 * export page objects meta data to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getParentId(), $this->getId(), gettype($this));
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}


	/* todo: this needs to be adopted
	 function modifyExportIdentifier($a_tag, $a_param, $a_value)
	 {
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
		$a_value = "il_".IL_INST_ID."_pg_".$this->getId();
		//$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
		}
		*/

	/**
	 * export page objects meta data to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */

	function exportXMLPageContent(&$a_xml_writer, $a_inst = 0)
	{
		$this->buildDom();
		$this->insertInstIntoIDs($a_inst);
		$cont_obj =& $this->getContentObject("pg");
		$this->mobs_contained = $this->collectMediaObjects(false);
		$this->files_contained = $this->collectFileItems();
		$xml = $this->getXMLFromDom(false, false, false, "", true);
		$xml = str_replace("&","&amp;", $xml);
		$a_xml_writer->appendXML($xml);

		$this->freeDom();
	}
	
	
	/**
	* get ids of all media objects within the page
	*
	* note: this method must be called afer exportXMLPageContent
	*/
	function getMediaObjectIds()
	{
		return $this->mobs_contained;
	}

	/**
	* get ids of all file items within the page
	*
	* note: this method must be called afer exportXMLPageContent
	*/
	function getFileItemIds()
	{
		return $this->files_contained;
	}
}
?>
