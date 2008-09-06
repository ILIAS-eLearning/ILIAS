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
	
	const MATERIALS_TESTS = 1;
	const MATERIALS_OTHER = 2;
	
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
		if($a_objective_id == $this->force_details)
		{
			return self::DETAILS_ALL;
		}
		return $this->details_level;
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

		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');


		include_once './classes/class.ilObjectListGUIFactory.php';

		$tpl = new ilTemplate ("tpl.container_page.html", true, true,"Services/Container");

		// Feedback
		// @todo
//		$this->__showFeedBack();

		if($ilAccess->checkAccess('write','',$this->getContainerObject()->getRefId()) or 1)
		{
			$this->showButton('askReset',$lng->txt('crs_reset_results'));
		}

		$this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());

		$this->showStatus($tpl);
		$this->showObjectives($tpl);
		$this->showMaterials($tpl,self::MATERIALS_TESTS);
		$this->showMaterials($tpl,self::MATERIALS_OTHER);
			
		// @todo: Move this completely to GUI class?
		$this->getContainerGUI()->adminCommands = $this->adminCommands;
		$this->getContainerGUI()->showAdministrationPanel($tpl);
		$this->getContainerGUI()->showPossibleSubObjects();
		$this->getContainerGUI()->showPermanentLink($tpl);

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
		
		$tpl->setCurrentBlock('cont_page_content');
		
		$info_tpl = new ilTemplate('tpl.crs_objectives_view_info_table.html',true,true,'Modules/Course');
		$info_tpl->setVariable("INFO_STRING",$lng->txt('crs_objectives_info_'.
			ilCourseObjectiveResultCache::getStatus($ilUser->getId(),$this->getContainerObject()->getId())));
		
		$tpl->setVariable('CONTAINER_PAGE_CONTENT',$info_tpl->get());
		$tpl->parseCurrentBlock();
		
	}
	
	/**
	 * show objectives
	 *
	 * @access public
	 * @param object $tpl template object
	 * @return
	 */
	public function showObjectives($a_tpl)
	{
		global $lng,$ilSetting;
		
		$this->clearAdminCommandsDetermination();
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}

		$tpl = $this->newBlockTemplate();
		
		// All objectives
		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		if(!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId())))
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
		
		
		$item_html = array();
		foreach($objective_ids as $objective_id)
		{
			if($html = $this->renderObjective($objective_id))
			{
				$item_html[] = $html;
			}
		}
		
		// if we have at least one item, output the block
		if (count($item_html) > 0)
		{
			$this->addHeaderRow($tpl,'lobj',$lng->txt('crs_objectives'));
			foreach($item_html as $h)
			{
				$this->addStandardRow($tpl, $h);
			}
		}
		
		$this->addFooterRow($tpl);

		$output_html .= $tpl->get();
		$a_tpl->setCurrentBlock('cont_page_content');
		$a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
		$a_tpl->parseCurrentBlock();
		
	
	}
	
	/**
	 * add footer row
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function addFooterRow($tpl)
	{
		$tpl->setCurrentBlock('details_img');
		
		
		$append = $this->details_level == 2 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details2'.$append.'.gif'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 2');
		$tpl->setVariable('DETAILS_LINK','repository.php?ref_id='.$this->getContainerObject()->getRefId().'&details_level=2');
		$tpl->parseCurrentBlock();

		$append = $this->details_level == 3 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details3'.$append.'.gif'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 3');
		$tpl->setVariable('DETAILS_LINK','repository.php?ref_id='.$this->getContainerObject()->getRefId().'&details_level=3');
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock('container_details_row');
		$tpl->setVariable('TXT_DETAILS',$this->lng->txt('details'));
		$tpl->parseCurrentBlock();
	}
	
	
	
	/**
	 * Show all other (no assigned tests, no assigned materials) materials
	 *
	 * @access public
	 * @param object $tpl template object
	 * @return void
	 */
	public function showMaterials($a_tpl,$a_mode)
	{
		global $ilAccess, $lng;

		$this->clearAdminCommandsDetermination();
		
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}
		
		$tpl = $this->newBlockTemplate();
		if (is_array($this->items["_all"]))
		{
			// all rows
			$item_html = array();
			
			$position = 1;
			foreach($this->items["_all"] as $k => $item_data)
			{
				if($a_mode == self::MATERIALS_TESTS and $item_data['type'] != 'tst')
				{
					continue;
				}
				
				if($this->rendered_items[$item_data["child"]] !== true)
				{
					$this->rendered_items[$item_data['child']] = true;
					
					// TODO: Position (DONE ?)
					$html = $this->renderItem($item_data,$position++,$a_mode == self::MATERIALS_TESTS ? false : true);
					if ($html != "")
					{
						$item_html[] = $html;
					}
				}
			}
			
			// if we have at least one item, output the block
			if (count($item_html) > 0)
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
				
				$this->addHeaderRow($tpl,$a_mode == self::MATERIALS_TESTS ? 'tst' : '',$txt);
				foreach($item_html as $h)
				{
					$this->addStandardRow($tpl, $h);
				}
			}
		}

		$output_html .= $tpl->get();
		$a_tpl->setCurrentBlock('cont_page_content');
		$a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	 * render objective
	 *
	 * @access protected
	 * @param int objective id
	 * @return string html
	 */
	protected function renderObjective($a_objective_id)
	{
		global $ilUser,$lng;
		
		include_once('./Modules/Course/classes/class.ilCourseObjective.php');
		$objective = new ilCourseObjective($this->getContainerObject(),$a_objective_id);
		
		include_once('./Services/Container/classes/class.ilContainerSorting.php');
		$items = $this->getContainerObject()->getCourseItemObject()->getItemsByObjective($a_objective_id);
		$items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('lobj',$a_objective_id,$items);

		include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
		$objectives_lm_obj = new ilCourseObjectiveMaterials($a_objective_id);

		$pos = 1;
		foreach($items as $item) 
		{
			if($this->getDetailsLevel($a_objective_id) < self::DETAILS_ALL)
			{
				continue;
			}
			
			$chapters = $objectives_lm_obj->getChapters();
			
			$item_list_gui2 = $this->getItemGUI($item);
			$item_list_gui2->enableIcon(true);

			if(count($chapters))
			{
				$num = 0;
				$has_sections = false;
				foreach($chapters as $chapter)
				{
					if($chapter['ref_id'] != $item['child'])
					{
						continue;
					}
					$has_sections = true;
					include_once './Modules/LearningModule/classes/class.ilLMObject.php';

					$details[$num]['desc'] = $lng->txt('obj_'.$chapter['type']).' -> ';
					$details[$num]['target'] = '_top';
					$details[$num]['link'] = "ilias.php?baseClass=ilLMPresentationGUI&ref_id=".$chapter['ref_id'].'&obj_id='.$chapter['obj_id'];
					$details[$num]['name'] = ilLMObject::_lookupTitle($chapter['obj_id']); 
					$num++;
				}
				if($has_sections)
				{
					$item_list_gui2->enableItemDetailLinks(true);
					$item_list_gui2->setItemDetailLinks($details,$lng->txt('crs_suggested_sections').': ');
				}
			}

			if ($this->getContainerGUI()->isActiveAdministrationPanel())
			{
				$item_list_gui2->enableCheckbox(true);
				if ($this->getContainerObject()->getOrderType() == ilContainer::SORT_MANUAL)
				{
					$item_list_gui2->setPositionInputField("[lobj][".$a_objective_id."][".$item["ref_id"]."]",
						sprintf('%.1f', $pos));
					$pos++;
				}
				
			}
			$this->rendered_items[$item['child']] = true;
			$sub_item_html = $item_list_gui2->getListItemHTML($item['ref_id'],
				$item['obj_id'], $item['title'], $item['description']);
				
			$this->determineAdminCommands($item["ref_id"],
				$item_list_gui2->adminCommandsIncluded());
			$this->objective_list_gui->addSubItemHTML($sub_item_html);
		}
		
		if($this->getDetailsLevel($a_objective_id) == self::DETAILS_ALL)
		{
			$this->objective_list_gui->enableCommands(false);
		}
		else
		{
			$this->objective_list_gui->enableCommands(true);
		}
		
		$html = $this->objective_list_gui->getListItemHTML(
			0,
			$a_objective_id,
			$objective->getTitle(),
			$objective->getDescription());
			
		return $html;
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
		
		if(isset($_GET['details_level']))
		{
			$this->details_level = (int) $_GET['details_level'];
			ilObjUser::_writePref($ilUser->getId(),'crs_objectives_details',$this->details_level);
		}
		else
		{
			$this->details_level = $ilUser->getPref('crs_objectives_details') ? $ilUser->getPref('crs_objectives_details') : self::DETAILS_TITLE_DESC;
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
	}
	
	/**
	 * show action button
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function showButton($a_cmd,$a_text,$a_target = '')
	{
		global $tpl,$ilCtrl;
		
		$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK",$ilCtrl->getLinkTarget($this->getContainerGUI(),$a_cmd));
		$tpl->setVariable("BTN_TXT",$a_text);

		if($a_target)
		{
			$tpl->setVariable("BTN_TARGET",$a_target);
		}

		$tpl->parseCurrentBlock();
	}
}
?>