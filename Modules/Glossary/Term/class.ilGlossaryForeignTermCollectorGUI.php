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
 * Collects terms (reference or copy) from other glossaries
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryForeignTermCollectorGUI
{
    protected ilObjGlossary $foreign_glossary;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $request;
    protected \ILIAS\Glossary\Term\TermManager $term_manager;
    protected ilObjGlossaryGUI $glossary_gui;
    protected ilObjGlossary $glossary;
    protected int $fglo_ref_id;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;

    protected function __construct(
        ilObjGlossaryGUI $a_glossary_gui
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        $this->glossary_gui = $a_glossary_gui;
        /** @var ilObjGlossary $glossary */
        $glossary = $a_glossary_gui->getObject();
        $this->glossary = $glossary;

        $this->term_manager = $DIC->glossary()
            ->internal()
            ->domain()
            ->term(
                $this->glossary,
                $this->user->getId()
            );
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->fglo_ref_id = $this->request->getForeignGlossaryRefId();
        if ($this->fglo_ref_id > 0 && ilObject::_lookupType($this->fglo_ref_id, true) == "glo") {
            $this->foreign_glossary = new ilObjGlossary($this->fglo_ref_id, true);
        }

        $this->ctrl->saveParameter($this, "fglo_ref_id");
    }

    public static function getInstance(ilObjGlossaryGUI $a_glossary_gui) : self
    {
        return new self($a_glossary_gui);
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("showGlossarySelector");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("showGlossarySelector", "setForeignGlossary", "showTerms", "copyTerms", "referenceTerms"))) {
                    $this->$cmd();
                }
        }
    }

    public function showGlossarySelector() : void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("glo_select_source_glo"));
        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "showGlossarySelector",
            $this,
            "setForeignGlossary",
            "fglo_ref_id"
        );
        $exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "glo", "fold"));
        $exp->setClickableTypes(array("glo"));
        if (!$exp->handleCommand()) {
            $this->tpl->setContent($exp->getHTML());
        }
    }

    public function setForeignGlossary() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ref_id = $this->request->getForeignGlossaryRefId();

        if ($ref_id == $this->glossary->getRefId()) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("glo_please_select_other_glo"), true);
            $ilCtrl->redirect($this, "showGlossarySelector");
        }

        $ilCtrl->redirect($this, "showTerms");
    }

    public function showTerms() : void
    {
        $t = new ilGlossaryForeignTermTableGUI($this, "showTerms", $this->foreign_glossary);

        $this->tpl->setContent($t->getHTML());
    }
    
    public function copyTerms() : void
    {
        $term_ids = $this->request->getTermIds();
        if (count($term_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        foreach ($term_ids as $id) {
            $this->term_manager->copyTermFromOtherGlossary(
                $this->foreign_glossary->getRefId(),
                $id
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }

    public function referenceTerms() : void
    {
        $term_ids = $this->request->getTermIds();
        if (count($term_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        $terms = array();
        foreach ($term_ids as $id) {
            $terms[] = $id;
        }
        $this->term_manager->referenceTermsFromOtherGlossary(
            $this->foreign_glossary->getRefId(),
            $terms
        );
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }
}
