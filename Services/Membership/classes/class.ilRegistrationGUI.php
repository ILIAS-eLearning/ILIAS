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
* Base class for Course and Group registration
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesRegistration
*/

abstract class ilRegistrationGUI
{
	protected $container = null;
	protected $ref_id;
	protected $obj_id;
	
	protected $participants;
	protected $form;
	
	protected $registration_possible = true;
	protected $join_error = '';
	

	protected $tpl;
	protected $lng;
	protected $ctrl; 

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object Course or Group object
	 * @return
	 */
	public function __construct($a_container)
	{
		global $lng,$ilCtrl,$tpl;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('grp');
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		
		$this->container = $a_container;
		$this->ref_id = $this->container->getRefId();
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
		$this->type = ilObject::_lookupType($this->obj_id);
		
		// Init participants
		$this->initParticipants();
	}
	
	/**
	 * check if registration is possible
	 *
	 * @access protected
	 * @return bool
	 */
	protected function isRegistrationPossible()
	{
		return (bool) $this->registration_possible;
	}
	
	/**
	 * set registration disabled
	 *
	 * @access protected
	 * @param bool 
	 * @return
	 */
	protected function enableRegistration($a_status)
	{
		$this->registration_possible = $a_status;
	}
	
	
	/**
	 * Init participants object (course or group participants)
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function initParticipants();
	
	/**
	 * Get title for property form
	 *
	 * @access protected
	 * @return string title
	 */
	abstract protected function getFormTitle();
	
	/**
	 * fill informations
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function fillInformations();
	
	/**
	 * show informations about the registration period
	 *
	 * @access protected
	 */
	abstract protected function fillRegistrationPeriod();
	
	/**
	 * show informations about the maximum number of user.
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	abstract protected function fillMaxMembers();
	
	
	/**
	 * show informations about registration procedure
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function fillRegistrationType();
	
	/**
	 * Show membership limitations
	 *
	 * @access protected
	 * @return
	 */
	protected function fillMembershipLimitation()
	{
		global $ilAccess;
		
		include_once('Modules/Course/classes/class.ilObjCourseGrouping.php');
		if(!$items = ilObjCourseGrouping::_getGroupingItems($this->container))
		{
			return true;
		}
		
		$mem = new ilCustomInputGUI($this->lng->txt('groupings'));
		
		$tpl = new ilTemplate('tpl.membership_limitation_form.html',true,true,'Services/Membership');
		$tpl->setVariable('LIMIT_INTRO',$this->lng->txt($this->type.'_grp_info_reg'));
		
		foreach($items as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$type = ilObject::_lookupType($obj_id);
			$title = ilObject::_lookupTitle($obj_id);
			
			if($ilAccess->checkAccess('visible','',$ref_id,$type))
			{
				include_once('./classes/class.ilLink.php');
				$tpl->setVariable('LINK_ITEM','repository.php?ref_id='.$ref_id);
				$tpl->setVariable('ITEM_LINKED_TITLE',$title);
			}
			else
			{
				$tpl->setVariable('ITEM_TITLE');
			}
			$tpl->setCurrentBlock('items');
			$tpl->setVariable('TYPE_ICON',ilUtil::getTypeIconPath($type,$obj_id,'tiny'));
			$tpl->setVariable('ALT_ICON',$this->lng->txt('obj_'.$type));
			$tpl->parseCurrentBlock();
		}
		
		$mem->setHtml($tpl->get());
		
		
		if(!ilObjCourseGrouping::_checkGroupingDependencies($this->container))
		{
			$mem->setAlert($this->container->getMessage());
			$this->enableRegistration(false);
		}
		$this->form->addItem($mem);
	}
	
	/**
	 * Show user agreement
	 *
	 * @access protected
	 * @return
	 */
	protected function fillAgreement()
	{
		return true;
	}
	
	
	/**
	 * cancel subscription
	 *
	 * @access public
	 */
	public function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * show registration form
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function show()
	{
		$this->initForm();
		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * join 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function join()
	{
		$this->initForm();

		if(!$this->validate())
		{
			ilUtil::sendInfo($this->join_error);
			$this->show();
			return false;
		}
		
		$this->add();
	}
	
	
	/**
	 * validate join request
	 *
	 * @access protected
	 * @return bool
	 */
	protected function validate()
	{
		return true;
	}
	
	/**
	 * init registration form
	 *
	 * @access protected
	 * @return
	 */
	protected function initForm()
	{
		if(is_object($this->form))
		{
			return true;
		}

		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'join'));
		$this->form->setTitle($this->getFormTitle());
		
		$this->fillInformations();
		$this->fillMembershipLimitation();
		$this->fillRegistrationPeriod();
		$this->fillRegistrationType();
		$this->fillMaxMembers();
		$this->fillAgreement();
		
		if($this->isRegistrationPossible())
		{
			$this->form->addCommandButton('join',$this->lng->txt('join'));
			$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		}
	}
}
?>