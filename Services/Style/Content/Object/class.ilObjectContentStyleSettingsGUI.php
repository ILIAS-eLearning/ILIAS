<?php

declare(strict_types=1);

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

use ILIAS\Style\Content\Object\ObjectManager;
use ILIAS\Style\Content\Container\ContainerManager;
use ILIAS\Style\Content\InternalDomainService;
use ILIAS\Style\Content\InternalGUIService;

/**
 * Style settings of a repository object
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjectContentStyleSettingsGUI: ilObjStyleSheetGUI
 */
class ilObjectContentStyleSettingsGUI
{
    protected ObjectManager $object_manager;
    protected ContainerManager $container_manager;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected int $current_style_id;
    protected int $ref_id;
    protected int $obj_id;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service,
        ?int $current_style_id,
        int $ref_id,
        int $obj_id
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->domain = $domain_service;
        $this->gui = $gui_service;
        $this->ref_id = $ref_id;
        $this->obj_id = ($obj_id > 0)
            ? $obj_id
            : ilObject::_lookupObjId($ref_id);
        $this->container_manager = $domain_service->repositoryContainer($ref_id);
        $this->object_manager = $domain_service->object($ref_id, $this->obj_id);
        $this->current_style_id = $current_style_id ?? $this->object_manager->getStyleId();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("settings");

        switch ($next_class) {

            case "ilobjstylesheetgui":
                $this->gui->tabs()->clearTargets();
                $ctrl->setReturn($this, "settings");
                $this->forwardToStyleSheet();
                break;

            default:
                if (in_array($cmd, [
                    "settings",
                    "editStyle",
                    "updateStyle",
                    "createStyle",
                    "deleteStyle",
                    "saveStyleSettings",
                    "saveIndividualStyleSettings"
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function settings(): void
    {
        $mt = $this->gui->mainTemplate();
        $form = $this->initStylePropertiesForm();
        $mt->setContent(
            $form->getHTML()
        );
    }

    /**
     * Init style properties form
     */
    public function initStylePropertiesForm(): ilPropertyFormGUI
    {
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $tabs = $this->gui->tabs();
        $settings = $this->domain->settings();
        $mt = $this->gui->mainTemplate();

        $style_id = $this->current_style_id;
        /*
        if (ilObject::_lookupType($style_id) == "sty") {
            $page_gui->setStyleId($style_id);
        } else {
            $style_id = 0;
        }

        $page_gui->setTabHook($this, "addPageTabs");
        $ilCtrl->getHTML($page_gui);
        $ilTabs->setTabActive("obj_sty");*/

        $lng->loadLanguageModule("style");

        $form = new ilPropertyFormGUI();
        $fixed_style = (int) $settings->get("fixed_content_style_id");
        if ($fixed_style > 0) {
            $st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
            $st->setValue(ilObject::_lookupTitle($fixed_style) . " (" .
                $lng->txt("global_fixed") . ")");
            $form->addItem($st);
        } else {
            $st_styles = $this->object_manager->getSelectableStyles();

            $st_styles[0] = $lng->txt("default");
            ksort($st_styles);

            if ($style_id > 0) {
                // individual style
                if ($this->object_manager->isOwned($style_id)) {
                    $st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
                    $st->setValue(ilObject::_lookupTitle($style_id));
                    $form->addItem($st);

                    if ($this->isContainer()) {
                        $cb = new ilCheckboxInputGUI($lng->txt("style_support_reuse"), "support_reuse");
                        $cb->setInfo($lng->txt("style_support_reuse_info"));
                        $cb->setChecked($this->container_manager->getReuse());
                        $form->addItem($cb);
                        $form->addCommandButton(
                            "saveIndividualStyleSettings",
                            $lng->txt("save")
                        );
                    }

                    $form->addCommandButton(
                        "editStyle",
                        $lng->txt("style_edit_style")
                    );
                    $form->addCommandButton(
                        "deleteStyle",
                        $lng->txt("style_delete_style")
                    );
                }
            }

            if ($style_id <= 0 || !$this->object_manager->isOwned($style_id)) {
                $style_sel = new ilSelectInputGUI(
                    $lng->txt("style_current_style"),
                    "style_id"
                );
                $style_sel->setOptions($st_styles);
                $style_sel->setValue($style_id);
                $form->addItem($style_sel);
                $form->addCommandButton(
                    "saveStyleSettings",
                    $lng->txt("save")
                );
                $form->addCommandButton(
                    "createStyle",
                    $lng->txt("sty_create_ind_style")
                );
            }
        }
        $form->setTitle($lng->txt("obj_sty"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    protected function isContainer(): bool
    {
        if ($this->ref_id > 0) {
            $type = ilObject::_lookupType($this->ref_id, true);
            if ($this->domain->objectDefinition()->isContainer($type)) {
                return true;
            }
        }
        return false;
    }

    public function forwardToStyleSheet()
    {
        $ctrl = $this->gui->ctrl();
        $cmd = $ctrl->getCmd();

        $style_gui = new ilObjStyleSheetGUI(
            "",
            $this->current_style_id,
            false
        );
        $style_id = $ctrl->forwardCommand($style_gui);
        if (in_array($cmd, ["save", "copyStyle", "importStyle", "confirmedDelete"])) {
            $style_id = $style_gui->getObject()->getId();
            if ($cmd == "confirmedDelete") {
                $style_id = 0;
            } else {
                $this->setOwnerId($style_id);
            }
            $this->updateStyleId($style_id);
            $ctrl->redirect($this, "settings");
        }
    }

    protected function updateStyleId(int $style_id): void
    {
        $this->object_manager->updateStyleId($style_id);
    }

    protected function setOwnerId(int $style_id): void
    {
        $this->object_manager->setOwnerOfStyle($style_id);
    }

    public function createStyle(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->redirectByClass("ilobjstylesheetgui", "create");
    }

    public function editStyle(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->redirectByClass("ilobjstylesheetgui", "edit");
    }

    public function deleteStyle(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->redirectByClass("ilobjstylesheetgui", "delete");
    }

    /**
     * Save style settings
     */
    protected function saveStyleSettings(): void
    {
        $settings = $this->domain->settings();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $form = $this->initStylePropertiesForm();
        $form->checkInput();
        if ($settings->get("fixed_content_style_id") <= 0 &&
            (ilObjStyleSheet::_lookupStandard($this->current_style_id)
                || $this->current_style_id == 0)) {
            $style_id = (int) $form->getInput("style_id");
            $this->updateStyleId($style_id);
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirect($this, "settings");
    }

    protected function saveIndividualStyleSettings(): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $form = $this->initStylePropertiesForm();
        $form->checkInput();
        if ($this->isContainer()) {
            $this->container_manager->saveReuse($form->getInput("support_reuse"));
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirect($this, "settings");
    }
}
