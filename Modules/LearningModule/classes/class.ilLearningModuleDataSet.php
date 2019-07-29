<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * LearningModule Data set class
 * 
 * This class implements the following entities:
 * - lm: data from content_object
 * - lm_tree: data from lm_tree/lm_data
 * - lm_data_transl: data from lm_data_transl
 * - lm_menu: data from lm_menu
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesLearningModule
 */
class ilLearningModuleDataSet extends ilDataSet
{
	protected $master_lang_only = false;
	protected $transl_into = false;
	protected $transl_into_lm = null;
	protected $transl_lang = "";

	/**
	 * @var ilLogger
	 */
	protected $lm_log;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->lm_log = ilLoggerFactory::getLogger('lm');
	}
	/**
	 * Set master language only (export)
	 *
	 * @param bool $a_val export only master language	
	 */
	function setMasterLanguageOnly($a_val)
	{
		$this->master_lang_only = $a_val;
	}
	
	/**
	 * Get master language only (export)
	 *
	 * @return bool export only master language
	 */
	function getMasterLanguageOnly()
	{
		return $this->master_lang_only;
	}

	/**
	 * Set translation import mode
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 * @param string $a_lang language
	 */
	function setTranslationImportMode($a_lm, $a_lang = "")
	{
		if ($a_lm != null)
		{
			$this->transl_into = true;
			$this->transl_into_lm = $a_lm;
			$this->transl_lang = $a_lang;
		}
		else
		{
			$this->transl_into = false;
		}
	}

	/**
	 * Get translation import mode
	 *
	 * @return bool check if translation import is activated
	 */
	function getTranslationImportMode()
	{
		return $this->transl_into;
	}

	/**
	 * Get translation lm (import
	 *
	 * @return ilObjLearningModule learning module
	 */
	function getTranslationLM()
	{
		return $this->transl_into_lm;
	}

	/**
	 * Get translation language (import
	 *
	 * @return string language
	 */
	function getTranslationLang()
	{
		return $this->transl_lang;
	}

	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("5.1.0", "5.4.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/LearningModule/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param string $a_entity entity
	 * @param string $a_version version number
	 * @return array types array
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "lm")
		{
			switch ($a_version)
			{
				case "5.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"DefaultLayout" => "text",
						"PageHeader" => "text",
						"TocActive" => "text",
						"LMMenuActive" => "text",
						"TOCMode" => "text",
						"PrintViewActive" => "text",
						"Numbering" => "text",
						"HistUserComments" => "text",
						"PublicAccessMode" => "text",
						"PubNotes" => "text",
						"HeaderPage" => "integer",
						"FooterPage" => "integer",
						"LayoutPerPage" => "integer",
						"Rating" => "integer",
						"HideHeadFootPrint" => "integer",
						"DisableDefFeedback" => "integer",
						"RatingPages" => "integer",
						"ProgrIcons" => "integer",
						"StoreTries" => "integer",
						"RestrictForwNav" => "integer",
						"Comments" => "integer",
						"ForTranslation" => "integer",
						"StyleId" => "integer"
					);

				case "5.4.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"DefaultLayout" => "text",
						"PageHeader" => "text",
						"TocActive" => "text",
						"LMMenuActive" => "text",
						"TOCMode" => "text",
						"PrintViewActive" => "text",
						"NoGloAppendix" => "text",
						"Numbering" => "text",
						"HistUserComments" => "text",
						"PublicAccessMode" => "text",
						"PubNotes" => "text",
						"HeaderPage" => "integer",
						"FooterPage" => "integer",
						"LayoutPerPage" => "integer",
						"Rating" => "integer",
						"HideHeadFootPrint" => "integer",
						"DisableDefFeedback" => "integer",
						"RatingPages" => "integer",
						"ProgrIcons" => "integer",
						"StoreTries" => "integer",
						"RestrictForwNav" => "integer",
						"Comments" => "integer",
						"ForTranslation" => "integer",
						"StyleId" => "integer"
					);

			}
		}

		if ($a_entity == "lm_tree")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					return array(
						"LmId" => "integer",
						"Child" => "integer",
						"Parent" => "integer",
						"Depth" => "integer",
						"Type" => "text",
						"Title" => "text",
						"ShortTitle" => "text",
						"PublicAccess" => "text",
						"Active" => "text",
						"Layout" => "text",
						"ImportId" => "text"
					);
			}
		}

		if ($a_entity == "lm_menu")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					return array(
						"LmId" => "integer",
						"LinkType" => "text",
						"Title" => "text",
						"Target" => "text",
						"LinkRefId" => "text",
						"Active" => "text"
					);
			}
		}

		if ($a_entity == "lm_data_transl")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					return array(
						"Id" => "integer",
						"Lang" => "text",
						"Title" => "text",
						"ShortTitle" => "text"
					);
			}
		}

	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		$ilDB = $this->db;

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
				
		if ($a_entity == "lm")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					switch ($a_version)
					{
						case "5.1.0":
							$q = "SELECT id, title, description,".
								" default_layout, page_header, toc_active, lm_menu_active, toc_mode, print_view_active, numbering,".
								" hist_user_comments, public_access_mode, header_page, footer_page, layout_per_page, rating, ".
								" hide_head_foot_print, disable_def_feedback, rating_pages, store_tries, restrict_forw_nav, progr_icons, stylesheet style_id".
								" FROM content_object JOIN object_data ON (content_object.id = object_data.obj_id)".
								" WHERE ".$ilDB->in("id", $a_ids, false, "integer");
								break;

						case "5.4.0":
							$q = "SELECT id, title, description,".
								" default_layout, page_header, toc_active, lm_menu_active, toc_mode, print_view_active, numbering,".
								" hist_user_comments, public_access_mode, no_glo_appendix, header_page, footer_page, layout_per_page, rating, ".
								" hide_head_foot_print, disable_def_feedback, rating_pages, store_tries, restrict_forw_nav, progr_icons, stylesheet style_id".
								" FROM content_object JOIN object_data ON (content_object.id = object_data.obj_id)".
								" WHERE ".$ilDB->in("id", $a_ids, false, "integer");

					}

					$set = $ilDB->query($q);
					$this->data = array();
					while ($rec  = $ilDB->fetchAssoc($set))
					{
						// comments activated?
						include_once("./Services/Notes/classes/class.ilNote.php");
						$rec["comments"] = ilNote::commentsActivated($rec["id"], 0, "lm");

						if ($this->getMasterLanguageOnly())
						{
							$rec["for_translation"] = 1;
						}
						$tmp = array();
						foreach ($rec as $k => $v)
						{
							$tmp[$this->convertToLeadingUpper($k)]
								= $v;
						}
						$rec = $tmp;

						$this->data[] = $rec;
					}
					break;


					break;
			}
		}

		if ($a_entity == "lm_tree")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					// the order by lft is very important, this ensures that parent nodes are written before
					// their childs and that the import can add nodes simply with a "add at last child" target
					$q = "SELECT lm_tree.lm_id, child, parent, depth, type, title, short_title, public_access, active, layout, import_id".
						" FROM lm_tree JOIN lm_data ON (lm_tree.child = lm_data.obj_id)".
						" WHERE ".$ilDB->in("lm_tree.lm_id", $a_ids, false, "integer").
						" ORDER BY lft";

					$set = $ilDB->query($q);
					$this->data = array();
					$obj_ids = array();
					while ($rec  = $ilDB->fetchAssoc($set))
					{
						$set2 = $ilDB->query("SELECT for_translation FROM content_object WHERE id = ".$ilDB->quote($rec["lm_id"], "integer"));
						$rec2 = $ilDB->fetchAssoc($set2);
						if (!$rec2["for_translation"])
						{
							$rec["import_id"] = "il_".IL_INST_ID."_".$rec["type"]."_".$rec["child"];
						}
						$tmp = array();
						foreach ($rec as $k => $v)
						{
							$tmp[$this->convertToLeadingUpper($k)]
								= $v;
						}
						$rec = $tmp;
						$obj_ids[] = $rec["Child"];
						$this->data[] = $rec;
					}

					// add free pages #18976
					$set3 = $ilDB->query($q = "SELECT lm_id, type, title, short_title, public_access, active, layout, import_id, obj_id child FROM lm_data ".
						"WHERE ".$ilDB->in("lm_id", $a_ids, false, "integer").
						" AND ".$ilDB->in("obj_id", $obj_ids, true, "integer").
						" AND type = ".$ilDB->quote("pg", "text"));
					while ($rec3 = $ilDB->fetchAssoc($set3))
					{
						$set2 = $ilDB->query("SELECT for_translation FROM content_object WHERE id = ".$ilDB->quote($rec3["lm_id"], "integer"));
						$rec2 = $ilDB->fetchAssoc($set2);
						if (!$rec2["for_translation"])
						{
							$rec3["import_id"] = "il_".IL_INST_ID."_pg_".$rec3["child"];
						}
						$rec3["type"] = "free_pg";
						$rec3["depth"] = 0;
						$rec3["parent"] = 0;
						$tmp = array();
						foreach ($rec3 as $k => $v)
						{
							$tmp[$this->convertToLeadingUpper($k)]
								= $v;
						}
						$this->data[] = $tmp;
					}
					break;
			}
		}

		if ($a_entity == "lm_menu")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					$this->getDirectDataFromQuery("SELECT lm_id, link_type, title, target, link_ref_id, active".
						" FROM lm_menu ".
						" WHERE ".$ilDB->in("lm_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "lm_data_transl")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.4.0":
					$this->getDirectDataFromQuery("SELECT id, lang, title, short_title".
						" FROM lm_data_transl ".
						" WHERE ".$ilDB->in("id", $a_ids, false, "integer"));
					break;
			}
		}


	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "lm":
				return array (
					"lm_tree" => array("ids" => $a_rec["Id"]),
					"lm_menu" => array("ids" => $a_rec["Id"])
				);

			case "lm_tree":
				if ($this->getMasterLanguageOnly())
				{
					return false;
				}
				else
				{
					return array (
						"lm_data_transl" => array("ids" => $a_rec["Child"])
					);
				}
		}

		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "lm":

				if ($this->getTranslationImportMode())
				{
					return;
				}
				
				include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjLearningModule();
					$newObj->setType("lm");
					$newObj->create(true);
					$newObj->createLMTree();
				}
					
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setLayout($a_rec["DefaultLayout"]);
				$newObj->setPageHeader($a_rec["PageHeader"]);
				$newObj->setActiveTOC(ilUtil::yn2tf($a_rec["TocActive"]));
				$newObj->setActiveLMMenu(ilUtil::yn2tf($a_rec["LmMenuActive"]));
				$newObj->setTOCMode($a_rec["TocMode"]);
				$newObj->setActivePrintView(ilUtil::yn2tf($a_rec["PrintViewActive"]));
				$newObj->setActivePreventGlossaryAppendix(ilUtil::yn2tf($a_rec["NoGloAppendix"]));
				$newObj->setActiveNumbering(ilUtil::yn2tf($a_rec["Numbering"]));
				$newObj->setHistoryUserComments(ilUtil::yn2tf($a_rec["HistUserComments"]));
				$newObj->setPublicAccessMode($a_rec["PublicAccessMode"]);
				$newObj->setPublicNotes(ilUtil::yn2tf($a_rec["PubNotes"]));
				// Header Page/ Footer Page ???
				$newObj->setLayoutPerPage($a_rec["LayoutPerPage"]);
				$newObj->setRating($a_rec["Rating"]);
				$newObj->setHideHeaderFooterPrint($a_rec["HideHeadFootPrint"]);
				$newObj->setDisableDefaultFeedback($a_rec["DisableDefFeedback"]);
				$newObj->setRatingPages($a_rec["RatingPages"]);
				$newObj->setForTranslation($a_rec["ForTranslation"]);
				$newObj->setProgressIcons($a_rec["ProgrIcons"]);
				$newObj->setStoreTries($a_rec["StoreTries"]);
				$newObj->setRestrictForwardNavigation($a_rec["RestrictForwNav"]);
				if ($a_rec["HeaderPage"] > 0)
				{
					$a_mapping->addMapping("Modules/LearningModule", "lm_header_page", $a_rec["HeaderPage"], "-");
				}
				if ($a_rec["FooterPage"] > 0)
				{
					$a_mapping->addMapping("Modules/LearningModule", "lm_footer_page", $a_rec["FooterPage"], "-");
				}

				$newObj->update(true);
				$this->current_obj = $newObj;

				// activated comments
				include_once("./Services/Notes/classes/class.ilNote.php");
				ilNote::activateComments($newObj->getId(), 0, "lm", (int) $a_rec["Comments"]);

				$a_mapping->addMapping("Modules/LearningModule", "lm", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Modules/LearningModule", "lm_style", $newObj->getId(), $a_rec["StyleId"]);
				$a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Services/MetaData", "md",
					$a_rec["Id"].":0:lm", $newObj->getId().":0:lm");
				break;

			case "lm_tree":
				if (!$this->getTranslationImportMode())
				{
					include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
					include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
					include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
					switch ($a_rec["Type"])
					{
						case "st":
							$parent = (int) $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Parent"]);
							$st_obj = new ilStructureObject($this->current_obj);
							$st_obj->setType("st");
							$st_obj->setLMId($this->current_obj->getId());
							$st_obj->setTitle($a_rec["Title"]);
							$st_obj->setShortTitle($a_rec["ShortTitle"]);
							$st_obj->setImportId($a_rec["ImportId"]);
							$st_obj->create(true);
							ilLMObject::putInTree($st_obj, $parent, IL_LAST_NODE);
							$a_mapping->addMapping("Modules/LearningModule", "lm_tree", $a_rec["Child"],
								$st_obj->getId());
							$a_mapping->addMapping("Services/MetaData", "md",
								$a_rec["LmId"].":".$a_rec["Child"].":st", $this->current_obj->getId().":".$st_obj->getId().":st");
							break;

						case "pg":
							$parent = (int) $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Parent"]);
							$pg_obj = new ilLMPageObject($this->current_obj);
							$pg_obj->setType("pg");
							$pg_obj->setLMId($this->current_obj->getId());
							$pg_obj->setTitle($a_rec["Title"]);
							$pg_obj->setShortTitle($a_rec["ShortTitle"]);
							$pg_obj->setImportId($a_rec["ImportId"]);
							$pg_obj->create(true, true);
							ilLMObject::putInTree($pg_obj, $parent, IL_LAST_NODE);
							$a_mapping->addMapping("Modules/LearningModule", "lm_tree", $a_rec["Child"],
								$pg_obj->getId());
							$a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_obj->getId());
							$this->lm_log->debug("add pg map (1), old : ".$a_rec["Child"].", new: ".$pg_obj->getId());
							$a_mapping->addMapping("Services/COPage", "pg", "lm:".$a_rec["Child"],
								"lm:".$pg_obj->getId());
							$a_mapping->addMapping("Services/MetaData", "md",
								$a_rec["LmId"].":".$a_rec["Child"].":pg", $this->current_obj->getId().":".$pg_obj->getId().":pg");
							break;

						// add free pages #18976
						case "free_pg":
							$pg_obj = new ilLMPageObject($this->current_obj);
							$pg_obj->setType("pg");
							$pg_obj->setLMId($this->current_obj->getId());
							$pg_obj->setTitle($a_rec["Title"]);
							$pg_obj->setShortTitle($a_rec["ShortTitle"]);
							$pg_obj->setImportId($a_rec["ImportId"]);
							$pg_obj->create(true, true);
							$a_mapping->addMapping("Modules/LearningModule", "lm_tree", $a_rec["Child"],
								$pg_obj->getId());
							$a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_obj->getId());
							$this->lm_log->debug("add pg map (2), old : ".$a_rec["Child"].", new: ".$pg_obj->getId());
							$a_mapping->addMapping("Services/COPage", "pg", "lm:".$a_rec["Child"],
								"lm:".$pg_obj->getId());
							$a_mapping->addMapping("Services/MetaData", "md",
								$a_rec["LmId"].":".$a_rec["Child"].":pg", $this->current_obj->getId().":".$pg_obj->getId().":pg");
							break;
					}
				}
				else
				{
					include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
					switch ($a_rec["Type"])
					{
						case "st":
							//"il_inst_st_66"
							$imp_id = explode("_", $a_rec["ImportId"]);
							if ($imp_id[0] == "il" &&
								(int) $imp_id[1] == (int) IL_INST_ID &&
								$imp_id[2] == "st"
								)
							{
								$st_id = $imp_id[3];
								if (ilLMObject::_lookupContObjId($st_id) == $this->getTranslationLM()->getId())
								{
									$trans = new ilLMObjTranslation($st_id, $this->getTranslationLang());
									$trans->setTitle($a_rec["Title"]);
									$trans->save();
									$a_mapping->addMapping("Modules/LearningModule", "link",
										"il_".$this->getCurrentInstallationId()."_".$a_rec["Type"]."_".$a_rec["Child"], $a_rec["ImportId"]);
								}
							}
							// no meta-data mapping, since we do not want to import metadata
							break;

						case "pg":
							//"il_inst_pg_66"
							$imp_id = explode("_", $a_rec["ImportId"]);
							if ($imp_id[0] == "il" &&
								(int) $imp_id[1] == (int) IL_INST_ID &&
								$imp_id[2] == "pg"
							)
							{
								$pg_id = $imp_id[3];
								if (ilLMObject::_lookupContObjId($pg_id) == $this->getTranslationLM()->getId())
								{
									$trans = new ilLMObjTranslation($pg_id, $this->getTranslationLang());
									$trans->setTitle($a_rec["Title"]);
									$trans->save();
									$a_mapping->addMapping("Modules/LearningModule", "pg", $a_rec["Child"], $pg_id);
									$this->lm_log->debug("add pg map (3), old : ".$a_rec["Child"].", new: ".$pg_id);
									$a_mapping->addMapping("Modules/LearningModule", "link",
										"il_".$this->getCurrentInstallationId()."_".$a_rec["Type"]."_".$a_rec["Child"], $a_rec["ImportId"]);
									$a_mapping->addMapping("Services/COPage", "pg", "lm:".$a_rec["Child"],
										"lm:".$pg_id);
								}
							}
							// no meta-data mapping, since we do not want to import metadata
							break;
					}
				}
				break;

			case "lm_data_transl":
				include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
				if (!$this->getTranslationImportMode())
				{
					// save page/chapter title translation
					$lm_obj_id = $a_mapping->getMapping("Modules/LearningModule", "lm_tree", $a_rec["Id"]);
					if ($lm_obj_id > 0)
					{
						$t = new ilLMObjTranslation($lm_obj_id, $a_rec["Lang"]);
						$t->setTitle($a_rec["Title"]);
						$t->setShortTitle($a_rec["ShortTitle"]);
						$t->save();
					}
				}
				break;

			case "lm_menu":
				$lm_id = (int)$a_mapping->getMapping("Modules/LearningModule", "lm", $a_rec["LmId"]);
				if ($lm_id > 0)
				{
					$lm_menu_ed = new ilLMMenuEditor();
					$lm_menu_ed->setObjId($lm_id);
					$lm_menu_ed->setTitle($a_rec["Title"]);
					$lm_menu_ed->setTarget($a_rec["Target"]);
					$lm_menu_ed->setLinkType($a_rec["LinkType"]);
					$lm_menu_ed->setLinkRefId($a_rec["LinkRefId"]);
					$lm_menu_ed->setActive($a_rec["Active"]);
					$lm_menu_ed->create();
				}
				break;

		}
	}
}
?>