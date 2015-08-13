<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestNavigationToolbarGUI extends ilToolbarGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var bool
	 */
	private $suspendTestEnabled = false;

	/**
	 * @param ilCtrl $ctrl
	 * @param ilLanguage $lng
	 */
	public function __construct(ilCtrl $ctrl, ilLanguage $lng)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		
		parent::__construct();
	}

	/**
	 * @return boolean
	 */
	public function isSuspendTestEnabled()
	{
		return $this->suspendTestEnabled;
	}

	/**
	 * @param boolean $suspendTestEnabled
	 */
	public function setSuspendTestEnabled($suspendTestEnabled)
	{
		$this->suspendTestEnabled = $suspendTestEnabled;
	}
	
	public function build()
	{
		if( $this->isSuspendTestEnabled() )
		{
			$this->addSuspendTestButton();
		}
	}
	
	private function addSuspendTestButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTargetByClass('ilObjTestGUI'));
		$btn->setCaption($this->lng->txt('suspend_test'));
		$this->addButtonInstance($btn);
	}
}