<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilBlockGUI.php");

/**
 * BlockGUI class calendar selection.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarSelectionBlockGUI extends ilBlockGUI
{
	static $block_type = "cal_sel";
	
	/**
	 * Constructor
	 */
	function ilCalendarSelectionBlockGUI($a_seed)
	{
		global $ilCtrl, $lng;
		
		$this->lng = $lng;
		parent::__construct();
		$lng->loadLanguageModule('pd');
		$lng->loadLanguageModule('dateplaner');
		
		$this->setLimit(5);
		$this->allow_moving = false;
		$this->seed = $a_seed;
		
		$this->setTitle($lng->txt('cal_table_categories'));
		
		include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
		$sel_type = ilCalendarUserSettings::_getInstance()->getCalendarSelectionType();
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'calendar_mode',ilCalendarUserSettings::CAL_SELECTION_ITEMS);
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'seed',$this->seed->get(IL_CAL_DATE));
		$this->addBlockCommand(
			$ilCtrl->getLinkTargetByClass("ilcalendarcategorygui",'switchCalendarMode'),
			$lng->txt('pd_my_offers'), "", "", false,
			($sel_type == ilCalendarUserSettings::CAL_SELECTION_ITEMS)
			);
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'calendar_mode',ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP);
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'seed',$this->seed->get(IL_CAL_DATE));
		$this->addBlockCommand(
			$ilCtrl->getLinkTargetByClass("ilcalendarcategorygui",'switchCalendarMode'),
			$lng->txt('pd_my_memberships'), "", "", false,
			($sel_type == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
			);

		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'calendar_mode',"");
		$this->addBlockCommand(
			$ilCtrl->getLinkTargetByClass("ilcalendarcategorygui", 'add'),
			$lng->txt('cal_add_calendar')
			);
	}
		
	/**
	 * Is this a repository object
	 *
	 * @return	string	Block type.
	 */
	static function isRepositoryObject()
	{
		return false;
	}

	/**
	 * Get block type
	 *
	 * @return	string	Block type.
	 */
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * Get Screen Mode for current command.
	 */
	static function getScreenMode()
	{
		global $ilCtrl;
		
		return IL_SCREEN_SIDE;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	 * Get calendars
	 */
	public function getCalendars()
	{
		global $ilUser,$tree;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');
		
		$hidden_obj = ilCalendarHidden::_getInstanceByUserId($ilUser->getId());
		$hidden = $hidden_obj->getHidden();
		
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		$all = $cats->getCategoriesInfo();
		$tmp_title_counter = array();
		$categories = array();
		foreach($all as $category)
		{
			$tmp_arr['obj_id'] = $category['obj_id'];
			$tmp_arr['id'] = $category['cat_id'];
			$tmp_arr['hidden'] = (bool) in_array($category['cat_id'],$hidden);
			$tmp_arr['title'] = $category['title'];
			$tmp_arr['type'] = $category['type'];
			
			// Append object type to make type sortable
			$tmp_arr['type_sortable'] = ilCalendarCategory::lookupCategorySortIndex($category['type']);
			if($category['type'] == ilCalendarCategory::TYPE_OBJ)
			{
				$tmp_arr['type_sortable'] .= ('_'.ilObject::_lookupType($category['obj_id']));
			}
			
			$tmp_arr['color'] = $category['color'];
			$tmp_arr['editable'] = $category['editable'];
			
			$categories[] = $tmp_arr;
			
			// count title for appending the parent container if there is more than one entry.
			$tmp_title_counter[$category['type'].'_'.$category['title']]++;
			
		}
		
		$path_categories = array();
		foreach($categories as $cat)
		{
			if($cat['type'] == ilCalendarCategory::TYPE_OBJ)
			{
				if($tmp_title_counter[$cat['type'].'_'.$cat['title']] > 1)
				{
					foreach(ilObject::_getAllReferences($cat['obj_id']) as $ref_id)
					{
						$cat['path'] = $this->buildPath($ref_id);
						break;					
					}
				}
			}			
			$path_categories[] = $cat;
		}
		$path_categories = ilUtil::sortArray($path_categories, 'title', "asc");

		$this->calendars = $path_categories;
	}

	/**
	 * Build path for ref id
	 *
	 * @param int $a_ref_id ref id
	 */
	protected function buildPath($a_ref_id)
	{
		global $tree;

		$path_arr = $tree->getPathFull($a_ref_id,ROOT_FOLDER_ID);
		$counter = 0;
		unset($path_arr[count($path_arr) - 1]);

		foreach($path_arr as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}
		if(strlen($path) > 30)
		{
			return '...'.substr($path,-30);
		}
		return $path;
	}

	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $lng, $ilCtrl;

		$tpl = new ilTemplate("tpl.cal_selection_block_content.html", true, true, "Services/Calendar");
		
		foreach ($this->calendars as $c)
		{
			$this->renderItem($c, $tpl);
		}
		
		$tpl->setVariable("TXT_SHOW", $lng->txt("select"));
		$tpl->setVariable("CMD_SHOW", "saveSelection");
		$tpl->setVariable("TXT_ACTION", $lng->txt("select"));
		$tpl->setVariable("SRC_ACTION", ilUtil::getImagePath("arrow_downright.svg"));
		$tpl->setVariable("FORM_ACTION", $ilCtrl->getFormActionByClass("ilcalendarcategorygui"));
		$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
		
		$this->setDataSection($tpl->get());
	}

	/**
	 * Render item
	 *
	 * @param array $a_set item datat
	 */
	protected function renderItem($a_set, $a_tpl)
	{
		global $ilCtrl;

		if(strlen($a_set['path']))
		{
			$a_tpl->setCurrentBlock('calendar_path');
			$a_tpl->setVariable('ADD_PATH_INFO',$a_set['path']);
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("item");
		
		$a_tpl->setVariable('VAL_ID',$a_set['id']);
		if(!$a_set['hidden'])
		{
			$a_tpl->setVariable('VAL_CHECKED','checked="checked"');
		}
		$a_tpl->setVariable('VAL_TITLE',$a_set['title']);
		$a_tpl->setVariable('BGCOLOR',$a_set['color']);
		
		$ilCtrl->setParameterByClass("ilcalendarcategorygui",'category_id',$a_set['id']);
		$a_tpl->setVariable('EDIT_LINK',$ilCtrl->getLinkTargetByClass("ilcalendarcategorygui", 'details'));
		$a_tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));

		switch($a_set['type'])
		{
			case ilCalendarCategory::TYPE_GLOBAL:
				$a_tpl->setVariable('IMG_SRC',ilUtil::getImagePath('icon_calg.svg'));
				$a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_system'));
				break;
				
			case ilCalendarCategory::TYPE_USR:
				$a_tpl->setVariable('IMG_SRC',ilUtil::getImagePath('icon_usr.svg'));
				$a_tpl->setVariable('IMG_ALT',$this->lng->txt('cal_type_personal'));
				break;
			
			case ilCalendarCategory::TYPE_OBJ:
				$type = ilObject::_lookupType($a_set['obj_id']);
				$a_tpl->setVariable('IMG_SRC',ilUtil::getImagePath('icon_'.$type.'.svg'));
				$a_tpl->setVariable('IMG_ALT',$this->lng->txt('cal_type_'.$type));
				break;				

			case ilCalendarCategory::TYPE_BOOK:
				$a_tpl->setVariable('IMG_SRC',ilUtil::getImagePath('icon_book.svg'));
				$a_tpl->setVariable('IMG_ALT',$this->lng->txt('cal_type_'.$type));
				break;				
		}
		
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Get block HTML code.
	 */
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser, $ilAccess, $ilSetting;
		
		$this->getCalendars();
		
		return parent::getHTML();
	}
}

?>
