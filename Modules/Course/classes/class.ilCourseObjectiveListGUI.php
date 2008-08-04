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

include_once "classes/class.ilObjectListGUI.php";
/**
* List gui for course objectives
*
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse 
*/
class ilCourseObjectiveListGUI extends ilObjectListGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * init
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = false;
		$this->cut_enabled = false;
		$this->subscribe_enabled = false;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->info_screen_enabled = false;
		$this->type = "lobj";
		//$this->gui_class_name = "ilobjcoursegui";
		
		// general commands array
		$this->commands = array();
	}
	
	/**
	 * get properties
	 *
	 * @access public
	 * @return
	 */
	public function getProperties()
	{
		return parent::getProperties();
	}
	
	/**
	 * get list item html
	 *
	 * @access public
	 * @param int ref_id
	 * @param int obj_id
	 * @param string title
	 * @param string description
	 * @return
	 */
	public function getListItemHTML($a_ref_id,$a_obj_id,$a_title,$a_description)
	{
		$this->tpl =& new ilTemplate ("tpl.container_list_item.html", true, true);
		$this->initItem($a_ref_id, $a_obj_id, $a_title, $a_description);

		$this->insertIconsAndCheckboxes();
		$this->insertTitle();
		$this->insertDescription();
		$this->insertObjectiveStatus();
		
		// subitems
		$this->insertSubItems();

		// reset properties and commands
		$this->cust_prop = array();
		$this->cust_commands = array();
		$this->sub_item_html = array();
		$this->position_enabled = false;
		
		return $this->tpl->get();
	}
	
	/**
	 * insert objective status
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function insertObjectiveStatus()
	{
		$this->tpl->setCurrentBlock('payment');
		$this->tpl->setVariable('PAYMENT_TYPE_IMG', ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable('PAYMENT_ALT_IMG',$this->lng->txt('ok'));
		$this->tpl->parseCurrentBlock();				
	}
}
?>