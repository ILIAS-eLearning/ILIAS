<?php
/**
 * Class ilBiblAdminFieldDeleteGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminFieldDeleteGUI {

	const CMD_STANDARD = 'delete';
	const CMD_CONFIRM_DELETE = 'confirmedDelete';
	const CMD_CANCEL_DELETE = 'canceledDelete';

	/**
	 * @var ilObjBibliographicAdmin
	 */
	public $il_obj_bibliographic_admin;
	/**
	 * @var ilBiblField
	 */
	public $il_bibl_field;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	public function __construct() {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->il_obj_bibliographic_admin = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
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
			case self::CMD_CONFIRM_DELETE:
			case self::CMD_CANCEL_DELETE:
			if ($this->dic->access()->checkAccess('write', "", $this->il_obj_bibliographic_admin->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}

	public function delete() {
		$this->ctrl->saveParameter($this, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();

		$cgui->setHeaderText($this->dic->language()->txt('confirm_delete_question'));
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->dic->language()->txt('cancel'), "canceledDelete");
		$cgui->setConfirm($this->dic->language()->txt('confirm'), "confirmedDelete");

		if($_GET['is_bibl_field']) {
			$cgui->addItem('', '', ilBiblField::where(array( 'id' => $_GET[ilBiblAdminFieldGUI::FIELD_IDENTIFIER] ))->first()->getIdentifier(), "");
		} else {
			$cgui->addItem('', '', ilBiblField::getBiblAttributeRecordById($_GET[ilBiblAdminFieldGUI::FIELD_IDENTIFIER]), "");
		}

		$this->tpl->setContent($cgui->getHTML());
	}


	public function confirmedDelete() {
		$ilBiblField = ilBiblField::where(array( 'id' => $_GET[ilBiblAdminFieldGUI::FIELD_IDENTIFIER] ))->first();

		// Delete the Item itself
		$ilBiblField->delete();

		ilUtil::sendSuccess($this->dic->language()->txt('successfully_deleted'), true);
		$this->ctrl->redirectByClass(ilBiblAdminFieldTableGUI::class);
	}


	public function canceledDelete() {
		if($_GET['is_bibl_field']) {
			$this->ctrl->setParameterByClass(ilBiblAdminFieldGUI::class, 'content_type', ilBiblField::DATA_TYPE_BIBTEX);
		} else {
			$this->ctrl->setParameterByClass(ilBiblAdminFieldGUI::class, 'content_type', ilBiblField::DATA_TYPE_RIS);
		}
		$this->ctrl->redirectByClass(ilBiblAdminFieldGUI::class, ilBiblAdminFieldGUI::CMD_STANDARD);
	}
}