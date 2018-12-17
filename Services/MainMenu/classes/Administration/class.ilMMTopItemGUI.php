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

	use ilMMHasher;
	const CMD_VIEW_TOP_ITEMS = 'subtab_topitems';
	const CMD_ADD = 'topitem_add';
	const CMD_RESTORE = 'restore';
	const CMD_CREATE = 'topitem_create';
	const CMD_EDIT = 'topitem_edit';
	const CMD_DELETE = 'topitem_delete';
	const CMD_CONFIRM_DELETE = 'topitem_confirm_delete';
	const CMD_TRANSLATE = 'topitem_translate';
	const CMD_UPDATE = 'topitem_update';
	const CMD_SAVE_TABLE = 'save_table';
	const CMD_CANCEL = 'cancel';
	const IDENTIFIER = 'identifier';
	const CMD_RENDER_INTERRUPTIVE = 'render_interruptive_modal';
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

		if (isset($DIC->http()->request()->getParsedBody()['interruptive_items'])) {
			$string = $DIC->http()->request()->getParsedBody()['interruptive_items'][0];
			$identification = $this->unhash($string);
		} else {
			$identification = $this->unhash($DIC->http()->request()->getQueryParams()[self::IDENTIFIER]);
		}

		return $this->repository->getItemFacadeForIdentificationString($identification);
	}


	private function dispatchCommand($cmd) {
		global $DIC;
		switch ($cmd) {
			case self::CMD_VIEW_TOP_ITEMS:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

				return $this->index($DIC);
			case self::CMD_ADD:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);

				return $this->add($DIC);
			case self::CMD_CREATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				$this->create($DIC);
				break;
			case self::CMD_EDIT:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);

				return $this->edit($DIC);
				break;
			case self::CMD_UPDATE:
				$this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
				$this->update($DIC);
				break;
			case self::CMD_SAVE_TABLE:
				$this->saveTable();

				break;
			case self::CMD_CONFIRM_DELETE:
				return $this->confirmDelete();
			case self::CMD_DELETE:
				$this->delete();
				break;
			case self::CMD_CANCEL:
				$this->cancel();
				break;
			case self::CMD_RESTORE:
				$this->restore();
				break;
			case self::CMD_RENDER_INTERRUPTIVE:
				$this->renderInterruptiveModal();
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
		$this->cancel();
	}


	public function executeCommand() {
		global $DIC;
		$next_class = $this->ctrl->getNextClass();

		if ($next_class == '') {
			$cmd = $this->ctrl->getCmd(self::CMD_VIEW_TOP_ITEMS);

			if (isset($DIC->http()->request()->getParsedBody()['interruptive_items'])) {
				$cmd = self::CMD_DELETE;
			}
			$this->tpl->setContent($this->dispatchCommand($cmd));

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


	/**
	 * @param $DIC
	 *
	 * @return string
	 */
	private function index(\ILIAS\DI\Container $DIC): string {
		// ADD NEW
		$b = ilLinkButton::getInstance();
		$b->setCaption($this->lng->txt(self::CMD_ADD), false);
		$b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD));
		$this->toolbar->addButtonInstance($b);

		// RESTORE
		$b = ilLinkButton::getInstance();
		$b->setCaption($this->lng->txt(self::CMD_RESTORE), false);
		$b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_RESTORE));
		// $this->toolbar->addButtonInstance($b);

		// TABLE
		$table = new ilMMTopItemTableGUI($this, new ilMMItemRepository($DIC->globalScreen()->storage()));

		return $table->getHTML();
	}


	private function cancel() {
		$this->ctrl->redirectByClass(self::class, self::CMD_VIEW_TOP_ITEMS);
	}


	/**
	 * @param $DIC
	 *
	 * @return string
	 * @throws Throwable
	 */
	private function add(\ILIAS\DI\Container $DIC): string {
		$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->repository->getItemFacade(), $this->repository);

		return $f->getHTML();
	}


	/**
	 * @param $DIC
	 *
	 * @throws Throwable
	 */
	private function create(\ILIAS\DI\Container $DIC) {
		$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->repository->getItemFacade(), $this->repository);
		$f->save();

		$this->cancel();
	}


	/**
	 * @param $DIC
	 *
	 * @return string
	 * @throws Throwable
	 */
	private function edit(\ILIAS\DI\Container $DIC): string {
		$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->getMMItemFromRequest(), $this->repository);

		return $f->getHTML();
	}


	/**
	 * @param $DIC
	 *
	 * @throws Throwable
	 */
	private function update(\ILIAS\DI\Container $DIC) {
		$f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->getMMItemFromRequest(), $this->repository);
		$f->save();

		$this->cancel();
	}


	private function delete() {
		$item = $this->getMMItemFromRequest();
		if ($item->isCustom()) {
			$this->repository->deleteItem($item);
		}
		ilUtil::sendSuccess($this->lng->txt("msg_topitem_deleted"), true);
		$this->cancel();
	}


	/**
	 * @return string
	 * @throws Throwable
	 */
	private function confirmDelete(): string {
		$this->ctrl->saveParameterByClass(self::class, self::IDENTIFIER);
		$i = $this->getMMItemFromRequest();
		$c = new ilConfirmationGUI();
		$c->addItem(self::IDENTIFIER, $this->hash($i->getId()), $i->getDefaultTitle());
		$c->setFormAction($this->ctrl->getFormActionByClass(self::class));
		$c->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
		$c->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
		$c->setHeaderText($this->lng->txt(self::CMD_CONFIRM_DELETE));

		return $c->getHTML();
	}


	private function restore() {
		ilGSProviderStorage::flushDB();
		ilGSIdentificationStorage::flushDB();
		ilMMItemStorage::flushDB();
		ilMMCustomItemStorage::flushDB();
		ilMMItemTranslationStorage::flushDB();
		ilMMTypeActionStorage::flushDB();

		$r = function ($path, $xml_name) {
			foreach (new DirectoryIterator($path) as $fileInfo) {
				$filename = $fileInfo->getPathname() . $xml_name;
				if ($fileInfo->isDir() && !$fileInfo->isDot() && file_exists($filename)) {
					$xml = simplexml_load_file($filename);
					if (isset($xml->gsproviders)) {
						foreach ($xml->gsproviders as $item) {
							if (isset($item->gsprovider)) {
								foreach ($item->gsprovider as $provider) {
									$attributes = $provider->attributes();
									if ($attributes->purpose == 'mainmenu') {
										$classname = $attributes->class_name[0];
										ilGSProviderStorage::registerIdentifications($classname, 'mainmenu');
									}
								}
							}
						}
					}
				}
			}
		};
		$r("./Services", "/service.xml");
		$r("./Modules", "/module.xml");

		ilGlobalCache::flushAll();

		$this->cancel();
	}


	public function renderInterruptiveModal() {
		global $DIC;
		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();

		$form_action = $this->ctrl->getFormActionByClass(self::class, self::CMD_DELETE);
		$delete_modal = $f->modal()->interruptive(
			$this->lng->txt("delete"),
			$this->lng->txt(self::CMD_CONFIRM_DELETE),
			$form_action
		);

		echo $r->render([$delete_modal]);
		exit;
	}
}
