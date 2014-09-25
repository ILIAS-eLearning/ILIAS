<?php
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
include_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecordList.php');

/**
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.6
 *
 */
class arGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @ar ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilPlugin
	 */
	protected $plugin_object = NULL;
	/**
	 * @param  $string
	 */
	protected $record_type;

    /**
     * @var ActiveRecord
     */
    protected $ar;

    /**
     * @param $record_type
     * @param ilPlugin $plugin_object
     */
    public function __construct($record_type, ilPlugin $plugin_object = NULL) {
		global $tpl, $ilCtrl, $ilAccess, $lng;

		$this->lng = $lng;

		if ($plugin_object) {
			$this->setLngPrefix($plugin_object->getPrefix());
			$plugin_object->loadLanguageModule();
		}

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->access = $ilAccess;
		$this->plugin_object = $plugin_object;
		$this->record_type = $record_type;
		$this->ar = new $record_type();
	}


	function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$this->$cmd();
	}

	function index() {
		$index_table_gui_class = $this->record_type . "IndexTableGUI";
		$table_gui = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
		$this->tpl->setContent($table_gui->getHTML());
	}

    function applyFilter()
    {
        $index_table_gui_class = $this->record_type . "IndexTableGUI";
        $table_gui             = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
        $table_gui->applyFilter();
        $this->index();
    }

    function resetFilter()
    {
        $index_table_gui_class = $this->record_type . "IndexTableGUI";
        $table_gui             = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
        $table_gui->resetFilter();
        $this->index();
    }

    function applyAssignmentFilter()
    {
        include_once("class.ilDigisemadminChangeAssignmentTableGUI.php");
        $table_gui = new ilDigisemadminChangeAssignmentTableGUI($this, "showAssignment");
        $table_gui->writeFilterToSession();
        $table_gui->resetOffset();
        $this->showContent();
    }

    function resetAssignmentFilter()
    {
        include_once("class.ilDigisemadminChangeAssignmentTableGUI.php");
        $table_gui = new ilDigisemadminChangeAssignmentTableGUI($this, "showAssignment");
        $table_gui->resetOffset();
        $table_gui->resetFilter();
        $this->showContent();
    }

	/**
	 * Configure screen
	 */
	function edit() {
		$edit_gui_class = $this->record_type . "EditGUI";
		$form = new $edit_gui_class($this, $this->ar->find($_GET['ar_id']));
		$this->tpl->setContent($form->getHTML());
	}


	function add() {
		$edit_gui_class = $this->record_type . "EditGUI";
		$form = new $edit_gui_class($this, $this->ar);
		$this->tpl->setContent($form->getHTML());
	}


	public function create() {
		$edit_gui_class = $this->record_type . "EditGUI";
		$form = new $edit_gui_class($this, $this->ar);
		$this->save($form);
	}


	public function update() {
		$edit_gui_class = $this->record_type . "EditGUI";
		$form = new $edit_gui_class($this, $this->ar->find($_GET['ar_id']));
		$this->save($form);
	}


	public function save(arEditGUI $form) {
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->txt('record_created'), true);
			$this->ctrl->redirect($this, "index");
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}

	function view() {
		$display_gui_class = $this->record_type . "DisplayGUI";
		$display_gui = new $display_gui_class($this, $this->ar->find($_GET['ar_id']));
		$this->tpl->setContent($display_gui->getHtml());
	}


	function delete() {
		$delete_gui_class = $this->record_type . "DeleteGUI";
		$form = new $delete_gui_class($this, $this->ar->find($_GET['ar_id']));
		$this->tpl->setContent($form->getHTML());
	}


	function deleteItem() {
		$record = $this->ar->find($_GET['ar_id']);
		$record->delete();
		ilUtil::sendSuccess("object_deleted");
		$this->ctrl->redirect($this, "index");
	}


	/**
	 * @param string $lng_prefix
	 */
	public function setLngPrefix($lng_prefix) {
		$this->lng_prefix = $lng_prefix;
	}


	/**
	 * @return string
	 */
	public function getLngPrefix() {
		return $this->lng_prefix;
	}

    /**
     * @param $txt
     * @param bool $plugin_txt
     * @return string
     */
    public function txt($txt, $plugin_txt = true) {
		if ($this->getLngPrefix() != "" && $plugin_txt) {
			return $this->lng->txt($this->getLngPrefix() . "_" . $txt, $this->getLngPrefix());
		} else {
			return $this->lng->txt($txt);
		}
	}
}

?>