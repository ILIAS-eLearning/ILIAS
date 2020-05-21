<?php

/**
 * Class ilBiblAdminFieldGUI
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBiblAdminFieldGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;

    const CMD_INIT_DEFAULT_FIELDS_AND_SORTING = 'initDefaultFieldsAndSorting';
    const SUBTAB_RIS = 'subtab_ris';
    const SUBTAB_BIBTEX = 'subtab_bibtex';
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
     * @var \ilBiblAdminFactoryFacadeInterface
     */
    protected $facade;

    /**
     * ilBiblAdminFieldGUI constructor.
     * @param \ilBiblAdminFactoryFacadeInterface $facade
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade)
    {
        $this->facade = $facade;
    }

    public function executeCommand()
    {
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
                $field = $this->facade->fieldFactory()->findById($field_id);

                $gui = new ilBiblTranslationGUI($this->facade, $field);
                $this->ctrl()->forwardCommand($gui);
                break;

            default:
                $this->performCommand();
        }
    }

    protected function performCommand() : void
    {
        $cmd = $this->ctrl()->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
                if ($this->checkPermissionBoolAndReturn('read')) {
                    $this->{$cmd}();
                }
                break;
            case self::CMD_EDIT:
            case self::CMD_UPDATE:
            case self::CMD_SAVE:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
                if ($this->checkPermissionBoolAndReturn('write')) {
                    $this->{$cmd}();
                }
                break;
        }
    }

    protected function index() : void
    {
        $this->setSubTabs();

        $table = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $this->tpl()->setContent($table->getHTML());
    }

    protected function setSubTabs() : void
    {
        $this->tabs()->addSubTab(
            self::SUBTAB_RIS,
            $this->lng()->txt('ris'),
            $this->ctrl()->getLinkTargetByClass(
                array(
                    ilObjBibliographicAdminGUI::class,
                    ilBiblAdminRisFieldGUI::class,
                ),
                ilBiblAdminRisFieldGUI::CMD_STANDARD
            )

        );
        $this->tabs()->activateSubTab(self::SUBTAB_RIS);

        $this->tabs()->addSubTab(
            self::SUBTAB_BIBTEX,
            $this->lng()->txt('bibtex'),
            $this->ctrl()->getLinkTargetByClass(
                array(
                    ilObjBibliographicAdminGUI::class,
                    ilBiblAdminBibtexFieldGUI::class,
                ),
                ilBiblAdminBibtexFieldGUI::CMD_STANDARD
            )
        );
        switch ($this->facade->type()->getId()) {
            case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
                $this->tabs()->activateSubTab(self::SUBTAB_BIBTEX);
                break;
            case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
                $this->tabs()->activateSubTab(self::SUBTAB_RIS);
                break;
        }
    }

    protected function save() : void
    {
        foreach ($_POST['position'] as $set) {
            $field_id = (int) key($set);
            $position = (int) current($set);

            $ilBiblField = $this->facade->fieldFactory()->findById($field_id);
            $ilBiblField->setPosition($position);
            $ilBiblField->store();
        }

        ilUtil::sendSuccess($this->lng()->txt('changes_successfully_saved'));
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function applyFilter() : void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->writeFilterToSession();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function resetFilter() : void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->resetFilter();
        $ilBiblAdminFieldTableGUI->resetOffset();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }
}
