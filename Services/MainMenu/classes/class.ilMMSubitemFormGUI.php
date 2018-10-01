<?php

/**
 * Class ilMMSubitemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubitemFormGUI {

	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ILIAS\UI\Factory
	 */
	protected $ui_fa;
	/**
	 * @var ILIAS\UI\Renderer
	 */
	protected $ui_re;


	/**
	 * ilMMSlateFormGUI constructor.
	 *
	 * @param ilCtrl             $ctrl
	 * @param \ILIAS\UI\Factory  $ui_fa
	 * @param \ILIAS\UI\Renderer $ui_re
	 */
	public function __construct(ilCtrl $ctrl, \ILIAS\UI\Factory $ui_fa, \ILIAS\UI\Renderer $ui_re, ilLanguage $lng) {
		$this->ctrl = $ctrl;
		$this->ui_fa = $ui_fa;
		$this->ui_re = $ui_re;
		$this->lng = $lng;
	}


	public function getHTML() {
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('sub_title_default'), $this->lng->txt('sub_title_default_byline'));
		$items[] = $title;

		$type = $this->ui_fa->input()
			->field()
			->radio($this->lng->txt('sub_type'), $this->lng->txt('sub_type_byline'))
			->withOption(1, 'Link', [$this->ui_fa->input()->field()->text("URL")])
			->withOption(2, 'Link List')
			->withOption(3, 'Separator')
			->withOption(4, 'Custom Entry Type from Plugin XY')->withValue(1);
		$items[] = $type;

		$mm_item = $this->ui_fa->input()->field()->select($this->lng->txt('sub_parent'), [0 => 'Repository', 1 => 'Personal Workspace', 2 => 'Organisation']);
		$items[] = $mm_item;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('sub_active'), $this->lng->txt('sub_active_byline'));
		$items[] = $active;

		// RETURN FORM
		$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt('add_subentry'));
		$form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilObjMainMenuGUI::class), [$section]);

		return $this->ui_re->render([$form]);
	}
}
