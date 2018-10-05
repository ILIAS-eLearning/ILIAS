<?php

/**
 * Class ilMMSubentriesTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubentriesTableGUI extends ilTable2GUI {

	/**
	 * @inheritDoc
	 */
	public function __construct(ilObjMainMenuGUI $a_parent_obj, string $a_parent_cmd = "", string $a_template_context = "") {
		$this->setId(self::class);
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		$this->lng = $this->parent_obj->lng;
		$this->initColumns();
		$this->addCommandButton('#', $this->lng->txt('button_save'));
		$this->setData($this->getFakeData());
		$this->setRowTemplate('tpl.sub_entries.html', 'Services/MainMenu');
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
		global $DIC;
		static $position;
		$position++;
		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('PARENT', $this->getSelect($a_set['parent'])->render());
		$this->tpl->setVariable('STATUS', $a_set['status']);
		if ($a_set['status'] !== 'Available') {
			$this->tpl->touchBlock('NONAV');
		} else {
			$this->tpl->touchBlock('CHECKED');
		}

		$this->tpl->setVariable('POSITION', $position * 10);
		$this->tpl->setVariable('TYPE', $a_set["type"]);
		$this->tpl->setVariable('PROVIDER', $a_set['provider']);

		$items[] = $factory->button()->shy($this->lng->txt('edit_subentry'), '#');
		$items[] = $factory->button()->shy($this->lng->txt('translate_subentry'), $this->ctrl->getLinkTarget($this->parent_obj, 'translate'));
		if ($a_set['provider'] === "Custom") {
			$items[] = $factory->button()->shy($this->lng->txt('delete_slate'), '#');
		}

		$this->tpl->setVariable('ACTIONS', $renderer->render($factory->dropdown()->standard($items)->withLabel($this->lng->txt('sub_actions'))));
	}


	private function getSelect($selected) {
		$s = new ilSelectInputGUI();
		$s->setOptions($this->getPossibleParent());
		$s->setValue($selected);

		return $s;
	}


	/**
	 * @return array
	 */
	private function getPossibleParent(): array {
		return [0 => 'Repository', 1 => 'Personal Workspace', 2 => 'Organisation'];
	}


	/**
	 * @return array
	 */
	private function getFakeData(): array {
		return array(
			0  =>
				array(
					'id'       => 8,
					'parent'   => 0,
					'position' => 90,
					'title'    => 'Last Visited',
					'provider' => 'Services/Repository',
					'status'   => 'Available',
					'type'     => 'Complex',
				),
			1  =>
				array(
					'id'       => 16,
					'parent'   => 0,
					'position' => 170,
					'title'    => 'Master Course',
					'provider' => 'Custom',
					'status'   => 'Available',
					'type'     => 'Link',
				),
			2  =>
				array(
					'id'       => 14,
					'parent'   => 0,
					'position' => 150,
					'title'    => 'Slave Course for Dummies',
					'provider' => 'Custom',
					'status'   => 'Available',
					'type'     => 'Link',
				),
			3  =>
				array(
					'id'       => 0,
					'parent'   => 1,
					'position' => 10,
					'title'    => 'My Badges',
					'provider' => 'Services/Badges',
					'status'   => 'Service is currently disabled. <a href="#">Enable</a>',
					'type'     => 'Link',
				),
			4  =>
				array(
					'id'       => 18,
					'parent'   => 1,
					'position' => 190,
					'title'    => 'Learning Progress',
					'provider' => 'Services/Tracking',
					'status'   => 'Available',
					'type'     => 'LinkList',
				),
			5  =>
				array(
					'id'       => 13,
					'parent'   => 1,
					'position' => 140,
					'title'    => 'Learning Progress Meter',
					'provider' => 'Plugin LPM',
					'status'   => 'Available',
					'type'     => 'Plugin',
				),
			6  =>
				array(
					'id'       => 12,
					'parent'   => 1,
					'position' => 130,
					'title'    => 'Skills',
					'provider' => 'Services/Skills',
					'status'   => 'Available',
					'type'     => 'Link',
				),
			12 =>
				array(
					'id'       => 9,
					'parent'   => 2,
					'position' => 100,
					'title'    => 'Staff',
					'provider' => 'Services/Staff',
					'status'   => 'Staff is currently disabled. <a href="#">Enable</a>',
					'type'     => 'Widget',
				),
			13 =>
				array(
					'id'       => 11,
					'parent'   => 2,
					'position' => 120,
					'title'    => 'My Organisations',
					'provider' => 'Plugin MyOrg',
					'status'   => 'Available',
					'type'     => 'LinkList',
				),
			14 =>
				array(
					'id'       => 6,
					'parent'   => 2,
					'position' => 70,
					'title'    => 'Intranet (external)',
					'provider' => 'Custom',
					'status'   => 'Available',
					'type'     => 'Link',
				),
			15 =>
				array(
					'id'       => 5,
					'parent'   => 2,
					'position' => 60,
					'title'    => 'Sharepoint (external)',
					'provider' => 'Custom',
					'status'   => 'Available',
					'type'     => 'Link',
				)
		);
	}
}
