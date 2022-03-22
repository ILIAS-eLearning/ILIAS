<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObjectEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @inheritDoc
     */
    public function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageObjectGUI $page_gui, int $style_id) : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $acc = new ilAccordionGUI();
        $acc->addItem($lng->txt("cont_upload_file"), $this->getRenderedUploadForm($ui_wrapper, $lng));
        $acc->addItem($lng->txt("cont_add_url"), $this->getRenderedUrlForm($ui_wrapper, $lng));
        $acc->addItem($lng->txt("cont_choose_from_pool"), $this->getRenderedPoolBar($ui_wrapper, $lng));
        $acc->addItem($lng->txt("cont_choose_from_clipboard"), $this->getRenderedClipboardBar($ui_wrapper, $lng, $page_gui));
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        return [
            "creation_form" => $acc->getHTML(),
            "icon" => $ui_wrapper->getRenderedIcon("pemed")
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEditComponentForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id, $pcid) : string
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $media_type = new ILIAS\MediaObjects\MediaType\MediaType();

        $form = new ilPropertyFormGUI();
        $form->setShowTopButtons(false);
        $form->setTitle($lng->txt("cont_edit_mob"));

        /** @var ilPCMediaObject $pc_media */
        $pc_media = $page_gui->getPageObject()->getContentObjectForPcId($pcid);

        $quick_edit = new ilPCMediaObjectQuickEdit($pc_media);

        $pc_media_gui = new ilPCMediaObjectGUI(
            $page_gui->getPageObject(),
            $pc_media,
            $page_gui->getPageObject()->getHierIdForPcId($pcid),
            $pcid
        );
        $pc_media_gui->setStyleId($style_id);
        $pc_media_gui->getCharacteristicsOfCurrentStyle("media_cont");

        $media = $pc_media->getMediaObject()->getMediaItem("Standard");

        // title
        $title = new ilTextInputGUI($lng->txt("title"), "standard_title");
        $title->setSize(40);
        $title->setMaxLength(120);
        $title->setValue($quick_edit->getTitle());
        $form->addItem($title);

        // style
        if ($pc_media_gui->checkStyleSelection()) {
            $style_input = $pc_media_gui->getStyleInput();
            $form->addItem($style_input);
        }

        // horizonal align
        $align_prop = new ilSelectInputGUI(
            $lng->txt("cont_align"),
            "horizontal_align"
        );
        $options = array(
            "Left" => $lng->txt("cont_left"),
            "Center" => $lng->txt("cont_center"),
            "Right" => $lng->txt("cont_right"),
            "LeftFloat" => $lng->txt("cont_left_float"),
            "RightFloat" => $lng->txt("cont_right_float"));
        $align_prop->setOptions($options);
        $align_prop->setValue($quick_edit->getHorizontalAlign());
        $form->addItem($align_prop);

        // fullscreen
        if ($media_type->isImage($media->getFormat())) {
            $cb = new ilCheckboxInputGUI($lng->txt("cont_show_fullscreen"), "fullscreen");
            $cb->setChecked($quick_edit->getUseFullscreen());
            $form->addItem($cb);
        }

        // standard caption
        $caption = new ilTextAreaInputGUI($lng->txt("cont_caption"), "standard_caption");
        $caption->setRows(2);
        $caption->setValue($quick_edit->getCaption());
        $form->addItem($caption);

        // text representation
        if ($media_type->usesAltTextProperty($media->getFormat())) {
            $ta = new ilTextAreaInputGUI($lng->txt("text_repr"), "text_representation");
            $ta->setRows(2);
            $ta->setInfo($lng->txt("text_repr_info"));
            $ta->setValue($quick_edit->getTextRepresentation());
            $form->addItem($ta);
        }

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.update", $lng->txt("save")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        $link = $ui_wrapper->getRenderedLink($lng->txt("cont_advanced_settings"), "Page", "link", "component.settings");

        return $html . $link;
    }

    /**
     * Get upload form
     * @param
     * @return
     */
    protected function getRenderedUploadForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        $form = new ilPropertyFormGUI();
        $form->setShowTopButtons(false);

        // standard type
        $hi = new ilHiddenInputGUI("standard_type");
        $hi->setValue("File");
        $form->addItem($hi);

        // standard size
        $hi2 = new ilHiddenInputGUI("standard_size");
        $hi2->setValue("original");
        $form->addItem($hi2);

        // standard size
        $hi3 = new ilHiddenInputGUI("full_type");
        $hi3->setValue("None");
        $form->addItem($hi3);

        // standard file
        $up = new ilFileInputGUI($lng->txt("cont_file"), "standard_file");
        $up->setSuffixes(ilObjMediaObject::getRestrictedFileTypes());
        $up->setForbiddenSuffixes(ilObjMediaObject::getForbiddenFileTypes());
        $form->addItem($up);

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.save", $lng->txt("insert")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }

    /**
     * Get upload form
     * @param
     * @return
     */
    protected function getRenderedUrlForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        $form = new ilPropertyFormGUI();
        $form->setShowTopButtons(false);

        // standard type
        $hi = new ilHiddenInputGUI("standard_type");
        $hi->setValue("Reference");
        $form->addItem($hi);

        // standard size
        $hi2 = new ilHiddenInputGUI("standard_size");
        $hi2->setValue("original");
        $form->addItem($hi2);

        // standard size
        $hi3 = new ilHiddenInputGUI("full_type");
        $hi3->setValue("None");
        $form->addItem($hi3);

        // standard reference
        $ti = new ilTextInputGUI($lng->txt("url"), "standard_reference");
        $ti->setInfo($lng->txt("cont_url_info"));
        $form->addItem($ti);


        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.save", $lng->txt("insert")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }

    /**
     * Get pool link
     * @param
     * @return
     */
    protected function getRenderedPoolBar(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        global $DIC;

        $ui = $DIC->ui();
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $buttons = [];

        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");
        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cont_choose_media_pool"),
            "media-action",
            "select.pool",
            [
                "url" => $ctrl->getLinkTargetByClass("ilpcmediaobjectgui", "insert")
            ],
            "MediaObject"
        );
        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cancel"),
            "form-button",
            "component.cancel",
            [
            ],
            "Page"
        );
        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");

        return $ui_wrapper->getRenderedFormFooter($buttons);
    }

    /**
     * Get pool link
     * @param \ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper
     * @param                                       $lng
     * @param                                       $page_gui
     * @return string
     */
    protected function getRenderedClipboardBar(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng,
        $page_gui)
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $return_cmd = $ctrl->getLinkTargetByClass("ilpageeditorgui", "insertFromClipboard");

        $ctrl->setParameterByClass("ileditclipboardgui", "returnCommand", rawurlencode($return_cmd));

        $buttons = [];

        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cont_open_clipboard"),
            "media-action",
            "open.clipboard",
            ["url" => $ctrl->getLinkTargetByClass([get_class($page_gui), "ileditclipboardgui"], "getObject")],
            "MediaObject"
        );

        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cancel"),
            "form-button",
            "component.cancel",
            [
            ],
            "Page"
        );

        return $ui_wrapper->getRenderedFormFooter($buttons);
    }

}
