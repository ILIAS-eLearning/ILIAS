<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Extension of ilPageObjectGUI for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilLMPageGUI: ilPageEditorGUI, ilObjectMetaDataGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector, ilCommonActionDispatcherGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilLMPageGUI: ilNewsItemGUI, ilQuestionEditGUI, ilAssQuestionFeedbackEditingGUI, ilPageMultiLangGUI, ilPropertyFormGUI
 * @ingroup ModuleLearningModule
 */
class ilLMPageGUI extends ilPageObjectGUI
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_prevent_get_id = false, $a_lang = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->log = $DIC["ilLog"];
        parent::__construct("lm", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        $this->getPageConfig()->setUseStoredQuestionTries(ilObjContentObject::_lookupStoreTries($this->getPageObject()->getParentId()));
    }

    /**
     * On feedback editing forwarding
     */
    public function onFeedbackEditingForwarding()
    {
        $lng = $this->lng;

        if (strtolower($_GET["cmdClass"]) == "ilassquestionfeedbackeditinggui") {
            if (ilObjContentObject::_lookupDisableDefaultFeedback($this->getPageObject()->getParentId())) {
                ilUtil::sendInfo($lng->txt("cont_def_feedb_deactivated"));
            } else {
                ilUtil::sendInfo($lng->txt("cont_def_feedb_activated"));
            }
        }
    }

    /**
     * Process answer
     */
    public function processAnswer()
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;
        $ilLog = $this->log;

        parent::processAnswer();

        //
        // Send notifications to authors that want to be informed on blocked users
        //

        $parent_id = ilPageObject::lookupParentId((int) $_GET["page_id"], "lm");

        // is restriction mode set?
        if (ilObjContentObject::_lookupRestrictForwardNavigation($parent_id)) {
            // check if user is blocked
            $id = ilUtil::stripSlashes($_POST["id"]);

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
                    $not->setRefId((int) $_GET["ref_id"]);
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
                "obj_id", $parent_id);
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
