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


/**
* Meta Data class (element general)
*
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/
include_once 'Services/MetaData/classes/class.ilMD.php';


class ilMDEditorGUI
{
	var $ctrl = null;
	var $lng = null;
	var $tpl = null;
	var $md_obj = null;

	var $observers = array();

	var $rbac_id = null;
	var $obj_id = null;
	var $obj_type = null;

	function ilMDEditorGUI($a_rbac_id,$a_obj_id,$a_obj_type)
	{
		global $ilCtrl,$lng,$tpl;

		$this->md_obj =& new ilMD($a_rbac_id,$a_obj_id,$a_obj_type);
		$this->ctrl =& $ilCtrl;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('meta');

		$this->tpl =& $tpl;

	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "listSection";
				}
				$this->$cmd();
				break;
		}
		return true;
	}


	function debug()
	{
		include_once 'Services/MetaData/classes/class.ilMD2XML.php';


		$xml_writer =& new ilMD2XML($this->md_obj->getRBACId(),$this->md_obj->getObjId(),$this->md_obj->getObjType());
		$xml_writer->startExport();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.md_editor.html','Services/MetaData');
		
		$this->__setTabs('meta_general');

		$this->tpl->setVariable("MD_CONTENT",htmlentities($xml_writer->getXML()));

		return true;
	}

	/*
	 * list general sections
	 */
	function listGeneral()
	{
		if(!is_object($this->md_section = $this->md_obj->getGeneral()))
		{
			$this->md_section = $this->md_obj->addGeneral();
			$this->md_section->save();
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.md_editor.html','Services/MetaData');
		
		$this->__setTabs('meta_general');

		$this->tpl->addBlockFile('MD_CONTENT','md_content','tpl.md_general.html','Services/MetaData');

		$this->ctrl->setReturn($this,'listGeneral');
		$this->ctrl->setParameter($this,'section','meta_general');
		$this->tpl->setVariable("EDIT_ACTION",$this->ctrl->getFormAction($this));

		$this->__fillSubelements();
		
		$this->tpl->setVariable("TXT_GENERAL", $this->lng->txt("meta_general"));
		$this->tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
		$this->tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
		$this->tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("meta_structure"));
		$this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$this->tpl->setVariable("TXT_ATOMIC", $this->lng->txt("meta_atomic"));
		$this->tpl->setVariable("TXT_COLLECTION", $this->lng->txt("meta_collection"));
		$this->tpl->setVariable("TXT_NETWORKED", $this->lng->txt("meta_networked"));
		$this->tpl->setVariable("TXT_HIERARCHICAL", $this->lng->txt("meta_hierarchical"));
		$this->tpl->setVariable("TXT_LINEAR", $this->lng->txt("meta_linear"));

		// Structure
		$this->tpl->setVariable("STRUCTURE_VAL_".strtoupper($this->md_section->getStructure())," selected=selected");

		// Identifier
		foreach($ids = $this->md_section->getIdentifierIds() as $id)
		{
			$md_ide = $this->md_section->getIdentifier($id);

			if(count($ids) > 1)
			{
				$this->ctrl->setParameter($this,'meta_index',$id);
				$this->ctrl->setParameter($this,'meta_path','meta_identifier');

				$this->tpl->setCurrentBlock("identifier_delete");
				$this->tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE",$this->ctrl->getLinkTarget($this,'deleteElement'));
				$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE",$this->lng->txt('delete'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("identifier_loop");
			$this->tpl->setVariable("IDENTIFIER_LOOP_NO", $id);
			$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
			$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
			$this->tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG", 
									ilUtil::prepareFormOutput($md_ide->getCatalog()));
			$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
			$this->tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY", 
									ilUtil::prepareFormOutput($md_ide->getEntry()));
			$this->tpl->parseCurrentBlock();
		}


		// Language
		foreach($ids = $this->md_section->getLanguageIds() as $id)
		{
			$md_lan = $this->md_section->getLanguage($id);

			if (count($ids) > 1)
			{
				$this->ctrl->setParameter($this,'meta_index',$id);
				$this->ctrl->setParameter($this,'meta_path','meta_language');

				$this->tpl->setCurrentBlock("language_delete");
				$this->tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE",$this->ctrl->getLinkTarget($this,'deleteElement'));
				$this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("language_loop");
			$this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect('gen_language['.$id.'][language]',
																						$md_lan->getLanguageCode()));
			$this->tpl->parseCurrentBlock();
		}

		// TITLE
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("VAL_TITLE",ilUtil::prepareFormOutput($this->md_section->getTitle()));
		$this->tpl->setVariable("VAL_TITLE_LANGUAGE",$this->__showLanguageSelect('gen_title_language',
																			   $this->md_section->getTitleLanguageCode()));


		// DESCRIPTION
		foreach($ids = $this->md_section->getDescriptionIds() as $id)
		{ 
			$md_des = $this->md_section->getDescription($id);

			if (count($ids) > 1)
			{
				$this->ctrl->setParameter($this,'meta_index',$id);
				$this->ctrl->setParameter($this,'meta_path','meta_description');

				$this->tpl->setCurrentBlock("description_delete");
				$this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE",$this->ctrl->getLinkTarget($this,'deleteElement'));
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("description_loop");
			$this->tpl->setVariable("DESCRIPTION_LOOP_NO",$id);
			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($md_des->getDescription()));
			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect("gen_description[".$id.'][language]', 
																				  $md_des->getDescriptionLanguageCode()));
			$this->tpl->parseCurrentBlock();
		}

		// KEYWORD
		foreach($ids = $this->md_section->getKeywordIds() as $id)
		{
			$md_key = $this->md_section->getKeyword($id);

			if(count($ids) > 1)
			{
				$this->ctrl->setParameter($this,'meta_index',$id);
				$this->ctrl->setParameter($this,'meta_path','meta_keyword');

				$this->tpl->setCurrentBlock("keyword_delete");
				$this->tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE",$this->ctrl->getLinkTarget($this,'deleteElement'));
				$this->tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("keyword_loop");
			$this->tpl->setVariable("KEYWORD_LOOP_NO",$id);
			$this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
			$this->tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
			$this->tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput($md_key->getKeyword()));
			$this->tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect("gen_keyword[".$id.'][language]',
																					   $md_key->getKeywordLanguageCode()));

			$this->tpl->parseCurrentBlock();
		}

		// Coverage
		$this->tpl->setVariable("COVERAGE_LOOP_TXT_COVERAGE",$this->lng->txt('meta_coverage'));
		$this->tpl->setVariable("COVERAGE_LOOP_VAL",ilUtil::prepareFormOutput($this->md_section->getCoverage()));
		$this->tpl->setVariable("COVERAGE_LOOP_TXT_LANGUAGE",$this->lng->txt('meta_language'));
		$this->tpl->setVariable("COVERAGE_LOOP_VAL_LANGUAGE",$this->__showLanguageSelect('gen_coverage_language',
																						 $this->md_section->getCoverageLanguageCode()));

		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));
	}

	function updateGeneral()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		// General values
		$this->md_section = $this->md_obj->getGeneral();
		$this->md_section->setStructure($_POST['gen_structure']);
		$this->md_section->setTitle(ilUtil::stripSlashes($_POST['gen_title']));
		$this->md_section->setTitleLanguage(new ilMDLanguageItem($_POST['gen_title_language']));
		$this->md_section->setCoverage(ilUtil::stripSlashes($_POST['gen_coverage']));
		$this->md_section->setCoverageLanguage(new ilMDLanguageItem($_POST['gen_coverage_language']));
		$this->md_section->update();

		// Identifier
		if(is_array($_POST['gen_identifier']))
		{
			foreach($_POST['gen_identifier'] as $id => $data)
			{
				$md_ide = $this->md_section->getIdentifier($id);
				$md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog']));
				$md_ide->setEntry(ilUtil::stripSlashes($data['Entry']));
				$md_ide->update();
			}
		}

		// Language
		if(is_array($_POST['gen_language']))
		{
			foreach($_POST['gen_language'] as $id => $data)
			{
				$md_lan = $this->md_section->getLanguage($id);
				$md_lan->setLanguage(new ilMDLanguageItem($data['language']));
				$md_lan->update();
			}
		}
		// Description
		if(is_array($_POST['gen_description']))
		{
			foreach($_POST['gen_description'] as $id => $data)
			{
				$md_des = $this->md_section->getDescription($id);
				$md_des->setDescription(ilUtil::stripSlashes($data['description']));
				$md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
				$md_des->update();
			}
		}
		// Keyword
		if(is_array($_POST['gen_keyword']))
		{
			foreach($_POST['gen_keyword'] as $id => $data)
			{
				$md_key = $this->md_section->getKeyword($id);

				$md_key->setKeyword(ilUtil::stripSlashes($data['keyword']));
				$md_key->setKeywordLanguage(new ilMDLanguageItem($data['language']));
				$md_key->update();
			}
		}
		$this->callListeners('General');

		// Redirect here to read new title and description
		// Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
		$this->ctrl->redirect($this,'listSection');
	}

	/*
	 * list rights section
	 */
	function listRights()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.md_editor.html','Services/MetaData');
		$this->__setTabs('meta_rights');
		$this->tpl->addBlockFile('MD_CONTENT','md_content','tpl.md_rights.html','Services/MetaData');

		if(!is_object($this->md_section = $this->md_obj->getRights()))
		{
			$this->tpl->setCurrentBlock("no_rights");
			$this->tpl->setVariable("TXT_NO_RIGHTS", $this->lng->txt("meta_no_rights"));
			$this->tpl->setVariable("TXT_ADD_RIGHTS", $this->lng->txt("meta_add"));
			$this->ctrl->setParameter($this, "section", "meta_rights");
			$this->tpl->setVariable("ACTION_ADD_RIGHTS",
				$this->ctrl->getLinkTarget($this, "addSection"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
	
			$this->ctrl->setReturn($this,'listRights');
			$this->ctrl->setParameter($this,'section','meta_rights');
			$this->tpl->setVariable("EDIT_ACTION",$this->ctrl->getFormAction($this));

			$this->tpl->setVariable("TXT_RIGHTS", $this->lng->txt("meta_rights"));
			$this->tpl->setVariable("TXT_COST", $this->lng->txt("meta_cost"));
			$this->tpl->setVariable("TXT_COPYRIGHTANDOTHERRESTRICTIONS", $this->lng->txt("meta_copyright_and_other_restrictions"));
			$this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$this->tpl->setVariable("TXT_YES", $this->lng->txt("meta_yes"));
			$this->tpl->setVariable("TXT_NO", $this->lng->txt("meta_no"));

			$this->ctrl->setParameter($this, "section", "meta_rights");
			$this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());
			$this->tpl->setVariable("ACTION_DELETE",
				$this->ctrl->getLinkTarget($this, "deleteSection"));

			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));

			$this->tpl->setVariable("VAL_COST_".strtoupper($this->md_section->getCosts()), " selected");
			$this->tpl->setVariable("VAL_COPYRIGHTANDOTHERRESTRICTIONS_".
				strtoupper($this->md_section->getCopyrightAndOtherRestrictions()), " selected");

			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::prepareFormOutput($this->md_section->getDescription()));
			$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE",
			$this->__showLanguageSelect('rights[DescriptionLanguage]',
				$this->md_section->getDescriptionLanguageCode()));

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("rights");
			$this->tpl->parseCurrentBlock();
			
		}
	}

	function updateRights()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		// update rights section
		$this->md_section = $this->md_obj->getRights();
		$this->md_section->setCosts($_POST['rights']['Cost']);
		$this->md_section->setCopyrightAndOtherRestrictions($_POST['rights']['CopyrightAndOtherRestrictions']);
		$this->md_section->setDescriptionLanguage(new ilMDLanguageItem($_POST['rights']['DescriptionLanguage']));
		$this->md_section->setDescription(ilUtil::stripSlashes($_POST['rights']['Description']));
		$this->md_section->update();

		$this->listSection();
	}

	/*
	 * list educational section
	 */
	function listEducational()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.md_editor.html','Services/MetaData');
		$this->__setTabs('meta_educational');
		$this->tpl->addBlockFile('MD_CONTENT','md_content','tpl.md_educational.html','Services/MetaData');

		if(!is_object($this->md_section = $this->md_obj->getEducational()))
		{
			$this->tpl->setCurrentBlock("no_educational");
			$this->tpl->setVariable("TXT_NO_EDUCATIONAL", $this->lng->txt("meta_no_educational"));
			$this->tpl->setVariable("TXT_ADD_EDUCATIONAL", $this->lng->txt("meta_add"));
			$this->ctrl->setParameter($this, "section", "meta_educational");
			$this->tpl->setVariable("ACTION_ADD_EDUCATIONAL",
				$this->ctrl->getLinkTarget($this, "addSection"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->ctrl->setReturn($this,'listEducational');
			$this->ctrl->setParameter($this,'section','meta_educational');
			$this->tpl->setVariable("EDIT_ACTION",$this->ctrl->getFormAction($this));

			$this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());
			$this->tpl->setVariable("ACTION_DELETE",
				$this->ctrl->getLinkTarget($this, "deleteSection"));

			$this->tpl->setVariable("TXT_EDUCATIONAL", $this->lng->txt("meta_educational"));
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$this->tpl->setVariable("TXT_TYPICALAGERANGE", $this->lng->txt("meta_typical_age_range"));
			$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

			$this->tpl->setVariable("TXT_INTERACTIVITYTYPE", $this->lng->txt("meta_interactivity_type"));
			$this->tpl->setVariable("TXT_LEARNINGRESOURCETYPE", $this->lng->txt("meta_learning_resource_type"));
			$this->tpl->setVariable("TXT_INTERACTIVITYLEVEL", $this->lng->txt("meta_interactivity_level"));
			$this->tpl->setVariable("TXT_SEMANTICDENSITY", $this->lng->txt("meta_semantic_density"));
			$this->tpl->setVariable("TXT_INTENDEDENDUSERROLE", $this->lng->txt("meta_intended_end_user_role"));
			$this->tpl->setVariable("TXT_CONTEXT", $this->lng->txt("meta_context"));
			$this->tpl->setVariable("TXT_DIFFICULTY", $this->lng->txt("meta_difficulty"));
			
			$this->tpl->setVariable("VAL_INTERACTIVITYTYPE_" . strtoupper($this->md_section->getInteractivityType()), " selected");
			$this->tpl->setVariable("VAL_LEARNINGRESOURCETYPE_" . strtoupper($this->md_section->getLearningResourceType()), " selected");
			$this->tpl->setVariable("VAL_INTERACTIVITYLEVEL_" . strtoupper($this->md_section->getInteractivityLevel()), " selected");
			$this->tpl->setVariable("VAL_SEMANTICDENSITY_" . strtoupper($this->md_section->getSemanticDensity()), " selected");
			$this->tpl->setVariable("VAL_INTENDEDENDUSERROLE_" . strtoupper($this->md_section->getIntendedEndUserRole()), " selected");
			$this->tpl->setVariable("VAL_CONTEXT_" . strtoupper($this->md_section->getContext()), " selected");
			$this->tpl->setVariable("VAL_DIFFICULTY_" . strtoupper($this->md_section->getDifficulty()), " selected");
			$this->tpl->setVariable("VAL_TYPICALLEARNINGTIME", ilUtil::prepareFormOutput($this->md_section->getTypicalLearningTime()));
			
			$this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt("meta_active"));
			$this->tpl->setVariable("TXT_EXPOSITIVE", $this->lng->txt("meta_expositive"));
			$this->tpl->setVariable("TXT_MIXED", $this->lng->txt("meta_mixed"));
			$this->tpl->setVariable("TXT_EXERCISE", $this->lng->txt("meta_exercise"));
			$this->tpl->setVariable("TXT_SIMULATION", $this->lng->txt("meta_simulation"));
			$this->tpl->setVariable("TXT_QUESTIONNAIRE", $this->lng->txt("meta_questionnaire"));
			$this->tpl->setVariable("TXT_DIAGRAMM", $this->lng->txt("meta_diagramm"));
			$this->tpl->setVariable("TXT_FIGURE", $this->lng->txt("meta_figure"));
			$this->tpl->setVariable("TXT_GRAPH", $this->lng->txt("meta_graph"));
			$this->tpl->setVariable("TXT_INDEX", $this->lng->txt("meta_index"));
			$this->tpl->setVariable("TXT_SLIDE", $this->lng->txt("meta_slide"));
			$this->tpl->setVariable("TXT_TABLE", $this->lng->txt("meta_table"));
			$this->tpl->setVariable("TXT_NARRATIVETEXT", $this->lng->txt("meta_narrative_text"));
			$this->tpl->setVariable("TXT_EXAM", $this->lng->txt("meta_exam"));
			$this->tpl->setVariable("TXT_EXPERIMENT", $this->lng->txt("meta_experiment"));
			$this->tpl->setVariable("TXT_PROBLEMSTATEMENT", $this->lng->txt("meta_problem_statement"));
			$this->tpl->setVariable("TXT_SELFASSESSMENT", $this->lng->txt("meta_self_assessment"));
			$this->tpl->setVariable("TXT_LECTURE", $this->lng->txt("meta_lecture"));
			$this->tpl->setVariable("TXT_VERYLOW", $this->lng->txt("meta_very_low"));
			$this->tpl->setVariable("TXT_LOW", $this->lng->txt("meta_low"));
			$this->tpl->setVariable("TXT_MEDIUM", $this->lng->txt("meta_medium"));
			$this->tpl->setVariable("TXT_HIGH", $this->lng->txt("meta_high"));
			$this->tpl->setVariable("TXT_VERYHIGH", $this->lng->txt("meta_very_low"));
			$this->tpl->setVariable("TXT_TEACHER", $this->lng->txt("meta_teacher"));
			$this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("meta_author"));
			$this->tpl->setVariable("TXT_LEARNER", $this->lng->txt("meta_learner"));
			$this->tpl->setVariable("TXT_MANAGER", $this->lng->txt("meta_manager"));
			$this->tpl->setVariable("TXT_SCHOOL", $this->lng->txt("meta_school"));
			$this->tpl->setVariable("TXT_HIGHEREDUCATION", $this->lng->txt("meta_higher_education"));
			$this->tpl->setVariable("TXT_TRAINING", $this->lng->txt("meta_training"));
			$this->tpl->setVariable("TXT_OTHER", $this->lng->txt("meta_other"));
			$this->tpl->setVariable("TXT_VERYEASY", $this->lng->txt("meta_very_easy"));
			$this->tpl->setVariable("TXT_EASY", $this->lng->txt("meta_easy"));
			$this->tpl->setVariable("TXT_DIFFICULT", $this->lng->txt("meta_difficult"));
			$this->tpl->setVariable("TXT_VERYDIFFICULT", $this->lng->txt("meta_very_difficult"));
			$this->tpl->setVariable("TXT_TYPICALLEARNINGTIME", $this->lng->txt("meta_typical_learning_time"));

			/* TypicalAgeRange */
			foreach($ids = $this->md_section->getTypicalAgeRangeIds() as $id)
			{
				$md_age = $this->md_section->getTypicalAgeRange($id);
				
				$this->ctrl->setParameter($this, 'meta_index', $id);
				$this->ctrl->setParameter($this, 'meta_path', 'educational_typical_age_range');
	
				$this->tpl->setCurrentBlock("typicalagerange_delete");
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_ACTION_DELETE",
					$this->ctrl->getLinkTarget($this, "deleteElement"));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("typicalagerange_loop");
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_TYPICALAGERANGE", $this->lng->txt("meta_typical_age_range"));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_VAL", ilUtil::prepareFormOutput($md_age->getTypicalAgeRange()));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_NO", $id);
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_VAL_LANGUAGE",
					$this->__showLanguageSelect('educational[TypicalAgeRange]['.$id.'][Language]',
					$md_age->getTypicalAgeRangeLanguageCode()));
				$this->ctrl->setParameter($this, "section_element", "educational_typical_age_range");
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_ACTION_ADD",
					$this->ctrl->getLinkTarget($this, "addSectionElement"));
				$this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$this->tpl->parseCurrentBlock();
			}

			/* Description */
			foreach($ids = $this->md_section->getDescriptionIds() as $id)
			{
				$md_des = $this->md_section->getDescription($id);
				
				$this->ctrl->setParameter($this, 'meta_index', $id);
				$this->ctrl->setParameter($this, 'meta_path', 'educational_description');
				
				$this->tpl->setCurrentBlock("description_loop");
				$this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($md_des->getDescription()));
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE",
					$this->__showLanguageSelect('educational[Description]['.$id.'][Language]',
						$md_des->getDescriptionLanguageCode()));
				$this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE",
					$this->ctrl->getLinkTarget($this, "deleteElement"));
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->ctrl->setParameter($this, "section_element", "educational_description");
				$this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_ADD",
					$this->ctrl->getLinkTarget($this, "addSectionElement"));
				$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$this->tpl->parseCurrentBlock();
			}


			/* Language */
			foreach($ids = $this->md_section->getLanguageIds() as $id)
			{
				$md_lang = $this->md_section->getLanguage($id);
				
				$this->ctrl->setParameter($this, 'meta_index', $id);
				$this->ctrl->setParameter($this, 'meta_path', 'educational_language');

				$this->tpl->setCurrentBlock("language_loop");
				$this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE",
					$this->__showLanguageSelect('educational[Language]['.$id.']',
						$md_lang->getLanguageCode()));

				$this->tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE",
					$this->ctrl->getLinkTarget($this, "deleteElement"));
				$this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$this->ctrl->setParameter($this, "section_element", "educational_language");
				$this->tpl->setVariable("LANGUAGE_LOOP_ACTION_ADD",
					$this->ctrl->getLinkTarget($this, "addSectionElement"));
				$this->tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$this->tpl->parseCurrentBlock();

			}

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

			$this->tpl->setCurrentBlock("educational");
			$this->tpl->parseCurrentBlock();
		}
	}

	function updateEducational()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		// update rights section
		$this->md_section = $this->md_obj->getEducational();
		$this->md_section->setInteractivityType($_POST['educational']['InteractivityType']);
		$this->md_section->setLearningResourceType($_POST['educational']['LearningResourceType']);
		$this->md_section->setInteractivityLevel($_POST['educational']['InteractivityLevel']);
		$this->md_section->setSemanticDensity($_POST['educational']['SemanticDensity']);
		$this->md_section->setIntendedEndUserRole($_POST['educational']['IntendedEndUserRole']);
		$this->md_section->setContext($_POST['educational']['Context']);
		$this->md_section->setDifficulty($_POST['educational']['Difficulty']);
		$this->md_section->setTypicalLearningTime(ilUtil::stripSlashes($_POST['educational']['TypicalLearningTime']));

		/* TypicalAgeRange */
		foreach($ids = $this->md_section->getTypicalAgeRangeIds() as $id)
		{
			$md_age = $this->md_section->getTypicalAgeRange($id);
			$md_age->setTypicalAgeRange(ilUtil::stripSlashes($_POST['educational']['TypicalAgeRange'][$id][Value]));
			$md_age->setTypicalAgeRangeLanguage(
				new ilMDLanguageItem($_POST['educational']['TypicalAgeRange'][$id]['Language']));
			$md_age->update();
		}

		/* Description */
		foreach($ids = $this->md_section->getDescriptionIds() as $id)
		{
			$md_des = $this->md_section->getDescription($id);
			$md_des->setDescription(ilUtil::stripSlashes($_POST['educational']['Description'][$id][Value]));
			$md_des->setDescriptionLanguage(
				new ilMDLanguageItem($_POST['educational']['Description'][$id]['Language']));
			$md_des->update();
		}

		/* Language */
		foreach($ids = $this->md_section->getLanguageIds() as $id)
		{
			$md_lang = $this->md_section->getLanguage($id);
			$md_lang->setLanguage(
				new ilMDLanguageItem($_POST['educational']['Language'][$id]));
			$md_lang->update();
		}
		
		$this->md_section->update();

		$this->listSection();
	}

	function deleteElement()
	{
		include_once 'Services/MetaData/classes/class.ilMDFactory.php';

		$md_element = ilMDFactory::_getInstance($_GET['meta_path'],$_GET['meta_index']);
		$md_element->delete();
		
		$this->listSection();

		return true;
	}
	
	function deleteSection()
	{
		include_once 'Services/MetaData/classes/class.ilMDFactory.php';

		$md_element = ilMDFactory::_getInstance($_GET['section'],$_GET['meta_index']);
		$md_element->delete();
		
		$this->listSection();

		return true;
	}
	
	function addSection()
	{
		// Switch section
		switch($_GET['section'])
		{
			case 'meta_rights':
				$this->md_section = $this->md_obj->addRights();
				$this->md_section->save();
				break;
				
			case 'meta_educational':
				$this->md_section = $this->md_obj->addEducational();
				$this->md_section->save();
				break;

		}
		
		$this->listSection();
		return true;
	}

	function addSectionElement()
	{
		// Switch section
		switch($_GET['section'])
		{
			case 'meta_general':
				$this->md_section = $this->md_obj->getGeneral();
				break;
				
			case 'meta_educational':
				$this->md_section = $this->md_obj->getEducational();
				break;

		}

		// Switch new element
		$section_element = (empty($_POST['section_element']))
			? $_GET['section_element']
			: $_POST['section_element'];
			
		switch($section_element)
		{
			case 'meta_identifier':
				$md_new = $this->md_section->addIdentifier();
				break;

			case 'educational_language':
			case 'meta_language':
				$md_new = $this->md_section->addLanguage();
				break;

			case 'educational_description':
			case 'meta_description':
				$md_new = $this->md_section->addDescription();
				break;

			case 'meta_keyword':
				$md_new = $this->md_section->addKeyword();
				break;

			case 'educational_typical_age_range':
				$md_new = $this->md_section->addTypicalAgeRange();
				break;
		}

		$md_new->save();

		$this->listSection();

		return true;
	}

	function listSection()
	{
		switch($_REQUEST['section'])
		{
			case 'meta_general':
				return $this->listGeneral();

			case 'debug':
				return $this->debug();
				
			case 'meta_rights':
				return $this->listRights();
				
			case 'meta_educational':
				return $this->listEducational();

			default:
				return $this->listGeneral();
		}
	}		


	// PRIVATE
	function __fillSubelements()
	{
		if(count($subs = $this->md_section->getPossibleSubelements()))
		{
			$subs = array_merge(array('' => 'meta_please_select'),$subs);

			$this->tpl->setCurrentBlock("subelements");
			$this->tpl->setVariable("SEL_SUBELEMENTS",ilUtil::formSelect('','section_element',$subs));
			$this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}



	function __setTabs($a_active)
	{
		$tabs = array('meta_general' => 'listGeneral',
					  'meta_lifecycle' => 'listLifecycle',
					  'meta_meta_metadata'	=> 'listMetaMetadata',
					  'meta_technical' => 'listTechnical',
					  'meta_educational' => 'listEducational',
					  'meta_rights' => 'listRights',
					  'meta_relation' => 'listRelation',
					  'meta_annotation' => 'listAnnotation',
					  'meta_classification' => 'listClassification',
					  'debug' => 'debug');

		foreach($tabs as $key => $target)
		{
			$this->tpl->setCurrentBlock("md_tabs");
			if($a_active == $key)
			{
				$this->tpl->setVariable("TAB_CLASS",'tabactive');
			}
			else
			{
				$this->tpl->setVariable("TAB_CLASS",'tabinactive');
			}
			$this->ctrl->setParameter($this,'section',$key);
			$this->tpl->setVariable("TAB_HREF",$this->ctrl->getLinkTarget($this,'listSection'));
			$this->tpl->setVariable("TAB_TXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}


	/**
	* shows language select box
	*/
	function __showLanguageSelect($a_name, $a_value = "")
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$tpl = new ilTemplate("tpl.lang_selection.html", true, true);

		foreach(ilMDLanguageItem::_getLanguages() as $code => $text)
		{
			$tpl->setCurrentBlock("lg_option");
			$tpl->setVariable("VAL_LG", $code);
			$tpl->setVariable("TXT_LG", $text);

			if ($a_value != "" &&
				$a_value == $code)
			{
				$tpl->setVariable("SELECTED", "selected");
			}

			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$tpl->setVariable("SEL_NAME", $a_name);

		$return = $tpl->get();
		unset($tpl);

		return $return;
	}

				
		

	// Observer methods
	function addObserver(&$a_class,$a_method,$a_element)
	{
		$this->observers[$a_element]['class'] =& $a_class;
		$this->observers[$a_element]['method'] =& $a_method;

		return true;
	}
	function callListeners($a_element)
	{
		if(isset($this->observers[$a_element]))
		{
			$class =& $this->observers[$a_element]['class'];
			$method = $this->observers[$a_element]['method'];

			return $class->$method($a_element);
		}
		return false;
	}
			

}
?>