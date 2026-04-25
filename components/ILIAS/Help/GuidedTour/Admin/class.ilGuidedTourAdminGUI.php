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

declare(strict_types=1);

use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Help\GuidedTour\Step\StepType;
use ILIAS\Help\GuidedTour\Step\Step;
use ILIAS\Help\GuidedTour\Settings\PermissionType;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;

/**
 * @ilCtrl_Calls ilGuidedTourAdminGUI: ilGuidedTourPageGUI, ilExportGUI, ilRepoStandardUploadHandlerGUI
 */
class ilGuidedTourAdminGUI // implements ilCtrlBaseClassInterface
{
    private \ILIAS\Help\GuidedTour\Step\StepManager $step_manager;
    protected \ILIAS\Help\GuidedTour\UserFinished\UserFinishedManager $finish_manager;
    protected \ILIAS\Help\GuidedTour\Tour\TourManager $tm;

    public function __construct(
        protected \ILIAS\Help\GuidedTour\InternalDataService $data,
        protected \ILIAS\Help\GuidedTour\InternalDomainService $domain,
        protected \ILIAS\Help\GuidedTour\InternalGUIService $gui,
        protected bool $edit = false
    ) {
        $ctrl = $this->gui->ctrl();
        $this->tm = $domain->tour();
        $ctrl->saveParameterByClass(self::class, "tour_id");
        $this->step_manager = $domain->step();
        $this->finish_manager = $domain->userFinished();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listTours");

        switch ($next_class) {
            case strtolower(ilExportGUI::class):
                $this->setStepsHeader();
                $this->setSettingsTabs("export");
                $tour_id = $this->gui->standardRequest()->getTourId();
                $exp_gui = new ilExportGUI($this->gui->objectGUI($tour_id));
                $exp_gui->addFormat("xml");
                $ctrl->forwardCommand($exp_gui);
                break;

            case strtolower(ilGuidedTourPageGUI::class):
                $mt = $this->gui->mainTemplate();
                $lng = $this->domain->lng();
                $this->setStepsHeader();
                $mt->setOnScreenMessage("info", $lng->txt("gdtr_edit_page_info"));
                $ctrl->setReturnByClass(self::class, "listSteps");
                $ctrl->saveParameterByClass(self::class, "step_id");
                $ret = $this->forwardToPageObject();
                $mt->setContent($ret);
                break;

            case strtolower(ilRepoStandardUploadHandlerGUI::class):
                $form = $this->getImportForm();
                $gui = $form->getRepoStandardUploadHandlerGUI("import");
                $ctrl->forwardCommand($gui);
                break;

            default:
                if (in_array($cmd, [
                    "listTours",
                    "addTour",
                    "saveTour",
                    "deleteTour",
                    "listSteps",
                    "addStep",
                    "saveStep",
                    "tableCommand",
                    "editStep",
                    "editPage",
                    "editSettings",
                    "saveSettings",
                    "idSettings",
                    "saveIdSettings",
                    "saveOrder",
                    "confirmStepDeletion",
                    "deleteStep",
                    "resetTour",
                    "importTourForm",
                    "importTour"
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function setSettingsTabs(string $active): void
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tabs->clearTargets();
        $this->gui->help()->setScreenIdComponent("hlps_gdtr");
        $tabs->setBackTarget(
            $lng->txt("gdtr_guided_tours"),
            $ctrl->getLinkTargetByClass(self::class, "listTours")
        );
        $ctrl->saveParameterByClass(self::class, "tour_id");
        $tabs->addTab(
            "steps",
            $lng->txt("gdtr_tour_steps"),
            $ctrl->getLinkTargetByClass(self::class, "listSteps")
        );
        $tabs->addTab(
            "settings",
            $lng->txt("settings"),
            $ctrl->getLinkTargetByClass(self::class, "editSettings")
        );
        $tabs->addTab(
            "export",
            $lng->txt("export"),
            $ctrl->getLinkTargetByClass(ilexportGUI::class, "export", ""),
        );
        $tabs->activateTab($active);
    }

    public function forwardToPageObject(): string
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $step_id = $this->gui->standardRequest()->getStepId();
        $tour_id = $this->gui->standardRequest()->getTourId();

        $tabs->clearTargets();

        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(ilGuidedTourPageGUI::class, "edit")
        );

        if (!ilGuidedTourPage::_exists(
            "gdtr",
            $step_id
        )) {
            $new_page_object = new ilGuidedTourPage();
            $new_page_object->setParentId($tour_id);
            $new_page_object->setId($step_id);
            $new_page_object->createFromXML();
        }

        // get page object
        $page_gui = new ilGuidedTourPageGUI($step_id);
        /*$page_gui->setStyleId(
            $style->getEffectiveStyleId()
        );*/
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setFileDownloadLink("");
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);

        // style tab
        //$page_gui->setTabHook($this, "addPageTabs");

        return $ctrl->forwardCommand($page_gui);
    }


    protected function listTours(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $this->setSubTabs("tours");
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $mt->setOnScreenMessage("info", $lng->txt("gdtr_list_tours_mess"));

        if ($this->edit) {
            $b = $f->button()->standard(
                $lng->txt("gdtr_add_tour"),
                $ctrl->getLinkTarget($this, "addTour")
            );
            $this->gui->toolbar()->addComponent($b);
            $b = $f->button()->standard(
                $lng->txt("gdtr_import_tour"),
                $ctrl->getLinkTarget($this, "importTourForm")
            );
            $this->gui->toolbar()->addComponent($b);
        }

        $items = [];
        $ui_items = [];
        foreach ($this->tm->getAll() as $tour) {
            $ctrl->setParameterByClass(self::class, "tour_id", $tour->getId());
            $actions = [];
            $actions[] = $f->link()->standard(
                $lng->txt("gdtr_edit_steps"),
                $ctrl->getLinkTargetByClass(self::class, "listSteps")
            );
            $actions[] = $f->link()->standard(
                $lng->txt("gdtr_tour_settings"),
                $ctrl->getLinkTargetByClass(self::class, "editSettings")
            );
            $reset_modal = $this->getResetTourModal($tour->getId());
            $actions[] = $f->button()->shy(
                $lng->txt("gdtr_reset_tour"),
                "#"
            )->withOnClick($reset_modal->getShowSignal());
            $ui_items[] = $reset_modal;
            $delete_modal = $this->getDeleteTourModal($tour->getId());
            $actions[] = $f->button()->shy(
                $lng->txt("gdtr_delete_tour"),
                "#"
            )->withOnClick($delete_modal->getShowSignal());
            $ui_items[] = $delete_modal;
            $properties = [];
            $settings = $this->domain->tourSettings()->getByObjId($tour->getId());
            $properties[$lng->txt("active")] = $settings?->isActive()
                ? $lng->txt("yes")
                : $lng->txt("no");
            if (!$this->edit) {
                $actions = [];
            }
            $items[] = $f->item()->standard($tour->getTitle())
                ->withActions($f->dropdown()->standard($actions))
                ->withProperties($properties);
        }
        if (count($items) > 0) {
            $grp = $f->item()->group("", $items);
            $panel = $f->panel()->listing()->standard(
                $lng->txt("gdtr_guided_tours"),
                [$grp]
            );
            $ui_items[] = $panel;
            $mt->setContent($r->render($ui_items));
        }
    }

    protected function getResetTourModal(int $tour_id): \ILIAS\UI\Component\Modal\Interruptive
    {
        $tour = $this->tm->getByObjId($tour_id);
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $res_items = [];
        $res_items[] = $f->modal()->interruptiveItem()->keyValue(
            (string) $tour_id,
            $tour->getTitle(),
            ""
        );
        $ctrl->setParameterByClass(self::class, "tour_id", $tour_id);
        $action = $ctrl->getLinkTargetByClass(self::class, "resetTour");

        return $f->modal()->interruptive(
            $lng->txt("gdtr_reset_tour"),
            $lng->txt("gdtr_reset_tour_mess"),
            $action
        )->withAffectedItems($res_items)
         ->withActionButtonLabel($lng->txt("gdtr_reset_tour"));
    }

    protected function resetTour(): void
    {
        if (!$this->edit) {
            return;
        }
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tour_id = $this->gui->standardRequest()->getTourId();
        $this->finish_manager->resetTour($tour_id);
        $mt->setOnScreenMessage("success", $lng->txt("gdtr_tour_has_been_reset"), true);
        $ctrl->redirectByClass(self::class, "listTours");
    }

    protected function getDeleteTourModal(int $tour_id): \ILIAS\UI\Component\Modal\Interruptive
    {
        $tour = $this->tm->getByObjId($tour_id);
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $del_items = [];
        $del_items[] = $f->modal()->interruptiveItem()->keyValue(
            (string) $tour_id,
            $tour->getTitle(),
            ""
        );
        $ctrl->setParameterByClass(self::class, "tour_id", $tour_id);
        $action = $ctrl->getLinkTargetByClass(self::class, "deleteTour");

        return $f->modal()->interruptive(
            $lng->txt("gdtr_delete_tour"),
            $lng->txt("gdtr_delete_tour_mess"),
            $action
        )->withAffectedItems($del_items);
    }

    protected function deleteTour(): void
    {
        if (!$this->edit) {
            return;
        }
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tour_id = $this->gui->standardRequest()->getTourId();
        $this->tm->deleteTour($tour_id);
        $mt->setOnScreenMessage("success", $lng->txt("gdtr_deleted_tour"), true);
        $ctrl->redirectByClass(self::class, "listTours");
    }

    protected function getCreateForm(): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        return $this->gui->form([self::class], "saveTour")
            ->section("sec", $lng->txt("gdtr_add_tour"))
            ->addStdTitleAndDescription(0, "gdtr");
    }

