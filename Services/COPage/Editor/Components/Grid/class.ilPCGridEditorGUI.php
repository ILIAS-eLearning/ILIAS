<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCGridEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    /**
     * @inheritDoc
     */
    public function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageObjectGUI $page_gui, int $style_id) : array
    {
        $form = $this->getCreationForm($page_gui, $ui_wrapper);

        return [
            "creation_form" => $form,
            "icon" => $ui_wrapper->getRenderedIcon("pecl")
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEditComponentForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, \ilPageObjectGUI $page_gui, int $style_id, $pcid) : string
    {
        return "";
    }

    /**
     * Get creation form
     * @param
     * @return
     */
    protected function getCreationForm(ilPageObjectGUI $page_gui, $ui_wrapper)
    {
        $lng = $this->lng;

        $grid_gui = new ilPCGridGUI($page_gui->getPageObject(), null, "", "");

        /** @var ilPropertyFormGUI $form */
        $form = $grid_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("save")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }
}
