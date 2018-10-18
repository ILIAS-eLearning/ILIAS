<?php

/**
 * Class ilMMTopItemGUI
 *
 * @ilCtrl_IsCalledBy ilMMSubItemGUI: ilObjMainMenuGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemGUI {

	const CMD_VIEW_SUB_ITEMS = 'subtab_subitems';
	const CMD_ADD = 'subitem_add';
	const CMD_CREATE = 'subitem_create';
	const CMD_DELETE = 'subitem_delete';
	const CMD_EDIT = 'subitem_edit';
	const CMD_TRANSLATE = 'subitem_translate';
	const CMD_UPDATE = 'subitem_update';
	const CMD_SAVE_TABLE = 'save_table';
	const IDENTIFIER = 'identifier';
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


	private function dispatchCommand($cmd) {
		switch ($cmd) {
			case self::CMD_ADD:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);

				return $f->getHTML();
			case self::CMD_CREATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);
				$f->save();
				$this->ctrl->redirect($this);
				break;
			case self::CMD_EDIT:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);

				return $f->getHTML();
			case self::CMD_UPDATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				global $DIC;
				$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);
				$f->save();
				$this->ctrl->redirect($this);
				break;
			case self::CMD_VIEW_SUB_ITEMS:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

				// ADD NEW
				$b = ilLinkButton::getInstance();
				$b->setUrl($this->ctrl->getLinkTarget($this, ilMMSubItemGUI::CMD_ADD));
				$b->setCaption($this->lng->txt(ilMMSubItemGUI::CMD_ADD), false);

				$this->toolbar->addButtonInstance($b);

				// TABLE
				$table = new ilMMSubItemTableGUI($this, $this->repository);

				return $table->getHTML();
			case self::CMD_SAVE_TABLE:
				$this->saveTable();

				return "";
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
			$item->setParent((string)$data['parent']);
			$this->repository->updateItem($item);
		}
		$this->ctrl->redirect($this, self::CMD_VIEW_SUB_ITEMS);
	}


	/**
	 * @return ilMMItemFacadeInterface
	 * @throws Throwable
	 */
	private function getMMItemFromRequest(): ilMMItemFacadeInterface {
		global $DIC;

		return $this->repository->getItemFacadeForIdentificationString($DIC->http()->request()->getQueryParams()[self::IDENTIFIER]);
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
