<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBiblAdminFieldGUI
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBiblAdminFieldGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;

    public const CMD_INIT_DEFAULT_FIELDS_AND_SORTING = 'initDefaultFieldsAndSorting';
    public const SUBTAB_RIS = 'subtab_ris';
    public const SUBTAB_BIBTEX = 'subtab_bibtex';
    public const FIELD_IDENTIFIER = 'field_id';
    public const DATA_TYPE = 'data_type';
    public const CMD_STANDARD = 'index';
    public const CMD_CANCEL = 'cancel';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_RESET_FILTER = 'resetFilter';
    public const CMD_SAVE = 'save';
    protected \ilBiblAdminFactoryFacadeInterface $facade;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilBiblAdminFieldGUI constructor.
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->facade = $facade;
    }

    public function executeCommand(): void
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

    protected function performCommand(): void
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

    protected function index(): void
    {
        $this->setSubTabs();

        $table = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $this->tpl()->setContent($table->getHTML());
    }

    protected function setSubTabs(): void
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

    protected function save(): void
    {
        // I currently did not find a way to use the wrapper here
        $positions = $this->http()->request()->getParsedBody()['position'];

        foreach ($positions as $set) {
            $field_id = (int) key($set);
            $position = (int) current($set);

            $ilBiblField = $this->facade->fieldFactory()->findById($field_id);
            $ilBiblField->setPosition($position);
            $ilBiblField->store();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng()->txt('changes_successfully_saved'));
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function applyFilter(): void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->writeFilterToSession();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function resetFilter(): void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->resetFilter();
        $ilBiblAdminFieldTableGUI->resetOffset();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }
}
