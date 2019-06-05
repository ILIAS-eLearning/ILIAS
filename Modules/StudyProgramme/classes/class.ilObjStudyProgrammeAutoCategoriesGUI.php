<?php

declare(strict_types=1);

/**
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilObjStudyProgrammeAutoCategoriesGUI
{
	/**
	 * @var ilTemplate
	 */
	public $tpl;

	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @var ilToolbarGUI
	 */
	public $toolbar;

	/**
	 * @var ilLng
	 */
	public $lng;

	public function __construct(
		ilTemplate $tpl,
		ilCtrl $ilCtrl,
		ilToolbarGUI $ilToolbar,
		ilLanguage $lng
	) {
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case "view":
				break;
			default:
				throw new ilException("ilObjStudyProgrammeAutoCategoriesGUI: ".
									  "Command not supported: $cmd");
		}

	}
}
