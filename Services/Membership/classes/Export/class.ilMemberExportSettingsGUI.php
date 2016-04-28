<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Export settings gui
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * 
 */
class ilMemberExportSettingsGUI
{
	const TYPE_PRINT_VIEW_SETTINGS = 'print_view';
	const TYPE_EXPORT_SETTINGS = 'member_export';
	

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ctrl = $GLOBALS['ilCtrl'];
		$this->lng = $GLOBALS['lng'];
	}
	
	/**
	 * Get language
	 * @return ilLanguage
	 */
	private function getLang()
	{
		return $this->lng;
	}
	
	
	/**
	 * Execute command
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
	 * Show print view settings
	 */
	protected function printViewSettings(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initForm(self::TYPE_PRINT_VIEW_SETTINGS);
		}
		
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	/**
	 * init settings form
	 */
	protected function initForm($a_type)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->getLang()->txt('mem_'.$a_type.'_form'));
		
		switch($a_type)
		{
			case self::TYPE_PRINT_VIEW_SETTINGS:
				$form->addCommandButton('savePrintViewSettings', $this->getLang()->txt('save'));
				break;
		}
		return $form;
	}
}
?>