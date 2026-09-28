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
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Repository\Table\TableAdapterGUI;

class ilLMEditShortTitlesGUI
{
    protected string $lang;
    protected ilCtrl $ctrl;
    protected ilObjLearningModule $lm;
    protected ilObjLearningModuleGUI $lm_gui;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected EditingGUIRequest $request;
    protected \ILIAS\LearningModule\InternalGUIService $gui;

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
        $this->gui = $DIC->learningModule()->internal()->gui();

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

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listShortTitles");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "listShortTitles",
                    "editShortTitle",
                    "saveShortTitle"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function listShortTitles(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_short_title_info"));
        $ml_head = ilObjContentObjectGUI::getMultiLangHeader($this->lm->getId(), $this->lm_gui, "short_titles");
        $table = $this->getTable();
        if ($table->handleCommand()) {
            return;
        }
        $this->tpl->setContent($ml_head . $table->render());
    }

    protected function getTable(): TableAdapterGUI
    {
        return $this->gui->editing()
            ->shortTitlesTableBuilder(
                $this->lm->getId(),
                $this->lang,
                $this,
                "listShortTitles"
            )
            ->getTable();
    }

    public function editShortTitle(int $id): void
    {
        $this->ctrl->setParameterByClass(self::class, "edit_id", $id);
        $this->gui->clearAsnyOnloadCode();
        $this->gui->modal($this->lng->txt("cont_short_title"))
            ->form($this->getShortTitleForm($id))
            ->send();
    }

    protected function getShortTitleForm(int $id): FormAdapterGUI
    {
        $short_title = "";
        foreach (ilLMObject::getShortTitles($this->lm->getId(), $this->lang) as $data) {
            if ((int) $data["obj_id"] === $id) {
                $short_title = (string) $data["short_title"];
                break;
            }
        }

        $this->ctrl->setParameterByClass(self::class, "edit_id", $id);
        return $this->gui->form([self::class], "saveShortTitle")
            ->text(
                "short_title",
                $this->lng->txt("cont_short_title"),
                "",
                $short_title,
                200
            );
    }

    public function saveShortTitle(): void
    {
        $id = $this->request->getEditId();
        if ((int) ilLMObject::_lookupContObjID($id) !== $this->lm->getId()) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listShortTitles");
            return;
        }

        $form = $this->getShortTitleForm($id);
        if (!$form->isValid()) {
            $this->gui->clearAsnyOnloadCode();
            $this->gui->modal($this->lng->txt("cont_short_title"))
                ->form($form)
                ->send();
            return;
        }

        ilLMObject::writeShortTitle(
            $id,
            ilUtil::stripSlashes((string) $form->getData("short_title")),
            $this->lang
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listShortTitles");
    }
}
