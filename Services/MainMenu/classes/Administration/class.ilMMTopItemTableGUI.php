<?php

/**
 * Class ilMMTopItemTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemTableGUI extends ilTable2GUI {

	/**
	 * @var ilMMProvider
	 */
	private $item_repository;


	/**
	 * @inheritDoc
	 */
	public function __construct(ilMMTopItemGUI $a_parent_obj, ilMMItemRepository $item_repository) {
		$this->setId(self::class);
		parent::__construct($a_parent_obj);
		$this->item_repository = $item_repository;
		$this->lng = $this->parent_obj->lng;
		$this->setData($this->resolveData());
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
		$this->addCommandButton(ilMMTopItemGUI::CMD_SAVE_TABLE, $this->lng->txt('button_save'));
		$this->initColumns();
		$this->setRowTemplate('tpl.top_items.html', 'Services/MainMenu');
	}


	private function initColumns() {
		$this->addColumn($this->lng->txt('slate_position'), '', '30px');
		$this->addColumn($this->lng->txt('slate_title'));
		// $this->addColumn($this->lng->txt('slate_icon'));
		$this->addColumn($this->lng->txt('slate_active'));
		// $this->addColumn($this->lng->txt('slate_sticky'));
		// $this->addColumn($this->lng->txt('slate_mobile'));
		$this->addColumn($this->lng->txt('slate_subentries'));
		$this->addColumn($this->lng->txt('slate_provider'));
		$this->addColumn($this->lng->txt('slate_actions'));
	}


	/**
	 * @inheritDoc
	 */
	protected function fillRow($a_set) {
		static $position;
		$position++;
		global $DIC;
		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		$item_facade = $this->item_repository->repository()->getItemFacade($DIC->globalScreen()->identification()->fromSerializedIdentification($a_set['identification']));

		$this->tpl->setVariable('IDENTIFIER', ilMMTopItemGUI::IDENTIFIER);
		$this->tpl->setVariable('ID', $item_facade->getId());
		$this->tpl->setVariable('TITLE', $item_facade->getTitleForPresentation());
		$this->tpl->setVariable('SUBENTRIES', $item_facade->getAmountOfChildren());
		$this->tpl->setVariable('POSITION', $position);
		if ($a_set['active'] || $item_facade->getGSItem()->isAlwaysAvailable()) {
			$this->tpl->touchBlock('is_active');
		}
		if ($item_facade->getGSItem()->isAlwaysAvailable()) {
			$this->tpl->touchBlock('is_active_blocked');
		}
		$this->tpl->setVariable('PROVIDER', $item_facade->getProviderNameForPresentation());

		$this->ctrl->setParameterByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::IDENTIFIER, $a_set['identification']);

		$items[] = $factory->button()->shy($this->lng->txt('edit_top_item'), $this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_EDIT));
		$items[] = $factory->button()->shy($this->lng->txt('translate_slate'), $this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, 'translate'));
		if ($a_set['provider'] === "Custom") {
			$items[] = $factory->button()->shy($this->lng->txt('delete_slate'), '#');
		}

		$this->tpl->setVariable('ACTIONS', $renderer->render($factory->dropdown()->standard($items)->withLabel($this->lng->txt('slate_actions'))));
	}


	/**
	 * @return array
	 */
	private function resolveData(): array {
		return $this->item_repository->getTopItems();
	}
}
