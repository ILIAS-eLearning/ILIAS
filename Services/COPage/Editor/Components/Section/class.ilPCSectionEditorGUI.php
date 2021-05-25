<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCSectionEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * @inheritDoc
     */
    public function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageObjectGUI $page_gui, int $style_id) : array
    {
        $form = $this->getCreationForm($page_gui, $ui_wrapper, $style_id);
        return [
            "creation_form" => $form,
            "icon" => $ui_wrapper->getRenderedIcon("pesc")
        ];
    }

    /**
     * Get creation form
     * @param
     * @return
     */
    protected function getCreationForm(ilPageObjectGUI $page_gui, $ui_wrapper, $style_id)
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $sec_gui = new ilPCSectionGUI($page_gui->getPageObject(), null, "", "");
        $sec_gui->setStyleId($style_id);
        $sec_gui->setPageConfig($page_gui->getPageConfig());

        $html = $ctrl->getHTML(
            $sec_gui,
            [
            "form" => true,
            "ui_wrapper" => $ui_wrapper,
            "buttons" => [["Page", "component.save", $lng->txt("save")],
                ["Page", "component.cancel", $lng->txt("cancel")]]
            ]
        );

        return $html;
    }

    /**
     * @inheritDoc
     */
    /**
     * @inheritDoc
     */
    public function getEditComponentForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id, $pcid) : string
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $page = $page_gui->getPageObject();
        $page->addHierIDs();
        $hier_id = $page->getHierIdForPcId($pcid);
        $sec = $page->getContentObjectForPcId($pcid);


        $sec_gui = new ilPCSectionGUI($page_gui->getPageObject(), $sec, $hier_id, $pcid);
        $sec_gui->setStyleId($style_id);
        $sec_gui->setPageConfig($page_gui->getPageConfig());

        $html = $ctrl->getHTML(
            $sec_gui,
            [
                "form" => true,
                "ui_wrapper" => $ui_wrapper,
                "buttons" => [["Page", "component.update", $lng->txt("save")],
                              ["Page", "component.cancel", $lng->txt("cancel")]]
            ]
        );

        return $html;
    }
}
