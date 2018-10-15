<?php

/**
 * Class ilMMTopItemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemFormGUI {

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
	 * ilMMTopItemFormGUI constructor.
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
		$title = $this->ui_fa->input()->field()->text($this->lng->txt('slate_title_default'), $this->lng->txt('slate_title_default_byline'));
		$items[] = $title;

		$type = $this->ui_fa->input()->field()->radio($this->lng->txt('slate_type'), $this->lng->txt('slate_type_byline'))
			->withOption(1, 'Main Menu Item with Subitems')
			->withOption(2, 'Link')
			->withValue(1);
		$items[] = $type;

		$active = $this->ui_fa->input()->field()->checkbox($this->lng->txt('slate_active'), $this->lng->txt('slate_active_byline'));
		$items[] = $active;

		$sticky = $this->ui_fa->input()->field()->checkbox($this->lng->txt('slate_sticky'), $this->lng->txt('slate_sticky_byline'));
		$items[] = $sticky;

		// RETURN FORM
		$section = $this->ui_fa->input()->field()->section($items, $this->lng->txt('add_slate'));
		$form = $this->ui_fa->input()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilObjMainMenuGUI::class), [$section]);

		return $this->ui_re->render([$form]);
	}
}
