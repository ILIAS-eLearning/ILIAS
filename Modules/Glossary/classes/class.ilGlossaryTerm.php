<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilGlossaryTerm
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilGlossaryTerm
{
	var $ilias;
	var $lng;
	var $tpl;

	var $id;
	var $glossary;
	var $term;
	var $language;
	var $glo_id;
	var $import_id;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryTerm($a_id = 0)
	{
		global $lng, $ilias, $tpl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;

		$this->id = $a_id;
		$this->type = "term";
		if ($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* read glossary term data
	*/
	function read()
	{
		global $ilDB;
		
		$q = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$term_set = $ilDB->query($q);
		$term_rec = $ilDB->fetchAssoc($term_set);

		$this->setTerm($term_rec["term"]);
		$this->setImportId($term_rec["import_id"]);
		$this->setLanguage($term_rec["language"]);
		$this->setGlossaryId($term_rec["glo_id"]);

	}

	/**
	* get current term id for import id (static)
	*
	* @param	int		$a_import_id		import id
	*
	* @return	int		id
	*/
	function _getIdForImportId($a_import_id)
	{
		global $ilDB;
		
		if ($a_import_id == "")
		{
			return 0;
		}
		
		$q = "SELECT * FROM glossary_term WHERE import_id = ".
			$ilDB->quote($a_import_id, "text").
			" ORDER BY create_date DESC";
		$term_set = $ilDB->query($q);
		while ($term_rec = $ilDB->fetchAssoc($term_set))
		{
			$glo_id = ilGlossaryTerm::_lookGlossaryID($term_rec["id"]);

			if (ilObject::_hasUntrashedReference($glo_id))
			{
				return $term_rec["id"];
			}
		}

		return 0;
	}
	
	
	/**
	* checks wether a glossary term with specified id exists or not
	*
	* @param	int		$id		id
	*
	* @return	boolean		true, if glossary term exists
	*/
	function _exists($a_id)
	{
		global $ilDB;
		
		include_once("./Services/Link/classes/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}

		$q = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($a_id, "integer");
		$obj_set = $ilDB->query($q);
		if ($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			return true;
		}
		else
		{
			return false;
		}

	}


	/**
	* set glossary term id (= glossary item id)
	*
	* @param	int		$a_id		glossary term id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}


	/**
	* get term id (= glossary item id)
	*
	* @return	int		glossary term id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* set glossary object
	*
	* @param	object		$a_glossary		glossary object
	*/
	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
		$this->setGlossaryId($a_glossary->getId());
	}


	/**
	* set glossary id
	*
	* @param	int		$a_glo_id		glossary id
	*/
	function setGlossaryId($a_glo_id)
	{
		$this->glo_id = $a_glo_id;
	}


	/**
	* get glossary id
	*
	* @return	int		glossary id
	*/
	function getGlossaryId()
	{
		return $this->glo_id;
	}


	/**
	* set term
	*
	* @param	string		$a_term		term
	*/
	function setTerm($a_term)
	{
		$this->term = $a_term;
	}


	/**
	* get term
	*
	* @return	string		term
	*/
	function getTerm()
	{
		return $this->term;
	}


	/**
	* set language
	*
	* @param	string		$a_language		two letter language code
	*/
	function setLanguage($a_language)
	{
		$this->language = $a_language;
	}

	/**
	* get language
	* @return	string		two letter language code
	*/
	function getLanguage()
	{
		return $this->language;
	}


	/**
	* set import id
	*/
	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}


	/**
	* get import id
	*/
	function getImportId()
	{
		return $this->import_id;
	}


	/**
	* create new glossary term
	*/
	function create()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId("glossary_term"));
		$ilDB->manipulate("INSERT INTO glossary_term (id, glo_id, term, language, import_id, create_date, last_update)".
			" VALUES (".
			$ilDB->quote($this->getId(), "integer").", ".
			$ilDB->quote($this->getGlossaryId(), "integer").", ".
			$ilDB->quote($this->term, "text").", ".
			$ilDB->quote($this->language, "text").",".
			$ilDB->quote($this->getImportId(), "text").",".
			$ilDB->now().", ".
			$ilDB->now().")");
	}


	/**
	* delete glossary term (and all its definition objects)
	*/
	function delete()
	{
		global $ilDB;
		
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		$defs = ilGlossaryDefinition::getDefinitionList($this->getId());
		foreach($defs as $def)
		{
			$def_obj =& new ilGlossaryDefinition($def["id"]);
			$def_obj->delete();
		}
		$ilDB->manipulate("DELETE FROM glossary_term ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}


	/**
	* update glossary term
	*/
	function update()
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE glossary_term SET ".
			" glo_id = ".$ilDB->quote($this->getGlossaryId(), "integer").", ".
			" term = ".$ilDB->quote($this->getTerm(), "text").", ".
			" import_id = ".$ilDB->quote($this->getImportId(), "text").", ".
			" language = ".$ilDB->quote($this->getLanguage(), "text").", ".
			" last_update = ".$ilDB->now()." ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}

	/**
	* get glossary id form term id
	*/
	static function _lookGlossaryID($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["glo_id"];
	}

	/**
	* get glossary term
	*/
	static function _lookGlossaryTerm($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["term"];
	}
	
	/**
	* lookup term language
	*/
	static function _lookLanguage($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["language"];
	}

	/**
	 * Get all terms for given set of glossary ids.
	 * 
	 * @param 	integer/array	array of glossary ids for meta glossaries
	 * @param	string			searchstring
	 * @param	string			first letter
	 * @return	array			array of terms 
	 */
	static function getTermList($a_glo_id, $searchterm = "", $a_first_letter = "", $a_def = "",
		$a_tax_node = 0, $a_add_amet_fields = false, array $a_amet_filter = null)
	{
		global $ilDB;

		$terms = array();

		// get all term ids under taxonomy node (if given)
		if ($a_tax_node > 1)
		{
			include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
			$tax_ids = ilObjTaxonomy::getUsageOfObject($a_glo_id);
			if (count($tax_ids) > 0)
			{
				$items = ilObjTaxonomy::getSubTreeItems("glo", $a_glo_id, "term", $tax_ids[0], $a_tax_node);
				$sub_tree_ids = array();
				foreach ($items as $i)
				{
					$sub_tree_ids[] = $i["item_id"];
				}
				$in = " AND ".$ilDB->in("gt.id", $sub_tree_ids, false, "integer");
			}
		}
		
		if ($a_def != "")
		{
			// meta glossary?
			if (is_array($a_glo_id))
			{
				$glo_where = $ilDB->in("page_object.parent_id", $a_glo_id, false, "integer");
			}
			else
			{
				$glo_where = " page_object.parent_id = ".$ilDB->quote($a_glo_id, "integer");
			}

			$join = " JOIN glossary_definition gd ON (gd.term_id = gt.id)".
			" JOIN page_object ON (".
			$glo_where.
			" AND page_object.parent_type = ".$ilDB->quote("gdf", "text").
			" AND page_object.page_id = gd.id".
			" AND ".$ilDB->like("page_object.content", "text", "%".$a_def."%").
			")";
		}

		$searchterm = (!empty ($searchterm))
			? " AND ".$ilDB->like("term", "text", "%".$searchterm."%")." "
			: "";
			
		if ($a_first_letter != "")
		{
			$searchterm.= " AND ".$ilDB->upper($ilDB->substr("term", 1, 1))." = ".$ilDB->upper($ilDB->quote($a_first_letter, "text"))." ";
		}
		
		// meta glossary
		if (is_array($a_glo_id))
		{
			$where = $ilDB->in("glo_id", $a_glo_id, false, "integer");
		}
		else
		{
			$where = " glo_id = ".$ilDB->quote($a_glo_id, "integer")." ";
		}
		
		$where.= $in;
		
		$q = "SELECT DISTINCT(gt.term), gt.id, gt.glo_id, gt.language FROM glossary_term gt ".$join." WHERE ".$where.$searchterm." ORDER BY term";
		$term_set = $ilDB->query($q);
//var_dump($q);
		while ($term_rec = $ilDB->fetchAssoc($term_set))
		{
			$terms[] = array("term" => $term_rec["term"],
				"language" => $term_rec["language"], "id" => $term_rec["id"], "glo_id" => $term_rec["glo_id"]);
		}
		
		// add advanced metadata
		if ($a_add_amet_fields || is_array($a_amet_filter))
		{			
			include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
			$terms = ilAdvancedMDValues::queryForRecords($a_glo_id, "term", $terms, "glo_id", "id", $a_amet_filter);
		}
		return $terms;
	}

	/**
	 * Get all terms for given set of glossary ids.
	 * 
	 * @param 	integer/array	array of glossary ids for meta glossaries
	 * @param	string			searchstring
	 * @param	string			first letter
	 * @return	array			array of terms 
	 */
	static function getFirstLetters($a_glo_id, $a_tax_node = 0)
	{
		global $ilDB;
		
		$terms = array();
				
		// meta glossary
		if (is_array($a_glo_id))
		{
			$where = $ilDB->in("glo_id", $a_glo_id, false, "integer");
		}
		else
		{
			$where = " glo_id = ".$ilDB->quote($a_glo_id, "integer")." ";
			
			// get all term ids under taxonomy node (if given)
			if ($a_tax_node > 1)
			{
				include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
				$tax_ids = ilObjTaxonomy::getUsageOfObject($a_glo_id);
				if (count($tax_ids) > 0)
				{
					$items = ilObjTaxonomy::getSubTreeItems("glo", $a_glo_id, "term", $tax_ids[0], $a_tax_node);
					$sub_tree_ids = array();
					foreach ($items as $i)
					{
						$sub_tree_ids[] = $i["item_id"];
					}
					$in = " AND ".$ilDB->in("id", $sub_tree_ids, false, "integer");
				}
			}
			
			$where.= $in;
		}
		
		$q = "SELECT DISTINCT ".$ilDB->upper($ilDB->substr("term", 1, 1))." let FROM glossary_term WHERE ".$where." ORDER BY let";
		$let_set = $ilDB->query($q);
		
		$lets = array();
		while ($let_rec = $ilDB->fetchAssoc($let_set))
		{
			$let[$let_rec["let"]] = $let_rec["let"];
		}
		return $let;
	}

	/**
	* export xml
	*/
	function exportXML(&$a_xml_writer, $a_inst)
	{

		$attrs = array();
		$attrs["Language"] = $this->getLanguage();
		$attrs["Id"] = "il_".IL_INST_ID."_git_".$this->getId();
		$a_xml_writer->xmlStartTag("GlossaryItem", $attrs);

		$attrs = array();
		$a_xml_writer->xmlElement("GlossaryTerm", $attrs, $this->getTerm());

		$defs = ilGlossaryDefinition::getDefinitionList($this->getId());

		foreach($defs as $def)
		{
			$definition = new ilGlossaryDefinition($def["id"]);
			$definition->exportXML($a_xml_writer, $a_inst);
		}

		$a_xml_writer->xmlEndTag("GlossaryItem");
	}

	/**
	 * Get number of usages
	 *
	 * @param	int		term id
	 * @return	int		number of usages
	 */
	static function getNumberOfUsages($a_term_id)
	{
		return count(ilGlossaryTerm::getUsages($a_term_id));
	}

	/**
	 * Get number of usages
	 *
	 * @param	int		term id
	 * @return	int		number of usages
	 */
	static function getUsages($a_term_id)
	{
		include_once("./Services/Link/classes/class.ilInternalLink.php");
		return (ilInternalLink::_getSourcesOfTarget("git", $a_term_id, 0));
	}	
	
	/**
	 * Copy a term to a glossary
	 *
	 * @param
	 * @return
	 */
	function _copyTerm($a_term_id, $a_glossary_id)
	{ 
		$old_term = new ilGlossaryTerm($a_term_id);

		// copy the term
		$new_term = new ilGlossaryTerm();
		$new_term->setTerm($old_term->getTerm());
		$new_term->setLanguage($old_term->getLanguage());
		$new_term->setGlossaryId($a_glossary_id);
		$new_term->create();

		// copy the definitions
		include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		$def_list = ilGlossaryDefinition::getDefinitionList($a_term_id);
		foreach ($def_list as $def)
		{
			$old_def = new ilGlossaryDefinition($def["id"]);
			
			$new_def = new ilGlossaryDefinition();
			$new_def->setShortText($old_def->getShortText());
			$new_def->setNr($old_def->getNr());
			$new_def->setTermId($new_term->getId());
			$new_def->create();
			
			// copy meta data
			include_once("Services/MetaData/classes/class.ilMD.php");
			$md = new ilMD($old_term->getGlossaryId(),
				$old_def->getPageObject()->getId(),
				$old_def->getPageObject()->getParentType());
			$new_md = $md->cloneMD($a_glossary_id,
				$new_def->getPageObject()->getId(),
				$old_def->getPageObject()->getParentType());


			$new_page = $new_def->getPageObject();
			$old_def->getPageObject()->copy($new_page->getId(), $new_page->getParentType(), $new_page->getParentId(), true);

			// page content
			//$new_def->getPageObject()->setXMLContent($old_def->getPageObject()->copyXmlContent(true));
			//$new_def->getPageObject()->buildDom();
			//$new_def->getPageObject()->update();
			
		}
		
		return $new_term->getId();
	}

	/**
	 * Get terms of glossary
	 *
	 * @param
	 * @return
	 */
	static function getTermsOfGlossary($a_glo_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT id FROM glossary_term WHERE ".
			" glo_id = ".$ilDB->quote($a_glo_id, "integer")
		);
		$ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ids[] = $rec["id"];
		}
		return $ids;
	}
}

?>
