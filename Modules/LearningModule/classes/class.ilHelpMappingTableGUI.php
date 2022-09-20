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
 * Help mapping
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpMappingTableGUI extends ilTable2GUI
{
    protected bool $validation;
    protected ilAccessHandler $access;
    public bool $online_help_mode = false;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_validation = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("lm_help_map");
        $this->validation = $a_validation;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->getChapters();

        $this->setTitle($lng->txt("help_assign_help_ids"));

        $this->addColumn($this->lng->txt("st"), "title");
        $this->addColumn($this->lng->txt("cont_screen_ids"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.help_map_row.html", "Modules/LearningModule");
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->addCommandButton("saveHelpMapping", $lng->txt("save"));
    }

    public function getChapters(): void
    {
        $hc = ilSession::get("help_chap");
        $lm_tree = $this->parent_obj->object->getTree();

        if ($hc > 0 && $lm_tree->isInTree($hc)) {
            //$node = $lm_tree->getNodeData($hc);
            //$chaps = $lm_tree->getSubTree($node);
            $chaps = $lm_tree->getFilteredSubTree($hc, array("pg"));
            unset($chaps[0]);
        } else {
            $chaps = ilStructureObject::getChapterList($this->parent_obj->object->getId());
        }

        $this->setData($chaps);
    }


    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("PAGE_ID", $a_set["obj_id"]);

        $screen_ids = ilHelpMapping::getScreenIdsOfChapter($a_set["obj_id"]);

        $this->tpl->setVariable(
            "SCREEN_IDS",
            ilLegacyFormElementsUtil::prepareFormOutput(implode("\n", $screen_ids))
        );
    }
}
