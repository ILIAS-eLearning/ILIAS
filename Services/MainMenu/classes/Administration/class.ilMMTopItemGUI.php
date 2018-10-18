<?php

/**
 * Class ilMMTopItemGUI
 *
 * @ilCtrl_IsCalledBy ilMMTopItemGUI: ilObjMainMenuGUI
 * @ilCtrl_Calls      ilMMTopItemGUI: ilMMItemTranslationGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemGUI {

	const CMD_VIEW_TOP_ITEMS = 'subtab_topitems';
	const CMD_ADD = 'topitem_add';
	const CMD_CREATE = 'topitem_create';
	const CMD_EDIT = 'topitem_edit';
	const CMD_DELETE = 'topitem_delete';
	const CMD_TRANSLATE = 'topitem_translate';
	const CMD_UPDATE = 'topitem_update';
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
		$this->lng = $DIC->language();
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl = $DIC['tpl'];
		$this->tree = $DIC['tree'];
		$this->rbacsystem = $DIC['rbacsystem'];
		$this->toolbar = $DIC['ilToolbar'];
	}


	/**
	 * @return ilMMItemFacadeInterface
	 * @throws Throwable
	 */
	private function getMMItemFromRequest(): ilMMItemFacadeInterface {
		global $DIC;

		return $this->repository->getItemFacadeForIdentificationString($DIC->http()->request()->getQueryParams()[self::IDENTIFIER]);
	}


	private function dispatchCommand($cmd) {
		global $DIC;
		switch ($cmd) {
			case self::CMD_VIEW_TOP_ITEMS:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

				// ADD NEW
				$b = ilLinkButton::getInstance();
				$b->setCaption($this->lng->txt(self::CMD_ADD), false);
				$b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD));
				$this->toolbar->addButtonInstance($b);

				// TABLE
				$table = new ilMMTopItemTableGUI($this, new ilMMItemRepository($DIC->globalScreen()->storage()));

				return $table->getHTML();
			case self::CMD_ADD:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				global $DIC;
				$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);

				return $f->getHTML();
			case self::CMD_CREATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				global $DIC;
				$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);
				$f->save();

				$this->ctrl->redirect($this);
				break;
			case self::CMD_EDIT:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				global $DIC;
				$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);

				return $f->getHTML();
				break;
			case self::CMD_UPDATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				global $DIC;
				$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);
				$f->save();

				$this->ctrl->redirect($this);
				break;
			case self::CMD_SAVE_TABLE:
				$this->saveTable();

				return "";
			case self::CMD_DELETE:
				$item = $this->getMMItemFromRequest();
				if ($item->isCustom()) {
					$this->repository->deleteItem($item);
				}
				$this->ctrl->redirect($this);
				break;
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
			$this->tpl->setContent($this->dispatchCommand($this->ctrl->getCmd(self::CMD_VIEW_TOP_ITEMS)));

			return;
		}

		switch ($next_class) {
			case strtolower(ilMMItemTranslationGUI::class):
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				$g = new ilMMItemTranslationGUI($this->getMMItemFromRequest(), $this->repository);
				$this->ctrl->forwardCommand($g);
				break;
			default:
				break;
		}
	}
}
