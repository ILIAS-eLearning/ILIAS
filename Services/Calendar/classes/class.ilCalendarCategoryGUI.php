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

/**
* Administration, Side-Block presentation of calendar categories
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryGUI
{
	protected $user_id;
	protected $tpl;
	protected $ctrl;
	protected $lng;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int user id
	 * @return
	 */
	public function __construct($a_user_id)
	{
		global $lng,$ilCtrl;
		
		$this->user_id = $a_user_id;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->ctrl = $ilCtrl;
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilSetting,$tpl;

		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * cancel
	 *
	 * @access protected
	 * @return
	 */
	protected function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * edit category
	 *
	 * @access protected
	 * @return
	 */
	protected function edit()
	{
		global $tpl;
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		
		$this->tpl = new ilTemplate('tpl.edit_category.html',true,true,'Services/Calendar');
		$this->initFormCategory('edit');
		$this->tpl->setVariable('EDIT_CAT',$this->form->getHTML());
		$tpl->setContent($this->tpl->get());
	}
	
	/**
	 * update
	 *
	 * @access protected
	 * @return
	 */
	protected function update()
	{
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$category = new ilCalendarCategory((int) $_GET['category_id']);
		$category->setTitle(ilUtil::stripSlashes($_POST['title']));
		$category->setColor($_POST['colors']);
		$category->update();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
	
	}
	
	
	
	/**
	 * save selection of categories
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function saveSelection()
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
	
		$selection = $_POST['cat_ids'] ? $_POST['cat_ids'] : array();
		$hidden = array();
		foreach(ilCalendarCategories::_getAvailableCategoriesOfUser($this->user_id) as $category_id)
		{
			if(!in_array($category_id,$selection))
			{
				$hidden[] = $category_id;
			}
		}
		include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');
		$hidden_categories = ilCalendarHidden::_getInstanceByUserId($this->user_id);
		$hidden_categories->hideSelected($hidden);
		$hidden_categories->save();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);	
	}
	
	/**
	 * init edit/create category form 
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormCategory($a_mode)
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		
		$this->form = new ilPropertyFormGUI();
		switch($a_mode)
		{
			case 'edit':
				$category = new ilCalendarCategory((int) $_GET['category_id']);	
				$this->form->setTitle($this->lng->txt('cal_edit_category'));
				$this->ctrl->saveParameter($this,array('seed','category_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->addCommandButton('update',$this->lng->txt('save'));
				break;				
			case 'create':
				$category = new ilCalendarCategory(0);	
				$this->ctrl->saveParameter($this,array('category_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->setTitle($this->lng->txt('cal_edit_category'));
				$this->form->addCommandButton('create',$this->lng->txt('save'));
				break;
		}
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		// Calendar name
		$title = new ilTextInputGUI($this->lng->txt('cal_calendar_name'),'title');
		$title->setRequired(true);
		$title->setMaxLength(64);
		$title->setSize(32);
		$title->setValue($category->getTitle());
		$this->form->addItem($title);
		
		// calendar color
		$color = new ilCustomInputGUI($this->lng->txt('cal_calendar_color'),'color');
		$color->setRequired(true);
		$tpl = new ilTemplate('tpl.color_selection.html',true,true,'Services/Calendar');
		
		$colors[] = '#FFFFFF'; 
		for($i = 1000000;$i < 16777215;$i += 500000)
		{
			$colors[] = '#'.dechex($i);
		}
		$colors[] = '#000000';
		
		foreach($colors as $current_color)
		{
			$tpl->setCurrentBlock('color_selection');
			$tpl->setVariable('COLOR',$current_color);
			$tpl->parseCurrentBlock();
		}
		$color->setHTML($tpl->get());
		$this->form->addItem($color);		
		
		
		
	}
	
	
	

	public function getHTML()
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryTableGUI.php');
		
		$table_gui = new ilCalendarCategoryTableGUI($this);
		$table_gui->setTitle($this->lng->txt('cal_table_categories'));
		$table_gui->addMultiCommand('saveSelection',$this->lng->txt('show'));
		$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'add'),
			$this->lng->txt('new'));
		$table_gui->parse();
		
		return $table_gui->getHTML();
	}
}
?>