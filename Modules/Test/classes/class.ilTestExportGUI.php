<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportGUI.php';

/**
 * Export User Interface Class
 * @author       Michael Jansen <mjansen@databay.de>
 * @version      $Id$
 * @ingroup      ModulesTest
 * @ilCtrl_Calls ilTestExportGUI:
 */
class ilTestExportGUI extends ilExportGUI
{
	public function __construct($a_parent_gui, $a_main_obj = null)
	{
		global $ilPluginAdmin;
		
		parent::__construct($a_parent_gui, $a_main_obj);
		
		$this->addFormat('xml', $a_parent_gui->lng->txt('ass_create_export_file'), $this, 'createTestExport');
		$this->addFormat('csv', $a_parent_gui->lng->txt('ass_create_export_test_results'), $this, 'createTestResultsExport');
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'Test', 'texp');
		foreach($pl_names as $pl)
		{
			/**
			 * @var $plugin ilTestExportPlugin
			 */
			$plugin = ilPluginAdmin::getPluginObject(IL_COMP_MODULE, 'Test', 'texp', $pl);
			$plugin->setTest($this->obj);
			$this->addFormat(
				$plugin->getFormat(),
				$plugin->getFormatLabel(),
				$plugin,
				'export'
			);
		}
	}
	
	/**
	 * Create test export file
	 */
	public function createTestExport()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		require_once 'Modules/Test/classes/class.ilTestExport.php';
		$test_exp = new ilTestExport($this->obj, 'xml');
		$test_exp->buildExportFile();
		ilUtil::sendSuccess($lng->txt('exp_file_created'), true);
		$ilCtrl->redirectByClass('iltestexportgui');
	}

	/**
	 * Create results export file
	 */
	public function createTestResultsExport()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		require_once 'Modules/Test/classes/class.ilTestExport.php';
		$test_exp = new ilTestExport($this->obj, 'results');
		$test_exp->buildExportFile();
		ilUtil::sendSuccess($lng->txt('exp_file_created'), true);
		$ilCtrl->redirectByClass('iltestexportgui');
	}
	
	/**
	 *
	 */
	public function listExportFiles()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng;

		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

		if(count($this->getFormats()) > 1)
		{
			foreach($this->getFormats() as $f)
			{
				$options[$f["key"]] = $f["txt"];
			}
			include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
			$si = new ilSelectInputGUI($lng->txt("type"), "format");
			$si->setOptions($options);
			$ilToolbar->addInputItem($si, true);
			$ilToolbar->addFormButton($lng->txt("exp_create_file"), "createExportFile");
		}
		else
		{
			$format = $this->getFormats();
			$format = $format[0];
			$ilToolbar->addFormButton($lng->txt("exp_create_file") . " (" . $format["txt"] . ")", "create_" . $format["key"]);
		}

		$export_dir   = $this->obj->getExportDirectory();
		$export_files = $this->obj->getExportFiles($export_dir);
		$data         = array();
		if(count($export_files) > 0)
		{
			foreach($export_files as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				array_push($data, array(
					'file'      => $exp_file,
					'size'      => filesize($export_dir . "/" . $exp_file),
					'timestamp' => $file_arr[0]
				));
			}
		}

		require_once 'Modules/Test/classes/tables/class.ilTestExportTableGUI.php';
		$table = new ilTestExportTableGUI($this, 'listExportFiles', $this->obj);
		$table->setSelectAllCheckbox("file");
		foreach($this->getCustomColumns() as $c)
		{
			$table->addCustomColumn($c["txt"], $c["obj"], $c["func"]);
		}
		foreach($this->getCustomMultiCommands() as $c)
		{
			$table->addCustomMultiCommand($c["txt"], "multi_".$c["func"]);
		}
		$table->setData($data);
		$tpl->setContent($table->getHTML());
	}

	/**
	 *
	 */
	public function download()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;
		
		if(!isset($_POST['file']))
		{
			ilUtil::sendInfo($lng->txt('no_checkbox'), true);
			$ilCtrl->redirect($this, 'listExportFiles');
		}

		if(count($_POST['file']) > 1)
		{
			ilUtil::sendInfo($lng->txt('select_max_one_item'), true);
			$ilCtrl->redirect($this, 'listExportFiles');
		}

		$export_dir = $this->obj->getExportDirectory();
		ilUtil::deliverFile($export_dir . '/' . $_POST['file'][0], $_POST['file'][0]);
	}

	/**
	 * Delete files
	 */
	public function delete()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;
		
		$export_dir = $this->obj->getExportDirectory();
		foreach($_POST['file'] as $file)
		{
			$exp_file = $export_dir.'/'.$file;
			$exp_dir = $export_dir.'/'.substr($file, 0, strlen($file) - 4);
			if(@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if(@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		ilUtil::sendSuccess($lng->txt('msg_deleted_export_files'), true);
		$ilCtrl->redirect($this, 'listExportFiles');
	}
}