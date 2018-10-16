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
	 * @var ilMMTabHandling
	 */
	private $tab_handling;
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
	const TAB_PERMISSIONS = 'perm_settings';
	const TAB_MAIN = 'main';
	const SUBTAB_ENTRIES = 'main_entries';
	const ADD_SUBITEM = 'add_subitem';


	/**
	 * ilObjMainMenuGUI constructor.
	 */
	public function __construct() {
		global $DIC;

		$ref_id = (int)$_GET['ref_id'];
		parent::__construct($ref_id);

		$this->tabs = $DIC['ilTabs'];
		$this->lng = new FakeLanguage('en');
		$this->lng->loadLanguageModule('mme');
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl = $DIC['tpl'];
		$this->tree = $DIC['tree'];
		$this->rbacsystem = $DIC['rbacsystem'];
		$this->tab_handling = new ilMMTabHandling($ref_id);

		$this->assignObject();
	}


	private function dispatchCommand($cmd) {
		if ($cmd === self::TAB_MAIN || $cmd === 'view') {
			$cmd = self::SUBTAB_ENTRIES;
		}
		switch ($cmd) {
			case self::SUBTAB_ENTRIES:
				$this->tab_handling->initTabs(self::TAB_MAIN, $cmd);

				// ADD NEW
				$b = ilLinkButton::getInstance();
				$b->setUrl($this->ctrl->getLinkTarget($this, self::ADD_SUBITEM));
				$b->setCaption($this->lng->txt('add_subentry'), false);

				$this->toolbar->addButtonInstance($b);

				// TABLE
				$table = new ilMMSubItemTableGUI($this);

				return $table->getHTML();
			case 'translate':
				$this->tab_handling->initTabs(self::TAB_MAIN, $cmd, true);
				// $this->tabs->setBackTarget("Back", $this->ctrl->getLinkTarget($this, self::SUBTAB_ENTRIES));
				$t = new ilTemplate("tpl.dummy_translate.html", false, false, 'Services/MainMenu');

				return $t->get();

			case self::ADD_SUBITEM:
				$this->tab_handling->initTabs(self::TAB_MAIN, self::SUBTAB_ENTRIES, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng);

				return $f->getHTML();
		}
	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();

		if ($next_class == '') {
			$this->ctrl->redirectByClass(ilMMTopItemGUI::class);

			return;
		}

		switch ($next_class) {
			case strtolower(ilPermissionGUI::class):
				$this->prepareAdminOutput();
				$this->tab_handling->initTabs(self::TAB_PERMISSIONS);
				$this->tabs->activateTab(self::TAB_PERMISSIONS);
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			case strtolower(ilMMTopItemGUI::class):
				$this->prepareAdminOutput();
				// $this->tab_handling->initTabs(self::TAB_MAIN, self::SUBTAB_SLATES);
				$g = new ilMMTopItemGUI($this->tab_handling);
				$this->ctrl->forwardCommand($g);
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
