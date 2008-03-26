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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* show presentation of calendar category side block
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj,'showCategories');
	 	$this->addColumn('','f',"1px");
	 	$this->addColumn($this->lng->txt('title'),'title',"99%");
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_category_row.html","Services/Calendar");
		$this->disable('sort');
		$this->disable('header');
		$this->disable('numinfo');
		$this->enable('select_all');
		$this->setSelectAllCheckbox('cat_ids');
		
	}
	
	/**
	 * fill row
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		if(!$a_set['hidden'])
		{
			$this->tpl->setVariable('VAL_CHECKED','checked="checked"');
		}
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		$this->tpl->setVariable('BGCOLOR',$a_set['color']);
		
		if($a_set['editable'])
		{
			$this->tpl->setCurrentBlock('editable');
			$this->ctrl->setParameter($this->getParentObject(),'category_id',$a_set['id']);
			$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'edit'));
			$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}
		switch($a_set['type'])
		{
			case ilCalendarCategory::TYPE_USR:
				#$this->tpl->setVariable('CAL_TYPE',$this->lng->txt('cal_type_usr'));
				break;
		}
	}
	
	/**
	 * parse
	 *
	 * @access public
	 * @return
	 */
	public function parse()
	{
		global $ilUser;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');
		
		$hidden_obj = ilCalendarHidden::_getInstanceByUserId($ilUser->getId());
		$hidden = $hidden_obj->getHidden();
		
		$categories = array();
		foreach(ilCalendarCategories::_getCategoriesOfUser($ilUser->getId()) as $category)
		{
			$tmp_arr['id'] = $category->getCategoryID();
			$tmp_arr['hidden'] = (bool) in_array($category->getCategoryId(),$hidden);
			$tmp_arr['title'] = $category->getTitle();
			$tmp_arr['type'] = $category->getType();
			$tmp_arr['color'] = $category->getColor();
			$tmp_arr['editable'] = true;
			
			$categories[] = $tmp_arr;
		}
		$this->setData($categories ? $categories : array());
	}
}
?>