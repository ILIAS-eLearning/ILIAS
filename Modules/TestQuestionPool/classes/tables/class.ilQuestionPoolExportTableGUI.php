<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportTableGUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * Class ilQuestionPoolExportTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesTest
 */
class ilQuestionPoolExportTableGUI extends ilExportTableGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);

		// NOT REQUIRED ANYMORE, PROBLEM NOW FIXED IN THE ROOT
		// KEEP CODE, JF OPINIONS / ROOT FIXINGS CAN CHANGE
		//$this->addCustomColumn($this->lng->txt('actions'), $this, 'formatActionsList');
	}

	/**
	 * @param string $type
	 * @param string $filename
	 */
	protected function formatActionsList($type, $filename)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($this->lng->txt('actions'));
		$ilCtrl->setParameter($this->getParentObject(), 'file',  $type.':'.$filename);
		$list->addItem($this->lng->txt('download'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'download'));
		$ilCtrl->setParameter($this->getParentObject(), 'file', '');
		return $list->getHTML();
	}

	/***
	 * 
	 */
	protected function initMultiCommands()
	{
		$this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
	}
} 