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

use ILIAS\LearningModule\Presentation\PresentationGUIRequest;

/**
 * Extension of ilPageObjectGUI for learning modules
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilLMPageGUI: ilPageEditorGUI, ilObjectMetaDataGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector, ilCommonActionDispatcherGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilLMPageGUI: ilNewsItemGUI, ilQuestionEditGUI, ilAssQuestionFeedbackEditingGUI, ilPageMultiLangGUI, ilPropertyFormGUI
 */
class ilLMPageGUI extends ilPageObjectGUI
{
    protected ilDBInterface $db;
    protected PresentationGUIRequest $pres_request;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_prevent_get_id = false,
        string $a_lang = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->log = $DIC["ilLog"];
        parent::__construct("lm", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);
        $this->pres_request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->presentation()
            ->request();

        $this->getPageConfig()->setUseStoredQuestionTries(ilObjContentObject::_lookupStoreTries($this->getPageObject()->getParentId()));
    }

    /**
     * On feedback editing forwarding
     */
    public function onFeedbackEditingForwarding() : void
    {
        $lng = $this->lng;

        if (strtolower($this->ctrl->getCmdClass()) == "ilassquestionfeedbackeditinggui") {
            if (ilObjContentObject::_lookupDisableDefaultFeedback($this->getPageObject()->getParentId())) {
                $this->tpl->setOnScreenMessage('info', $lng->txt("cont_def_feedb_deactivated"));
            } else {
                $this->tpl->setOnScreenMessage('info', $lng->txt("cont_def_feedb_activated"));
            }
        }
    }

    /**
     * Process answer
     */
    public function processAnswer() : void
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;

        parent::processAnswer();

        //
        // Send notifications to authors that want to be informed on blocked users
        //

        $parent_id = ilPageObject::lookupParentId(
            $this->pres_request->getQuestionPageId(),
            "lm"
        );

        // is restriction mode set?
        if (ilObjContentObject::_lookupRestrictForwardNavigation($parent_id)) {
            // check if user is blocked
            $id = ilUtil::stripSlashes($this->pres_request->getQuestionId());

            $as = ilPageQuestionProcessor::getAnswerStatus($id, $ilUser->getId());
            // get question information
            $qlist = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin);
            $qlist->setParentObjId(0);
            $qlist->setJoinObjectData(false);
            $qlist->addFieldFilter("question_id", array($id));
            $qlist->load();
            $qdata = $qlist->getQuestionDataArray();
            // has the user been blocked?
            if ($as["try"] >= $qdata[$as["qst_id"]]["nr_of_tries"] && $qdata[$as["qst_id"]]["nr_of_tries"] > 0 && !$as["passed"]) {
                $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_LM_BLOCKED_USERS, $parent_id);

                if (count($users) > 0) {
                    $not = new ilLMMailNotification();
                    $not->setType(ilLMMailNotification::TYPE_USER_BLOCKED);
                    $not->setQuestionId($id);
                    $not->setRefId($this->pres_request->getRefId());
                    $not->setRecipients($users);
                    $not->send();
                }
            }
        }
    }

    public function finishEditing() : void
    {
        $lm_tree = new ilLMTree($this->getPageObject()->getParentId());
        if ($lm_tree->isInTree($this->getPageObject()->getId())) {
            $parent_id = $lm_tree->getParentId($this->getPageObject()->getId());
            $this->ctrl->setParameterByClass(
                ilStructureObjectGUI::class,
                "obj_id",
                $parent_id
            );
            $this->ctrl->redirectByClass([
                ilObjLearningModuleGUI::class,
                ilStructureObjectGUI::class
            ], "view");
        }
        $this->ctrl->redirectByClass(
            ilObjLearningModuleGUI::class,
            "pages"
        );
    }
}
