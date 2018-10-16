<?php

/**
 * Class ilMMTopItemGUI
 *
 * @ilCtrl_IsCalledBy ilMMSubItemGUI: ilObjMainMenuGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemGUI {

	const CMD_ADD_SUB_ITEM = 'add_sub_item';
	const CMD_VIEW_SUB_ITEMS = 'main_Sub_items';
	const CMD_EDIT = 'edit_sub_item';
	const CMD_TRANSLATE = 'translate_sub_item';
	const CMD_UPDATE = 'update_sub_item';
	const CMD_SAVE_TABLE = 'save_table';
	const IDENTIFIER = 'identifier';
	const ADD_SUBITEM = 'add_subitem';
	const SUBTAB_ENTRIES = 'main_entries';
	/**
	 * @var ilMMItemRepository
	 */
	private $repository;
	/**
	 * @var ilToolbarGUI
	 */
	private $toolbar;
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
	/**
	 * ilMMTopItemGUI constructor.
	 *
	 * @param ilMMTabHandling $tab_handling
	 */
	const CMD_TRANSLATE_SUB_ITEM = 'translate_top_item';


	public function __construct(ilMMTabHandling $tab_handling) {
		global $DIC;

		$this->repository = new ilMMItemRepository($DIC->globalScreen()->storage());
		$this->tab_handling = $tab_handling;
		$this->tabs = $DIC['ilTabs'];
		$this->lng = new FakeLanguage('en');
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl = $DIC['tpl'];
		$this->tree = $DIC['tree'];
		$this->rbacsystem = $DIC['rbacsystem'];
		$this->toolbar = $DIC['ilToolbar'];
	}


	/**
	 * @return ilMMItemFacade
	 */
	private function getMMItemFromRequest(): ilMMItemFacade {
		global $DIC;

		return $this->repository->getItemFacadeForIdentificationString($DIC->http()->request()->getQueryParams()[self::IDENTIFIER]);
	}


	private function dispatchCommand($cmd) {
		global $DIC;
		switch ($cmd) {
			case ilMMSubItemGUI::ADD_SUBITEM:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::SUBTAB_ENTRIES, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng);

				return $f->getHTML();
			case ilMMSubItemGUI::SUBTAB_ENTRIES:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

				// ADD NEW
				$b = ilLinkButton::getInstance();
				$b->setUrl($this->ctrl->getLinkTarget($this, ilMMSubItemGUI::ADD_SUBITEM));
				$b->setCaption($this->lng->txt('add_subentry'), false);

				$this->toolbar->addButtonInstance($b);

				// TABLE
				$table = new ilMMSubItemTableGUI($this, $this->repository);

				return $table->getHTML();
		}

		return "";
	}


	private function saveTable() {
		global $DIC;
		$r = $DIC->http()->request()->getParsedBody();
		foreach ($r[self::IDENTIFIER] as $identification_string => $data) {
			$item = $this->repository->getItemFacadeForIdentificationString($identification_string);
			$item->setPosition((int)$data['position']);
			$item->setActiveStatus((bool)$data['active']);
			$this->repository->updateItem($item);
		}
		$this->ctrl->redirect($this);
	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();

		if ($next_class == '') {
			$this->tpl->setContent($this->dispatchCommand($this->ctrl->getCmd(self::CMD_VIEW_SUB_ITEMS)));

			return;
		}

		switch ($next_class) {
			default:
				break;
		}
	}
}
