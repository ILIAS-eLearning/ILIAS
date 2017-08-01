<?php
require_once("class.ilMStShowUserCoursesTableGUI.php");

/**
 * GUI-Class Table ilMStShowUserGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStShowUserGUI: ilMyStaffGUI
 */
class ilMStShowUserGUI {

	/**
	 * @var  ilTable2GUI
	 */
	protected $table;
	protected $tpl;
	protected $ctrl;
	protected $toolbar;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	protected $access;

    protected $usr_id;


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

		$this->usr_id = $_GET['usr_id'];
	}


	protected function checkAccessOrFail() {
	    //TODO

        if(!$this->usr_id) {
            exit;
            return false;
        }

        return true;

	}


	public function executeCommand() {
        $this->checkAccessOrFail();

		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

		switch($next_class) {
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
		}
	}

	public function index() {
		$this->listUsers();
	}

	public function listUsers() {
		$this->tpl->setTitle($this->lng->txt('listUsers'));
		$this->table = new ilMStShowUserCoursesTableGUI($this, 'index');

        $pub_profile = new ilPublicUserProfileGUI($this->usr_id);

		//TODO
		$this->tpl->setContent('<div style="float:left; width: 60%">'.$this->table->getHTML().'</div><div style="float:left; width: 40%">'.$pub_profile->getEmbeddable().'</div>');
	}


	public function applyFilter() {
        $this->table = new ilMStShowUserCoursesTableGUI($this, 'applyFilter');
        $this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	public function resetFilter() {
        $this->table = new ilMStShowUserCoursesTableGUI($this, 'resetFilter');
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}

	public function cancel() {
		$this->ctrl->redirect($this);
	}
}
