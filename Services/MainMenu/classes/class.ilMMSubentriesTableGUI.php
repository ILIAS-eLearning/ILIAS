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

		$this->tpl->setVariable('POSITION', $position * 10);
		$this->tpl->setVariable('TYPE', $a_set["type"]);
		$this->tpl->setVariable('PROVIDER', $a_set['provider']);

		$items[] = $factory->button()->shy($this->lng->txt('edit_subentry'), '#');
		$items[] = $factory->button()->shy($this->lng->txt('translate_subentry'), $this->ctrl->getLinkTarget($this->parent_obj, 'translate'));
		if($a_set['provider'] === "Custom") {
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
	 * @return string
	 */
	private function getRandomString(): string {
		static $lorem_ipsum;
		if (!$lorem_ipsum) {
			$lorem_ipsum_unfiltered = explode(
				" ", preg_replace(
					   '/[^a-z]+/i', " ", file_get_contents('http://loripsum.net/api')
				   )
			);
			$lorem_ipsum = array_filter($lorem_ipsum_unfiltered, function ($var) { return (strlen($var) > 5); });
			sort($lorem_ipsum);
		}

		return ucfirst($lorem_ipsum[rand(0, count($lorem_ipsum))]);
	}


	/**
	 * @return array
	 */
	private function getFakeData(): array {
		$fake_data = [];
		for ($x = 0; $x < 10; $x++) {
			$f = [
				'id'       => $x,
				'parent'   => rand(0, 2),
				'position' => ($x + 1) * 10,
				'title'    => $this->getRandomString(),
				'provider' => $this->getRandomProvider(),
				'status'   => str_repeat("&nbsp;", 50),
				'type'   => $this->getRandomType(),
			];
			$fake_data[] = $f;
		}

		usort(
			$fake_data, function ($a, $b) {
			return $a['parent'] <=> $b['parent'];
		}
		);

		return $fake_data;
	}


	private function getRandomType(): string {
		$possible = ["Link", "LinkList", "Widget"];

		return $possible[rand(0, 2)];
	}


	/**
	 * @return string
	 */
	private function getRandomProvider(): string {
		if (rand(0, 3) === 0) {
			return "Custom";
		}
		if (rand(0, 3) === 0) {
			return "Plugin {$this->getRandomString()}";
		}

		if (rand(0, 2) === 0) {
			return "Modules/{$this->getRandomString()}";
		} else {
			return "Services/{$this->getRandomString()}";
		}
	}
}
