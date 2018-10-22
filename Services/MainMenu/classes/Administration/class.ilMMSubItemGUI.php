<?php

/**
 * Class ilMMTopItemGUI
 *
 * @ilCtrl_IsCalledBy ilMMSubItemGUI: ilObjMainMenuGUI
 * @ilCtrl_Calls      ilMMSubItemGUI: ilMMItemTranslationGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemGUI {

	const CMD_VIEW_SUB_ITEMS = 'subtab_subitems';
	const CMD_ADD = 'subitem_add';
	const CMD_CREATE = 'subitem_create';
	const CMD_DELETE = 'subitem_delete';
	const CMD_CONFIRM_DELETE = 'subitem_confirm_delete';
	const CMD_EDIT = 'subitem_edit';
	const CMD_TRANSLATE = 'subitem_translate';
	const CMD_UPDATE = 'subitem_update';
	const CMD_SAVE_TABLE = 'save_table';
	const IDENTIFIER = 'identifier';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
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
	const CMD_CANCEL = 'cancel';


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


	private function dispatchCommand($cmd) {
		global $DIC;
		switch ($cmd) {
			case self::CMD_ADD:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);

				return $this->add($DIC);
			case self::CMD_CREATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				$this->create($DIC);
				break;
			case self::CMD_EDIT:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);

				return $this->edit($DIC);
			case self::CMD_UPDATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true);
				$this->update($DIC);
				break;
			case self::CMD_APPLY_FILTER:
				$this->applyFilter();
				break;
			case self::CMD_RESET_FILTER :
				$this->resetFilter();
				break;
			case self::CMD_VIEW_SUB_ITEMS:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

				return $this->index();
			case self::CMD_SAVE_TABLE:
				$this->saveTable();
				break;
			case self::CMD_CONFIRM_DELETE:
				return $this->confirmDelete();
				break;
			case self::CMD_DELETE:
				$this->delete();
				break;
			case self::CMD_CANCEL:
				$this->cancel();
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
			$item->setParent((string)$data['parent']);
			$this->repository->updateItem($item);
		}
		$this->cancel();
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
			case strtolower(ilMMItemTranslationGUI::class):
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_SUB_ITEMS, true);
				$g = new ilMMItemTranslationGUI($this->getMMItemFromRequest(), $this->repository);
				$this->ctrl->forwardCommand($g);
				break;
			default:
				break;
		}
	}


	/**
	 * @param $DIC
	 *
	 * @return string
	 * @throws Throwable
	 */
	private function add($DIC): string {
		$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);

		return $f->getHTML();
	}


	/**
	 * @param $DIC
	 *
	 * @throws Throwable
	 */
	private function create($DIC) {
		$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);
		$f->save();

		$this->cancel();
	}


	/**
	 * @param $DIC
	 *
	 * @return string
	 * @throws Throwable
	 */
	private function edit($DIC): string {
		$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);

		return $f->getHTML();
	}


	/**
	 * @param $DIC
	 *
	 * @throws Throwable
	 */
	private function update($DIC) {
		$f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);
		$f->save();

		$this->cancel();
	}


	private function applyFilter() {
		$table = new ilMMSubItemTableGUI($this, $this->repository);
		$table->writeFilterToSession();

		$this->cancel();
	}


	private function resetFilter() {
		$table = new ilMMSubItemTableGUI($this, $this->repository);
		$table->resetFilter();
		$table->resetOffset();

		$this->cancel();
	}


	/**
	 * @return string
	 */
	private function index(): string {
		// ADD NEW
		$b = ilLinkButton::getInstance();
		$b->setUrl($this->ctrl->getLinkTarget($this, ilMMSubItemGUI::CMD_ADD));
		$b->setCaption($this->lng->txt(ilMMSubItemGUI::CMD_ADD), false);

		$this->toolbar->addButtonInstance($b);

		// TABLE
		$table = new ilMMSubItemTableGUI($this, $this->repository);

		return $table->getHTML();
	}


	private function delete() {
		$item = $this->getMMItemFromRequest();
		if ($item->isCustom()) {
			$this->repository->deleteItem($item);
		}

		ilUtil::sendSuccess($this->lng->txt("msg_subitem_deleted"), true);
		$this->cancel();
	}


	private function cancel() {
		$this->ctrl->redirectByClass(self::class, self::CMD_VIEW_SUB_ITEMS);
	}


	/**
	 * @return string
	 * @throws Throwable
	 */
	private function confirmDelete(): string {
		$this->ctrl->saveParameterByClass(self::class, self::IDENTIFIER);
		$i = $this->getMMItemFromRequest();
		$c = new ilConfirmationGUI();
		$c->addItem(self::IDENTIFIER, $i->getId(), $i->getDefaultTitle());
		$c->setFormAction($this->ctrl->getFormActionByClass(self::class));
		$c->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
		$c->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
		$c->setHeaderText($this->lng->txt(self::CMD_CONFIRM_DELETE));

		return $c->getHTML();
	}
}