    protected function addTour(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getCreateForm()->render());
    }

    public function saveTour(): void
    {
        if (!$this->edit) {
            return;
        }
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $form = $this->getCreateForm();
        if ($form->isValid()) {
            $obj_id = $this->tm->createTour("dummy", "");
            $form->saveStdTitleAndDescription($obj_id, "gdtr");
            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "listTours");
        } else {
            $mt->setContent($form->render());
        }
    }

    protected function editSettings(): void
    {
        $this->setStepsHeader();
        $this->setSettingsTabs("settings");
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getSettingsForm()->render());
    }

    protected function getSettingsForm(): FormAdapterGUI
    {
        $tour_id = $this->gui->standardRequest()->getTourId();
        $lng = $this->domain->lng();
        $settings = $this->domain->tourSettings()->getByObjId($tour_id);
        $perm_val = (string) $settings?->getPermission()->value;
        if ($perm_val === "0") {
            $perm_val = "";
        }
        $lang_val = (string) $settings?->getLanguage();
        $lang_vals = $this->domain->tourSettings()->getLangOptions($lang_val);
        if ($perm_val === "0") {
            $perm_val = "";
        }
        return $this
            ->gui
            ->form([self::class], "saveSettings")
            ->section("sec", $lng->txt("settings"))
            ->addStdTitleAndDescription($tour_id, "gdtr")
            ->checkbox(
                "active",
                $lng->txt("gdtr_active"),
                "",
                $settings?->isActive()
            )
            ->section("sec2", $lng->txt("gdtr_presentation_limitation"))
            ->text(
                "screen_ids",
                $lng->txt("gdtr_screen_ids"),
                $lng->txt("gdtr_screen_ids_info"),
                $settings?->getScreenIds()
            )
            ->select(
                "permission",
                $lng->txt("gdtr_permission"),
                [
                    (string) PermissionType::Read->value => $lng->txt("read"),
                    (string) PermissionType::Write->value => $lng->txt("write"),
                    (string) PermissionType::Create->value => $lng->txt("create"),
                ],
                $lng->txt("gdtr_permission_info"),
                $perm_val
            )
            ->select(
                "lang",
                $lng->txt("gdtr_language"),
                $lang_vals,
                $lng->txt("gdtr_language_info"),
                $lang_val
            );

    }

    public function saveSettings(): void
    {
        if (!$this->edit) {
            return;
        }
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getSettingsForm();
        $tour_id = $this->gui->standardRequest()->getTourId();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $tour_settings = $this->domain->tourSettings();
        if ($form->isValid()) {
            $form->saveStdTitleAndDescription($tour_id, "gdtr");
            $tour_settings->save($this->data->settings(
                $tour_id,
                (bool) $form->getData("active"),
                $form->getData("screen_ids"),
                PermissionType::from((int) $form->getData("permission")),
                $form->getData("lang")
            ));
            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "editSettings");
        } else {
            $mt->setContent($form->render());
        }
    }


    protected function setStepsHeader(): void
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $mt = $this->gui->ui()->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $tour = $this->tm->getByObjId($this->gui->standardRequest()->getTourId());
        $mt->setTitle($lng->txt("guided_tour") . ": " . $tour?->getTitle());
        $mt->setDescription($tour?->getDescription());
        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(self::class, "listTours")
        );
    }

    protected function setSingleStepHeader(): void
    {
        $this->setStepsHeader();
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(self::class, "listSteps")
        );
    }

    protected function listSteps(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $table = $this->getStepTable();
        if ($table->handleCommand()) {
            return;
        }

        $this->setStepsHeader();
        $this->setSettingsTabs("steps");

        $b = $f->button()->standard(
            $lng->txt("gdtr_add_step"),
            $ctrl->getLinkTarget($this, "addStep")
        );
        $this->gui->toolbar()->addComponent($b);

        $mt->setContent($table->render());
    }

    protected function getStepTable(): \ILIAS\Repository\Table\TableAdapterGUI
    {
        return $this->gui->stepTableGUI(
            $this->gui->standardRequest()->getTourId(),
            $this,
            "listSteps"
        );
    }

    /*
    public function tableCommand(): void
    {
        $table = $this->getStepTable();
        $table->handleCommand();
    }*/

    protected function addStep(): void
    {
        $this->setSingleStepHeader();
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $mt->setOnScreenMessage("info", $lng->txt("gdtr_edit_step_info"));
        $mt->setContent($this->getStepForm()->render());
    }

    protected function getStepForm(?Step $step = null): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $type_val = (string) $step?->getType()->value;
        $mb_element_id = $step?->getType()->value === StepType::Mainbar->value
            ? $step?->getElementId()
            : null;
        $mt_element_id = $step?->getType()->value === StepType::Metabar->value
            ? $step?->getElementId()
            : null;
        $tab_element_id = $step?->getType()->value === StepType::Tab->value
            ? $step?->getElementId()
            : null;
        return $this->gui->form([self::class], "saveStep")
            ->section("sec", $lng->txt("gdtr_step"))
            ->switch("type", $lng->txt("gdtr_step_type"), "", $type_val)
            ->group((string) StepType::Mainbar->value, $lng->txt("gdtr_mainbar"), $lng->txt("gdtr_mainbar_info"))
            ->text("mb_element_id", $lng->txt("gdtr_element_id"), "", $mb_element_id)
            ->group((string) StepType::Metabar->value, $lng->txt("gdtr_metabar"), $lng->txt("gdtr_metabar_info"))
            ->text("mt_element_id", $lng->txt("gdtr_element_id"), "", $mt_element_id)
            ->group((string) StepType::Tab->value, $lng->txt("gdtr_tabs"), $lng->txt("gdtr_tabs_info"))
            ->text("tab_element_id", $lng->txt("gdtr_element_id"), "", $tab_element_id)
            ->group((string) StepType::Form->value, $lng->txt("gdtr_form"), $lng->txt("gdtr_form_info"))
            ->group((string) StepType::Table->value, $lng->txt("gdtr_table"), $lng->txt("gdtr_table_info"))
            ->group((string) StepType::Toolbar->value, $lng->txt("gdtr_toolbar"), $lng->txt("gdtr_toolbar_info"))
            ->group((string) StepType::PrimaryButton->value, $lng->txt("gdtr_primary_button"), $lng->txt("gdtr_primary_button_info"))
            ->end();
    }

    public function saveStep(): void
    {
        if (!$this->edit) {
            return;
        }
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();
        $oder_nr = 0;
        if (($step_id = $this->gui->standardRequest()->getStepId()) > 0) {
            $step = $this->step_manager->getById($step_id);
            $oder_nr = $step->getOrderNr();
        }
        $form = $this->getStepForm();
        if ($form->isValid()) {
            $element_id = match ((int) $form->getData("type")) {
                StepType::Mainbar->value => $form->getData("mb_element_id"),
                StepType::Metabar->value => $form->getData("mt_element_id"),
                StepType::Tab->value => $form->getData("tab_element_id"),
                default => ''
            };
            $step = $this->data->step(
                $step_id,
                $this->gui->standardRequest()->getTourId(),
                $oder_nr,
                StepType::from((int) $form->getData("type")),
                $element_id
            );
            if ($step_id > 0) {
                $this->step_manager->update($step);
                $ctrl->redirectByClass(self::class, "listSteps");
            } else {
                $new_id = $this->step_manager->create($step);
                $ctrl->setParameterByClass(self::class, "step_id", $new_id);
                $ctrl->redirectByClass(ilGuidedTourPageGUI::class, "edit");
            }
        } else {
            $mt->setContent($form->render());
        }
    }

    public function editStep(int $step_id): void
    {
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $this->setSingleStepHeader();
        $mt->setOnScreenMessage("info", $lng->txt("gdtr_edit_step_info"));
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass(self::class, "step_id", $step_id);
        $step = $this->step_manager->getById($step_id);
        $form = $this->getStepForm($step);
        $mt->setContent($form->render());
    }

    public function editPage(int $step_id): void
    {
        if (!$this->edit) {
            return;
        }
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass(self::class, "step_id", $step_id);
        $ctrl->redirectByClass(ilGuidedTourPageGUI::class, "edit");
    }

    protected function setSubTabs(string $active): void
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tabs->addSubTab(
            "tours",
            $lng->txt("gdtr_tours"),
            $ctrl->getLinkTargetByClass(self::class, "listTours")
        );
        $tabs->addSubTab(
            "id_settings",
            $lng->txt("gdtr_id_settings"),
            $ctrl->getLinkTargetByClass(self::class, "idSettings")
        );
        $tabs->activateSubTab($active);
    }

    protected function idSettings(): void
    {
        $this->setSubTabs("id_settings");
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getIdForm()->render());
    }

    protected function getIdForm(): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $id_pres = $this->domain->idPresentation();
        $form = $this
            ->gui
            ->form([self::class], "saveIdSettings")
            ->section("sec", $lng->txt("gdtr_id_settings"))
            ->text(
                "users",
                $lng->txt("gdtr_id_pres_users"),
                $lng->txt("gdtr_id_pres_users_info"),
                $id_pres->getIdPresentationUsers()
            );
        if (!$this->edit) {
            $form = $form->disabled();
        }
        return $form;
    }

    protected function saveIdSettings(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $form = $this->getIdForm();
        if ($this->edit) {
            $id_pres = $this->domain->idPresentation();
            $id_pres->saveIdPresentationUsers($form->getData("users"));
            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirectByClass(self::class, "idSettings");
    }

    protected function saveOrder(): void
    {
        if (!$this->edit) {
            return;
        }
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl->saveParameterByClass(self::class, "tour_id");

        $table = $this->getStepTable();
        $data = $table->getData();
        if (is_array($data)) {
            $this->step_manager->saveOrder(
                $this->gui->standardRequest()->getTourId(),
                $data
            );
            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirectByClass(self::class, "listSteps");
    }

    public function confirmStepDeletion(int $step_id): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $table = $this->getStepTable();
        $step = $this->step_manager->getById($step_id);
        $ctrl->setParameterByClass(self::class, "step_id", $step_id);
        $title = $this->step_manager->getStepName($step->getType());
        if ($step->getElementId() !== "") {
            $title .= " (" . $step->getElementId() . ")";
        }
        $table->renderDeletionConfirmation(
            $lng->txt("gdtr_delete_step"),
            $lng->txt("gdtr_delete_step_mess"),
            "deleteStep",
            [
                $step_id => $title
            ]
        );
    }

    public function deleteStep(): void
    {
        if (!$this->edit) {
            return;
        }
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $tour_id = $this->gui->standardRequest()->getTourId();
        $step_id = $this->gui->standardRequest()->getStepId();

        $this->step_manager->delete($tour_id, $step_id);
        $mt->setOnScreenMessage("success", $lng->txt("gdtr_deleted_step"), true);
        $ctrl->redirectByClass(self::class, "listSteps");
    }

    protected function importTourForm(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getImportForm()->render());
    }

    protected function getImportForm(): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        return $this->gui->form([self::class], "importTour")
            ->section("sec", $lng->txt("gdtr_import_tour"))
            ->file(
                "import",
                $lng->txt("import_file"),
                $this->handleImportUpload(...),
                "id",
                "",
                1,
                ["application/zip"]
            );
    }

    protected function handleImportUpload(
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult {
        $new_id = 0;
        if ($this->edit) {
            $new_id = $this->importTourFile($result->getName(), $result->getPath());
        }
        return new BasicHandlerResult(
            '',
            \ILIAS\FileUpload\Handler\HandlerResult::STATUS_OK,
            (string) $new_id,
            ''
        );
    }


    protected function importTourFile(
        string $filename,
        string $path
    ): int {
        $new_id = 0;
        if (!$this->edit) {
            return $new_id;
        }
        $fname = explode("_", $filename);
        if ($fname[4] == "gdtr") {
            $imp = new ilImport();
            $new_id = $imp->importObject(
                null,
                $path,
                $filename,
                "gdtr",
                "",
                true
            );
        }
        return $new_id;
    }

    protected function importTour(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->redirectByClass(self::class, "listTours");
    }
}
