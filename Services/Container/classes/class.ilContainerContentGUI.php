<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


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
		global $ilUser;
		$obj_id = ilObject::_lookupObjId($this->getContainerObject()->getRefId());
		ilChangeEvent::_recordReadEvent(
			$this->getContainerObject()->getType(),
			$this->getContainerObject()->getRefId(),
			$obj_id, $ilUser->getId());
		// END ChangeEvent: record read event.
		
		$tpl->setContent($this->getCenterColumnHTML());
		$tpl->setRightContent($this->getRightColumnHTML());
	}

	/**
	* Get HTML for right column
	*/
	protected function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl, $ilAccess, $ilPluginAdmin;;

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
				$html = "";
				
				// user interface plugin slot + default rendering
				include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
				$uip = new ilUIHookProcessor("Services/Container", "right_column",
					array("container_content_gui" => $this));
				if (!$uip->replaced())
				{
					$html = $ilCtrl->getHTML($column_gui);
				}
				$html = $uip->getHTML($html);
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
		
		$tpl->addOnLoadCode("il.Object.setRedrawListItemUrl('".
			$ilCtrl->getLinkTarget($this->container_gui, "redrawListItem", "", true)."');");
		
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
		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

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
//			$item_list_gui->enableDelete(false);
//			$item_list_gui->enableLink(false);
//			$item_list_gui->enableCut(false);
		}
		
		// activate common social commands
		$item_list_gui->enableComments(true);
		$item_list_gui->enableNotes(true);
		$item_list_gui->enableTags(true);

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
		
		// determine item groups
		while (eregi("\[(item-group-([0-9]*))\]", $a_container_page_html, $found))
		{
			$this->addEmbeddedBlock("itgr", (int) $found[2]);
			
			$a_container_page_html = eregi_replace("\[".$found[1]."\]", $html, $a_container_page_html);
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

						if($type == 'sess')
						{
							$this->items['sess'] = ilUtil::sortArray($this->items['sess'],'start','ASC',true,true);
						}

						foreach($this->items[$type] as $k => $item_data)
						{
							$html = $this->renderItem($item_data,$position++);
							if ($html != "")
							{
								$this->addStandardRow($tpl, $html, $item_data["child"]);
								$item_rendered = true;
								$this->rendered_items[$item_data["child"]] = true;
							}
						}
					
						// if no item has been rendered, add message
						if (!$item_rendered)
						{
							//$this->addMessageRow($tpl, $lng->txt("msg_no_type_accessible"), $type);
							$this->rendered_block["type"][$type] = "";
						}
						else
						{
							$this->rendered_block["type"][$type] = $tpl->get();
						}
					}
				}
			}
		}
		
		// all item groups
		if (is_array($this->embedded_block["itgr"]))
		{
			$item_groups = array();
			if (is_array($this->items["itgr"]))
			{
				
				foreach ($this->items["itgr"] as $ig)
				{
					$item_groups[$ig["ref_id"]] = $ig;
				}
			}

			// all embedded typed blocks
			foreach ($this->embedded_block["itgr"] as $ref_id)
			{
				// render only item groups of $this->items (valid childs)
				if ($this->rendered_block["itgr"][$ref_id] == "" && isset($item_groups[$ref_id]))
				{
					$tpl = $this->newBlockTemplate();
					$this->renderItemGroup($tpl, $item_groups[$ref_id]);
					$this->rendered_block["itgr"][$ref_id] = $tpl->get();
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
	function renderItem($a_item_data,$a_position = 0,$a_force_icon = false, $a_pos_prefix = "")
	{
		global $ilSetting,$ilAccess,$ilCtrl;

		// Pass type, obj_id and tree to checkAccess method to improve performance
		if(!$ilAccess->checkAccess('visible','',$a_item_data['ref_id'],$a_item_data['type'],$a_item_data['obj_id'],$a_item_data['tree']))
		{
			return '';
		}
		
		$item_list_gui = $this->getItemGUI($a_item_data);
		if ($ilSetting->get("icon_position_in_lists") == "item_rows" ||
			$a_item_data["type"] == "sess" || $a_force_icon)
		{
			$item_list_gui->enableIcon(true);
		}
		if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"])
		{
			$item_list_gui->enableCheckbox(true);
		}
		if ($this->getContainerGUI()->isActiveOrdering() && ($a_item_data['type'] != 'sess' || get_class($this) != 'ilContainerSessionsContentGUI'))
		{
			$item_list_gui->setPositionInputField($a_pos_prefix."[".$a_item_data["ref_id"]."]",
				sprintf('%d', (int)$a_position*10));
		}
		if($a_item_data['type'] == 'sess' and get_class($this) != 'ilContainerObjectiveGUI')
		{
			switch($this->getDetailsLevel($a_item_data['obj_id']))
			{
				case self::DETAILS_TITLE:
					$item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_MINIMAL);
					$item_list_gui->enableExpand(true);
					$item_list_gui->setExpanded(false);
					$item_list_gui->enableDescription(false);
					$item_list_gui->enableProperties(true);
					break;
					
				case self::DETAILS_ALL:
					$item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
					$item_list_gui->enableExpand(true);
					$item_list_gui->setExpanded(true);
					$item_list_gui->enableDescription(true);
					$item_list_gui->enableProperties(true);
					break;
										
				default:
					$item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
					$item_list_gui->enableExpand(true);
					$item_list_gui->enableDescription(true);
					$item_list_gui->enableProperties(true);
					break;
			}
		}

		// show subitems
		if($a_item_data['type'] == 'sess' and (
											$this->getDetailsLevel($a_item_data['obj_id']) != self::DETAILS_TITLE or
											$this->getContainerGUI()->isActiveAdministrationPanel() or
											$this->getContainerGUI()->isActiveOrdering()
											)
		)
		{											
			$pos = 1;
						
			include_once('./Services/Container/classes/class.ilContainerSorting.php');			
			include_once('./Services/Object/classes/class.ilObjectActivation.php');			
			$items = ilObjectActivation::getItemsByEvent($a_item_data['obj_id']);
			$items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('sess',$a_item_data['obj_id'],$items);
			
			
			$item_readable = $ilAccess->checkAccess('read','',$a_item_data['ref_id']);
			
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
				
				// @see mantis 10488
				if(!$item_readable and !$ilAccess->checkAccess('write','',$item['ref_id']))
				{
					$item_list_gui2->forceVisibleOnly(true);
				}
				
				if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"])
				{
					$item_list_gui2->enableCheckbox(true);
				}
				if ($this->getContainerGUI()->isActiveOrdering())
				{
					if ($this->getContainerObject()->getOrderType() == ilContainer::SORT_MANUAL)
					{
						$item_list_gui2->setPositionInputField("[sess][".$a_item_data['obj_id']."][".$item["ref_id"]."]",
							sprintf('%d', (int)$pos*10));
						$pos++;
					}					
				}
				
				// #10611
				ilObjectActivation::addListGUIActivationProperty($item_list_gui2, $item);		
				
				$sub_item_html = $item_list_gui2->getListItemHTML($item['ref_id'],
					$item['obj_id'], $item['title'], $item['description']);
										
				$this->determineAdminCommands($item["ref_id"],$item_list_gui2->adminCommandsIncluded());
				if(strlen($sub_item_html))
				{
					$item_list_gui->addSubItemHTML($sub_item_html);
				}
			}
		}

		if ($ilSetting->get("item_cmd_asynch"))
		{
			$asynch = true;
			$ilCtrl->setParameter($this->container_gui, "cmdrefid", $a_item_data['ref_id']);
			$asynch_url = $ilCtrl->getLinkTarget($this->container_gui,
					"getAsynchItemList", "", true, false);
			$ilCtrl->setParameter($this->container_gui, "cmdrefid", "");
		}
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_item_data);
		
		$html = $item_list_gui->getListItemHTML($a_item_data['ref_id'],
			$a_item_data['obj_id'], $a_item_data['title'], $a_item_data['description'],
			$asynch, false, $asynch_url);
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
	function newBlockTemplate()
	{
		$tpl = new ilTemplate("tpl.container_list_block.html", true, true,
			"Services/Container");
		$this->cur_row_type = "row_type_1";
		return $tpl;
	}

	/**
	* add item row to template
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_ref_id = 0)
	{
		global $ilSetting, $lng;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		if ($a_ref_id > 0)
		{
			$a_tpl->setCurrentBlock($this->cur_row_type);
			$a_tpl->setVariable("ROW_ID", 'id="item_row_'.$a_ref_id.'"');
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->touchBlock($this->cur_row_type);
		}
		
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
		global $lng, $ilSetting, $objDefinition;
		
		$a_tpl->setVariable("CB_ID", ' id="bl_cntr_'.$this->bl_cnt.'"');
		if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"])
		{
			$a_tpl->setCurrentBlock("select_all_row");
			$a_tpl->setVariable("CHECKBOXNAME", "bl_cb_".$this->bl_cnt);
			$a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_".$this->bl_cnt);
			$a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_".$this->bl_cnt);
			$a_tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
			$a_tpl->parseCurrentBlock();
			$this->bl_cnt++;
		}
		
		if ($a_text == "" && $a_type != "")
		{
			if (!$objDefinition->isPlugin($a_type))
			{
				$title = $lng->txt("objs_".$a_type);
			}
			else
			{
				include_once("./Services/Component/classes/class.ilPlugin.php");
				$title = ilPlugin::lookupTxt("rep_robj", $a_type, "objs_".$a_type);
			}
		}
		else
		{
			$title = $a_text;
		}

		if ($ilSetting->get("icon_position_in_lists") != "item_rows" &&
			$a_type != "")
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".png");

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
		
		// insert all item groups
		while (eregi("\[(item-group-([0-9]*))\]", $a_output_html, $found))
		{
			$itgr_ref_id = (int) $found[2];
			
			// check whether this item group is child -> insert editing html
			$html = "";
			if (isset($this->rendered_block["itgr"][$itgr_ref_id]))
			{
				$html = $this->rendered_block["itgr"][$itgr_ref_id];
				$this->rendered_items[$itgr_ref_id] = true;
			}
			$a_output_html = eregi_replace("\[".$found[1]."\]", $html, $a_output_html);
		}

		return $a_output_html;
	}

	/**
	* add message row
	*/
	function addMessageRow(&$a_tpl, $a_message, $a_type)
	{
		global $lng, $objDefinition;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		if (!$objDefinition->isPlugin($type))
		{
			$type = $lng->txt("obj_".$a_type);
		}
		else
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$title = ilPlugin::lookupTxt("rep_robj", $a_type, "objs_".$a_type);
		}
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
		global $lng;
		
		$a_tpl->setCurrentBlock("container_block");
		$a_tpl->parseCurrentBlock();
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

	/**
	* Get introduction.
	*/
	function getIntroduction()
	{
		global $ilUser, $lng, $ilCtrl;
		
		$lng->loadLanguageModule("rep");
		
		$tpl = new ilTemplate("tpl.rep_intro.html", true, true, "Services/Repository");
		$tpl->setVariable("IMG_REP_LARGE", ilUtil::getImagePath("icon_root_xxl.png"));
		$tpl->setVariable("TXT_WELCOME", $lng->txt("rep_intro"));
		$tpl->setVariable("TXT_INTRO_1", $lng->txt("rep_intro1"));
		$tpl->setVariable("TXT_INTRO_2", $lng->txt("rep_intro2"));
		$tpl->setVariable("TXT_INTRO_3", sprintf($lng->txt("rep_intro3"), $lng->txt("add")));
		$tpl->setVariable("TXT_INTRO_4", sprintf($lng->txt("rep_intro4"), $lng->txt("cat_add")));
		$tpl->setVariable("TXT_INTRO_5", $lng->txt("rep_intro5"));
		$tpl->setVariable("TXT_INTRO_6", $lng->txt("rep_intro6"));
		
		return $tpl->get();
	}

	/**
	 * Get item groups HTML
	 *
	 * @param
	 * @return
	 */
	function getItemGroupsHTML($a_tpl)
	{
		$rendered = false;
		if (is_array($this->items["itgr"]))
		{
			foreach ($this->items["itgr"] as $itgr)
			{
				if (!$this->rendered_items[$itgr["child"]])
				{
					$this->renderItemGroup($a_tpl, $itgr);
					$rendered = true;
				}
			}
		}
		return $rendered;
	}
	
	/**
	 * Render item group
	 *
	 * @param
	 * @return
	 */
	function renderItemGroup($a_tpl, $a_itgr)
	{
		global $ilAccess, $lng;
		
		$perm_ok = $ilAccess->checkAccess("read", "", $a_itgr['ref_id']);

		include_once('./Services/Container/classes/class.ilContainerSorting.php');			
		include_once('./Services/Object/classes/class.ilObjectActivation.php');
		$items = ilObjectActivation::getItemsByItemGroup($a_itgr['ref_id']);
		$items = ilContainerSorting::_getInstance(
			$this->getContainerObject()->getId())->sortSubItems('itgr', $a_itgr['obj_id'],$items);
		
		// if no permissoin is given, set the items to "rendered" but
		// do not display the whole block
		if (!$perm_ok)
		{
			foreach($items as $item)
			{
				$this->rendered_items[$item["child"]] = true;
			}
			return;
		}
		
		$item_list_gui = $this->getItemGUI($a_itgr);
		$item_list_gui->enableNotes(false);
		$item_list_gui->enableTags(false);
		$item_list_gui->enableComments(false);
		$item_list_gui->enableTimings(false);
		$item_list_gui->getListItemHTML($a_itgr["ref_id"], $a_itgr["obj_id"],
			$a_itgr["title"], $a_itgr["description"]);
		$commands_html = $item_list_gui->getCommandsHTML(); 
		
		$this->addSeparatorRow($a_tpl);
		
		$a_tpl->setVariable("CB_ID", ' id="bl_cntr_'.$this->bl_cnt.'"');
		if ($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"])
		{
			$a_tpl->setCurrentBlock("select_all_row");
			$a_tpl->setVariable("CHECKBOXNAME", "bl_cb_".$this->bl_cnt);
			$a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_".$this->bl_cnt);
			$a_tpl->setVariable("SEL_ALL_PARENT", "bl_cntr_".$this->bl_cnt);
			$a_tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
			$a_tpl->parseCurrentBlock();
			$this->bl_cnt++;
		}
		
		$a_tpl->setCurrentBlock("container_header_row");
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $a_itgr["title"]);
		$a_tpl->setVariable("CHR_COMMANDS", $commands_html);
		$a_tpl->parseCurrentBlock();
		
		$a_tpl->touchBlock("container_row");
		$this->resetRowType();

		// render item group sub items
				
		$position = 1;
		foreach($items as $item)
		{
			$html2 = $this->renderItem($item, $position++, false, "[itgr][".$a_itgr['obj_id']."]");
			if ($html2 != "")
			{
				$this->addStandardRow($a_tpl, $html2, $item["child"]);
				$this->rendered_items[$item["child"]] = true;
			}
		}

		// finish block
		$a_tpl->setCurrentBlock("container_block");
		$a_tpl->parseCurrentBlock();
	}
	
}
?>
