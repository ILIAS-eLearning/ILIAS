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
	private $collector;


	/**
	 * @inheritDoc
	 */
	public function __construct(ilMMTopItemGUI $a_parent_obj, ilMainMenuCollector $collector) {
		$this->setId(self::class);
		parent::__construct($a_parent_obj);
		$this->collector = $collector;
		$this->lng = $this->parent_obj->lng;
		$this->setData($this->resolveData());
		$this->addCommandButton('#', $this->lng->txt('button_save'));
		$this->initColumns();
		$this->setRowTemplate('tpl.top_items.html', 'Services/MainMenu');
	}


	private function initColumns() {
		$this->addColumn($this->lng->txt('slate_position'));
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
		global $DIC;
		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();
		/**
		 * @var $gs_item \ILIAS\GlobalScreen\MainMenu\isParent
		 */
		$item_facade = $this->collector->repository()->getItemFacade($DIC->globalScreen()->identification()->fromSerializedIdentification($a_set['identification']));

		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('SUBENTRIES', count($gs_item->getChildren()));
		$this->tpl->setVariable('POSITION', $a_set['position']);
		if ($a_set['active']) {
			$this->tpl->touchBlock('is_active');
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
		global $DIC;
		$c = new ilMMItemRepository($DIC->globalScreen()->storage());

		return $c->getTopItems();
	}
}
