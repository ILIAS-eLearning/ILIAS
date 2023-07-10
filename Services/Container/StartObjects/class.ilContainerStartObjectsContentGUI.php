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
 * Class ilContainerStartObjectsContentGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContainerStartObjectsContentGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilObjUser $user;
    protected ilContainerStartObjects $start_object;
    protected bool $enable_desktop;
    protected ilContainerGUI $parent_gui;
    protected ilContainer $parent_obj;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        ilContainerGUI $a_gui,
        ilContainer $a_parent_obj
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $this->parent_gui = $a_gui;
        $this->parent_obj = $a_parent_obj;
        $this->start_object = new ilContainerStartObjects(
            $a_parent_obj->getRefId(),
            $a_parent_obj->getId()
        );
        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain()->styleForRefId($a_parent_obj->getRefId());
        $this->content_style_gui = $cs->gui();
    }

    public function enableDesktop(
        bool $a_value,
        ilContainerGUI $a_parent_gui
    ): void {
        $this->enable_desktop = $a_value;

        if ($this->enable_desktop) {
            $this->parent_gui = $a_parent_gui;
        }
    }

    // Set HTML in main template
    public function getHTML(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $lng->loadLanguageModule("crs");

        $tbl = new ilContainerStartObjectsContentTableGUI(
            $this->parent_gui,
            "",
            $this->start_object,
            $this->enable_desktop
        );
        $tpl->setContent(
            $this->getPageHTML() .
            $tbl->getHTML()
        );
    }

    protected function getPageHTML(): string
    {
        $tpl = $this->tpl;
        $ilSetting = $this->settings;

        if (!$ilSetting->get("enable_cat_page_edit")) {
            return "";
        }

        $page_id = $this->start_object->getObjId();

        // if page does not exist, return nothing
        if (!ilPageUtil::_existsAndNotEmpty("cstr", $page_id)) {
            return "";
        }

        $this->content_style_gui->addCss($tpl, $this->parent_obj->getRefId());
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();

        $page_gui = new ilContainerStartObjectsPageGUI($page_id);

        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        return $page_gui->showPage();
    }
}
