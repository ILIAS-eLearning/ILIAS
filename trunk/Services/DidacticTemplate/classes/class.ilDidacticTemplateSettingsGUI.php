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
		global $ilToolbar,$lng, $ilCtrl;

		$ilToolbar->addButton(
			$lng->txt('didactic_import_btn'),
			$ilCtrl->getLinkTarget($this,'showImportForm')
		);

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettingsTableGUI.php';
		$table = new ilDidacticTemplateSettingsTableGUI($this,'overview');
		$table->init();
		$table->parse();

		$GLOBALS['tpl']->setContent($table->getHTML());
	}

	/**
	 * Show template import form
	 *
	 * @global ilTabsGUI $ilTabs
	 */
	protected function showImportForm(ilPropertyFormGUI $form = NULL)
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('didactic_back_to_overview'),
			$ilCtrl->getLinkTarget($this,'overview')
		);

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->createImportForm();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}

	/**
	 * Create template import form
	 * @return ilPropertyFormGUI $form
	 */
	protected function createImportForm()
	{
		global $ilCtrl;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setShowTopButtons(false);
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt('didactic_import_table_title'));
		$form->addCommandButton('importTemplate', $this->lng->txt('import'));
		$form->addCommandButton('overview', $this->lng->txt('cancel'));

		$file = new ilFileInputGUI($this->lng->txt('import_file'), 'file');
		$file->setSuffixes(array('xml'));
		$file->setRequired(TRUE);
		$form->addItem($file);

		$created = true;

		return $form;
	}

	/**
	 * Import template
	 */
	protected function importTemplate()
	{
		global $ilCtrl;

		$form = $this->createImportForm();
		if(!$form->checkInput())
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$form->setValuesByPost();
			return $this->showImportForm($form);
		}

		// Do import
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateImport.php';
		
		$import = new ilDidacticTemplateImport(ilDidacticTemplateImport::IMPORT_FILE);

		$file = $form->getInput('file');
		$tmp = ilUtil::ilTempnam();

		// move uploaded file
		ilUtil::moveUploadedFile(
			$file['tmp_name'],
			$file['name'],
			$tmp
		);
		$import->setInputFile($tmp);

		$GLOBALS['ilLog']->write(__METHOD__.': Using '.$tmp);

		try {
			$import->import();
		}
		catch(ilDidacticTemplateImportException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Import failed with message: '. $e->getMessage());
			ilUtil::sendFailure($this->lng->txt('didactic_import_failed').': '.$e->getMessage());
		}

		ilUtil::sendSuccess($this->lng->txt('didactic_import_success'),TRUE);
		$ilCtrl->redirect($this,'overview');
	}

	/**
	 * Edit template
	 * @return void
	 */
	protected function editTemplate(ilPropertyFormGUI $form = null)
	{
		global $ilCtrl,$ilTabs;

		if(!$_REQUEST['tplid'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('didactic_back_to_overview'),
			$ilCtrl->getLinkTarget($this,'overview')
		);


		$ilCtrl->saveParameter($this,'tplid');

		if(!$form instanceof ilPropertyFormGUI)
		{
			$settings = new ilDidacticTemplateSetting((int) $_REQUEST['tplid']);
			$form = $this->initEditTemplate($settings);
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}

	/**
	 * Update template
	 */
	protected function updateTemplate()
	{
		global $ilCtrl;

		$temp = new ilDidacticTemplateSetting((int) $_REQUEST['tplid']);
		$form = $this->initEditTemplate($temp);

		if($form->checkInput())
		{
			$temp->setTitle($form->getInput('title'));
			$temp->setDescription($form->getInput('description'));
			$temp->setInfo($form->getInput('info'));
			$temp->setAssignments(array($form->getInput('type')));
			$temp->update();

			ilUtil::sendSuccess($this->lng->txt('save_settings'));
			$ilCtrl->redirect($this,'overview');
		}

		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$form->setValuesByPost();
		$this->editTemplate($form);
	}

	/**
	 * Init edit template form
	 */
	protected function initEditTemplate(ilDidacticTemplateSetting $set)
	{
		global $ilCtrl,$objDefinition;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setShowTopButtons(false);
		$form->setFormAction($ilCtrl->getFormAction($this,'updateTemplate'));
		$form->setTitle($this->lng->txt('didactic_edit_tpl'));
		$form->addCommandButton('updateTemplate', $this->lng->txt('save'));
		$form->addCommandButton('overview', $this->lng->txt('cancel'));

		// title
		$title = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$title->setValue($set->getTitle());
		$title->setSize(40);
		$title->setMaxLength(64);
		$title->setRequired(true);
		$form->addItem($title);

		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
		$desc->setValue($set->getDescription());
		$desc->setRows(3);
		$form->addItem($desc);

		// info
		$info = new ilTextAreaInputGUI($this->lng->txt('didactic_install_info'), 'info');
		$info->setValue($set->getInfo());
		$info->setRows(6);
		$form->addItem($info);

		// object type
		$type = new ilSelectInputGUI($this->lng->txt('obj_type'),'type');
		$type->setRequired(true);
		$assigned = $set->getAssignments();
		$type->setValue(isset($assigned[0]) ? $assigned[0] : '');
		$subs = $objDefinition->getCreatableSubobjects('root', false);
		$options = array();
		foreach(array_merge($subs,array('fold' => 1)) as $obj => $null)
		{
			if($objDefinition->isAllowedInRepository($obj))
			{
				$options[$obj] = $this->lng->txt('obj_'.$obj);
			}
		}
		$type->setOptions($options);
		$form->addItem($type);

		return $form;
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

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateCopier.php';

		$copier = new ilDidacticTemplateCopier((int) $_REQUEST['tplid']);
		$copier->start();

		ilUtil::sendSuccess($this->lng->txt('didactic_copy_suc_message'), true);
		$ilCtrl->redirect($this,'overview');
	}

	/**
	 * Export one template
	 */
	protected function exportTemplate()
	{
		global $ilErr, $ilCtrl;

		if(!$_REQUEST['tplid'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateXmlWriter.php';
		$writer = new ilDidacticTemplateXmlWriter((int) $_REQUEST['tplid']);
		$writer->write();

		ilUtil::deliverData(
			$writer->xmlDumpMem(TRUE), 
			$writer->getSetting()->getTitle().'.xml',
			'application/xml'
		);
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
			$confirm->addItem('tpls[]', $tpl->getId(), $tpl->getTitle());
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

	/**
	 * Activate didactic templates
	 * @global ilErrorHandling $ilErr
	 * @global ilCtrl $ilCtrl
	 * @return void
	 */
	protected function activateTemplates()
	{
		global $ilErr, $ilCtrl;

		if(!$_REQUEST['tpls'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		foreach($_REQUEST['tpls'] as $tplid)
		{
			$tpl = new ilDidacticTemplateSetting($tplid);
			$tpl->enable(true);
			$tpl->update();
		}

		ilUtil::sendSuccess($this->lng->txt('didactic_activated_msg'),true);
		$ilCtrl->redirect($this,'overview');
	}

	/**
	 * Activate didactic templates
	 * @global ilErrorHandling $ilErr
	 * @global ilCtrl $ilCtrl
	 * @return void
	 */
	protected function deactivateTemplates()
	{
		global $ilErr, $ilCtrl;

		if(!$_REQUEST['tpls'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $ilCtrl->redirect($this,'overview');
		}

		foreach($_REQUEST['tpls'] as $tplid)
		{
			$tpl = new ilDidacticTemplateSetting($tplid);
			$tpl->enable(false);
			$tpl->update();
		}

		ilUtil::sendSuccess($this->lng->txt('didactic_deactivated_msg'),true);
		$ilCtrl->redirect($this,'overview');
	}


}
?>