<?php

/**
 * Class ilMMSubItemTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemTableGUI extends ilTable2GUI {

	/**
	 * @var ilMMCustomProvider
	 */
	private $item_repository;
	/**
	 * @inheritDoc
	 */
	const IDENTIFIER = 'identifier';


	public function __construct(ilMMSubItemGUI $a_parent_obj, ilMMItemRepository $item_repository) {
		$this->setId(self::class);
		parent::__construct($a_parent_obj);
		$this->item_repository = $item_repository;
		$this->lng = $this->parent_obj->lng;
		$this->setData($this->resolveData());
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
		$this->addCommandButton(ilMMSubItemGUI::CMD_SAVE_TABLE, $this->lng->txt('button_save'));
		$this->initColumns();
		$this->setRowTemplate('tpl.sub_items.html', 'Services/MainMenu');
	}


	private function initColumns() {
		$this->addColumn($this->lng->txt('sub_parent'));
		$this->addColumn($this->lng->txt('sub_position'));
		$this->addColumn($this->lng->txt('sub_title'));
		$this->addColumn($this->lng->txt('sub_type'));
		$this->addColumn($this->lng->txt('sub_active'));
		$this->addColumn($this->lng->txt('sub_status'));
		$this->addColumn($this->lng->txt('sub_provider'));
		$this->addColumn($this->lng->txt('sub_actions'));
	}


	/**
	 * @inheritDoc
	 */
	protected function fillRow($a_set) {
		static $position;
		static $current_parent;
		$position++;
		global $DIC;

		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		$item_facade = $this->item_repository->repository()->getItemFacade($DIC->globalScreen()->identification()->fromSerializedIdentification($a_set['identification']));

		if (!$current_parent || $current_parent->getProviderIdentification() !== $item_facade->item()->getParent()) {
			$current_parent = $this->item_repository->getSingleItem($item_facade->item()->getParent());
			$this->tpl->setVariable("PARENT_TITLE", $current_parent->getTitle());
			$position = 1;
		}
		$this->tpl->setVariable('IDENTIFIER', self::IDENTIFIER);
		$this->tpl->setVariable('ID', $item_facade->getId());
		$this->tpl->setVariable('TITLE', $item_facade->getDefaultTitle());
		$this->tpl->setVariable('PARENT', $this->getSelect($item_facade)->render());
		$this->tpl->setVariable('STATUS', $item_facade->getStatus());
		if ($item_facade->isAvailable()) {
			$this->tpl->touchBlock('is_active');
		}
		if ($item_facade->item()->isAlwaysAvailable() || !$item_facade->item()->isAvailable()) {
			$this->tpl->touchBlock('is_active_blocked');
		}

		$this->tpl->setVariable('POSITION', $position);
		$this->tpl->setVariable('TYPE', $item_facade->getTypeForPresentation());
		$this->tpl->setVariable('PROVIDER', $item_facade->getProviderNameForPresentation());

		$this->ctrl->setParameterByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::IDENTIFIER, $a_set['identification']);

		$items[] = $factory->button()->shy($this->lng->txt(ilMMSubItemGUI::CMD_EDIT), $this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_EDIT));
		$items[] = $factory->button()->shy($this->lng->txt(ilMMSubItemGUI::CMD_TRANSLATE), $this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_TRANSLATE));
		if ($item_facade->isCustom()) {
			$items[] = $factory->button()->shy($this->lng->txt(ilMMSubItemGUI::CMD_DELETE), $this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_DELETE));
		}

		$this->tpl->setVariable('ACTIONS', $renderer->render($factory->dropdown()->standard($items)->withLabel($this->lng->txt('sub_actions'))));
	}


	/**
	 * @param ilMMItemFacadeInterface $child
	 *
	 * @return ilSelectInputGUI
	 */
	private function getSelect(ilMMItemFacadeInterface $child): ilSelectInputGUI {
		$s = new ilSelectInputGUI('', self::IDENTIFIER . "[{$child->getId()}][parent]");
		$s->setOptions($this->getPossibleParentsForFormAndTable());
		$s->setValue($child->getParentIdentificationString());

		return $s;
	}


	/**
	 * @return array
	 */
	public function getPossibleParentsForFormAndTable(): array {
		return $this->item_repository->getPossibleParentsForFormAndTable();
	}


	private function resolveData(): array {
		return $this->item_repository->getSubItemsForTable();
	}
}
