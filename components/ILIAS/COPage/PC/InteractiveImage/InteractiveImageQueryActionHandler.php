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

namespace ILIAS\COPage\PC\InteractiveImage;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InteractiveImageQueryActionHandler implements Server\QueryActionHandler
{
    protected \ILIAS\COPage\PC\InteractiveImage\IIMManager $iim_manager;
    protected \ILIAS\COPage\InternalGUIService $gui;
    protected string $pc_id = "";
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilPageObjectGUI $page_gui;
    protected \ilObjUser $user;
    protected Server\UIWrapper $ui_wrapper;
    protected \ilCtrl $ctrl;
    protected \ilComponentFactory $component_factory;

    public function __construct(\ilPageObjectGUI $page_gui, string $pc_id = "")
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->component_factory = $DIC["component.factory"];
        $this->pc_id = $pc_id;

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
        $this->gui = $DIC->copage()->internal()->gui();
        $this->iim_manager = $DIC->copage()->internal()->domain()->pc()->interactiveImage();
    }

    /**
     * @throws Exception
     */
    public function handle(array $query): Server\Response
    {
        switch ($query["action"]) {
            case "init":
                return $this->init();

        }
        throw new Exception("Unknown action " . $query["action"]);
    }

    protected function init(): Server\Response
    {
        $ctrl = $this->ctrl;
        $o = new \stdClass();
        $o->uiModel = new \stdClass();
        $o->uiModel->mainHead = $this->getMainHead();
        $o->uiModel->addTriggerMessage = $this->getAddTriggerMessage();
        $o->uiModel->selectTriggerMessage = $this->getSelectTriggerMessage();
        $o->uiModel->commonSuccessMessage = $this->getCommonSuccessMessage();
        $o->uiModel->triggerPropertiesMesssage = $this->getTriggerPropertiesInfo();
        $o->uiModel->mainSlate = $this->getMainSlate();
        $o->uiModel->backgroundImage = $this->getBackgroundImage();
        $o->uiModel->triggerProperties = $this->getTriggerProperties();
        $o->uiModel->triggerOverlay = $this->getTriggerOverlay();
        $o->uiModel->triggerPopup = $this->getTriggerPopup();
        $o->uiModel->popupOverview = $this->getPopupOverview();
        $o->uiModel->overlayOverview = $this->getOverlayOverview();
        $o->uiModel->overlayUpload = $this->getOverlayUpload();
        $o->uiModel->popupForm = $this->getPopupForm();
        $o->uiModel->backgroundProperties = $this->getBackgroundProperties();
        $o->uiModel->modal = $this->getModalTemplate();
        $o->uiModel->loader = $this->getLoader();
        $o->uiModel->popupDummy = $this->getPopupDummy();
        $o->uiModel->lore = $this->getLore();
        $o->uiModel->backUrl = $ctrl->getLinkTarget($this->page_gui, "edit") . "#pc" . $this->pc_id;

        $o->iimModel = $this->getIIMModel();
        /*
        $o->errorMessage = $this->getErrorMessage();
        $o->errorModalMessage = $this->getErrorModalMessage();
        $o->pcModel = $this->getPCModel();
        $o->pcDefinition = $this->getComponentsDefinitions();
        $o->modal = $this->getModalTemplate();
        $o->confirmation = $this->getConfirmationTemplate();
        $o->autoSaveInterval = $this->getAutoSaveInterval();
        $o->backUrl = $ctrl->getLinkTarget($this->page_gui, "edit");
        $o->loaderUrl = \ilUtil::getImagePath("loader.svg");*/

        return new Server\Response($o);
    }


    /**
     * Get interactive image model
     */
    protected function getIIMModel(): ?\stdClass
    {
        if ($this->pc_id !== "") {
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
            return $pc->getIIMModel();
        }
        return null;
    }

    protected function getPopupDummy(): string
    {
        if ($this->pc_id !== "") {
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
            return $pc->getPopupDummy();
        }
        return "";
    }

    protected function getLore(): string
    {
        return "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
    }

    public function getMainHead(): string
    {
        $lng = $this->lng;
        $tpl = new \ilTemplate("tpl.main_head.html", true, true, "components/ILIAS/COPage/PC/InteractiveImage");
        $tpl->setVariable("TITLE", $lng->txt("cont_iim_edit"));
        $tpl->setVariable("HEAD_TRIGGER", $lng->txt("cont_iim_trigger"));
        $tpl->setVariable(
            "CLOSE_BUTTON",
            $this->section($this->ui_wrapper->getRenderedButton(
                $lng->txt("cont_iim_finish_editing"),
                "button",
                "component.back",
                null,
                "InteractiveImage",
                true
            ))
        );
        return $tpl->get();
    }

    protected function getSelectTriggerMessage(): string
    {
        $lng = $this->lng;
        return $this->section($this->ui_wrapper->getRenderedInfoBox(
            $lng->txt("cont_iim_select_trigger")
        ));
    }

    protected function getCommonSuccessMessage(): string
    {
        $lng = $this->lng;
        return $this->section($this->ui_wrapper->getRenderedSuccessBox(
            $lng->txt("msg_obj_modified")
        ));
    }

    protected function getLoader(): string
    {
        $lng = $this->lng;
        return $this->section("<img src='" . \ilUtil::getImagePath("loader.svg") . "' />");
    }

    protected function getAddTriggerMessage(): string
    {
        $lng = $this->lng;
        return $this->section($this->ui_wrapper->getRenderedInfoBox(
            $lng->txt("cont_iim_add_trigger_text")
        ));
    }

    public function getMainSlate(): string
    {
        $lng = $this->lng;

        $tpl = new \ilTemplate("tpl.main_slate.html", true, true, "components/ILIAS/COPage/PC/InteractiveImage");
        $tpl->setVariable("HEAD_SETTINGS", $lng->txt("settings"));
        $tpl->setVariable("HEAD_OVERVIEW", $lng->txt("cont_iim_overview"));

        $tpl->setVariable(
            "ADD_BUTTON",
            $this->section($this->ui_wrapper->getRenderedButton(
                $this->lng->txt("cont_iim_add_trigger"),
                "button",
                "add.trigger",
                null,
                "InteractiveImage"
            ))
        );

        $tpl->setVariable(
            "LINK_SETTINGS",
            $this->section($this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_iim_background_image_and_caption"),
                "InteractiveImage",
                "link",
                "switch.settings",
                null
            ))
        );

        $tpl->setVariable(
            "LINK_OVERLAY",
            $this->section($this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_overlay_images"),
                "InteractiveImage",
                "link",
                "switch.overlays",
                null
            ))
        );

        $tpl->setVariable(
            "LINK_POPUPS",
            $this->section($this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_content_popups"),
                "InteractiveImage",
                "link",
                "switch.popups",
                null
            ))
        );

        return $tpl->get();
    }

    public function getBackgroundImage(
    ): string {

        if ($this->pc_id !== "") {
            /** @var \ilPCInteractiveImage $pc */
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
        } else {
            return "";
        }

        return $pc->getBackgroundImage();
    }

    protected function section(string $content): string
    {
        return "<div class='copg-slate-section'>" . $content . "</div>";
    }

    protected function getTriggerBackButton(): string
    {
        return $this->section($this->ui_wrapper->getRenderedButton(
            $this->lng->txt("back"),
            "button",
            "trigger.back",
            null,
            "InteractiveImage"
        ));
    }

    protected function getTriggerHeader(): string
    {
        return "<h2>" . $this->lng->txt("cont_iim_edit_trigger") . "</h2>";
    }

    protected function getTriggerViewControls(): string
    {
        return $this->section($this->ui_wrapper->getRenderedViewControl(
            [
                ["InteractiveImage", "trigger.properties", $this->lng->txt("properties")],
                ["InteractiveImage", "trigger.overlay", $this->lng->txt("cont_overlay_image")],
                ["InteractiveImage", "trigger.popup", $this->lng->txt("cont_content_popup")]
            ]
        ));
    }

    protected function getTriggerPropertiesFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        return $this->gui->form(null, "#")
                          ->text(
                              "title",
                              $this->lng->txt("title")
                          )
                          ->select(
                              "shape",
                              $this->lng->txt("cont_shape"),
                              [
                                  "Rect" => $this->lng->txt("cont_Rect"),
                                  "Circle" => $this->lng->txt("cont_Circle"),
                                  "Poly" => $this->lng->txt("cont_Poly"),
                                  "Marker" => $this->lng->txt("cont_marker")
                              ],
                              "",
                              "Rect"
                          )->required();
    }

    protected function getMessageArea(): string
    {
        return "<div id='cont_iim_message'></div>";
    }

    protected function getTriggerPropertiesInfo(): string
    {
        return $this->section($this->ui_wrapper->getRenderedInfoBox($this->lng->txt("cont_iim_tr_properties_info")));
    }

    protected function getTriggerProperties(): string
    {
        $content = $this->getTriggerBackButton() .
            $this->getTriggerHeader() .
            $this->getTriggerViewControls();
        $content .= $this->getMessageArea();
        $content .= $this->ui_wrapper->getRenderedAdapterForm(
            $this->getTriggerPropertiesFormAdapter(),
            [["InteractiveImage", "trigger.properties.save", $this->lng->txt("save")]],
            "copg-iim-trigger-prop-form"
        );

        return $content;
    }

    protected function getTriggerOverlayFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        return $this->gui->form(null, "#")
                         ->select(
                             "overlay",
                             $this->lng->txt("cont_iim_select_overlay"),
                             [
                             ]
                         );
    }

    protected function getTriggerOverlay(): string
    {
        $content = $this->getTriggerBackButton() .
            $this->getTriggerHeader() .
            $this->getTriggerViewControls();
        $content .= $this->getMessageArea();
        $content .= $this->section($this->ui_wrapper->getRenderedButton(
            $this->lng->txt("cont_iim_add_overlay"),
            "button",
            "trigger.add.overlay",
            null,
            "InteractiveImage"
        ));
        $content .= $this->ui_wrapper->getRenderedAdapterForm(
            $this->getTriggerOverlayFormAdapter(),
            [["InteractiveImage", "trigger.overlay.save", $this->lng->txt("save")]],
            "copg-iim-trigger-overlay-form"
        );

        return $content;
    }

    protected function getTriggerPopupFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        return $this->gui->form(null, "#")
                         ->select(
                             "popup",
                             $this->lng->txt("cont_content_popup"),
                             [
                             ]
                         )->select(
                             "size",
                             $this->lng->txt("cont_iim_size"),
                             [
                        "sm" => $this->lng->txt("cont_iim_sm"),
                        "md" => $this->lng->txt("cont_iim_md"),
                        "lg" => $this->lng->txt("cont_iim_lg")
                    ],
                             "",
                             "md"
                         )->required();
    }

    protected function getTriggerPopup(): string
    {
        $content = $this->getTriggerBackButton() .
            $this->getTriggerHeader() .
            $this->getTriggerViewControls();
        $content .= $this->getMessageArea();
        $content .= $this->section($this->ui_wrapper->getRenderedButton(
            $this->lng->txt("cont_iim_tr_add_popup"),
            "button",
            "trigger.add.popup",
            null,
            "InteractiveImage"
        ));
        $content .= $this->ui_wrapper->getRenderedAdapterForm(
            $this->getTriggerPopupFormAdapter(),
            [["InteractiveImage", "trigger.save.popup", $this->lng->txt("save")]],
            "copg-iim-trigger-popup-form"
        );
        return $content;
    }

    protected function getPopupOverview(): string
    {
        $content = $this->getTriggerBackButton();
        $content .= "<h3>" . $this->lng->txt("cont_content_popups") . "</h3>";
        $content .= $this->getMessageArea();
        $content .= $this->section($this->ui_wrapper->getRenderedButton(
            $this->lng->txt("cont_iim_tr_add_popup"),
            "button",
            "trigger.add.popup",
            null,
            "InteractiveImage"
        ));
        $content .= $this->section($this->ui_wrapper->getRenderedListingPanelTemplate($this->lng->txt("cont_iim_overview")));
        return $content;
    }

    protected function getOverlayOverview(): string
    {
        $content = $this->getTriggerBackButton();
        $content .= "<h3>" . $this->lng->txt("cont_overlay_images") . "</h3>";
        $content .= $this->getMessageArea();
        $content .= $this->section($this->ui_wrapper->getRenderedButton(
            $this->lng->txt("cont_iim_add_overlay"),
            "button",
            "trigger.add.overlay",
            null,
            "InteractiveImage"
        ));
        $content .= $this->section($this->ui_wrapper->getRenderedListingPanelTemplate($this->lng->txt("cont_iim_overview"), true));

        return $content;
    }


    protected function getBackgroundProperties(): string
    {
        $this->ctrl->setParameterByClass(
            \ilPCInteractiveImageGUI::class,
            "mode",
            "backgroundUpdate"
        );

        $content = $this->getTriggerBackButton();
        $content .= "<h3>" . $this->lng->txt("cont_iim_background_image") . "</h3>";
        $content .= $this->getMessageArea();
        $content .= $this->ui_wrapper->getRenderedAdapterForm(
            $this->getPCInteractiveImageGUI()->getBackgroundPropertiesFormAdapter([get_class($this->page_gui), \ilPageEditorGUI::class, \ilPCInteractiveImageGUI::class]),
            [["InteractiveImage", "component.save", $this->lng->txt("save")]]
        );

        return $content;
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

    protected function getPCInteractiveImageGUI(): \ilPCInteractiveImageGUI
    {
        $pg = $this->page_gui->getPageObject();
        $iim = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
        $iim_gui = new \ilPCInteractiveImageGUI($pg, $iim, "", $this->pc_id);
        $iim_gui->setPageConfig($pg->getPageConfig());
        return $iim_gui;
    }

    protected function getOverlayUploadFormAdapter(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        return $this->getPCInteractiveImageGUI()
                    ->getOverlayUploadFormAdapter([get_class($this->page_gui), \ilPageEditorGUI::class, \ilPCInteractiveImageGUI::class]);
    }

    protected function getOverlayUpload(): string
    {
        $this->ctrl->setParameterByClass(
            \ilPCInteractiveImageGUI::class,
            "mode",
            "overlayUpload"
        );
        $content = $this->ui_wrapper->getRenderedAdapterForm(
            $this->getOverlayUploadFormAdapter(),
            [["InteractiveImage", "overlay.upload", $this->lng->txt("add")]]
        );
        $this->ctrl->setParameterByClass(
            \ilPCInteractiveImageGUI::class,
            "mode",
            null
        );
        return $content;
    }

    protected function getPopupForm(): string
    {
        $iim_gui = $this->getPCInteractiveImageGUI();
        $content = $this->ui_wrapper->getRenderedAdapterForm(
            $iim_gui->getPopupFormAdapter(),
            [["InteractiveImage", "popup.save", $this->lng->txt("save")]]
        );
        return $content;
    }
}
