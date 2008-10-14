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


/**
* Parent class of all container content GUIs.
*
* These classes are responsible for displaying the content, i.e. the
* side column and main column and its subitems in container objects.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
abstract class ilContainerContentGUI
{
	const DETAILS_DEACTIVATED = 0;
	const DETAILS_TITLE = 1;
	const DETAILS_ALL = 2;
	
	
	protected $details_level = self::DETAILS_DEACTIVATED;
	
	var $container_gui;
	var $container_obj;

	/**
	* Constructor
	*
	*/
	function __construct(&$container_gui_obj)
	{
		$this->container_gui = $container_gui_obj;
		$this->container_obj = $this->container_gui->object;
	}
	
	/**
	 * get details level
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function getDetailsLevel($a_item_id)
	{
		return $this->details_level;
	}

	/**
	* Get container object.
	*
	* @return 	object		container object instance
	*/
	public function getContainerObject()
	{
		return $this->container_obj;
	}
	
	/**
	* Get container GUI object
	*
	* @return 	object		container GUI instance
	*/
	public function getContainerGUI()
	{
		return $this->container_gui;
	}

	/**
	* Sets view output into column layout
	*
	* This method sets the output of the right and main column
	* in the global standard template.
	*/
	public function setOutput()
	{
		global $tpl;
		
		// BEGIN ChangeEvent: record read event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilUser;
			$obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
			ilChangeEvent::_recordReadEvent($obj_id, $ilUser->getId());
		}
		// END ChangeEvent: record read event.

		$tpl->setContent($this->getCenterColumnHTML());
		$tpl->setRightContent($this->getRightColumnHTML());
	}

	/**
	* Get HTML for right column
	*/
	protected function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl, $ilAccess;

		$ilCtrl->saveParameterByClass("ilcolumngui", "col_return");

		$obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
		$obj_type = ilObject::_lookupType($obj_id);

		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

		if ($column_gui->getScreenMode() == IL_SCREEN_FULL)
		{
			return "";
		}

		$this->getContainerGUI()->setColumnSettings($column_gui);
		
		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_RIGHT &&
			$column_gui->getScreenMode() == IL_SCREEN_SIDE)
		{

			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				$html = $ilCtrl->getHTML($column_gui);
			}
		}

		return $html;
	}

	/**
	* Get HTML for center column
	*/
	protected function getCenterColumnHTML()
	{
		global $ilCtrl, $tpl;
		
		$ilCtrl->saveParameterByClass("ilcolumngui", "col_return");
		
		switch ($ilCtrl->getNextClass())
		{	
			case "ilcolumngui":
				$ilCtrl->setReturn($this->container_gui, "");
				$html = $this->__forwardToColumnGUI();
				break;
				
			default:
				$html = $this->getMainContent();
				break;
		}
		
		return $html;
	}

	/**
	* Get content HTML for main column, this one must be
	* overwritten in derived classes.
	*/
	abstract function getMainContent();
	
	/**
	* Get columngui output
	*/
	final private function __forwardToColumnGUI()
	{
		global $ilCtrl, $ilAccess;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");

		// this gets us the subitems we need in setColumnSettings()
		// todo: this should be done in ilCourseGUI->getSubItems
		//$this->course_obj->initCourseItemObject($this->getContainerObject()->getRefId());
		
		$obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
		$obj_type = ilObject::_lookupType($obj_id);

		if (!$ilCtrl->isAsynch())
		{
			//if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
			if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE)
			{
				// right column wants center
				if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT)
				{
					$column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
					$this->getContainerGUI()->setColumnSettings($column_gui);
					$html = $ilCtrl->forwardCommand($column_gui);
				}
				// left column wants center
				if (ilColumnGUI::getCmdSide() == IL_COL_LEFT)
				{
					$column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
					$this->getContainerGUI()->setColumnSettings($column_gui);
					$html = $ilCtrl->forwardCommand($column_gui);
				}
			}
			else
			{
				$html = $this->getMainContent();
			}
		}

		return $html;
	}

	/**
	* cleaer administration commands determination
	*/
	protected function clearAdminCommandsDetermination()
	{
		$this->adminCommands = false;
	}
	
	/**
	* determin admin commands
	*/
	protected function determineAdminCommands($a_ref_id, $a_admin_com_included_in_list = false)
	{
		global $rbacsystem;
		
//echo "-".$a_admin_com_included_in_list."-";
		
		if (!$this->adminCommands)
		{
			if (!$this->getContainerGUI()->isActiveAdministrationPanel())
			{
				if ($rbacsystem->checkAccess("delete", $a_ref_id))
				{
					$this->adminCommands = true;
				}
			}
			else
			{
				$this->adminCommands = $a_admin_com_included_in_list;
			}
		}
	}

	/**
	* Get ListGUI object for item
	*/
	protected function getItemGUI($item_data,$a_show_path = false)
	{
		include_once './classes/class.ilObjectListGUIFactory.php';

		// get item list gui object
		if (!is_object ($this->list_gui[$item_data["type"]]))
		{
			$item_list_gui =& ilObjectListGUIFactory::_getListGUIByType($item_data["type"]);
			$item_list_gui->setContainerObject($this->getContainerGUI());
			$this->list_gui[$item_data["type"]] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->list_gui[$item_data["type"]];
		}

		// show administration command buttons (or not)
		if (!$this->getContainerGUI()->isActiveAdministrationPanel())
		{
			$item_list_gui->enableDelete(false);
			$item_list_gui->enableLink(false);
			$item_list_gui->enableCut(false);
		}

		// container specific modifications
		$this->getContainerGUI()->modifyItemGUI($item_list_gui, $item_data, $a_show_path);

		return $item_list_gui;
	}

	/**
	* Determine all blocks that are embedded in the container page
	*/
	function determinePageEmbeddedBlocks($a_container_page_html)
	{
		$type_grps = $this->getGroupedObjTypes();
		
		// iterate all types
		foreach ($type_grps as $type => $v)
		{
			// set template (overall or type specific)
			if (is_int(strpos($a_container_page_html, "[list-".$type."]")))
			{
				$this->addEmbeddedBlock("type", $type);
			}
		}
	}
	
	/**
	* Add embedded block
	*
	* @param	
	*/
	function addEmbeddedBlock($block_type, $block_parameter)
	{
		$this->embedded_block[$block_type][] = $block_parameter;
	}
	
	/**
	* Get page embedded blocks
	*/
	function getEmbeddedBlocks()
	{
		return $this->embedded_block;
	}
	
	/**
	* Render Page Embedded Blocks
	*/
	function renderPageEmbeddedBlocks()
	{
		global $lng;
		
		// first all type specific blocks
		if (is_array($this->embedded_block["type"]))
		{
			// all embedded typed blocks
			foreach ($this->embedded_block["type"] as $k => $type)
			{
				if ($this->rendered_block["type"][$type] == "")
				{
					if (is_array($this->items[$type]))
					{
						$tpl = $this->newBlockTemplate();
						
						// the header
						$this->addHeaderRow($tpl, $type);
						
						// all rows
						$item_rendered = false;
						$position = 1;
						foreach($this->items[$type] as $k => $item_data)
						{
							$html = $this->renderItem($item_data,$position++);
							if ($html != "")
							{
								$this->addStandardRow($tpl, $html);
								$item_rendered = true;
								$this->rendered_items[$item_data["child"]] = true;
							}
						}
					
						// if no item has been rendered, add message
						if (!$item_rendered)
						{
							$this->addMessageRow($tpl, $lng->txt("msg_no_type_accessible"), $type);
						}

						$this->rendered_block["type"][$type] = $tpl->get();
					}
				}
			}
		}
	}
	
	/**
	* Render an item
	*
	* @param	array		item data
	*
	* @return	string		item HTML
	*/
	function renderItem($a_item_data,$a_position = 0,$a_force_icon = false)
	{
		global $ilSetting,$ilAccess;
		
		$item_list_gui = $this->getItemGUI($a_item_data);
		if ($ilSetting->get("icon_position_in_lists") == "item_rows" ||
			$a_item_data["type"] == "sess" || $a_force_icon)
		{
			$item_list_gui->enableIcon(true);
		}
		if ($this->getContainerGUI()->isActiveAdministrationPanel())
		{
			$item_list_gui->enableCheckbox(true);
			if ($this->getContainerObject()->getOrderType() == ilContainer::SORT_MANUAL)
			{
				$item_list_gui->setPositionInputField("[".$a_item_data["ref_id"]."]",
					sprintf('%.1f', $a_position));
			}
		}
		if($a_item_data['type'] == 'sess' and get_class($this) != 'ilContainerObjectiveGUI')
		{
			switch($this->getDetailsLevel($a_item_data['obj_id']))
			{
				case self::DETAILS_TITLE:
					$item_list_gui->enableExpand(true);
					$item_list_gui->setExpanded(false);
					$item_list_gui->enableDescription(false);
					$item_list_gui->enableProperties(false);
					break;
					
				case self::DETAILS_ALL:
					$item_list_gui->enableExpand(true);
					$item_list_gui->setExpanded(true);
					$item_list_gui->enableDescription(true);
					$item_list_gui->enableProperties(true);
					break;
										
				default:
					$item_list_gui->enableExpand(false);
					$item_list_gui->enableDescription(true);
					$item_list_gui->enableProperties(true);
					break;
			}
		}
		
		// show subitems
		if ($a_item_data['type'] == 'sess' and $this->getDetailsLevel($a_item_data['obj_id']) != self::DETAILS_TITLE)
		{
			$pos = 1;
			
			include_once('./Services/Container/classes/class.ilContainerSorting.php');
			$items = $this->getContainerObject()->items_obj->getItemsByEvent($a_item_data['obj_id']);
			$items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('sess',$a_item_data['obj_id'],$items);
			
			foreach($items as $item)
			{
				// TODO: this should be removed and be handled by if(strlen($sub_item_html))
				// 	see mantis: 0003944
				if(!$ilAccess->checkAccess('visible','',$item['ref_id']))
				{
					continue;
				}
				
				$item_list_gui2 = $this->getItemGUI($item);
				$item_list_gui2->enableIcon(true);
				$item_list_gui2->enableItemDetailLinks(false);
				if ($this->getContainerGUI()->isActiveAdministrationPanel())
				{
					$item_list_gui2->enableCheckbox(true);
					if ($this->getContainerObject()->getOrderType() == ilContainer::SORT_MANUAL)
					{
						$item_list_gui2->setPositionInputField("[sess][".$a_item_data['obj_id']."][".$item["ref_id"]."]",
							sprintf('%.1f', $pos));
						$pos++;
					}
					
				}
				$sub_item_html = $item_list_gui2->getListItemHTML($item['ref_id'],
					$item['obj_id'], $item['title'], $item['description']);
					
					
				$this->determineAdminCommands($item["ref_id"],$item_list_gui2->adminCommandsIncluded());
				if(strlen($sub_item_html))
				{
					$item_list_gui->addSubItemHTML($sub_item_html);
				}
			}
		}

		$html = $item_list_gui->getListItemHTML($a_item_data['ref_id'],
			$a_item_data['obj_id'], $a_item_data['title'], $a_item_data['description']);
		$this->determineAdminCommands($a_item_data["ref_id"],
			$item_list_gui->adminCommandsIncluded());
			
			
		return $html;
	}
	
	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.container_list_block.html", true, true,
			"Services/Container");
		$this->cur_row_type = "row_type_1";
		return $tpl;
	}

	/**
	* add item row to template
	*/
	function addStandardRow(&$a_tpl, $a_html)
	{
		global $ilSetting, $lng;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* Add header row to block template
	*/
	function addHeaderRow($a_tpl, $a_type = "", $a_text = "")
	{
		global $lng, $ilSetting;
		
		if ($a_text == "" && $a_type != "")
		{
			$title = $lng->txt("objs_".$a_type);
		}
		else
		{
			$title = $a_text;
		}

		if ($ilSetting->get("icon_position_in_lists") != "item_rows" &&
			$a_type != "")
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".gif");

			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
		
		$this->resetRowType();
	}
	

	/**
	* Reset row type (toggling background colors)
	*/
	function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
	/**
	* Insert blocks into container page
	*/
	function insertPageEmbeddedBlocks($a_output_html)
	{
		$this->determinePageEmbeddedBlocks($a_output_html);
		$this->renderPageEmbeddedBlocks($this->items);
		
		$type_grps = $this->getGroupedObjTypes();
		
		// iterate all types
		foreach ($type_grps as $type => $v)
		{
			// set template (overall or type specific)
			if (is_int(strpos($a_output_html, "[list-".$type."]")))
			{
				$a_output_html = eregi_replace("\[list-".$type."\]",
					$this->rendered_block["type"][$type], $a_output_html);
			}
		}

		return $a_output_html;
	}

	/**
	* add message row
	*/
	function addMessageRow(&$a_tpl, $a_message, $a_type)
	{
		global $lng;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		$type = $lng->txt("obj_".$a_type);
		$a_message = str_replace("[type]", $type, $a_message);
		
		$a_tpl->setVariable("ROW_NBSP", "&nbsp;");

		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT",
			$a_message);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* Add separator row between two blocks
	*/
	function addSeparatorRow(&$a_tpl)
	{
		$a_tpl->touchBlock("separator_row");
		$a_tpl->touchBlock("container_row");
	}

	/**
	* Get grouped repository object types.
	*
	* @return	array	array of object types
	*/
	function getGroupedObjTypes()
	{
		global $objDefinition;
		
		if (empty($this->type_grps))
		{
			$this->type_grps =
				$objDefinition->getGroupedRepositoryObjectTypes($this->getContainerObject()->getType());
		}
		return $this->type_grps;
	}

} // END class.ilContainerContentGUI
?>
