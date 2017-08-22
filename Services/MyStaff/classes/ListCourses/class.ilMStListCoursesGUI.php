<?php
require_once("class.ilMStListCoursesTableGUI.php");
require_once("Services/Form/classes/class.ilRepositorySelectorInputGUI.php");

/**
 * GUI-Class Table ilMStListCoursesGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListCoursesGUI: ilMyStaffGUI
 * @ilCtrl_Calls ilMStListCoursesGUI:ilFormPropertyDispatchGUI
 */
class ilMStListCoursesGUI {

	/**
	 * @var  ilTable2GUI
	 */
	protected $table;
	protected $tpl;
	protected $ctrl;
	protected $pl;
	protected $toolbar;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	protected $access;


	function __construct() {
		global $tpl, $ilCtrl, $ilAccess, $lng, $ilToolbar, $ilTabs;
		/**
		 * @var ilTemplate      $tpl
		 * @var ilCtrl          $ilCtrl
		 * @var ilAccessHandler $ilAccess
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->tabs = $ilTabs;
		$this->lng = $lng;
	}


	protected function checkAccessOrFail() {
        return true;
		//todo
	}


	public function executeCommand() {
        $this->checkAccessOrFail();

		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();



		switch($next_class) {
            case 'ilformpropertydispatchgui':
                $table = new ilMStListCoursesTableGUI($this, 'index');
                $table->executeCommand();
                break;
			default:
				switch ($cmd) {
					case 'resetFilter':
					case 'applyFilter':
                    case 'index':
                        $this->$cmd();
						break;
					default:
						$this->index();
						break;
				}
                break;
		}
	}

	public function index() {
		$this->listUsers();
	}

	public function listUsers() {
		$this->tpl->setTitle($this->lng->txt('listUsers'));
		$this->table = new ilMStListCoursesTableGUI($this, 'index');
		$this->tpl->setContent($this->table->getHTML());
	}


	public function applyFilter() {
        $this->table = new ilMStListCoursesTableGUI($this, 'applyFilter');
        $this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	public function resetFilter() {
        $this->table = new ilMStListCoursesTableGUI($this, 'resetFilter');
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}

    public function getId() {
        $this->table = new ilMStListCoursesTableGUI($this, 'resetFilter');
        return $this->table->getId();
    }

	public function cancel() {
		$this->ctrl->redirect($this);
	}
}
