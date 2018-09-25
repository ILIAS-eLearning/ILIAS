<?php

/**
 * Class ilObjMainMenuGUI
 *
 * @ilCtrl_IsCalledBy ilObjMainMenuGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMainMenuGUI: ilPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuGUI extends ilObject2GUI {

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilLanguage
	 */
	public $lng;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilTree
	 */
	public $tree;
	/**
	 * ilObjWorkflowEngineGUI constructor.
	 */
	const TAB_MAIN = 'main';
	const SUBTAB_SLATES = 'main_slates';
	const SUBTAB_ENTRIES = 'main_entries';


	public function __construct() {
		global $DIC;

		$this->tabs = $DIC['ilTabs'];
		$this->lng = $DIC['lng'];
		$this->lng->loadLanguageModule('mme');
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl = $DIC['tpl'];
		$this->tree = $DIC['tree'];
		$this->rbacsystem = $DIC['rbacsystem'];

		parent::__construct((int)$_GET['ref_id']);
		$this->assignObject();
	}


	private function dispatchCommand($cmd) {
		switch ($cmd) {
			case self::SUBTAB_SLATES:
				$this->initTabs(self::TAB_MAIN, $cmd);
				return "CONTENT";
			case self::SUBTAB_ENTRIES:
				$this->initTabs(self::TAB_MAIN, $cmd);
				return "CONTENT";
		}

	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();

		if ($next_class == '') {
			$this->prepareAdminOutput();
			$this->tpl->setContent($this->dispatchCommand($this->ctrl->getCmd(self::SUBTAB_SLATES)));

			return;
		}

		switch ($next_class) {
			case 'ilpermissiongui':
				$this->prepareAdminOutput();
				$this->initTabs('permissions');
				$this->tabs->activateTab('perm_settings');
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			default:
				break;
		}
	}


	/**
	 * @return void
	 */
	private function prepareAdminOutput() {
		$this->tpl->getStandardTemplate();
		$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_mme.svg'));
		$this->tpl->setTitle($this->object->getPresentationTitle());
		$this->tpl->setDescription($this->object->getLongDescription());
		$this->initLocator();
	}


	/**
	 * @param string      $tab
	 * @param string|null $subtab
	 */
	private function initTabs(string $tab, string $subtab = null) {
		if ($this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
			$this->tabs->addTab(
				self::TAB_MAIN,
				$this->lng->txt(self::TAB_MAIN),
				$this->ctrl->getLinkTarget($this, self::TAB_MAIN)
			);
			switch ($tab) {
				case self::TAB_MAIN:
					$this->tabs->addSubTab(self::SUBTAB_SLATES, $this->lng->txt(self::SUBTAB_SLATES), $this->ctrl->getLinkTarget($this, self::SUBTAB_SLATES));
					$this->tabs->addSubTab(self::SUBTAB_ENTRIES, $this->lng->txt(self::SUBTAB_ENTRIES), $this->ctrl->getLinkTarget($this, self::SUBTAB_ENTRIES));
					break;
			}
			if ($subtab === null) {
				$subtab = self::SUBTAB_SLATES;
			}
			$this->tabs->activateSubTab($subtab);
		}
		if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs->addTab(
				'perm_settings',
				$this->lng->txt('perm_settings'),
				$this->ctrl->getLinkTargetByClass(array(self::class, ilPermissionGUI::class), 'perm')
			);
		}

		$this->tabs->setTabActive($tab);
	}


	/**
	 * @return void
	 */
	private function initLocator() {
		$path = $this->tree->getPathFull((int)$_GET["ref_id"]);
		foreach ((array)$path as $key => $row) {
			if ($row["title"] == "Main Menu") {
				$row["title"] = $this->lng->txt("obj_mme");
			}

			$this->ctrl->setParameter($this, "ref_id", $row["child"]);
			$this->locator->addItem(
				$row["title"],
				$this->ctrl->getLinkTarget($this, self::TAB_MAIN),
				ilFrameTargetInfo::_getFrame("MainContent"),
				$row["child"]
			);

			$this->ctrl->setParameter($this, "ref_id", $_GET["ref_id"]);
		}

		$this->tpl->setLocator();
	}


	/**
	 * @inheritDoc
	 */
	public function getType() {
		return null;
	}
}
