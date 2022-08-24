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
 * TableGUI class for lm short titles
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMEditShortTitlesTableGUI extends ilTable2GUI
{
    protected string $lang;
    protected ilObjLearningModule $lm;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm,
        string $a_lang
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lm = $a_lm;
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->lang = $a_lang;

        $this->setId("lm_short_title");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData(ilLMObject::getShortTitles($this->lm->getId(), $this->lang));
        $this->setTitle($this->lng->txt("cont_short_titles"));

        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("cont_short_title"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.short_title_row.html", "Modules/LearningModule");

        $this->addCommandButton("save", $this->lng->txt("save"));
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("DEFAULT_TITLE", $a_set["default_title"] ?? "");
        $this->tpl->setVariable("DEFAULT_SHORT_TITLE", $a_set["default_short_title"] ?? "");
        $this->tpl->setVariable("ID", $a_set["obj_id"]);
        $this->tpl->setVariable("SHORT_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["short_title"]));
    }
}
