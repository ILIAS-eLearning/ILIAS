<?php
/**
 * Class ilBiblAdminFieldGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

abstract class ilBiblAdminFieldGUI {

	const SUBTAB_RIS = 'subtab_ris';
	const SUBTAB_BIBTEX = 'subtab_bibtex';
	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const FIELD_IDENTIFIER = 'field_id';
	const DATA_TYPE = 'data_type';
	const CMD_STANDARD = 'index';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_SAVE = 'save';
	/**
	 * @var \ilObjBibliographicAdmin
	 */
	protected $object;
	/**
	 * @var \ilBiblTranslationFactoryInterface
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblTypeFactory
	 */
	protected $type_factory;
	/**
	 * @var \ilBiblTypeInterface
	 */
	protected $type;


	/**
	 * ilBiblAdminFieldGUI constructor.
	 *
	 * @param \ilBiblFieldFactoryInterface       $field_factory
	 * @param \ilBiblTypeFactoryInterface        $type_factory
	 * @param \ilBiblTranslationFactoryInterface $translation_factory
	 */
	public function __construct(ilBiblFieldFactoryInterface $field_factory, ilBiblTypeFactoryInterface $type_factory, ilBiblTranslationFactoryInterface $translation_factory) {
		$this->field_factory = $field_factory;
		$this->type_factory = $type_factory;
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->translation_factory = $translation_factory;
		$this->initType();
	}


	abstract protected function initType();


	public function executeCommand() {
		$nextClass = $this->ctrl()->getNextClass();
		$this->tabs()->activateTab(ilObjBibliographicAdminGUI::TAB_FIELDS);
		switch ($nextClass) {
			case strtolower(ilBiblTranslationGUI::class):
				$this->tabs()->clearTargets();
				$target = $this->ctrl()->getLinkTarget($this);
				$this->tabs()->setBackTarget($this->lng()->txt('back'), $target);

				$field_id = $this->http()->request()->getQueryParams()[self::FIELD_IDENTIFIER];
				if (!$field_id) {
					throw new ilException("Field not found");
				}
				$this->ctrl()->saveParameter($this, self::FIELD_IDENTIFIER);

				$field = $this->field_factory->findById($field_id);
				$gui = new ilBiblTranslationGUI($this->type, $this->translation_factory, $field);
				$this->ctrl()->forwardCommand($gui);
				break;

			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
			case self::CMD_SAVE:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
				if ($this->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->lng()->txt("no_permission"), true);
					break;
				}
		}
	}


	protected function index() {
		$this->setSubTabs($this->type);
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type, $this->field_factory, $this->translation_factory);
		$this->tpl()->setContent($ilBiblAdminFieldTableGUI->getHTML());
	}


	protected function setSubTabs($a_active_tab) {
		$this->tabs()->addSubTab(self::SUBTAB_RIS, $this->lng()->txt('ris'), $this->ctrl()
		                                                                          ->getLinkTargetByClass(array(
			                                                                          ilObjBibliographicAdminGUI::class,
			                                                                          ilBiblAdminRisFieldGUI::class,
		                                                                          ), ilBiblAdminRisFieldGUI::CMD_STANDARD)

		);
		$this->tabs()->activateSubTab(self::SUBTAB_RIS);

		$this->tabs()->addSubTab(self::SUBTAB_BIBTEX, $this->lng()->txt('bibtex'), $this->ctrl()
		                                                                                ->getLinkTargetByClass(array(
			                                                                                ilObjBibliographicAdminGUI::class,
			                                                                                ilBiblAdminBibtexFieldGUI::class,
		                                                                                ), ilBiblAdminBibtexFieldGUI::CMD_STANDARD));
		$this->tabs()->activateSubTab($a_active_tab);
	}


	protected function save() {
		foreach ($_POST['row_values'] as $id => $data) {
			if (!empty($data['position'])) {
				$ilBiblField = $this->field_factory->findById($id);
				$ilBiblField->setIdentifier($_POST['row_values'][$id]['identifier']);
				$ilBiblField->setDataType($_POST['row_values'][$id]['data_type']);
				$ilBiblField->setPosition($_POST['row_values'][$id]['position']);
				$ilBiblField->setIsStandardField($_POST['row_values'][$id]['is_standard_field']);
				$ilBiblField->store();
			}
		}
		ilUtil::sendSuccess($this->lng()->txt("changes_successfully_saved"));
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	protected function applyFilter() {
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type, $this->field_factory, $this->translation_factory);
		$ilBiblAdminFieldTableGUI->writeFilterToSession();
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	protected function resetFilter() {
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type, $this->field_factory, $this->translation_factory);
		$ilBiblAdminFieldTableGUI->resetFilter();
		$ilBiblAdminFieldTableGUI->resetOffset();
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}
}