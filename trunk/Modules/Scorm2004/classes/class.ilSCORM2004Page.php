<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	protected $glossary_id = 0;
	protected $mobs_contained = array();
	protected $files_contained = array();
	
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "sahs";
	}	

	/**
	 * After constructor
	 *
	 * @param
	 * @return
	 */
	function afterConstructor()
	{
		$this->getPageConfig()->configureByObjectId($this->getParentId());
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
	 * Set glossary id
	 *
	 * @param	int	glossary id
	 */
	function setGlossaryId($a_val)
	{
		$this->glossary_id = $a_val;
	}
	
	/**
	 * Get glossary id
	 *
	 * @return	int	glossary id
	 */
	function getGlossaryId()
	{
		return $this->glossary_id;
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
		include_once("./Services/COPage/classes/class.ilPCFileList.php");
		$this->files_contained = ilPCFileList::collectFileItems($this, $this->getDomDoc());
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
	
	/**
	 * Perform automatic modifications (may be overwritten by sub classes)
	 */
	function performAutomaticModifications()
	{
		if ($this->getGlossaryId() > 0)
		{
			// we fix glossary links here
			$this->buildDom();
			$xpc = xpath_new_context($this->dom);
			$path = "//IntLink[@Type='GlossaryItem']";
			$res =& xpath_eval($xpc, $path);
			for ($i=0; $i < count($res->nodeset); $i++)
			{
				$target = $res->nodeset[$i]->get_attribute("Target");
//echo "<br>".$target;
				$tarr = explode("_", $target);
				$term_id = $tarr[count($tarr) - 1];
				if (is_int(strpos($target, "__")) && $term_id > 0)
				{
					include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
//echo "<br>-".ilGlossaryTerm::_lookGlossaryID($term_id)."-".$this->getGlossaryId()."-";
					if (ilGlossaryTerm::_lookGlossaryID($term_id) != $this->getGlossaryId())
					{
						// copy the glossary term from glossary a to b
						$new_id = ilGlossaryTerm::_copyTerm($term_id, $this->getGlossaryId());
						$res->nodeset[$i]->set_attribute("Target", "il__git_".$new_id);
					}
				}
			}
		}
//exit;
	}
	
}
?>
