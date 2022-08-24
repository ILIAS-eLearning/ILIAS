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
 * GUI class for glossary term definition editor
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilTermDefinitionEditorGUI: ilGlossaryDefPageGUI
 */
class ilTermDefinitionEditorGUI
{
    protected ilObjGlossary $term_glossary;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public ilObjGlossary $glossary;
    public ilGlossaryDefinition $definition;
    public ilGlossaryTerm $term;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct()
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();


        $this->glossary = new ilObjGlossary($this->request->getRefId(), true);
        $this->definition = new ilGlossaryDefinition($this->request->getDefinitionId());
        $this->term = new ilGlossaryTerm($this->definition->getTermId());
        $this->term_glossary = new ilObjGlossary(ilGlossaryTerm::_lookGlossaryID($this->definition->getTermId()), false);
        $this->tabs_gui = $ilTabs;

        $this->ctrl->saveParameter($this, array("def"));

        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        $this->content_style_domain = $cs->domain()->styleForRefId($this->glossary->getRefId());
    }


    public function executeCommand(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        // content style
        $this->content_style_gui->addCss(
            $this->tpl,
            $this->term_glossary->getRefId(),
            $this->term_glossary->getId()
        );

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setTerm($this->term);
        $gloss_loc->setGlossary($this->glossary);
        $gloss_loc->setDefinition($this->definition);

        $this->tpl->setTitle($this->term->getTerm() . " - " .
            $this->lng->txt("cont_definition") . " " .
            $this->definition->getNr());
        if ($this->ctrl->getNextClass() == "ilglossarydefpagegui") {
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
        }

        switch ($next_class) {

            case "ilglossarydefpagegui":
                // output number of usages
                if ($ilCtrl->getCmd() == "edit" &&
                    $ilCtrl->getCmdClass() == "ilglossarydefpagegui") {
                    $nr = ilGlossaryTerm::getNumberOfUsages($this->request->getTermId());
                    if ($nr > 0) {
                        $link = "[<a href='" .
                            $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages") .
                            "'>" . $lng->txt("glo_list_usages") . "</a>]";
                        $this->tpl->setOnScreenMessage("info", sprintf(
                            $lng->txt("glo_term_is_used_n_times"),
                            $nr
                        ) . " " . $link);
                    }
                }

                // not so nice, to do: revise locator handling
                if ($this->ctrl->getNextClass() == "ilglossarydefpagegui"
                    || $this->ctrl->getCmdClass() == "ileditclipboardgui") {
                    $gloss_loc->display();
                }
                $this->setTabs();
                $this->ctrl->setReturnByClass("ilGlossaryDefPageGUI", "edit");
                $this->ctrl->setReturn($this, "listDefinitions");
                $page_gui = new ilGlossaryDefPageGUI($this->definition->getId());
                // @var ilGlossaryDefPage $page
                $page = $page_gui->getPageObject();
                $this->definition->assignPageObject($page);
                $page->addUpdateListener($this, "saveShortText");
                $page_gui->setEditPreview(true);

                // metadata
                // ... set title to term, if no title is given
                $md = new ilMD($this->term_glossary->getId(), $this->definition->getId(), "gdf");
                $md_gen = $md->getGeneral();
                if ($md_gen->getTitle() == "") {
                    $md_gen->setTitle($this->term->getTerm());
                    $md_gen->update();
                }

                $page_gui->activateMetaDataEditor($this->term_glossary, "gdf", $this->definition->getId());

                $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->request->getRefId());
                $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=fullscreen&amp;ref_id=" . $this->request->getRefId());
                $page_gui->setTemplateTargetVar("ADM_CONTENT");
                $page_gui->setOutputMode("edit");

                $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());

                $page_gui->setIntLinkReturn($this->ctrl->getLinkTargetByClass(
                    "ilobjglossarygui",
                    "quickList",
                    "",
                    false,
                    false
                ));
                $page_gui->setPageBackTitle($this->lng->txt("cont_definition"));
                $page_gui->setLinkParams("ref_id=" . $this->request->getRefId());
                $page_gui->setHeader($this->term->getTerm());
                $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=" . $this->request->getRefId());
                $page_gui->setPresentationTitle($this->term->getTerm());
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != "") {
                    $tpl->setContent($ret);
                }
                break;

            default:
                $this->setTabs();
                $gloss_loc->display();
                $this->$cmd();
                break;

        }
    }

    public function setTabs(): void
    {
        $this->getTabs();
    }

    public function getTabs(): void
    {
        // back to glossary
        $this->tabs_gui->setBack2Target(
            $this->lng->txt("glossary"),
            $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "")
        );

        // back to upper context
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("term"),
            $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm")
        );
    }

    public function saveShortText(): void
    {
        $this->definition->updateShortText();
    }
}
