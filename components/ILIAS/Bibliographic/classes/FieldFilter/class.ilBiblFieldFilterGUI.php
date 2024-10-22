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

use ILIAS\Bibliographic\FieldFilter\Table;

/**
 * Class ilBiblFieldFilterGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterGUI
{
    use \ILIAS\components\OrgUnit\ARHelper\DIC;

    public const FILTER_ID = 'filter_id';
    public const CMD_STANDARD = 'index';
    public const CMD_ADD = 'add';
    public const CMD_CREATE = 'create';
    public const CMD_CANCEL = 'cancel';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_DELETE = 'delete';
    public const CMD_RENDER_INTERRUPTIVE = 'renderInterruptiveModal';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_RESET_FILTER = 'resetFilter';
    private \ILIAS\HTTP\Services $http;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    protected \ilBiblFactoryFacade $facade;
    protected Table $table;
    protected \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilBiblFieldFilterGUI constructor.
     */
    public function __construct(ilBiblFactoryFacade $facade)
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

    public function renderInterruptiveModal(): void
    {
        $f = $this->dic()->ui()->factory();
        $r = $this->dic()->ui()->renderer();
        $ilBiblFieldFilter = $this->getFieldFilterFromRequest();
        $form_action = $this->ctrl->getFormActionByClass(
            ilBiblFieldFilterGUI::class,
            ilBiblFieldFilterGUI::CMD_DELETE
        );
        $delete_modal = $f->modal()->interruptive(
            $this->lng->txt("delete"),
            $this->lng->txt('msg_confirm_delete_filter'),
            $form_action
        )->withAffectedItems(
            [
                $f->modal()->interruptiveItem()->standard(
                    (string) $ilBiblFieldFilter->getId(),
                    $this->facade->translationFactory()->translate(
                        $this->facade->fieldFactory()->findById($ilBiblFieldFilter->getFieldId())
                    )
                )
            ]
        );

        echo $r->render([$delete_modal]);
        exit;
    }

    public function executeCommand(): void
    {
        $this->saveFieldIdsInRequest();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            default:
                $this->tabs->activateTab(ilObjBibliographicGUI::TAB_SETTINGS);
                $this->performCommand();
        }
    }

    protected function performCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_EDIT:
            case self::CMD_DELETE:
                $field = $this->getFieldFromRequest();
                if ($field === null) {
                    throw new ilException("Field not found");
                }
                // no break
            case self::CMD_STANDARD:
            case self::CMD_ADD:
            case self::CMD_UPDATE:
            case self::CMD_CREATE:
            case self::CMD_CANCEL:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
            case self::CMD_RENDER_INTERRUPTIVE:
                if ($this->access->checkAccess('write', "", $this->facade->iliasRefId())) {
                    $this->{$cmd}();
                    break;
                } else {
                    $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
                    break;
                }
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

    public function index(): void
    {
        if ($this->access->checkAccess('write', "", $this->facade->iliasRefId())) {
            // mantis 0038219: added infobox to describe the filter functionality
            $infobox = $this->dic()->ui()->factory()->messageBox()->info($this->lng()->txt('msg_filter_info'));
            $this->tpl()->setVariable("MESSAGE", $this->dic()->ui()->renderer()->render($infobox));

            $button = $this->dic()->ui()->factory()->button()->primary(
                $this->lng->txt("add_filter"),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD)
            );
            $this->toolbar()->addComponent($button);
        }

        $this->main_tpl->setContent($this->table->getHTML());
    }

    protected function add(): void
    {
        $ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, new ilBiblFieldFilter(), $this->facade);
        $this->main_tpl->setContent($ilBiblSettingsFilterFormGUI->getHTML());
    }

    protected function create(): void
    {
        $this->tabs->activateTab(self::CMD_STANDARD);
        $il_bibl_field = new ilBiblFieldFilter();
        $il_bibl_field->setObjectId($this->facade->iliasObjId());
        $form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('changes_saved'), true);
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $form->setValuesByPost();
        $this->main_tpl->setContent($form->getHTML());
    }

    public function edit(): void
    {
        $ilBiblSettingsFilterFormGUI = $this->initEditForm();
        $this->main_tpl->setContent($ilBiblSettingsFilterFormGUI->getHTML());
    }

    public function update(): void
    {
        $il_bibl_field = $this->getFieldFilterFromRequest();
        $this->tabs->activateTab(self::CMD_STANDARD);

        $form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('changes_saved'), true);
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $form->setValuesByPost();
        $this->main_tpl->setContent($form->getHTML());
    }

    public function delete(): void
    {
        $token = $this->table->getIdToken()->getName();
        $items = $this->http->request()->getQueryParams()[$token] ?? [];

        foreach ($items as $filter_id) {
            $il_bibl_field = $this->facade->filterFactory()->findById((int) $filter_id);
            $il_bibl_field->delete();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('filter_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    /**
     * cancel
     */
    public function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    private function getFieldFilterFromRequest(): \ilBiblFieldFilterInterface
    {
        $table = new Table(
            $this,
            $this->facade
        );

        $token = $table->getIdToken()->getName();
        if (isset($this->http->request()->getQueryParams()[$token])) {
            $field_id = $this->http->request()->getQueryParams()[$token] ?? null;
            if (is_array($field_id)) {
                $field_id = $field_id[0];
            }
            return $this->facade->filterFactory()->findById($field_id ?? 0);
        }

        $field = $this->http->request()->getQueryParams()[self::FILTER_ID];

        return $this->facade->filterFactory()->findById($field);
    }

    protected function initEditForm(): ilBiblFieldFilterFormGUI
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD)
        );

        $ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI(
            $this,
            $this->getFieldFilterFromRequest(),
            $this->facade
        );
        $ilBiblSettingsFilterFormGUI->fillForm();

        return $ilBiblSettingsFilterFormGUI;
    }

    protected function applyFilter(): void
    {
        $table = new ilBiblFieldFilterTableGUI($this, $this->facade);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function resetFilter(): void
    {
        $table = new ilBiblFieldFilterTableGUI($this, $this->facade);
        $table->resetFilter();
        $table->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    public function checkPermissionAndFail(string $permission): void
    {
        if (!$this->checkPermissionBoolAndReturn($permission)) {
            throw new \ilObjectException($this->lng()->txt("permission_denied"));
        }
    }

    public function checkPermissionBoolAndReturn(string $permission): bool
    {
        return (bool) $this->access->checkAccess($permission, '', $this->http->request()->getQueryParams()['ref_id']);
    }
}
