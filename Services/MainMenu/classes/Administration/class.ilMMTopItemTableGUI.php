<?php

/**
 * Class ilMMTopItemTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemTableGUI extends ilTable2GUI {

	/**
	 * @var ilMMCustomProvider
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
		$this->addColumn($this->lng->txt('topitem_position'), '', '30px');
		$this->addColumn($this->lng->txt('topitem_title'));
		// $this->addColumn($this->lng->txt('topitem_icon'));
		$this->addColumn($this->lng->txt('topitem_active'));
		// $this->addColumn($this->lng->txt('topitem_sticky'));
		// $this->addColumn($this->lng->txt('topitem_mobile'));
		$this->addColumn($this->lng->txt('topitem_subentries'));
		$this->addColumn($this->lng->txt('topitem_css_id'));
		$this->addColumn($this->lng->txt('topitem_provider'));
		$this->addColumn($this->lng->txt('topitem_actions'));
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
		$this->tpl->setVariable('TITLE', $item_facade->getDefaultTitle());
		$this->tpl->setVariable('SUBENTRIES', $item_facade->getAmountOfChildren());
		$this->tpl->setVariable('CSS_ID', "mm_" . $item_facade->identification()->getInternalIdentifier());
		$this->tpl->setVariable('POSITION', $position * 10);
		if ($item_facade->isAvailable()) {
			$this->tpl->touchBlock('is_active');
		}
		if ($item_facade->item()->isAlwaysAvailable()) {
			$this->tpl->touchBlock('is_active_blocked');
		}
		$this->tpl->setVariable('PROVIDER', $item_facade->getProviderNameForPresentation());

		$this->ctrl->setParameterByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::IDENTIFIER, $a_set['identification']);
		$this->ctrl->setParameterByClass(ilMMItemTranslationGUI::class, ilMMItemTranslationGUI::IDENTIFIER, $a_set['identification']);

		$items[] = $factory->button()->shy($this->lng->txt(ilMMTopItemGUI::CMD_EDIT), $this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_EDIT));
		$items[] = $factory->button()->shy($this->lng->txt(ilMMTopItemGUI::CMD_TRANSLATE), $this->ctrl->getLinkTargetByClass(ilMMItemTranslationGUI::class, ilMMItemTranslationGUI::CMD_DEFAULT));
		if ($item_facade->isCustom()) {
			$items[] = $factory->button()->shy($this->lng->txt(ilMMTopItemGUI::CMD_DELETE), $this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_CONFIRM_DELETE));
		}

		$this->tpl->setVariable('ACTIONS', $renderer->render($factory->dropdown()->standard($items)->withLabel($this->lng->txt('topitem_actions'))));
	}


	/**
	 * @return array
	 */
	private function resolveData(): array {
		return $this->item_repository->getTopItems();
	}
}
