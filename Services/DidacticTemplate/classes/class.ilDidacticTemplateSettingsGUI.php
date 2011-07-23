<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';

/**
 * Settings for a single didactic template
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 * @ilCtrl_IsCalledBy ilDidacticTemplateSettingsGUI: ilObjRoleFolderGUI
 */
class ilDidacticTemplateSettingsGUI
{
	private $parent_object;

	private $lng;

	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj)
	{
		global $lng;
		
		$this->parent_object = $a_parent_obj;
		$this->lng = $lng;
	}

	/**
	 * Execute command
	 * @return <type> 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'overview';
				}
				$this->$cmd();

				break;
		}
		return true;
	}

	/**
	 * Show didactic template administration
	 *
	 * @global ilToolbarGUI $ilToolbar
	 */
	protected function overview()
	{
		global $ilToolbar,$lng;

		$ilToolbar->addButton($lng->txt('didactic_import_btn'), 'showImportForm');

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettingsTableGUI.php';
		$table = new ilDidacticTemplateSettingsTableGUI($this,'overview');
		$table->init();
		$table->parse();

		$GLOBALS['tpl']->setContent($table->getHTML());
	}

	/**
	 * Copy on template
	 */
	protected function copyTemplate()
	{
		global $ilErr, $ilCtrl;;

		if(!$_REQUEST['tplid'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		$orig = new ilDidacticTemplateSetting((int) $_REQUEST['tplid']);
		$copy = clone $orig;
		$copy->save();

		ilUtil::sendSuccess($this->lng->txt('didactic_copy_suc_message'), true);
		$ilCtrl->redirect($this,'overview');

	}

	/**
	 * Show delete confirmation screen
	 */
	protected function confirmDelete()
	{
		global $ilErr, $ilCtrl;

		if(!$_REQUEST['tpls'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';

		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteTemplates');
		$confirm->setCancel($this->lng->txt('cancel'), 'overview');

		foreach((array) $_REQUEST['tpls'] as $tplid)
		{
			$tpl = new ilDidacticTemplateSetting($tplid);
			$confirm->addItem('tpls', $tpl->getId(), $tpl->getTitle());
		}

		ilUtil::sendQuestion($this->lng->txt('didactic_confirm_delete_msg'));
		$GLOBALS['tpl']->setContent($confirm->getHTML());
	}

	/**
	 * Delete chosen didactic templates
	 * @global ilErrorHandling $ilErr
	 * @global ilCtrl $ilCtrl
	 * @return void 
	 */
	protected function deleteTemplates()
	{
		global $ilErr, $ilCtrl;

		if(!$_REQUEST['tpls'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		foreach((array) $_REQUEST['tpls'] as $tplid)
		{
			$tpl = new ilDidacticTemplateSetting($tplid);
			$tpl->delete();
		}

		ilUtil::sendSuccess($this->lng->txt('didactic_delete_msg'),true);
		$ilCtrl->redirect($this,'overview');
	}



}
?>