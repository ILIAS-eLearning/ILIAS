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
		if (($a_set['active'] && $item_facade->item()->isAvailable()) || $item_facade->item()->isAlwaysAvailable()) {
			$this->tpl->touchBlock('is_active');
		}
		if ($item_facade->item()->isAlwaysAvailable() || !$item_facade->item()->isAvailable()) {
			$this->tpl->touchBlock('is_active_blocked');
		}

		$this->tpl->setVariable('POSITION', $position);
		$this->tpl->setVariable('TYPE', $item_facade->getTypeForPresentation());
		$this->tpl->setVariable('PROVIDER', $item_facade->getProviderNameForPresentation());

		$items[] = $factory->button()->shy($this->lng->txt('edit_subentry'), '#');
		$items[] = $factory->button()->shy($this->lng->txt('translate_subentry'), $this->ctrl->getLinkTarget($this->parent_obj, 'translate'));
		if ($a_set['provider'] === "Custom") {
			$items[] = $factory->button()->shy($this->lng->txt('delete_slate'), '#');
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
		$s->setOptions($this->getPossibleParent());
		$s->setValue($child->getParentIdentificationString());

		return $s;
	}


	/**
	 * @return array
	 */
	private function getPossibleParent(): array {
		static $parents;
		if (is_null($parents)) {
			global $DIC;
			$parents = [];
			foreach ($this->item_repository->getTopItems() as $top_item_identification => $data) {
				$identification = $DIC->globalScreen()->identification()->fromSerializedIdentification($top_item_identification);
				$item = $this->item_repository->getSingleItem($identification);
				if ($item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem) {
					$parents[$top_item_identification] = $this->item_repository->getItemFacade($identification)
						->getDefaultTitle();
				}
			}
		}

		return $parents;
	}


	private function resolveData(): array {
		return $this->item_repository->getSubItems();
	}
}
