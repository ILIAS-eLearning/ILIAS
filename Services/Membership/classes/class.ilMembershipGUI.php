<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Membership GUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesMembership
 */
class ilMembershipGUI
{
	protected $ctrl;
	protected $lng;
	private $object = null;
	
	public function __construct(ilObjectGUI $object)
	{
		global $ilCtrl, $lng;
		
		$this->object = $object;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
	}
	
	public function getCurrentObject()
	{
		return $this->object;
	}
	
	/**
	 * execute command
	 *
	 * @access public
	 * @return
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
  		switch($next_class)
		{
		
			default:
				$this->$cmd();
				break;
		}
		
  		return true;
	}
	
	/**
	 * show send mail
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sendMailToSelectedUsers()
	{
		if(isset($_GET['member_id']))
		{
			$_POST['participants'] = array($_GET['member_id']);
		}
		else
		{
			$_POST['participants'] = array_unique(array_merge(
				(array) $_POST['admins'],
				(array) $_POST['tutors'],
				(array) $_POST['members'],
				(array) $_POST['roles'],
				(array) $_POST['waiting'],
				(array) $_POST['subscribers']));
		}
		
		if (!count($_POST['participants']))
		{
			ilUtil::sendFailure($GLOBALS['lng']->txt("no_checkbox"),TRUE);
			$this->ctrl->returnToParent($this);
			return false;
		}
		foreach($_POST['participants'] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}
		
        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(ilMailFormCall::getRedirectTarget(
			$this->getCurrentObject(), 
			'members',
			array(), 
			array('type' => 'new', 'rcp_to' => implode(',',$rcps),'sig' => $this->createMailSignature())));
		return true;
	}
	
	/**
	 * Create a course mail signature
	 * @return 
	 */
	protected function createMailSignature()
	{
		$GLOBALS['lng']->loadLanguageModule($this->getCurrentObject()->object->getType());
		
		$link = chr(13).chr(10).chr(13).chr(10);
		$link .= $this->lng->txt($this->getCurrentObject()->object->getType().'_mail_permanent_link');
		$link .= chr(13).chr(10).chr(13).chr(10);
		include_once 'Services/Link/classes/class.ilLink.php';
		$link .= ilLink::_getLink($this->getCurrentObject()->object->getRefId());
		return rawurlencode(base64_encode($link));
	}
	
	
	
}
?>
