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

namespace ILIAS\COPage\Editor\Components\Page;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;
use ILIAS\COPage\Editor\Components\Paragraph\ParagraphStyleSelector;
use ILIAS\COPage\Editor\Components\Section\SectionStyleSelector;
use ILIAS\COPage\Editor\Components\MediaObject\MediaObjectStyleSelector;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageQueryActionHandler implements Server\QueryActionHandler
{
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilPageObjectGUI $page_gui;
    protected \ilObjUser $user;
    protected Server\UIWrapper $ui_wrapper;
    protected \ilCtrl $ctrl;
    protected \ilComponentFactory $component_factory;

    public function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->component_factory = $DIC["component.factory"];

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    /**
     * @throws Exception
     */
    public function handle(array $query): Server\Response
    {
        switch ($query["action"]) {
            case "ui.all":
                return $this->allCommand();

            case "component.edit.form":
                return $this->componentEditFormResponse($query);
        }
        throw new Exception("Unknown action " . $query["action"]);
    }

    protected function allCommand(): Server\Response
    {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();
        $dd = $f->dropdown()->standard([
            $f->link()->standard("label", "#")
        ]);
        $r = $this->ui->renderer();
        $o = new \stdClass();
        $o->dropdown = $r->render($dd);
        $o->addCommands = $this->getAddCommands();
        $o->pageEditHelp = $this->getPageEditHelp();
        $o->multiEditHelp = $this->getMultiEditHelp();
        $o->pageTopActions = $this->getTopActions();
        $o->multiActions = $this->getMultiActions();
        $o->pasteMessage = $this->getPasteMessage();
        $o->errorMessage = $this->getErrorMessage();
        $o->errorModalMessage = $this->getErrorModalMessage();
        $o->config = $this->getConfig();
        $o->components = $this->getComponentsEditorUI();
        $o->pcModel = $this->getPCModel();
        $o->pcDefinition = $this->getComponentsDefinitions();
        $o->formatSelection = $this->getFormatSelection();
        $o->modal = $this->getModalTemplate();
        $o->confirmation = $this->getConfirmationTemplate();
        $o->autoSaveInterval = $this->getAutoSaveInterval();
        $o->backUrl = $ctrl->getLinkTarget($this->page_gui, "edit");
        $o->pasting = in_array(\ilEditClipboard::getAction(), ["copy", "cut"]) &&
            count($this->user->getPCClipboardContent()) > 0;
        $o->loaderUrl = \ilUtil::getImagePath("loader.svg");
        return new Server\Response($o);
    }

    protected function getConfig(): \stdClass
    {
        $config = new \stdClass();
        $config->user = $this->user->getLogin();
        $config->content_css =
            \ilObjStyleSheet::getContentStylePath($this->page_gui->getStyleId()) . ", " .
            \ilUtil::getStyleSheetLocation() . ", " .
            "./Services/COPage/css/tiny_extra.css";
        $config->text_formats = \ilPCParagraphGUI::_getTextCharacteristics($this->page_gui->getStyleId());
        $config->editPlaceholders = $this->page_gui->getPageConfig()->getEnablePCType("PlaceHolder");
        $config->activatedProtection =
            ($this->page_gui->getPageConfig()->getSectionProtection() == \ilPageConfig::SEC_PROTECT_PROTECTED);

        return $config;
    }

    protected function getAddCommands(): array
    {
        $lng = $this->lng;

        $commands = [];

        // content types
        $config = $this->page_gui->getPageConfig();
        foreach ($config->getEnabledTopPCTypes() as $def) {
            $commands[$def["pc_type"]] = $lng->txt("cont_ed_insert_" . $def["pc_type"]);
        }

        // content templates
        if (count($this->page_gui->getPageObject()->getContentTemplates()) > 0) {
            $commands["templ"] = $lng->txt("cont_ed_insert_templ");
        }

        // plugins
        foreach ($this->component_factory->getActivePluginsInSlot("pgcp") as $plugin) {
            $commands["plug_" . $plugin->getPluginName()] =
                $plugin->txt(\ilPageComponentPlugin::TXT_CMD_INSERT);
        }
        return $commands;
    }

    /**
     * Get page help (general)
     */
    protected function getPageEditHelp(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.page_edit_help.html", true, true, "Services/COPage/Editor");
        $tpl->setCurrentBlock("help");
        $tpl->setVariable("TXT_ADD_EL", $lng->txt("cont_add_elements"));
        $tpl->setVariable("PLUS", \ilGlyphGUI::get(\ilGlyphGUI::ADD));
        $tpl->setVariable("DRAG_ARROW", \ilGlyphGUI::get(\ilGlyphGUI::DRAG));
        $tpl->setVariable("TXT_DRAG", $lng->txt("cont_drag_and_drop_elements"));
        $tpl->setVariable("TXT_EDIT", $lng->txt("cont_click_edit"));
        $tpl->setVariable("TXT_SEL", $lng->txt("cont_shift_click_to_select"));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Get page help (multi editing)
     */
    protected function getMultiEditHelp(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.page_edit_help.html", true, true, "Services/COPage/Editor");
        $tpl->setCurrentBlock("multi-help");
        $tpl->setVariable("TXT_SEL", $lng->txt("cont_click_multi_select"));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function getTopActions(): string
    {
        $ui = $this->ui;
        $ctrl = $this->ctrl;

        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.top_actions.html", true, true, "Services/COPage/Editor");

        $dd = $this->getActionsDropDown();
        $tpl->setVariable("DROPDOWN", $ui->renderer()->renderAsync($dd));

        if ($this->page_gui->getPageObject()->getEffectiveEditLockTime() > 0) {
            $mess = $this->page_gui->getBlockingInfoMessage();
            $tpl->setVariable("MESSAGE", $mess);
            $b = $ui->factory()->button()->standard(
                $lng->txt("cont_finish_editing"),
                $ctrl->getLinkTarget($this->page_gui, "releasePageLock")
            );
        } else {
            $b = $ui->factory()->button()->standard(
                $lng->txt("cont_finish_editing"),
                $ctrl->getLinkTarget($this->page_gui, "finishEditing")
            );
        }
        $tpl->setVariable("MESSAGE2", $this->getMultiLangInfo());
        $tpl->setVariable("QUIT_BUTTON", $ui->renderer()->renderAsync($b));

        $html = $this->ui_wrapper->getRenderedViewControl(
            [
                ["Page", "switch.single", $lng->txt("cont_edit_comp")],
                ["Page", "switch.multi", $lng->txt("cont_edit_multi")]
            ]
        );
        $tpl->setVariable("SWITCH", $html);
        $tpl->setVariable("SRC_LOADER", \ilUtil::getImagePath("loader.svg"));

        return $tpl->get();
    }

    public function getActionsDropDown(): \ILIAS\UI\Component\Dropdown\Standard
    {
        $ui = $this->ui;
        $user = $this->user;
        $config = $this->page_gui->getPageConfig();
        $page = $this->page_gui->getPageObject();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        // determine media, html and javascript mode
        $sel_media_mode = ($user->getPref("ilPageEditor_MediaMode") != "disable");
        $sel_html_mode = ($user->getPref("ilPageEditor_HTMLMode") != "disable");
        $items = [];

        // activate/deactivate
        if ($config->getEnableActivation()) {
            $captions = $this->page_gui->getActivationCaptions();

            if ($page->getActive()) {
                $items[] = $ui->factory()->link()->standard(
                    $captions["deactivatePage"],
                    $ctrl->getLinkTarget($this->page_gui, "deactivatePage")
                );
            } else {
                $items[] = $ui->factory()->link()->standard(
                    $captions["activatePage"],
                    $ctrl->getLinkTarget($this->page_gui, "activatePage")
                );
            }
        }

        // initially opened content
        if ($config->getUseAttachedContent()) {
            $items[] = $ui->factory()->link()->standard(
                $lng->txt("cont_initial_attached_content"),
                $ctrl->getLinkTarget($this->page_gui, "initialOpenedContent")
            );
        }

        // multi-lang actions
        foreach ($this->getMultiLangActions() as $item) {
            $items[] = $item;
        }

        $lng->loadLanguageModule("content");

        // media mode
        if ($sel_media_mode) {
            $ctrl->setParameter($this->page_gui, "media_mode", "disable");
            $items[] = $ui->factory()->link()->standard(
                $lng->txt("cont_deactivate_media"),
                $ctrl->getLinkTarget($this->page_gui, "setEditMode")
            );
        } else {
            $ctrl->setParameter($this->page_gui, "media_mode", "enable");
            $items[] = $ui->factory()->link()->standard(
                $lng->txt("cont_activate_media"),
                $ctrl->getLinkTarget($this->page_gui, "setEditMode")
            );
        }
        $ctrl->setParameter($this, "media_mode", "");

        // html mode
        if (!$config->getPreventHTMLUnmasking()) {
            if ($sel_html_mode) {
                $ctrl->setParameter($this->page_gui, "html_mode", "disable");
                $items[] = $ui->factory()->link()->standard(
                    $lng->txt("cont_deactivate_html"),
                    $ctrl->getLinkTarget($this->page_gui, "setEditMode")
                );
            } else {
                $ctrl->setParameter($this->page_gui, "html_mode", "enable");
                $items[] = $ui->factory()->link()->standard(
                    $lng->txt("cont_activate_html"),
                    $ctrl->getLinkTarget($this->page_gui, "setEditMode")
                );
            }
        }
        $ctrl->setParameter($this->page_gui, "html_mode", "");

        $lm_set = new \ilSetting("lm");
        if ($this->page_gui->getEnableEditing() && $this->user->getId() != ANONYMOUS_USER_ID) {
            // history
            if ($lm_set->get("page_history", 1)) {
                $items[] = $ui->factory()->link()->standard(
                    $lng->txt("history"),
                    $ctrl->getLinkTarget($this->page_gui, "history")
                );
            }

            if ($config->getEnableScheduledActivation()) {
                $items[] = $ui->factory()->link()->standard(
                    $lng->txt("cont_activation"),
                    $ctrl->getLinkTarget($this->page_gui, "editActivation")
                );
            }

            // clipboard
            $items[] = $ui->factory()->link()->standard(
                $lng->txt("clipboard"),
                $ctrl->getLinkTargetByClass([get_class($this->page_gui), "ilEditClipboardGUI"], "view")
            );
        }


        // additional page actions
        foreach ($this->page_gui->getAdditionalPageActions() as $item) {
            $items[] = $item;
        }

        return $ui->factory()->dropdown()->standard($items);
    }

    /**
     * Add multi-language actions to menu
     */
    public function getMultiLangActions(): array
    {
        $config = $this->page_gui->getPageConfig();
        $page = $this->page_gui->getPageObject();
        $ctrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $items = [];


        // general multi lang support and single page mode?
        if ($config->getMultiLangSupport()) {
            $ot = \ilObjectTranslation::getInstance($page->getParentId());

            if ($ot->getContentActivated()) {
                $lng->loadLanguageModule("meta");

                if ($page->getLanguage() != "-") {
                    $l = $ot->getMasterLanguage();
                    $items[] = $ui->factory()->link()->standard(
                        $lng->txt("cont_edit_language_version") . ": " .
                        $lng->txt("meta_l_" . $l),
                        $ctrl->getLinkTarget($this->page_gui, "editMasterLanguage")
                    );
                }

                foreach ($ot->getLanguages() as $al => $lang) {
                    if ($page->getLanguage() != $al &&
                        $al != $ot->getMasterLanguage()) {
                        $ctrl->setParameter($this->page_gui, "totransl", $al);
                        $items[] = $ui->factory()->link()->standard(
                            $lng->txt("cont_edit_language_version") . ": " .
                            $lng->txt("meta_l_" . $al),
                            $ctrl->getLinkTarget($this->page_gui, "switchToLanguage")
                        );
                        $ctrl->setParameter($this->page_gui, "totransl", "");
                    }
                }
            }
        }

        return $items;
    }

    public function getMultiLangInfo(): string
    {
        $info = "";

        $config = $this->page_gui->getPageConfig();
        $page = $this->page_gui->getPageObject();
        $lng = $this->lng;
        $ui = $this->ui;

        // general multi lang support and single page mode?
        if ($config->getMultiLangSupport()) {
            $ot = \ilObjectTranslation::getInstance($page->getParentId());

            if ($ot->getContentActivated()) {
                $lng->loadLanguageModule("meta");

                $ml_gui = new \ilPageMultiLangGUI(
                    $page->getParentType(),
                    $page->getParentId()
                );
                $info = $ml_gui->getMultiLangInfo($page->getLanguage());
                $info = $ui->renderer()->renderAsync($ui->factory()->messageBox()->info($info));
            }
        }

        return $info;
    }

    protected function getMultiActions(): string
    {
        $groups = [
            [
                "cut" => "cut",
                "copy" => "copy",
                "delete" => "delete"
            ],
            [
                "all" => "select_all",
                "none" => "cont_select_none",
            ],
            [
                "activate" => "cont_ed_enable",
                "characteristic" => "cont_assign_characteristic"
            ]
        ];

        return $this->ui_wrapper->getRenderedButtonGroups($groups);
    }

    /**
     * Confirmation screen for cut/paste step
     */
    protected function getPasteMessage(): string
    {
        $lng = $this->lng;

        $html = $this->ui_wrapper->getRenderedInfoBox($lng->txt("cont_sel_el_use_paste"));

        return $html;
    }

    /**
     * Confirmation screen for cut/paste step
     */
    protected function getErrorMessage(): string
    {
        $html = $this->ui_wrapper->getRenderedFailureBox();

        return $html;
    }

    protected function getErrorModalMessage(): string
    {
        $html = $this->ui_wrapper->getRenderedModalFailureBox();

        return $html;
    }

    protected function getFormatSelection(): string
    {
        $lng = $this->lng;
        $ui = $this->ui;
        $tpl = new \ilTemplate("tpl.format_selection.html", true, true, "Services/COPage/Editor");
        $tpl->setVariable("TXT_PAR", $lng->txt("cont_choose_characteristic_text"));
        $tpl->setVariable("TXT_SECTION", $lng->txt("cont_choose_characteristic_section"));
        $tpl->setVariable("TXT_MEDIA", $lng->txt("cont_media"));

        $par_sel = new ParagraphStyleSelector($this->ui_wrapper, $this->page_gui->getStyleId());
        $tpl->setVariable("PAR_SELECTOR", $ui->renderer()->renderAsync($par_sel->getStyleSelector("", "format", "format.paragraph", "format")));

        $sec_sel = new SectionStyleSelector($this->ui_wrapper, $this->page_gui->getStyleId());
        $tpl->setVariable("SEC_SELECTOR", $ui->renderer()->renderAsync($sec_sel->getStyleSelector("", "format", "format.section", "format")));

        $med_sel = new MediaObjectStyleSelector($this->ui_wrapper, $this->page_gui->getStyleId());
        $tpl->setVariable("MEDIA_SELECTOR", $ui->renderer()->renderAsync($med_sel->getStyleSelector("", "format", "format.media", "format")));

        $tpl->setVariable(
            "SAVE_BUTTON",
            $this->ui_wrapper->getRenderedButton(
                $lng->txt("save"),
                "format",
                "format.save"
            )
        );
        $tpl->setVariable(
            "CANCEL_BUTTON",
            $this->ui_wrapper->getRenderedButton(
                $lng->txt("cancel"),
                "format",
                "format.cancel"
            )
        );
        return $tpl->get();
    }


    /**
     * Get page component model
     */
    protected function getPCModel(): array
    {
        return $this->page_gui->getPageObject()->getPCModel();
    }

    protected function componentEditFormResponse(array $query): Server\Response
    {
        $pc_edit = \ilCOPagePCDef::getPCEditorInstanceByName($query["cname"]);
        $form = "";
        if (!is_null($pc_edit)) {
            $form = $pc_edit->getEditComponentForm(
                $this->ui_wrapper,
                $this->page_gui->getPageObject()->getParentType(),
                $this->page_gui,
                $this->page_gui->getStyleId(),
                $query["pcid"]
            );
        }
        $o = new \stdClass();
        $o->editForm = $form;
        return new Server\Response($o);
    }

    /**
     * Get components ui elements
     */
    protected function getComponentsEditorUI(): array
    {
        $ui = [];
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pc_edit = \ilCOPagePCDef::getPCEditorInstanceByName($def["name"]);
            if (!is_null($pc_edit)) {
                $ui[$def["name"]] = $pc_edit->getEditorElements(
                    $this->ui_wrapper,
                    $this->page_gui->getPageObject()->getParentType(),
                    $this->page_gui,
                    $this->page_gui->getStyleId()
                );
            }
        }
        return $ui;
    }

    protected function getComponentsDefinitions(): array
    {
        $pcdef = [];
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pcdef["types"][$def["name"]] = $def["pc_type"];
            $pcdef["names"][$def["pc_type"]] = $def["name"];
            $pcdef["txt"][$def["pc_type"]] = $this->lng->txt("cont_" . "pc_" . $def["pc_type"]);
        }
        return $pcdef;
    }

    public function getModalTemplate(): array
    {
        $ui = $this->ui;
        $modal = $ui->factory()->modal()->roundtrip('#title#', $ui->factory()->legacy('#content#'))
                    ->withActionButtons([
                        $ui->factory()->button()->standard('#button_title#', '#'),
                    ]);
        $modalt["signal"] = $modal->getShowSignal()->getId();
        $modalt["template"] = $ui->renderer()->renderAsync($modal);

        return $modalt;
    }

    /**
     * Get confirmation template
     */
    public function getConfirmationTemplate(): string
    {
        $ui = $this->ui;

        $confirmation = $ui->factory()->messageBox()->confirmation("#text#");

        return $ui->renderer()->renderAsync($confirmation);
    }

    /**
     * Get auto save interval
     */
    protected function getAutoSaveInterval(): int
    {
        $aset = new \ilSetting("adve");
        return (int) $aset->get("autosave");
    }
}
