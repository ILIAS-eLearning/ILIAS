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


include_once('./Services/Membership/classes/class.ilRegistrationGUI.php');

/**
* GUI class for course registrations
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
* 
* @ilCtrl_Calls ilCourseRegistrationGUI: 
*/
class ilCourseRegistrationGUI extends ilRegistrationGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object course object
	 */
	public function __construct($a_container)
	{
		parent::__construct($a_container);	
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 */
	public function executeCommand()
	{
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
	 * get form title
	 *
	 * @access protected
	 * @return string title
	 */
	protected function getFormTitle()
	{
		return $this->lng->txt('crs_registration');
	}
	
	/**
	 * fill informations
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillInformations()
	{
		if($this->container->getImportantInformation())
		{
			$imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'));
			$value =  nl2br(ilUtil::makeClickable($this->container->getImportantInformation(), true));
			$imp->setValue($value);
			$this->form->addItem($imp);
		}
		
		if($this->container->getSyllabus())
		{
			$syl = new ilNonEditableValueGUI($this->lng->txt('crs_syllabus'));
			$value = nl2br(ilUtil::makeClickable ($this->container->getSyllabus(), true));
			$syl->setValue($value);
			$this->form->addItem($syl);
		}
	}
	
	/**
	 * Init course participants
	 *
	 * @access protected
	 */
	protected function initParticipants()
	{
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		$this->participants = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
	}
}
?>