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

use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCDataTableEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    protected \ILIAS\COPage\InternalGUIService $gui;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->gui = $DIC->copage()->internal()->gui();
    }

    public function getEditorElements(
        UIWrapper $ui_wrapper,
        string $page_type,
        ilPageObjectGUI $page_gui,
        int $style_id
    ): array {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $acc = new ilAccordionGUI();
        $acc->addItem($lng->txt("cont_set_manuall"), $this->getCreationForm($page_gui, $ui_wrapper, $style_id));
        $acc->addItem($lng->txt("cont_paste_from_spreadsheet"), $this->getImportForm($page_gui, $ui_wrapper, $style_id));
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        $form = $this->getCreationForm($page_gui, $ui_wrapper, $style_id);
        return [
            "creation_form" => $acc->getHTML(true),
            "icon" => $ui_wrapper->getRenderedIcon("pedt"),
            "top_actions" => $this->getTopActions($ui_wrapper, $page_gui),
            "cell_info" => $this->getCellInfo(),
            "cell_actions" => $this->getCellActions($ui_wrapper, $page_gui, $style_id),
            "merge_actions" => $this->getMergeActions($ui_wrapper, $page_gui, $style_id),
            "number_input_modal" => $this->getModalNumberInputTemplate()
        ];
    }

    protected function getCreationForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ): string {
        $lng = $this->lng;

        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), null, "", "");
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }

    protected function getEditForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id,
        string $pcid
    ): string {
        $lng = $this->lng;

        /** @var ilPCDataTable $tab */
        $tab = $page_gui->getPageObject()->getContentObjectForPcId($pcid);
        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), $tab, "", $pcid);
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initEditingForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.update.back", $lng->txt("save")],
                /*["Page", "component.back", $lng->txt("cancel")]*/
            ]
        );

        return $html;
    }


    protected function getImportForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ): string {
        $lng = $this->lng;

        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), null, "", "");
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initImportForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }

    protected function getTopActions(UIWrapper $ui_wrapper, ilPageObjectGUI $page_gui): string
    {
        $ui = $this->ui;
        $ctrl = $this->ctrl;

        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.table_top_actions.html", true, true, "Services/COPage/Editor");

        $quit_button = $ui_wrapper->getRenderedButton(
            $lng->txt("cont_finish_table_editing"),
            "button",
            "component.back",
            null,
            "Table",
            true
        );

        /*
        $b = $ui->factory()->button()->primary(
            $lng->txt("cont_finish_table_editing"),
            $ctrl->getLinkTarget($page_gui, "edit")
        );*/

        //$tpl->setVariable("QUIT_BUTTON", $ui->renderer()->renderAsync($b));
        $tpl->setVariable("QUIT_BUTTON", $quit_button);

        $html = $ui_wrapper->getRenderedViewControl(
            [
                ["Table", "switch.edit.table", $lng->txt("cont_edit_table")],
                ["Table", "switch.format.cells", $lng->txt("cont_format_cells")],
                ["Table", "switch.merge.cells", $lng->txt("cont_merge_cells")]
            ]
        );
        $tpl->setVariable("SWITCH", $html);

        return $tpl->get();
    }

    public function getEditComponentForm(
        UIWrapper $ui_wrapper,
        string $page_type,
        \ilPageObjectGUI $page_gui,
        int $style_id,
        string $pcid
    ) : string {
        return $this->getTopActions($ui_wrapper, $page_gui) .
            $this->getEditForm($page_gui, $ui_wrapper, $style_id, $pcid) .
            $this->getAdvancedSettingsLink($page_gui, $pcid);
    }

    protected function getAdvancedSettingsLink(
        \ilPageObjectGUI $page_gui,
        string $pcid):string
    {
        $page = $page_gui->getPageObject();
        /** @var \ilPCDataTable $tab */
        $tab = $page->getContentObjectForPcId($pcid);
        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), $tab, "", $pcid);
        $link = $this->ui->factory()->button()->shy(
            $this->lng->txt("cont_table_adv_settings"),
            $this->ctrl->getLinkTarget($tab_gui, "editProperties")
        );
        return $this->ui->renderer()->renderAsync($link);
    }

    protected function getCellInfo() : string
    {
        return "<div id='ilPageEditLegend' class='subtitle'>".
            "<p>".$this->lng->txt("cont_table_cell_edit_info_1")."</p>".
            "<p>".$this->lng->txt("cont_table_cell_edit_info_2")."</p>".
            "<p>".$this->lng->txt("cont_table_cell_edit_info_3")."</p>".
            "</div>";
    }

    protected function getCellActions(
        UIWrapper $ui_wrapper,
        ilPageObjectGUI $page_gui,
        int $style_id = 0) : string
    {
        $lng = $this->lng;

        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), null, "", "");
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initCellPropertiesForm();

        /*
        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Table", "toggle.merge", $lng->txt("cont_merge_cells")],
                ["Table", "properties.set", $lng->txt("cont_set_properties")],
                ["Page", "component.back", $lng->txt("cancel")]
            ]
        );*/

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Table", "properties.set", $lng->txt("cont_set_properties")],
            ]
        );

        return $html;
    }

    protected function getMergeActions(
        UIWrapper $ui_wrapper,
        ilPageObjectGUI $page_gui,
        int $style_id = 0) : string
    {
        $lng = $this->lng;

        $html = $ui_wrapper->getRenderedButton(
            $lng->txt("cont_merge_cells"),
            "button",
            "toggle.merge",
            null,
            "Table"
        );

        return '<div class="copg-edit-button-group">'.$html.'</div>';
    }

    public function getModalNumberInputTemplate(): array
    {
        $form = $this
            ->gui
            ->form(["ilPCDataTableGUI"], "#")
            ->select("number", "#select-title#", [
                "1" => "1",
                "2" => "2",
                "3" => "3",
                "4" => "4",
                "5" => "5",
                "6" => "6",
                "7" => "7",
                "8" => "8",
                "9" => "9",
                "10" => "10"
            ],
            "",
            "1")
            ->required();
        $components = $this
            ->gui
            ->modal("#modal-title#")
            ->form($form, "#on-form-submit-click#")->getTriggerButtonComponents(
                "#button-title#",
                true
            );

        $modalt["signal"] = $components["modal"]->getShowSignal()->getId();
        $modalt["modal"] = $this->ui->renderer()->renderAsync($components["modal"]);
        $modalt["button"] = $this->ui->renderer()->renderAsync($components["button"]);

        return $modalt;
    }

}
