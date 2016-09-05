<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

ilAptarInterfaceLogOverviewPlugin::getInstance()->includeClass('class.ilAptarInterfaceLogOverviewSettings.php');

require_once 'Services/Component/classes/class.ilPluginConfigGUI.php';

/**
 * Class ilAptarInterfaceLogOverviewConfigGUI
 */
class ilAptarInterfaceLogOverviewConfigGUI extends ilPluginConfigGUI
{
	/** @var $tpl ilTemplate */
	protected $tpl;

	/** @var $lng ilLanguage */
	protected $lng;

	/** @var $tabs ilTabsGUI */
	protected $tabs;

	/** @var $ctrl ilCtrl */
	protected $ctrl;

	/** @var $ilDB   ilDB */
	protected $db;

	/**
	 * {@inheritdoc}
	 */
	public function executeCommand()
	{
		/**
		 * @var $ilDB   ilDB
		 * @var $ilTabs ilTabsGUI
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 * @var $tpl ilTemplate
		 */
		global $ilDB, $ilTabs, $ilCtrl, $lng, $tpl;

		$this->db	= $ilDB;
		$this->ctrl	= $ilCtrl;
		$this->tpl	= $tpl;
		$this->lng	= $lng;
		$this->tabs	= $ilTabs;

		$next_class	= $ilCtrl->getNextClass();
		switch($next_class)
		{
			default:
				parent::executeCommand();
				$this->showTabs();
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function performCommand($cmd)
	{
		switch($cmd)
		{
			case 'saveConfigurationForm':
				$this->saveConfigurationForm();
				break;
			case 'downloadLogFile':
				$this->downloadFile();
				$this->getDataTableHTML();
				break;
			case 'showDataTable':
				$this->getDataTableHTML();
				break;
			case 'confirmDeleteLogfile':
				$this->deleteLogFiles();
				$this->getDataTableHTML();
				break;
			case 'doUserAutoComplete':
				$this->doUserAutoComplete();
				break;
			case 'showConfigurationForm':
			case 'configure':
			default:
				$this->showConfigurationForm();
				break;
		}
	}

	protected function deleteLogFiles()
	{
		if(array_key_exists('log_id',$_POST))
		{
			$log_ids = ilUtil::stripSlashesRecursive($_POST['log_id']);
			if(is_array($log_ids) && sizeof($log_ids))
			{
				$data = $this->getDataForLogs($log_ids);
				foreach($data as $key => $row)
				{
					if(is_array($row) && array_key_exists('file_path', $row))
					{
						if(file_exists($row['file_path']))
						{
							unlink($row['file_path']);
						}
					}
				}
				ilAptarInterfaceLogOverviewSettings::getInstance()->removeDataFromDb($log_ids);
			}
		}
	}
	
	protected function downloadFile()
	{
		$log_id = ilUtil::stripOnlySlashes($_GET['download_id']);
		$file =  ilAptarInterfaceLogOverviewSettings::getInstance()->getFileNameForLogId($log_id);
		if(file_exists($file))
		{
			ilUtil::deliverFile($file, basename($file),false);
		}
	}

	/**
	 * @param array $log_ids
	 * @return array
	 */
	protected function getDataForLogs($log_ids)
	{
			$in		= $this->db->in('log_id',$log_ids, false, 'integer');
			$res	= $this->db->query('SELECT * FROM aptar_ilo_log_table WHERE ' . $in);
			
			$data	= array();
			while($row = $this->db->fetchAssoc($res))
			{
				$data [] = $row;
			}
			return $data;
	}
	

	protected function getDataTableHTML()
	{
		ilAptarInterfaceLogOverviewPlugin::getInstance()->includeClass('class.ilAptarDataTableGUI.php');
		$tbl = new ilAptarDataTableGUI($this, 'showDataTable');
		$tbl->setData($this->getDataFromDb());
		$this->tpl->setContent($tbl->getHTML());
		$this->tabs->activateTab('logfiles');
	}

	protected function getDataFromDb()
	{
		$res		= $this->db->query('SELECT * 	FROM aptar_ilo_log_table ');
		$data	= array();
		while($row = $this->db->fetchAssoc($res))
		{
			$data [] = $row;
		}
		return $data;
	}
	
	/**
	 *
	 */
	protected function showTabs()
	{

		$this->tabs->clearTargets();
		if(isset($_GET['plugin_id']) && $_GET['plugin_id'])
		{
			$this->tabs->setBackTarget(
				$this->lng->txt("cmps_plugin"),
				$this->ctrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "showPlugin")
			);
		}
		else
		{
			$this->tabs->setBackTarget(
				$this->lng->txt("cmps_plugins"),
				$this->ctrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "listPlugins")
			);
		}
		$this->tabs->addTarget(
			'settings', $this->ctrl->getLinkTarget($this, 'showConfigurationForm'),
			array('configure', 'showConfigurationForm', 'saveConfigurationForm'), __CLASS__
		);
		$this->tabs->addTarget(
			$this->getPluginObject()->txt('datatable'), $this->ctrl->getLinkTarget($this, 'showDataTable'),
			array('showDataTable', 'confirmDeleteLogfile'), __CLASS__,  "", false, true
		);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function showConfigurationForm(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			
			
			
			$form = $this->getConfigurationForm();
			$form->setValuesByArray(array(
				'cleanup_status'			=> ilAptarInterfaceLogOverviewSettings::getInstance()->isCleanupEnabled(),
				'cleanup_boundary_value'	=> ilAptarInterfaceLogOverviewSettings::getInstance()->getCleanupBoundaryValue(),
				'cleanup_boundary_unit'		=> ilAptarInterfaceLogOverviewSettings::getInstance()->getCleanupBoundaryUnit(),
				'is_mail_enabled'			=> ilAptarInterfaceLogOverviewSettings::getInstance()->isMailEnabled(),
				'recipients'				=> ilAptarInterfaceLogOverviewSettings::getInstance()->getRecipients(),
				'subject'					=> ilAptarInterfaceLogOverviewSettings::getInstance()->getSubject(),
				'error_level'				=> ilAptarInterfaceLogOverviewSettings::getInstance()->getErrorLevel(),
				'add_attachment'			=> ilAptarInterfaceLogOverviewSettings::getInstance()->isAddAttachment(),
			));
		}
		
		$this->tpl->setContent($form->getHTML());
	}
	/**
	 *
	 */
	protected function saveConfigurationForm()
	{
		$form = $this->getConfigurationForm();
		if($form->checkInput())
		{
			$recipients = $form->getInput('recipients');
			
			ilAptarInterfaceLogOverviewSettings::getInstance()->setIsCleanupEnabled($form->getInput('cleanup_status'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setCleanupBoundaryValue($form->getInput('cleanup_boundary_value'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setCleanupBoundaryUnit($form->getInput('cleanup_boundary_unit'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setIsMailEnabled($form->getInput('is_mail_enabled'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setRecipients($recipients);
			ilAptarInterfaceLogOverviewSettings::getInstance()->setSubject($form->getInput('subject'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setErrorLevel($form->getInput('error_level'));
			ilAptarInterfaceLogOverviewSettings::getInstance()->setAddAttachment($form->getInput('add_attachment'));

			try
			{
				ilAptarInterfaceLogOverviewSettings::getInstance()->save();
				ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
				$this->ctrl->redirect($this, 'showConfigurationForm');
			}
			catch(ilException $e)
			{
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			}
		}

		$form->setValuesByPost();
		$this->showConfigurationForm($form);
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	private function getConfigurationForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('settings'));
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveConfigurationForm'));
		$form->setShowTopButtons(false);


		$cleanup = new ilCheckboxInputGUI($this->getPluginObject()->txt('cleanup_status'), 'cleanup_status');
		$cleanup->setInfo($this->getPluginObject()->txt('cleanup_status_info'));
		$cleanup_boundary_value = new ilNumberInputGUI($this->getPluginObject()->txt('cleanup_boundary_value'), 'cleanup_boundary_value');
		$cleanup_boundary_value->allowDecimals(false);
		$cleanup_boundary_value->setSize(2);
		$cleanup_boundary_value->setMinValue(1);
		$cleanup_boundary_value->setInfo($this->getPluginObject()->txt('cleanup_boundary_value_info'));
		$cleanup_boundary_value->setRequired(true);
		$cleanup->addSubItem($cleanup_boundary_value);
		$cleanup_boundary_unit= new ilSelectInputGUI($this->getPluginObject()->txt('cleanup_boundary_unit'), 'cleanup_boundary_unit');
		$cleanup_boundary_unit->setInfo($this->getPluginObject()->txt('cleanup_boundary_unit_info'));
		$cleanup_boundary_unit->setRequired(true);
		$options = array();
		foreach(ilAptarInterfaceLogOverviewSettings::getBoundaryUnits() as $unit)
		{
			$options[$unit] = $this->getPluginObject()->txt('cleanup_boundary_unit_' . $unit);
		}
		$cleanup_boundary_unit->setOptions($options);
		$cleanup->addSubItem($cleanup_boundary_unit);
		$form->addItem($cleanup);

		$mail = new ilCheckboxInputGUI($this->getPluginObject()->txt('notification_mail'), 'is_mail_enabled');
		$mail->setInfo($this->getPluginObject()->txt('notification_mail_info'));

		// RECIPIENT
		$dsDataLink = $this->ctrl->getLinkTarget($this, 'doUserAutoComplete', '', true);
		$recipients = new ilTextInputGUI($this->getPluginObject()->txt('recipients'), 'recipients');
		$recipients->setRequired(true);
		$recipients->setValue(array());
		$recipients->setDataSource($dsDataLink);
		$recipients->setMaxLength(null);
		$recipients->setMulti(true);
		$recipients->setInfo($this->getPluginObject()->txt('recipients_info'));
		$mail->addSubItem($recipients);

		$subject = new ilTextInputGUI($this->getPluginObject()->txt('subject'), 'subject');
		$subject->setInfo($this->getPluginObject()->txt('subject_info'));
		$subject->setRequired(true);
		$mail->addSubItem($subject);

		$error_level = new ilSelectInputGUI($this->getPluginObject()->txt('error_level'), 'error_level');
		$error_level->setInfo($this->getPluginObject()->txt('error_level_info'));
		$error_level->setOptions(array(ilAptarInterfaceLogOverviewSettings::EMERG	=> 'EMERG', 
									   ilAptarInterfaceLogOverviewSettings::ALERT	=> 'ALERT',
									   ilAptarInterfaceLogOverviewSettings::CRIT	=> 'CRIT',
									   ilAptarInterfaceLogOverviewSettings::ERR		=> 'ERR',
									   ilAptarInterfaceLogOverviewSettings::WARN	=> 'WARN',
									   ilAptarInterfaceLogOverviewSettings::NOTICE	=> 'NOTICE',
									   ilAptarInterfaceLogOverviewSettings::INFO	=> 'INFO',
									   ilAptarInterfaceLogOverviewSettings::DEBUG	=> 'DEBUG',
									   ));
		$mail->addSubItem($error_level);

		$add_attachment = new ilCheckboxInputGUI($this->getPluginObject()->txt('add_attachment'), 'add_attachment');
		$add_attachment->setInfo($this->getPluginObject()->txt('add_attachment_info'));
		$mail->addSubItem($add_attachment);

		$form->addItem($mail);
		$form->addCommandButton('saveConfigurationForm', $this->lng->txt('save'));

		return $form;
	}

	/**
	 * Do auto completion
	 * @return void
	 */
	public function doUserAutoComplete()
	{

		if(!isset($_GET['autoCompleteField']))
		{
			$a_fields = array('login','firstname','lastname','email', 'recipients');
			$result_field = 'login';
		}
		else
		{
			$a_fields = array((string) $_GET['autoCompleteField']);
			$result_field = (string) $_GET['autoCompleteField'];
		}

		$GLOBALS['ilLog']->write(print_r($a_fields,true));
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields($a_fields);
		$auto->setResultField($result_field);
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList($_REQUEST['term']);
		exit();
	}
}