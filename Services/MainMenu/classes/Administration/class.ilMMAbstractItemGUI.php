<?php

/**
 * Class ilMMAbstractItemGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMAbstractItemGUI {

	const IDENTIFIER = 'identifier';
	use ilMMHasher;
	/**
	 * @var ilMMItemRepository
	 */
	protected $repository;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilMMTabHandling
	 */
	protected $tab_handling;
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
	 * ilMMAbstractItemGUI constructor.
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
	 * @param string $standard
	 *
	 * @return string
	 */
	protected function determineCommand(string $standard, string $delete): string {
		global $DIC;
		$cmd = $this->ctrl->getCmd();
		if ($cmd !== '') {
			return $cmd;
		}

		$r = $DIC->http()->request();
		$post = $r->getParsedBody();

		if ($cmd == "" && isset($post['interruptive_items'])) {
			$cmd = $delete;
		} else {
			$cmd = $standard;
		}

		return $cmd;
	}


	/**
	 * @return ilMMItemFacadeInterface
	 * @throws Throwable
	 */
	protected function getMMItemFromRequest(): ilMMItemFacadeInterface {
		global $DIC;

		$r = $DIC->http()->request();
		$get = $r->getQueryParams();
		$post = $r->getParsedBody();

		if (!isset($post['cmd']) && isset($post['interruptive_items'])) {
			$string = $post['interruptive_items'][0];
			$identification = $this->unhash($string);
		} else {

			$identification = $this->unhash($get[self::IDENTIFIER]);
		}

		return $this->repository->getItemFacadeForIdentificationString($identification);
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
		//->withAffectedItems(
		//[$f->modal()->interruptiveItem($ilBiblFieldFilter->getId(), $this->facade->translationFactory()->translate($this->facade->fieldFactory()->findById($ilBiblFieldFilter->getFieldId())))]
		//);

		echo $r->render([$delete_modal]);
		exit;
	}
}
