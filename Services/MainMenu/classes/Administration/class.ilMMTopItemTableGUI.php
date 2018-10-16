<?php

/**
 * Class ilMMTopItemTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemTableGUI extends ilTable2GUI {

	/**
	 * @inheritDoc
	 */
	public function __construct(ilMMTopItemGUI $a_parent_obj, string $a_parent_cmd = "", string $a_template_context = "") {
		$this->setId(self::class);
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		$this->lng = $this->parent_obj->lng;
		$this->setData($this->resolveData());
		$this->addCommandButton('#', $this->lng->txt('button_save'));
		$this->initColumns();
		$this->setRowTemplate('tpl.slate_entries.html', 'Services/MainMenu');
	}


	private function initColumns() {
		$this->addColumn($this->lng->txt('slate_position'));
		$this->addColumn($this->lng->txt('slate_title'));
		// $this->addColumn($this->lng->txt('slate_icon'));
		$this->addColumn($this->lng->txt('slate_active'));
		$this->addColumn($this->lng->txt('slate_sticky'));
		// $this->addColumn($this->lng->txt('slate_mobile'));
		$this->addColumn($this->lng->txt('slate_subentries'));
		$this->addColumn($this->lng->txt('slate_provider'));
		$this->addColumn($this->lng->txt('slate_actions'));
	}


	/**
	 * @inheritDoc
	 */
	protected function fillRow($a_set) {
		echo '<pre>' . print_r($a_set, 1) . '</pre>';
		global $DIC;
		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('SUBENTRIES', $a_set['entries']);
		$this->tpl->setVariable('POSITION', $a_set['position']);
		$this->tpl->setVariable('ACTIVE', $a_set['active']);
		$this->tpl->setVariable('STICKY', $a_set['sticky']);
		// $this->tpl->setVariable('ICON', $renderer->render($factory->icon()->standard('copa', '')->withDisabled(true)));
		$this->tpl->setVariable('PROVIDER', $a_set['identification']);

		$items[] = $factory->button()->shy($this->lng->txt('edit_slate'), '#');
		$items[] = $factory->button()->shy($this->lng->txt('translate_slate'), $this->ctrl->getLinkTarget($this->parent_obj, 'translate'));
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
		$c = new ilMMTopItemRepository($DIC->globalScreen()->storage());

		return $c->getTopItems();
	}
}
