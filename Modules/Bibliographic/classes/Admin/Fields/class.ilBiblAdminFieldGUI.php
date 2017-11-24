<?php
/**
 * Class ilBiblAdminFieldGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

abstract class ilBiblAdminFieldGUI {

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
	 * @var integer
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
				$this->tabs()->setBackTarget($this->lng()->txt('common_back'), $this->ctrl()
				                                                                    ->getLinkTargetByClass(ilBiblAdminBibtexFieldGUI::class));
				$this->ctrl()
				     ->forwardCommand(new ilBiblTranslationGUI($this->translation_factory, $this->field_factory));
				break;

			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl()->getCmd();
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
		$this->ctrl()
		     ->saveParameterByClass(ilBiblAdminFieldTableGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type_factory->getInstanceForType($this->type), $this->field_factory, $this->type_factory);
		$this->tpl()->setContent($ilBiblAdminFieldTableGUI->getHTML());
	}


	protected function setSubTabs($a_active_tab) {
		$this->tabs()->addSubTab(ilBiblField::DATA_TYPE_RIS, $this->lng()->txt('ris'), $this->ctrl()
		                                                                                    ->getLinkTargetByClass(array(
			                                                                                    ilObjBibliographicAdminGUI::class,
			                                                                                    ilBiblAdminRisFieldGUI::class,
		                                                                                    ), ilBiblAdminRisFieldGUI::CMD_STANDARD)

		);
		$this->tabs()->activateSubTab('ris');

		$this->tabs()->addSubTab(ilBiblField::DATA_TYPE_BIBTEX, $this->lng()
		                                                             ->txt('bibtex'), $this->ctrl()
		                                                                                   ->getLinkTargetByClass(array(
			                                                                                   ilObjBibliographicAdminGUI::class,
			                                                                                   ilBiblAdminBibtexFieldGUI::class,
		                                                                                   ), ilBiblAdminBibtexFieldGUI::CMD_STANDARD));
		$this->tabs()->activateSubTab($a_active_tab);
	}


	protected function save() {
		foreach ($_POST['row_values'] as $id => $data) {
			if (!empty($data['position'])) {
				$ilBiblField = ilBiblField::find($id);
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
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type, $this->field_factory, $this->type_factory);
		$ilBiblAdminFieldTableGUI->writeFilterToSession();
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	protected function resetFilter() {
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_STANDARD, $this->object, $this->type, $this->field_factory, $this->type_factory);
		$ilBiblAdminFieldTableGUI->resetFilter();
		$ilBiblAdminFieldTableGUI->resetOffset();
		$this->ctrl()->redirect($this, self::CMD_STANDARD);
	}
}