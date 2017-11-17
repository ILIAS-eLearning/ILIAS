<?php
/**
 * Class ilBiblAdminFieldTranslationGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminFieldTranslationGUI {

	const CMD_STANDARD = 'translate';

	/**
	 * @var ilObjBibliographicAdmin
	 */
	protected $object;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilObjUser
	 */
	protected $user;


	/**
	 * Constructor
	 */
	function __construct($a_obj_gui)
	{
		global $DIC;

		$this->dic = $DIC;
		$this->toolbar = $this->dic->toolbar();
		$this->user = $this->dic->user();
		$lng = $this->dic->language();
		$ilCtrl = $this->dic->ctrl();
		$tpl = $this->dic["tpl"];
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->obj = $a_obj_gui->object;

		include_once("./Services/Object/classes/class.ilObjectTranslation.php");
		$this->obj_trans = ilObjectTranslation::getInstance($this->obj->getId());

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
	}


	public function executeCommand() {

		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(ilObjBibliographicAdminGUI::TAB_FIELDS);
				$this->performCommand();
		}
	}

	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
				if ($this->dic->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}



}