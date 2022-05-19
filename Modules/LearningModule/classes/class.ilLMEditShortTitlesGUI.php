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

use ILIAS\LearningModule\Editing\EditingGUIRequest;

class ilLMEditShortTitlesGUI
{
    protected string $lang;
    protected ilCtrl $ctrl;
    protected ilObjLearningModule $lm;
    protected ilObjLearningModuleGUI $lm_gui;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected EditingGUIRequest $request;

    public function __construct(
        ilObjLearningModuleGUI $a_lm_gui,
        string $requested_transl
    ) {
        global $DIC;

        $this->request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->ctrl = $DIC->ctrl();
        /** @var ilObjLearningModule $lm */
        $lm = $a_lm_gui->getObject();
        $this->lm = $lm;
        $this->lm_gui = $a_lm_gui;
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();

        $this->lang = ($requested_transl == "")
            ? "-"
            : $requested_transl;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listShortTitles");
    
        switch ($next_class) {
            default:
                if (in_array($cmd, array("listShortTitles", "save"))) {
                    $this->$cmd();
                }
        }
    }

    public function listShortTitles() : void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_short_title_info"));
        $ml_head = ilObjContentObjectGUI::getMultiLangHeader($this->lm->getId(), $this->lm_gui, "short_titles");
        $tab = new ilLMEditShortTitlesTableGUI($this, "listShortTitles", $this->lm, $this->lang);
        $this->tpl->setContent($ml_head . $tab->getHTML());
    }

    public function save() : void
    {
        $short_titles = $this->request->getShortTitles();

        foreach ($short_titles as $id => $title) {
            if (ilLMObject::_lookupContObjID($id) == $this->lm->getId()) {
                ilLMObject::writeShortTitle($id, ilUtil::stripSlashes($title), $this->lang);
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listShortTitles");
    }
}
