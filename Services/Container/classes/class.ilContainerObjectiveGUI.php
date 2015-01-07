<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


include_once("./Services/Container/classes/class.ilContainerContentGUI.php");

/**
* GUI class for course objective view
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerObjectiveGUI extends ilContainerContentGUI
{
	protected $force_details = 0;
	protected $loc_settings; // [ilLOSettings]
	
	const MATERIALS_TESTS = 1;
	const MATERIALS_OTHER = 2;
	
	private $output_html = '';
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object container gui object
	 * @return
	 */
	public function __construct($a_container_gui)
	{
		global $lng;
		
		$this->lng = $lng;
		parent::__construct($a_container_gui);
		
		$this->initDetails();
	}
	
	/**
	 * get details level
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getDetailsLevel($a_objective_id)
	{
		// no details anymore
		return self::DETAILS_ALL;
		
		/*
		if($a_objective_id == $this->force_details)
		{
			return self::DETAILS_ALL;
		}
		return $this->details_level;		 
		*/
	}
	
	/**
	 * Impementation of abstract method getMainContent
	 *
	 * @access public
	 * @return
	 */
	public function getMainContent()
	{
		global $lng,$ilTabs,$ilAccess;

		// see bug #7452
//		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');


		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

		$tpl = new ilTemplate("tpl.container_page.html", true, true,"Services/Container");

		if($GLOBALS['ilAccess']->checkAccess('write','',$this->getContainerObject()->getRefId()))
		{
			// check for results
			include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
			if(ilLOUserResults::hasResults($this->getContainerObject()->getId(),$GLOBALS['ilUser']->getId()))
			{
				include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
				$ilToolbar = new ilToolbarGUI();
				$ilToolbar->addButton(
						$lng->txt('crs_reset_results'), 
						$GLOBALS['ilCtrl']->getLinkTargetByClass(get_class($this->getContainerGUI()),'reset')
				);
			}
			
		}

		// Feedback
		// @todo
//		$this->__showFeedBack();

		$this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
	
		$is_manage = $this->getContainerGUI()->isActiveAdministrationPanel();			
		$is_order = $this->getContainerGUI()->isActiveOrdering();		
		
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$this->loc_settings = ilLOSettings::getInstanceByObjId($this->getContainerObject()->getId());	
					
		$this->initRenderer();			
	
		if(!$is_manage && !$is_order)
		{						
			// currently inactive
			// $this->showStatus($tpl);
		}
		if(!$is_manage)
		{
			$this->showObjectives($tpl, $is_order);						
			
			// $this->showMaterials($tpl,self::MATERIALS_TESTS, false, !$is_order);		
			if(
				$this->loc_settings->getQualifiedTest() &&
				$this->loc_settings->isGeneralQualifiedTestVisible()
			)
			{
				$this->output_html .= $this->renderTest($this->loc_settings->getQualifiedTest(), null, false, true);
			}
			
			$this->showMaterials($tpl,self::MATERIALS_OTHER, false, !$is_order);
		}
		else
		{
			$this->showMaterials($tpl, null, $is_manage);
		}

		// reset results by setting or for admins
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		if(
			ilLOSettings::getInstanceByObjId($this->getContainerObject()->getId())->isResetResultsEnabled() or
			$GLOBALS['ilAccess']->checkAccess('write','',$this->getContainerObject()->getRefId())
		)
		{
			// check for results
			include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
			if(ilLOUserResults::hasResults($this->getContainerObject()->getId(),$GLOBALS['ilUser']->getId()))
			{
				if (!$is_manage && !$is_order)
				{
					$this->showButton('askReset',$lng->txt('crs_reset_results'));
				}
			}
		}

		$tpl->setVariable('CONTAINER_PAGE_CONTENT',$this->output_html);		
		
		return $tpl->get();
	}
	
	/**
	 * show status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function showStatus($tpl)
	{
		global $ilUser,$lng;
		
		include_once('./Modules/Course/classes/class.ilCourseObjectiveResultCache.php');		
		
		$status = ilCourseObjectiveResultCache::getStatus($ilUser->getId(),$this->getContainerObject()->getId());
		if($status == IL_OBJECTIVE_STATUS_EMPTY) {
			return;
		}
		$info_tpl = new ilTemplate('tpl.crs_objectives_view_info_table.html',true,true,'Modules/Course');
		$info_tpl->setVariable("INFO_STRING",$lng->txt('crs_objectives_info_'.$status));

		$this->output_html .= $info_tpl->get();
	}
	
	/**
	 * show objectives
	 *
	 * @access public
	 * @param object $tpl template object
	 * @return
	 */
	public function showObjectives($a_tpl, $a_is_order = false)
	{
		global $lng,$ilSetting;
		
		$this->clearAdminCommandsDetermination();
		
		// get embedded blocks
		$has_container_page = false;
		if(!$a_is_order)
		{
			$output_html = $this->getContainerGUI()->getContainerPageHTML();				
			if ($output_html != "")
			{
				$has_container_page = true;
				$this->output_html .= $this->insertPageEmbeddedBlocks($output_html);
			}
			unset($output_html);
		}
		
		// All objectives
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId(),true)))
		{
			return false;
		}
		
		include_once('./Modules/Course/classes/class.ilCourseObjectiveListGUI.php');
		$this->objective_list_gui = new ilCourseObjectiveListGUI();
		$this->objective_list_gui->setContainerObject($this->getContainerGUI());
		if ($ilSetting->get("icon_position_in_lists") == "item_rows")
		{
			$this->objective_list_gui->enableIcon(true);
		}
		
		$acc = null;
		if(!$a_is_order)
		{
			include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
			$acc = new ilAccordionGUI();
			$acc->setUseSessionStorage(true);
			$acc->setAllowMultiOpened(true);
			$acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
			$acc->setId("crsobjtv_".$this->container_obj->getId());
		}
		else
		{
			$this->renderer->addCustomBlock('lobj',$lng->txt('crs_objectives'));
		}
		
		$lur_data = $this->parseLOUserResults();
		
		$has_initial = ilLOSettings::getInstanceByObjId($this->container_obj->getId())->worksWithInitialTest();				
		
		$has_lo_page = false;
		$obj_cnt = 0;
		foreach($objective_ids as $objective_id)
		{
			include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
			if(
				$has_initial &&
				(
					!isset($lur_data[$objective_id]) or 
					ilLOUtils::hasActiveRun(
						$this->container_obj->getId(),
						ilLOSettings::getInstanceByObjId($this->container_obj->getId())->getInitialTest(),
						$objective_id)
				)
			)
			{
				$lur_data[$objective_id] = array("type"=>ilLOSettings::TYPE_TEST_INITIAL);
			}

			if($html = $this->renderObjective($objective_id, $has_lo_page, $acc, $lur_data[$objective_id]))
			{
				$this->renderer->addItemToBlock('lobj', 'lobj', $objective_id, $html);
			}
			$obj_cnt++;
		}

		// buttons for showing/hiding all objectives
		if (!$a_is_order && $obj_cnt > 1)
		{
			$this->showButton("", $lng->txt("crs_show_all_obj"), "", "crs_show_all_obj_btn");
			$this->showButton("", $lng->txt("crs_hide_all_obj"), "", "crs_hide_all_obj_btn");
			$acc->setShowAllElement("crs_show_all_obj_btn");
			$acc->setHideAllElement("crs_hide_all_obj_btn");
		}
		
		if(!$has_container_page && $has_lo_page)
		{			
			// add core co page css
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			$GLOBALS["tpl"]->setVariable("LOCATION_CONTENT_STYLESHEET",
				ilObjStyleSheet::getContentStylePath(0));
			$GLOBALS["tpl"]->setCurrentBlock("SyntaxStyle");
			$GLOBALS["tpl"]->setVariable("LOCATION_SYNTAX_STYLESHEET",
				ilObjStyleSheet::getSyntaxStylePath());			
			$GLOBALS["tpl"]->parseCurrentBlock();			
		}			
	
		// order/block
		if($a_is_order)
		{					
			$this->addFooterRow();
		
			$this->output_html .= $output_html.$this->renderer->getHTML();

			$this->renderer->resetDetails();					
		}
		// view/accordion
		else
		{
			$this->output_html .= "<div class='ilCrsObjAcc'>".$acc->getHTML()."</div>";
		}		
	}
	
	/**
	 * add footer row
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function addFooterRow()
	{		
		// no details
		return;
		
		/*
		global $ilCtrl;
				
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getContainerObject()->getRefId());
		$ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "1");
		$url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");		
		$this->renderer->addDetailsLevel(2, $url, ($this->details_level == self::DETAILS_TITLE));
		
		$ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "2");
		$url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");		
		$this->renderer->addDetailsLevel(3, $url, ($this->details_level == self::DETAILS_ALL));
		
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);	
		*/
	}		
	
	protected function renderTest($a_test_ref_id, $a_objective_id, $a_is_initial = false, $a_add_border = false, $a_lo_result = array())
	{
		
		$node_data = $GLOBALS['tree']->getNodeData($a_test_ref_id);

		// update ti
		if($a_objective_id)
		{
			if($a_is_initial)
			{
				$title = sprintf($this->lng->txt('crs_loc_itst_for_objective'),  ilCourseObjective::lookupObjectiveTitle($a_objective_id));
			}
			else
			{
				$title = sprintf($this->lng->txt('crs_loc_qtst_for_objective'),  ilCourseObjective::lookupObjectiveTitle($a_objective_id));				
			}
			$node_data['objective_id'] = $a_objective_id;
			$node_data['objective_status'] = 
				(
					$a_lo_result['status'] == ilLOUserResults::STATUS_COMPLETED ?
					false : 
					false
				);
		}
		else
		{
			$obj_id = ilObject::_lookupObjId($a_test_ref_id);
			$title = ilObject::_lookupTitle($obj_id);
			
			$title .= (
					' ('.
					(
						$a_is_initial
							? $this->lng->txt('crs_loc_itest_info')
							: $this->lng->txt('crs_loc_qtest_info')
					).
					')'
			);
			$node_data['objective_id'] = 0;
		}
		
		$node_data['title'] = $title;
		
		return "<div class='ilContObjectivesViewTestItem'>".$this->renderItem($node_data)."</div>";
	}
	
	/**
	 * Show all other (no assigned tests, no assigned materials) materials
	 *
	 * @access public
	 * @param object $tpl template object
	 * @return void
	 */
	public function showMaterials($a_tpl,$a_mode = null,$a_is_manage = false,$a_as_accordion = false)
	{
		global $ilAccess, $lng;

		$this->clearAdminCommandsDetermination();
		
		if (is_array($this->items["_all"]))
		{								
			$this->objective_map = $this->buildObjectiveMap();			
			
			// all rows
			$item_r = array();
			
			$position = 1;
			foreach($this->items["_all"] as $k => $item_data)
			{
				if($a_mode == self::MATERIALS_TESTS and $item_data['type'] != 'tst')
				{
					continue;
				}
				if ($item_data['type'] == 'itgr')
				{
					continue;
				}
				if(!$a_is_manage)
				{
					// if test object is qualified or initial do not show here
					if($this->objective_map["test_i"] && $item_data["child"] == $this->objective_map["test_i"])
					{
						continue;
					}
					if($this->objective_map["test_q"] && $item_data["child"] == $this->objective_map["test_q"])
					{
						continue;
					}	
				}
				
				if($this->rendered_items[$item_data["child"]] !== true &&
					!$this->renderer->hasItem($item_data["child"]))
				{
					$this->rendered_items[$item_data['child']] = true;
					
					// TODO: Position (DONE ?)
					$html = $this->renderItem($item_data,$position++,$a_mode == self::MATERIALS_TESTS ? false : true);
					if ($html != "")
					{
						$item_r[] = array("html" => $html, "id" => $item_data["child"], "type" => $item_data["type"]);
					}
				}
			}
			
			// if we have at least one item, output the block
			if (count($item_r) > 0)
			{				
				if(!$a_as_accordion)
				{		
					$pos = 0;
					
					switch($a_mode)
					{
						case self::MATERIALS_TESTS:
							$block_id = "tst";
							$this->renderer->addTypeBlock($block_id);						
							break;

						case self::MATERIALS_OTHER:
							$block_id = "oth";
							$this->renderer->addCustomBlock($block_id, $lng->txt('crs_other_resources'));					
							break;
						
						// manage
						default:
							$block_id = "all";
							$this->renderer->addCustomBlock($block_id, $lng->txt('content'));					
							break;
					}				

					// :TODO:
					if ($a_mode != self::MATERIALS_TESTS)
					{
						$pos = $this->getItemGroupsHTML();
					}
				
					foreach($item_r as $h)
					{
						if(!$this->renderer->hasItem($h["id"]))
						{					
							$this->renderer->addItemToBlock($block_id, $h["type"], $h["id"], $h["html"]);		
						}
					}
					
					$this->output_html .= $this->renderer->getHTML();
				}
				else
				{
					switch($a_mode)
					{
						case self::MATERIALS_TESTS:
							$txt = $lng->txt('objs_tst');
							break;

						case self::MATERIALS_OTHER:
							$txt = $lng->txt('crs_other_resources');
							break;
					}
					
					include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
					$acc = new ilAccordionGUI();
					$acc->setId("crsobjtvmat".$a_mode."_".$this->container_obj->getId());
					
					$acc_content = array();
					foreach($item_r as $h)
					{
						$acc_content[] = $h["html"];
					}							
					$acc->addItem($txt, $this->buildAccordionContent($acc_content));
					
					$this->output_html .= $acc->getHTML();
				}					
			}
		}
	}
	
	protected function buildObjectiveMap()
	{
		$objective_map = array();
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		// begin-patch lok
		if(count($objective_ids = ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId(),true)))
		// end-patch lok
		{				
			include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');					
			foreach($objective_ids as $objective_id)
			{													
				foreach(ilCourseObjectiveMaterials::_getAssignedMaterials($objective_id) as $mat_ref_id)
				{
					$objective_map["material"][$mat_ref_id][] = $objective_id;

					if(!isset($objective_map["names"][$objective_id]))
					{
						$objective = new ilCourseObjective($this->getContainerObject(), $objective_id);
						$objective_map["names"][$objective_id] = $objective->getTitle();
					}
				}								
			}

			// initial/qualifying test
			$tst = $this->loc_settings->getInitialTest();								
			if($tst)
			{
				$objective_map["test_i"] = $tst;
			}
			$tst = $this->loc_settings->getQualifiedTest();
			if($tst)
			{
				$objective_map["test_q"] = $tst;
			}
		}
		
		return $objective_map;
	}
	
	protected function addItemDetails(ilObjectListGUI $a_item_list_gui, array $a_item)
	{
		global $lng, $ilCtrl;
						
		$item_ref_id = $a_item["ref_id"];
		
		if(is_array($this->objective_map))
		{
			$details = array();
			if(isset($this->objective_map["material"][$item_ref_id]))
			{				
				// #12965
				foreach($this->objective_map["material"][$item_ref_id] as $objective_id)
				{
					$ilCtrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', $objective_id);
					$url = $ilCtrl->getLinkTargetByClass(array('illoeditorgui', 'ilcourseobjectivesgui'), 'edit');
					$ilCtrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', '');

					$details[] = array(
						'desc' => $lng->txt('crs_loc_tab_materials').': ',
						'target' => '_top',
						'link' => $url,
						'name' => $this->objective_map["names"][$objective_id]
					);
				}
			}
			if($this->objective_map["test_i"] == $item_ref_id)
			{			
				$ilCtrl->setParameterByClass('illoeditorgui', 'tt', 1);
				$details[] = array(
					'desc' => '',
					'target' => '_top',
					'link' => $ilCtrl->getLinkTargetByClass('illoeditorgui', 'testOverview'),
					'name' => $lng->txt('crs_loc_tab_itest')
				);			
				$ilCtrl->setParameterByClass('illoeditorgui', 'tt', 0);
			}
			if($this->objective_map["test_q"] == $item_ref_id)
			{			
				$ilCtrl->setParameterByClass('illoeditorgui', 'tt', 2);
				$details[] = array(
					'desc' => '',
					'target' => '_top',
					'link' => $ilCtrl->getLinkTargetByClass('illoeditorgui', 'testOverview'),
					'name' => $lng->txt('crs_loc_tab_qtest')
				);			
				$ilCtrl->setParameterByClass('illoeditorgui', 'tt', 0);
			}
		
			if(sizeof($details))
			{
				$a_item_list_gui->enableItemDetailLinks(true);
				$a_item_list_gui->setItemDetailLinks($details, $lng->txt('crs_loc_settings_tbl').': ');
			}
			else
			{
				$a_item_list_gui->enableItemDetailLinks(false);
			}
		}
		
		// order
		if($this->getContainerGUI()->isActiveOrdering())
		{
			$a_item_list_gui->enableCommands(true, true);
			$a_item_list_gui->enableProperties(false);
		}
		// view
		else if(!$this->getContainerGUI()->isActiveAdministrationPanel())
		{
			$a_item_list_gui->enableCommands(true, true);
			$a_item_list_gui->enableProperties(false);
		}
		
		if($a_item['objective_id'])
		{
			$a_item_list_gui->setDefaultCommandParameters(array('objective_id' => $a_item['objective_id']));
			
			
			if($this->loc_settings->getQualifiedTest() == $a_item['ref_id'])
			{
				$a_item_list_gui->setConditionTarget($this->getContainerObject()->getRefId(), $a_item['objective_id'], 'lobj');
				// check conditions of target
				include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
				$fullfilled = ilConditionHandler::_checkAllConditionsOfTarget($this->getContainerObject()->getRefId(),$a_item['objective_id'],'lobj');
				if(!$fullfilled || $a_item['objective_status'])
				{
					$a_item_list_gui->disableTitleLink(true);
				}
			}
			include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
			$res = ilLOUserResults::lookupResult(
					$this->getContainerObject()->getId(),
					$GLOBALS['ilUser']->getId(),
					$a_item['objective_id'], 
					ilLOUserResults::TYPE_QUALIFIED);
			
			$res = $this->updateResult($res,$a_item['ref_id'],$a_item['objective_id'],$GLOBALS['ilUser']->getId());
			
			if($res['is_final'])
			{
				$a_item_list_gui->disableTitleLink(true);
				$a_item_list_gui->enableProperties(true);
				$a_item_list_gui->addCustomProperty(
						$this->lng->txt('crs_loc_passes_reached'),
						'',
						true
				);
			}
			elseif($this->loc_settings->getQualifiedTest() == $a_item['ref_id'])
			{
				include_once './Modules/Course/classes/class.ilCourseObjective.php';
				$poss_pass = ilCourseObjective::lookupMaxPasses($a_item['objective_id']);
				
				if($poss_pass)
				{
					$a_item_list_gui->enableProperties(true);
					$a_item_list_gui->addCustomProperty(
							$this->lng->txt('crs_loc_passes_left'),
							(($poss_pass - $res['tries']) > 0) ? ($poss_pass - $res['tries']) : 1,
							false
					);
				}
			}
		}
	}
	
	protected function updateResult($a_res,$a_item_ref_id,$a_objective_id,$a_user_id)
	{
		
		if($this->loc_settings->getQualifiedTest() == $a_item_ref_id)
		{
			// Check for existing test run, and decrease tries, reset final if run exists
			include_once './Modules/Test/classes/class.ilObjTest.php';
			include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
			$active = ilObjTest::isParticipantsLastPassActive(
				$a_item_ref_id,
				$a_user_id);
			
			if($active)
			{
				include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
				if(ilLOTestRun::lookupRunExistsForObjective(
						ilObject::_lookupObjId($a_item_ref_id),
						$a_objective_id, 
						$a_user_id))
				{
					($a_res['tries'] > 0) ? --$a_res['tries'] : 0;
					$a_res['is_final'] = 0;
				}
			}
		}
		return $a_res;
		
	}
	
	/**
	 * render objective
	 *
	 * @access protected
	 * @param int objective id
	 * @param bool co page status
	 * @param ilAccordionGUI $a_accordion 
	 * @param array $a_lo_result 
	 * @return string html
	 */
	protected function renderObjective($a_objective_id, &$a_has_lo_page, ilAccordionGUI $a_accordion = null, array $a_lo_result = null)
	{
		global $ilUser,$lng;
		
		include_once('./Modules/Course/classes/class.ilCourseObjective.php');
		$objective = new ilCourseObjective($this->getContainerObject(),$a_objective_id);
		
		include_once('./Services/Container/classes/class.ilContainerSorting.php');
		include_once('./Services/Object/classes/class.ilObjectActivation.php');
		$items = ilObjectActivation::getItemsByObjective($a_objective_id);
		
		// sorting is handled by ilCourseObjectiveMaterials
		// $items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('lobj',$a_objective_id,$items);
		
		include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
		$objectives_lm_obj = new ilCourseObjectiveMaterials($a_objective_id);
		
		// #13381 - map material assignment to position
		$sort_map = array();
		foreach($objectives_lm_obj->getMaterials() as $item)
		{
			$sort_map[$item["lm_ass_id"]] = $item["position"];			
		}
	
		$is_manage = $this->getContainerGUI()->isActiveAdministrationPanel();			
		$is_order = $this->getContainerGUI()->isActiveOrdering();
				
		$sort_content = array();
		
		foreach($items as $item) 
		{
			if($this->getDetailsLevel($a_objective_id) < self::DETAILS_ALL)
			{
				continue;
			}
									
			$item_list_gui2 = $this->getItemGUI($item);
			$item_list_gui2->enableIcon(true);
			
			if($is_order || $a_accordion)
			{
				$item_list_gui2->enableCommands(true, true);
				$item_list_gui2->enableProperties(false);
			}				
						
			$chapters = $objectives_lm_obj->getChapters();
			if(count($chapters))
			{								
				$has_sections = false;
				foreach($chapters as $chapter)
				{
					if($chapter['ref_id'] != $item['child'])
					{
						continue;
					}
					$has_sections = true;

					include_once './Modules/LearningModule/classes/class.ilLMObject.php';
					$title =  $item['title'].
						" &rsaquo; ".ilLMObject::_lookupTitle($chapter['obj_id']).
						" (".$lng->txt('obj_'.$chapter['type']).")";

					$item_list_gui2->setDefaultCommandParameters(array(
						"obj_id" => $chapter['obj_id'],
						"focus_id" => $chapter['obj_id'],
						"focus_return" => $this->container_obj->getRefId()));

					if($is_order)
					{
						$item_list_gui2->setPositionInputField("[lobj][".$a_objective_id."][".$chapter['lm_ass_id']."]",
							sprintf('%d', $chapter['position']*10));		
					}

					$sub_item_html = $item_list_gui2->getListItemHTML($item['ref_id'],
						$item['obj_id'], $title, $item['description']);				
					
					// #13381 - use materials order
					$sort_key = str_pad($chapter['position'], 5, 0, STR_PAD_LEFT)."_".strtolower($title)."_".$chapter['lm_ass_id'];
					$sort_content[$sort_key] = $sub_item_html;					
				}				
			}		

			$this->rendered_items[$item['child']] = true;
			
			if($lm_ass_id = $objectives_lm_obj->isAssigned($item['ref_id'], true))			
			{														
				if($is_order)
				{
					$item_list_gui2->setPositionInputField("[lobj][".$a_objective_id."][".$lm_ass_id."]",
						sprintf('%d', $sort_map[$lm_ass_id]*10));
				}
								
				$sub_item_html = $item_list_gui2->getListItemHTML($item['ref_id'],
					$item['obj_id'], $item['title'], $item['description']);			
								
				// #13381 - use materials order
				$sort_key = str_pad($sort_map[$lm_ass_id], 5, 0, STR_PAD_LEFT)."_".strtolower($item['title'])."_".$lm_ass_id;
				$sort_content[$sort_key]  = $sub_item_html;				
			}					
		}
		
		if($this->getDetailsLevel($a_objective_id) == self::DETAILS_ALL)
		{
			$this->objective_list_gui->enableCommands(false);
		}
		else
		{
			$this->objective_list_gui->enableCommands(true);
		}
		
		if($is_order)
		{					
			$this->objective_list_gui->setPositionInputField("[lobj][".$a_objective_id."][0]",
				$objective->__getPosition()*10);
		}
			
		ksort($sort_content);
		
		if(!$a_accordion)
		{		
			foreach($sort_content as $sub_item_html)
			{
				$this->objective_list_gui->addSubItemHTML($sub_item_html);	
			}
			
			return $this->objective_list_gui->getListItemHTML(
				0,
				$a_objective_id,
				$objective->getTitle(),
				$objective->getDescription(),
				($is_manage || $is_order));
		}
		else
		{			
			$acc_content = $sort_content;
			
			$initial_shown = false;
			if($this->loc_settings->getInitialTest() &&
				$this->loc_settings->getType() == ilLOSettings::LOC_INITIAL_SEL &&
				!$a_lo_result["initial_status"])
			{
				$acc_content[] = $this->renderTest($this->loc_settings->getInitialTest(), $a_objective_id, true, false, $a_lo_result);
				$initial_shown = true;
			}	
			
			if(!$initial_shown &&
				$this->loc_settings->getQualifiedTest() && 
				$this->loc_settings->isQualifiedTestPerObjectiveVisible())
			{
				$acc_content[] = $this->renderTest($this->loc_settings->getQualifiedTest(), $a_objective_id, false, false, $a_lo_result);
			}
			
			$co_page = null;
			include_once("./Services/COPage/classes/class.ilPageUtil.php");
			if(ilPageUtil::_existsAndNotEmpty("lobj", $objective->getObjectiveId()))
			{
				$a_has_lo_page = true;
				
				include_once 'Modules/Course/classes/Objectives/class.ilLOPageGUI.php';
				$page_gui = new ilLOPageGUI($objective->getObjectiveId());
				
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));
				$page_gui->setPresentationTitle("");
				$page_gui->setTemplateOutput(false);
				$page_gui->setHeader("");
				
				$co_page = "<div class='ilContObjectiveIntro'>".$page_gui->showPage()."</div>";
			}
			
			$a_accordion->addItem(
				$this->buildAccordionTitle($objective, $a_lo_result), 
				$co_page.
				$this->buildAccordionContent($acc_content)
			);
		}
	}
	
	/**
	 * init details
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initDetails()
	{
		global $ilUser;
				
		// no details
		return;
		
		/*
		if(isset($_GET['details_level']))
		{
			$this->details_level = (int) $_GET['details_level'];
			ilObjUser::_writePref($ilUser->getId(),'crs_objectives_details',$this->details_level);
		}
		else
		{
			$this->details_level = $ilUser->getPref('crs_objectives_details') ? $ilUser->getPref('crs_objectives_details') : self::DETAILS_TITLE;
		}
		if(isset($_GET['objective_details']))
		{
			$this->force_details = (int) $_GET['objective_details'];
			ilObjUser::_writePref($ilUser->getId(),'crs_objectives_force_details_'.$this->getContainerObject()->getId(),$this->force_details);
		}
		elseif($details_id = $ilUser->getPref('crs_objectives_force_details_'.$this->getContainerObject()->getId()))
		{
			$this->force_details = $details_id;
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			include_once('./Modules/Course/classes/class.ilCourseObjectiveResultCache.php');
			foreach(ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId()) as $objective_id)
			{
				if(ilCourseObjectiveResultCache::isSuggested($ilUser->getId(),$this->getContainerObject()->getId(),$objective_id))
				{
					$this->force_details = $objective_id;
					break;
				}
			}
		}
		return true;		 
		*/
	}
	
	protected function parseLOUserResults()
	{
		global $ilUser;
		
		$res = array();
		
		include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
		$lur = new ilLOUserResults($this->getContainerObject()->getId(), $ilUser->getId());		
		foreach($lur->getCourseResultsForUserPresentation() as $objective_id => $types)
		{
			// show either initial or qualified for objective
			if(isset($types[ilLOUserResults::TYPE_INITIAL]))
			{
				$initial_status = $types[ilLOUserResults::TYPE_INITIAL]["status"];
			}
			
			// qualified test has priority
			if(isset($types[ilLOUserResults::TYPE_QUALIFIED]))
			{
				$result = $types[ilLOUserResults::TYPE_QUALIFIED];	
				$result["type"] = ilLOUserResults::TYPE_QUALIFIED;				
			}
			else
			{
				$result = $types[ilLOUserResults::TYPE_INITIAL];
				$result["type"] = ilLOUserResults::TYPE_INITIAL;
			}		
						
			$result["initial_status"] = $initial_status;
									
			$res[$objective_id] = $result;
		}
		
		return $res;
	}
	
	public static function buildObjectiveProgressBar($a_has_initial_test, $a_objective_id, array $a_lo_result, $a_list_mode = false)
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.objective_progressbar.html", true, true, "Services/Container");
		
		$tooltip_id = "crsobjtvusr_".$a_objective_id;
					
		$tt_txt = sprintf($lng->txt("crs_loc_tt_info"),
			$a_lo_result["result_perc"], $a_lo_result["limit_perc"]);
		
		// #12970
		$perc_resize = (int)$a_lo_result["result_perc"];
		/*
		if($a_lo_result["limit_perc"] && $a_lo_result["result_perc"])
		{
			$perc_resize = min(round($a_lo_result["result_perc"]*(100/$a_lo_result["limit_perc"])), 100);
		}
		*/ 
		
		$next_step = $progress_txt = $bar_color = null;
	
		// qualifying test
		if($a_lo_result["type"] == ilLOUserResults::TYPE_QUALIFIED)
		{
			$progress_txt = $lng->txt("crs_loc_progress_result_qtest");
			$tt_txt = $lng->txt("crs_loc_tab_qtest").": ".$tt_txt;						
										
			if($a_lo_result["status"] == ilLOUserResults::STATUS_COMPLETED)
			{			
				$next_step = $lng->txt("crs_loc_progress_objective_complete");		
				$bar_color = "#80f080";
			}
			else
			{
				$next_step = $lng->txt("crs_loc_progress_do_qualifying_again");
				$bar_color = "#f08080";
			}
		}					
		// initial test
		else 
		{						
			if($a_lo_result["status"])
			{									
				$progress_txt = $lng->txt("crs_loc_progress_result_itest");
				$tt_txt = $lng->txt("crs_loc_tab_itest").": ".$tt_txt;		
				
				$bar_color = "#aaa";
									
				if($a_lo_result["status"] == ilLOUserResults::STATUS_COMPLETED)
				{
					$next_step = $lng->txt("crs_loc_progress_do_qualifying");
				}
				else
				{
					$next_step = $lng->txt("crs_loc_suggested");		
				}
			}
			// not attempted: no progress bar
			else 
			{
				if((bool)$a_has_initial_test)
				{
					$next_step = $lng->txt("crs_loc_progress_no_result_do_initial");					
				}
				else
				{
					$next_step = $lng->txt("crs_loc_progress_no_result_no_initial");		
				}
			}
		}
		
		if($progress_txt)
		{
			$tpl->setCurrentBlock("statustxt_bl");
			$tpl->setVariable("TXT_PROGRESS_STATUS", $progress_txt);				
			$tpl->parseCurrentBlock();	
		}
		
		if($bar_color)
		{			
			if($a_lo_result["limit_perc"])
			{
				$limit_pos = (121-ceil(125/100*$a_lo_result["limit_perc"]))*-1;
			}
			else
			{
				$limit_pos = -121;
			}
			
			$tpl->setCurrentBlock("statusbar_bl");
			$tpl->setVariable("PERC_STATUS", $a_lo_result["result_perc"]);
			$tpl->setVariable("LIMIT_POS", $limit_pos);
			$tpl->setVariable("PERC_WIDTH", $perc_resize);
			$tpl->setVariable("PERC_COLOR", $bar_color);
			$tpl->setVariable("BG_COLOR", "#fff");
			$tpl->setVariable("TT_ID", $tooltip_id);
			$tpl->parseCurrentBlock();
		}
		
		if($next_step && !$a_list_mode)
		{
			$tpl->setCurrentBlock("nstep_bl");
			$tpl->setVariable("TXT_NEXT_STEP", $next_step);				
			$tpl->parseCurrentBlock();
		}

		if($tt_txt)
		{
			include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
			ilTooltipGUI::addTooltip($tooltip_id, $tt_txt);
		}
		
		return $tpl->get();
	}
	
	protected function buildAccordionTitle(ilCourseObjective $a_objective, array $a_lo_result = null)
	{	
		$tpl = new ilTemplate("tpl.objective_accordion_title.html", true, true, "Services/Container");
			
		if($a_lo_result)
		{			
			$tpl->setVariable("PROGRESS_BAR", self::buildObjectiveProgressBar(
				(bool)$this->loc_settings->getInitialTest(), 
				$a_objective->getObjectiveId(), 
				$a_lo_result)
			);
		}		
		
		// $tpl->setVariable("ICON_SRC", ilObject::_getIcon($a_objective->getObjectiveId(), "small", "lobj"));
		// $tpl->setVariable("ICON_TXT", $this->lng->txt("icon")." ".$this->lng->txt("crs_objectives"));
		$tpl->setVariable("TITLE", $this->lng->txt("crs_loc_learning_objective").": ".trim($a_objective->getTitle()));
		$tpl->setVariable("DESCRIPTION", nl2br(trim($a_objective->getDescription())));
				
		return $tpl->get();
	}
	
	protected function buildAccordionContent(array $a_items)
	{		
		$tpl = new ilTemplate("tpl.objective_accordion_content.html", true, true, "Services/Container");		
		foreach($a_items as $item)
		{
			$tpl->setCurrentBlock("items_bl");
			$tpl->setVariable("ITEM", $item);
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}
	
	/**
	 * show action button
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function showButton($a_cmd,$a_text,$a_target = '', $a_id = "")
	{
		global $ilToolbar, $ilCtrl;
		
		// #11842
		$ilToolbar->addButton($a_text,
			$ilCtrl->getLinkTarget($this->getContainerGUI(),$a_cmd),
			$a_target, "", '', $a_id);
	}
}
?>