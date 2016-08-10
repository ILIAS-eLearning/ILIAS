<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for member tab content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilMembershipGUI
{
	/**
	 * @var ilObject
	 */
	private $repository_object = null;
	
	/**
	 * @var ilObjectGUI 
	 */
	private $repository_gui = null;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng = null;
	
	/**
	 * @var ilCtrl
	 */
	protected $ctrl = null;
	
	/**
	 * @var ilLogger
	 */
	protected $logger = null;
	
	/**
	 * Constructor
	 * @param ilObject $repository_obj
	 */
	public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
	{
		$this->repository_gui = $repository_gui;
		$this->repository_object = $repository_obj;
		
		$this->logger = ilLoggerFactory::getLogger($this->getParentObject()->getType());
		$this->lng = $GLOBALS['DIC']['lng'];
		$this->lng->loadLanguageModule($this->getParentObject()->getType());
	}
	
	/**
	 * Get parent gui
	 * @return ilObjectGUI
	 */
	public function getParentGUI()
	{
		return $this->repository_gui;
	}
	
	/**
	 * Get parent object
	 * @return ilObject
	 */
	public function getParentObject()
	{
		return $this->repository_object;
	}
	
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		
	}
}
?>