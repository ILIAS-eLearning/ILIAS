<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Demo/StorageRecord/class.arStorageRecord.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditGUI.php');
require_once('./Services/PersonalDesktop/classes/class.ilPersonalDesktopGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableGUI.php');
//require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/View/class.ActiveRecordViewGUI.php');

/**
 * Class arStorageRecordGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arStorageRecordGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;


	public function __construct() {
		global $ilCtrl, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->object = new arStorageRecord();
	}


	public function executeCommand() {
		$cmd = $_GET['cmd'] ? $_GET['cmd'] : 'index';
		$this->{$cmd}();
	}


	public function index() {
		$table = new arIndexTableGUI(new ilPersonalDesktopGUI(), 'index', arStorageRecordStorage::getCollection());
		$this->tpl->setContent($table->getHTML());
	}


	public function edit() {
		$editGUI = new arEditGUI(new ilPersonalDesktopGUI(), $this->object->getStorage());
		$this->tpl->setContent($editGUI->getHTML());
	}


	public function view() {
		//		$editGUI = new ActiveRecordViewGUI(new ilPersonalDesktopGUI(), $this->object->getStorage());
		//		$this->tpl->setContent($editGUI->getHTML());
	}
}

?>

