<?php
/**
 * Class ilBiblAdminFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

abstract class ilBiblAdminFieldGUI {

	const FIELD_IDENTIFIER = 'field_id';
	const DATA_TYPE = 'data_type';
	const CMD_STANDARD = 'content';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_SAVE = 'save';
	const CMD_DELETE = 'delete';
	const CMD_CONFIRM_DELETE = 'confirmedDelete';
	const CMD_CANCEL_DELETE = 'canceledDelete';

	/**
	 * @var ilObjBibliographicAdmin
	 */
	protected $object;
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
	/**
	 * @var integer
	 */
	protected $type;

	public function __construct() {
		global $DIC;
		$this->initType();
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
	}

	abstract protected function initType();

	public function executeCommand() {

		$ctrl_flow = $this->ctrl->getCallHistory();
		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(ilObjBibliographicAdminGUI::TAB_FIELDS);
				$this->performCommand();
		}
	}

	protected function performCommand() {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
			case self::CMD_DELETE:
			case self::CMD_SAVE:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
			case self::CMD_CONFIRM_DELETE:
			case self::CMD_CANCEL_DELETE:
				if ($this->dic->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}

	public function content() {
		$this->setSubTabs($this->type);
		$this->ctrl->saveParameterByClass(ilBiblAdminFieldTableGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type);
		$this->tpl->setContent($ilBiblAdminFieldTableGUI->getHTML());
	}

	protected function setSubTabs($a_active_tab) {
		$this->dic['ilTabs']->addSubTab(ilBiblField::DATA_TYPE_RIS, $this->dic->language()->txt('ris'), $this->ctrl->getLinkTargetByClass(array(
			'ilObjBibliographicAdminGUI',
			ilBiblAdminRisFieldGUI::class), ilBiblAdminRisFieldGUI::CMD_STANDARD)

		);
		$this->dic['ilTabs']->activateSubTab('ris');

		$this->dic['ilTabs']->addSubTab(ilBiblField::DATA_TYPE_BIBTEX, $this->dic->language()->txt('bibtex'), $this->ctrl->getLinkTargetByClass(array(
			'ilObjBibliographicAdminGUI',
			ilBiblAdminBibtexFieldGUI::class), ilBiblAdminBibtexFieldGUI::CMD_STANDARD)
		);
		$this->dic['ilTabs']->activateSubTab($a_active_tab);
	}

	protected function save() {
		foreach($_POST['row_values'] as $id => $data) {
			if(!empty($data['position'])) {
				if(!$_POST['row_values'][$id]['is_bibl_field']) {
					$il_bibl_field = new ilBiblField();
				} else {
					$il_bibl_field = ilBiblField::find($id);
				}
					$il_bibl_field->setIdentifier($_POST['row_values'][$id]['identifier']);
					$il_bibl_field->setDataType($_POST['row_values'][$id]['data_type']);
					$il_bibl_field->setPosition($_POST['row_values'][$id]['position']);
					$il_bibl_field->setIsStandardField($_POST['row_values'][$id]['is_standard_field']);
					$il_bibl_field->store();
			}
		}
		ilUtil::sendSuccess($this->dic->language()->txt("changes_successfully_saved"));
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	protected function applyFilter() {
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type);
		$ilBiblAdminFieldTableGUI->writeFilterToSession();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	protected function resetFilter() {
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type);
		$ilBiblAdminFieldTableGUI->resetFilter();
		$ilBiblAdminFieldTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
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
		$this->ctrl->redirectByClass(ilBiblAdminFieldGUI::class, ilBiblAdminFieldGUI::CMD_STANDARD);
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