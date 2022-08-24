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
 * Class ilBiblFieldFilterGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
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
    public const CMD_TRANSLATE = 'translate';
    protected \ilBiblFactoryFacade $facade;
    private \ilGlobalTemplateInterface $main_tpl;


    /**
     * ilBiblFieldFilterGUI constructor.
     */
    public function __construct(ilBiblFactoryFacade $facade)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->facade = $facade;
    }


    public function renderInterruptiveModal(): void
    {
        $f = $this->dic()->ui()->factory();
        $r = $this->dic()->ui()->renderer();
        $ilBiblFieldFilter = $this->getFieldFilterFromRequest();
        $form_action = $this->ctrl()->getFormActionByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_DELETE);
        $delete_modal = $f->modal()->interruptive(
            $this->lng()->txt("delete"),
            $this->lng()->txt('msg_confirm_delete_filter'),
            $form_action
        )->withAffectedItems(
            [$f->modal()->interruptiveItem((string) $ilBiblFieldFilter->getId(), $this->facade->translationFactory()->translate($this->facade->fieldFactory()->findById($ilBiblFieldFilter->getFieldId())))]
        );

        echo $r->render([$delete_modal]);
        exit;
    }


    public function executeCommand(): void
    {
        $nextClass = $this->ctrl()->getNextClass();
        switch ($nextClass) {
            default:
                $this->tabs()->activateTab(ilObjBibliographicGUI::TAB_SETTINGS);
                $this->performCommand();
        }
    }


    protected function performCommand(): void
    {
        $cmd = $this->ctrl()->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
            case self::CMD_ADD:
            case self::CMD_EDIT:
            case self::CMD_UPDATE:
            case self::CMD_CREATE:
            case self::CMD_DELETE:
            case self::CMD_CANCEL:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
            case self::CMD_RENDER_INTERRUPTIVE:
                if ($this->access()->checkAccess('write', "", $this->facade->iliasRefId())) {
                    $this->{$cmd}();
                    break;
                } else {
                    $this->main_tpl->setOnScreenMessage('failure', $this->lng()->txt("no_permission"), true);
                    break;
                }
        }
    }


    public function index(): void
    {
        if ($this->access()->checkAccess('write', "", $this->facade->iliasRefId())) {
            $button = $this->dic()->ui()->factory()->button()->primary($this->lng()->txt("add_filter"), $this->ctrl()->getLinkTarget($this, self::CMD_ADD));
            $this->toolbar()->addText($this->dic()->ui()->renderer()->render([$button]));
        }

        $table = new ilBiblFieldFilterTableGUI($this, $this->facade);
        $this->tpl()->setContent($table->getHTML());
    }


    protected function add(): void
    {
        $ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, new ilBiblFieldFilter(), $this->facade);
        $this->tpl()->setContent($ilBiblSettingsFilterFormGUI->getHTML());
    }


    protected function create(): void
    {
        $this->tabs()->activateTab(self::CMD_STANDARD);
        $il_bibl_field = new ilBiblFieldFilter();
        $il_bibl_field->setObjectId($this->facade->iliasObjId());
        $form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng()->txt('changes_saved'), true);
            $this->ctrl()->redirect($this, self::CMD_STANDARD);
        }
        $form->setValuesByPost();
        $this->tpl()->setContent($form->getHTML());
    }


    public function edit(): void
    {
        $ilBiblSettingsFilterFormGUI = $this->initEditForm();
        $this->tpl()->setContent($ilBiblSettingsFilterFormGUI->getHTML());
    }


    public function update(): void
    {
        $il_bibl_field = $this->getFieldFilterFromRequest();
        $this->tabs()->activateTab(self::CMD_STANDARD);

        $form = new ilBiblFieldFilterFormGUI($this, $il_bibl_field, $this->facade);
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng()->txt('changes_saved'), true);
            $this->ctrl()->redirect($this, self::CMD_STANDARD);
        }
        $form->setValuesByPost();
        $this->tpl()->setContent($form->getHTML());
    }


    public function delete(): void
    {
        global $DIC;
        $items = $this->http()->request()->getParsedBody()['interruptive_items'];
        if (is_array($items)) {
            foreach ($items as $filter_id) {
                $il_bibl_field = $this->facade->filterFactory()->findById($filter_id);
                $il_bibl_field->delete();
            }
        }
        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('filter_deleted'), true);
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    /**
     * cancel
     */
    public function cancel(): void
    {
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    private function getFieldFilterFromRequest(): \ilBiblFieldFilterInterface
    {
        $field = $this->http()->request()->getQueryParams()[self::FILTER_ID];

        return $this->facade->filterFactory()->findById($field);
    }


    protected function initEditForm(): ilBiblFieldFilterFormGUI
    {
        $this->tabs()->clearTargets();
        $this->tabs()->setBackTarget(
            $this->lng()->txt("back"),
            $this->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD)
        );

        $ilBiblSettingsFilterFormGUI = new ilBiblFieldFilterFormGUI($this, $this->getFieldFilterFromRequest(), $this->facade);
        $ilBiblSettingsFilterFormGUI->fillForm();

        return $ilBiblSettingsFilterFormGUI;
    }


    protected function applyFilter(): void
    {
        $table = new ilBiblFieldFilterTableGUI($this, $this->facade);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    protected function resetFilter(): void
    {
        $table = new ilBiblFieldFilterTableGUI($this, $this->facade);
        $table->resetFilter();
        $table->resetOffset();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }
}
