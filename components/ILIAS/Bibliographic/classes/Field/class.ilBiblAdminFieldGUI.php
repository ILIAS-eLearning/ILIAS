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

use ILIAS\Bibliographic\Field\Table;

/**
 * Class ilBiblAdminFieldGUI
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBiblAdminFieldGUI
{
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
    private \ILIAS\HTTP\Services $http;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs;
    private ilLanguage $lng;
    private ilAccessHandler $access;
    protected \ilBiblAdminFactoryFacadeInterface $facade;
    protected Table $table;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilBiblAdminFieldGUI constructor.
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->facade = $facade;
        $this->table = new Table(
            $this,
            $this->facade
        );
        $this->http = $DIC['http'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC['lng'];
        $this->access = $DIC['ilAccess'];
    }

    public function executeCommand(): void
    {
        $this->saveFieldIdsInRequest();
        $next_class = $this->ctrl->getNextClass();
        $this->tabs->activateTab(ilObjBibliographicAdminGUI::TAB_FIELDS);
        switch ($next_class) {
            case strtolower(ilBiblTranslationGUI::class):
                $this->tabs->clearTargets();
                $target = $this->ctrl->getLinkTarget($this);
                $this->tabs->setBackTarget($this->lng->txt('back'), $target);

                $field = $this->getFieldFromRequest();
                if ($field === null) {
                    throw new ilException("Field not found");
                }
                $gui = new ilBiblTranslationGUI($this->facade, $field);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->performCommand();
        }
    }

    protected function performCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
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

    protected function getFieldIdFromRequest(): int
    {
        $query_params = $this->http->request()->getQueryParams(); // aka $_GET
        $name = $this->table->getIdToken()->getName(); // name of the query parameter from the table
        $field_ids = $query_params[$name] ?? []; // array of field ids
        return (int) (is_array($field_ids) ? end($field_ids) : $field_ids); // return the last field id
    }

    private function saveFieldIdsInRequest(): void
    {
        $field_id = $this->getFieldIdFromRequest();

        $this->ctrl->setParameter($this, $this->table->getIdToken()->getName(), $field_id);
    }

    private function getFieldFromRequest(): ilBiblFieldInterface
    {
        $field_id = $this->getFieldIdFromRequest();

        return $this->facade->fieldFactory()->findById($field_id); // get field from id from the factory
    }

    protected function index(): void
    {
        $this->setSubTabs();
        //$table = new ilBiblAdminFieldTableGUI($this, $this->facade);
        //$this->tpl()->setContent($table->getHTML());
        $this->main_tpl->setContent($this->table->getHTML());
    }

    protected function setSubTabs(): void
    {
        $this->tabs->addSubTab(
            self::SUBTAB_RIS,
            $this->lng->txt('ris'),
            $this->ctrl->getLinkTargetByClass(
                array(
                    ilObjBibliographicAdminGUI::class,
                    ilBiblAdminRisFieldGUI::class,
                ),
                ilBiblAdminRisFieldGUI::CMD_STANDARD
            )
        );
        $this->tabs->activateSubTab(self::SUBTAB_RIS);

        $this->tabs->addSubTab(
            self::SUBTAB_BIBTEX,
            $this->lng->txt('bibtex'),
            $this->ctrl->getLinkTargetByClass(
                array(
                    ilObjBibliographicAdminGUI::class,
                    ilBiblAdminBibtexFieldGUI::class,
                ),
                ilBiblAdminBibtexFieldGUI::CMD_STANDARD
            )
        );
        switch ($this->facade->type()->getId()) {
            case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
                $this->tabs->activateSubTab(self::SUBTAB_BIBTEX);
                break;
            case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
                $this->tabs->activateSubTab(self::SUBTAB_RIS);
                break;
        }
    }

    protected function save(): void
    {
        // I currently did not find a way to use the wrapper here
        $positions = $this->http->request()->getParsedBody()['position'];

        foreach ($positions as $set) {
            $field_id = (int) key($set);
            $position = (int) current($set);

            $ilBiblField = $this->facade->fieldFactory()->findById($field_id);
            $ilBiblField->setPosition($position);
            $ilBiblField->store();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('changes_successfully_saved'));
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function applyFilter(): void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function resetFilter(): void
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->resetFilter();
        $ilBiblAdminFieldTableGUI->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    public function checkPermissionAndFail(string $permission): void
    {
        if (!$this->checkPermissionBoolAndReturn($permission)) {
            throw new \ilObjectException($this->lng->txt("permission_denied"));
        }
    }

    public function checkPermissionBoolAndReturn(string $permission): bool
    {
        return (bool) $this->access->checkAccess($permission, '', $this->http->request()->getQueryParams()['ref_id']);
    }
}
