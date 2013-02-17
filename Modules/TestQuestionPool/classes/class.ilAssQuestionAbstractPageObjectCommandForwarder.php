<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

/**
 * abstract parent class for page object forwarders
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
abstract class ilAssQuestionAbstractPageObjectCommandForwarder
{
	/**
	 * object instance of current question
	 * 
	 * @access protected
	 * @var assQuestion 
	 */
	protected $questionOBJ = null;
	
	/**
	 * global $ilCtrl
	 *
	 * @access protected
	 * @var ilCtrl
	 */
	protected $ctrl = null;

	/**
	 * global $ilCtrl
	 *
	 * @access protected
	 * @var ilCtrl
	 */
	protected $tabs = null;

	/**
	 * global $ilCtrl
	 *
	 * @access protected
	 * @var ilCtrl
	 */
	protected $lng = null;
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param assQuestion $questionOBJ
	 * @param ilCtrl $ctrl
	 * @param ilTabsGUI $tabs
	 * @param ilLanguage $lng
	 */
	public function __construct(assQuestion $questionOBJ, ilCtrl $ctrl, ilTabsGUI $tabs, ilLanguage $lng)
	{
		$this->questionOBJ = $questionOBJ;
		
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->lng = $lng;

		$this->tabs->clearTargets();
		
		$this->lng->loadLanguageModule('content');
	}

	/**
	 * this is the actual forward method that is to be implemented
	 * by derived forwarder classes
	 */
	abstract function forward();
	
	/**
	 * ensures an existing page object with giben type/id
	 * 
	 * @access protected
	 */
	protected function ensurePageObjectExists($pageObjectType, $pageObjectId)
	{
		if( !ilPageObject::_exists($pageObjectType, $pageObjectId) )
		{
			$pageObject = new ilPageObject($pageObjectType);
			$pageObject->setParentId($this->questionOBJ->getId());
			$pageObject->setId($pageObjectId);
			$pageObject->createFromXML();
		}
	}
	
	/**
	 * instantiates, initialises and returns a ilPageObjectGUI
	 * 
	 * @access protected
	 * @return \ilPageObjectGUI
	 */
	protected function getPageObjectGUI($pageObjectType, $pageObjectId)
	{
		$pageObjectGUI = new ilPageObjectGUI($pageObjectType, $pageObjectId);

		$pageObjectGUI->setTemplateTargetVar('ADM_CONTENT');
		$pageObjectGUI->setTemplateOutput(true);
		
//		$pageObjectGUI->setIntLinkHelpDefault('StructureObject', $pageObject->getId());
//		$pageObjectGUI->setLinkXML('');
//		$pageObjectGUI->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
//		$pageObjectGUI->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
//		$pageObjectGUI->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
//		$pageObjectGUI->setPresentationTitle('');
//		$pageObjectGUI->setHeader('');
//		$pageObjectGUI->setEnabledRepositoryObjects(false);
//		$pageObjectGUI->setEnabledFileLists(true);
//		$pageObjectGUI->setEnabledMaps(true);
//		$pageObjectGUI->setEnabledPCTabs(true);
		
		return $pageObjectGUI;
	}
}