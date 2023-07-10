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
 * Survey sync table GUI class
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 */
class ilSurveySyncTableGUI extends ilTable2GUI
{
    protected SurveyQuestion $question;
    protected ilAccessHandler $access;
    protected ilTree $tree;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        SurveyQuestion $a_question
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->question = $a_question;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("il_svy_spl_sync");

        $this->setTitle($this->question->getTitle());
        $this->setDescription($lng->txt("survey_sync_question_copies_info"));

        $this->addCommandButton("synccopies", $lng->txt("survey_sync_question_copies"));
        $this->addCommandButton("cancelsync", $lng->txt("cancel"));

        // $this->setSelectAllCheckbox("id[]");
        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("title"), "");

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.il_svy_qpl_sync.html", "Modules/SurveyQuestionPool");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;

        $table_data = array();
        foreach ($this->question->getCopyIds(true) as $survey_obj_id => $questions) {
            $survey_id = new ilObjSurvey($survey_obj_id, false);
            $survey_id->loadFromDb();
            $survey_id = $survey_id->getSurveyId();

            $ref_ids = ilObject::_getAllReferences($survey_obj_id);
            $message = "";

            // check permissions for "parent" survey
            $can_write = false;
            if (!ilObjSurvey::_hasDatasets($survey_id)) {
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccess("edit", "", $ref_id)) {
                        $can_write = true;
                        break;
                    }
                }

                if (!$can_write) {
                    $message = $lng->txt("survey_sync_insufficient_permissions");
                }
            } else {
                $message = $lng->txt("survey_has_datasets_warning");
            }

            $survey_title = ilObject::_lookupTitle($survey_obj_id);
            $survey_path = $this->buildPath($ref_ids);

            foreach ($questions as $question_id) {
                $title = SurveyQuestion::_getTitle($question_id);

                if (!$can_write) {
                    $question_id = null;
                }

                $table_data[] = array(
                    "id" => $question_id,
                    "title" => $title,
                    "path" => $survey_path,
                    "message" => $message
                    );
            }
        }

        $this->setData($table_data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("TXT_PATH", $lng->txt("path"));

        if ($a_set["message"]) {
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("TXT_MESSAGE", $a_set["message"]);
            $this->tpl->parseCurrentBlock();
        }

        // question
        if ($a_set["id"]) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("VALUE_PATH", implode("<br />", $a_set["path"]));
    }

    /**
     * Build path with deep-link
     */
    protected function buildPath(array $ref_ids): array
    {
        $tree = $this->tree;

        $result = [];
        foreach ($ref_ids as $ref_id) {
            $path = "...";

            $counter = 0;
            $path_full = $tree->getPathFull($ref_id);
            if (count($path_full)) {
                foreach ($path_full as $data) {
                    if (++$counter < (count($path_full) - 1)) {
                        continue;
                    }
                    $path .= " &raquo; ";
                    if ($ref_id != $data['ref_id']) {
                        $path .= $data['title'];
                    } else {
                        $path .= ('<a target="_top" href="' .
                                  ilLink::_getLink($data['ref_id'], $data['type']) . '">' .
                                  $data['title'] . '</a>');
                    }
                }
            }

            $result[] = $path;
        }
        return $result;
    }
}
