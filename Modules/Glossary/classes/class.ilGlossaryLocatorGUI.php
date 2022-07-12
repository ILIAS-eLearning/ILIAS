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

use ILIAS\Glossary\Presentation\PresentationGUIRequest;

/**
 * Glossary Locator GUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryLocatorGUI
{
    protected PresentationGUIRequest $presentation_request;
    protected ?ilGlossaryDefinition $definition = null;
    protected ?ilGlossaryTerm $term = null;
    protected ilCtrl $ctrl;
    protected ilLocatorGUI $locator;

    public string $mode;
    public string $temp_var;
    public ilTree $tree;
    public ilObjGlossary $glossary;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->locator = $DIC["ilLocator"];
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $tree = $DIC->repositoryTree();

        $this->mode = "edit";
        $this->temp_var = "LOCATOR";
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->presentation_request = $DIC->glossary()
            ->internal()
            ->gui()
            ->presentation()
            ->request();
    }

    public function setTemplateVariable(string $a_temp_var) : void
    {
        $this->temp_var = $a_temp_var;
    }

    public function setTerm(ilGlossaryTerm $a_term) : void
    {
        $this->term = $a_term;
    }

    public function setGlossary(ilObjGlossary $a_glossary) : void
    {
        $this->glossary = $a_glossary;
    }

    public function setDefinition(ilGlossaryDefinition $a_def) : void
    {
        $this->definition = $a_def;
    }

    public function setMode(string $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    /**
     * display locator
     */
    public function display() : void
    {
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        
        // repository links
        $ilLocator->addRepositoryItems();
        
        // glossary link
        $title = $this->glossary->getTitle();
        if ($this->mode == "edit") {
            $link = $ilCtrl->getLinkTargetByClass("ilobjglossarygui", "listTerms");
        } else {
            $ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", "");
            $link = $ilCtrl->getLinkTargetByClass("ilglossarypresentationgui");
            if (is_object($this->term)) {
                $ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", $this->term->getId());
            }
        }
        $ilLocator->addItem($title, $link, "");
        
        if (is_object($this->term) && $this->mode != "edit") {
            $ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", $this->term->getId());
            $ilLocator->addItem(
                $this->term->getTerm(),
                $ilCtrl->getLinkTargetByClass("ilglossarypresentationgui", "listDefinitions")
            );
            $ilCtrl->setParameterByClass(
                "ilglossarypresentationgui",
                "term_id",
                $this->presentation_request->getTermId()
            );
        }

        if (is_object($this->definition)) {
            $title = $this->term->getTerm() . " (" . $this->lng->txt("cont_definition") . " " . $this->definition->getNr() . ")";
            if ($this->mode == "edit") {
                $link = $ilCtrl->getLinkTargetByClass("ilglossarydefpagegui", "edit");
            } else {
                $ilCtrl->setParameterByClass(
                    "ilglossarypresentationgui",
                    "def",
                    $this->presentation_request->getDefinitionId()
                );
                $link = $ilCtrl->getLinkTargetByClass("ilglossarypresentationgui", "view");
            }
            $ilLocator->addItem($title, $link);
        }
        
        $tpl->setLocator();
    }
}
