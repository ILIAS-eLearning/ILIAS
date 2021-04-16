<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Self evaluation
 *
 * @author Alex Killing <alex.kiling@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSkillSelfEvaluationGUI:
 * @ingroup ServicesSkill
 */
class ilSkillSelfEvaluationGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_se_id;

    /**
     * @var int
     */
    protected $requested_sn_id;

    /**
     * @var int
     */
    protected $requested_step;

    /**
     * @var int
     */
    protected $sn_id;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $ilCtrl->saveParameter($this, array("se_id", "sn_id"));
        $lng->loadLanguageModule("skmg");

        $params = $this->request->getQueryParams();
        $this->requested_se_id = (int) ($params["se_id"] ?? 0);
        $this->requested_sn_id = (int) ($params["sn_id"] ?? 0);
        $this->requested_step = (int) ($params["step"] ?? 0);

        $this->readSelfEvaluation();

        $this->sn_id = ((int) $_POST["sn_id"] > 0)
            ? (int) $_POST["sn_id"]
            : $this->requested_sn_id;
        $ilCtrl->setParameter($this, "sn_id", $this->sn_id);
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("listSelfEvaluations");
        $this->$cmd();
    }

    /**
     * Read self_evaluation
     */
    public function readSelfEvaluation()
    {
        //		$this->self_evaluation = new ilSelfEvaluation((int) $_GET[save_param]);
    }

    /**
     * List all self evaluations
     */
    public function listSelfEvaluations()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

        // desc
        /*$ne = new ilNonEditableValueGUI($lng->txt("lang"), var);
        $ne->setValue();
        $ne->setInfo();
        $this->form->addItem($ne);*/

        // select skill for self evaluation
        $se_nodes = ilSkillTreeNode::getAllSelfEvaluationNodes();
        $options = [];
        foreach ($se_nodes as $n_id => $title) {
            $options[$n_id] = $title;
        }
        $si = new ilSelectInputGUI($lng->txt("skmg_please_select_self_skill"), "sn_id");
        $si->setOptions($options);

        //$si->setInfo($lng->txt(""));
        $ilToolbar->addInputItem($si, true);

        $ilToolbar->addFormButton($lng->txt("skmg_execute_self_evaluation"), "startSelfEvaluation");

        $table = new ilSelfEvaluationTableGUI($this, "listSelfEvaluations");

        $tpl->setContent($table->getHTML());
    }


    /**
     * Confirm self_evaluation deletion
     */
    public function confirmSelfEvaluationDeletion()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listSelfEvaluations");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_sure_delete_self_evaluation"));
            $cgui->setCancel($lng->txt("cancel"), "listSelfEvaluations");
            $cgui->setConfirm($lng->txt("delete"), "deleteSelfEvaluation");

            foreach ($_POST["id"] as $i) {
                $se = new ilSkillSelfEvaluation((int) $i);
                $se_title =
                    ilSkillTreeNode::_lookupTitle($se->getTopSkillId());
                $cgui->addItem("id[]", $i, $se_title . ", " . $lng->txt("created") . ": " .
                    $se->getCreated() . ", " . $lng->txt("last_update") . ": " . $se->getLastUpdate());
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete self_evaluation
     */
    public function deleteSelfEvaluation()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $i) {
                $se = new ilSkillSelfEvaluation((int) $i);
                if ($se->getUserId() == $ilUser->getId()) {
                    $se->delete();
                }
            }
        }
        ilUtil::sendSuccess("msg_obj_modified");
        $ilCtrl->redirect($this, "listSelfEvaluations");
    }


    /**
     * startSelfEvaluation
     *
     * @param
     */
    public function startSelfEvaluation($a_mode = "create")
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $se = null;
        if ($a_mode == "edit") {
            $se = new ilSkillSelfEvaluation($this->requested_se_id);
            $this->sn_id = $se->getTopSkillId();
        }
        ilUtil::sendInfo($lng->txt("skmg_please_select_your_skill_levels"));

        $se_tpl = new ilTemplate("tpl.self_evaluation.html", true, true, "Services/Skill");

        $steps = ilSkillSelfEvaluation::determineSteps($this->sn_id);
        $cstep = $this->requested_step;
        $ilCtrl->setParameter($this, "step", $cstep);
        $table = new ilSkillSelfEvalSkillTableGUI(
            $this,
            "startSelfEvaluation",
            $steps[$cstep],
            $se
        );
        
        $se_tpl->setCurrentBlock("se_table");
        $se_tpl->setVariable("SE_TABLE", $table->getHTML());
        $se_tpl->parseCurrentBlock();

        $tb = new ilToolbarGUI();
        if ($a_mode == "edit") {
            if ($cstep > 0) {
                $tb->addFormButton("< " . $lng->txt("skmg_previous_step"), "updateBackSelfEvaluation");
            }
            if ($cstep < count($steps) - 1) {
                $tb->addFormButton($lng->txt("skmg_next_step") . " >", "updateSelfEvaluation");
            } elseif ($cstep == count($steps) - 1) {
                $tb->addFormButton($lng->txt("skmg_save_self_evaluation"), "updateSelfEvaluation");
            }
        } elseif ($cstep < count($steps) - 1) {
            $tb->addFormButton($lng->txt("skmg_next_step") . " >", "saveSelfEvaluation");
        } elseif ($cstep == count($steps) - 1) {
            $tb->addFormButton($lng->txt("skmg_save_self_evaluation"), "saveSelfEvaluation");
        }
        $se_tpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this));
        $se_tpl->setVariable("TOOLBAR", $tb->getHTML());
        $tpl->setContent($se_tpl->get());
    }

    /**
     * Save self evaluation
     */
    public function saveSelfEvaluation()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $se = new ilSkillSelfEvaluation();
        $se->setUserId($ilUser->getId());
        $se->setTopSkillId($this->requested_sn_id);
        if (is_array($_POST["se_sk"])) {
            $se->setLevels($_POST["se_sk"]);
        }
        $se->create();
        
        $steps = ilSkillSelfEvaluation::determineSteps($this->sn_id);
        $cstep = $this->requested_step;
        
        if (count($steps)) {
            $ilCtrl->setParameter($this, "step", 1);
            $ilCtrl->setParameter($this, "se_id", $se->getId());
            $ilCtrl->redirect($this, "editSelfEvaluation");
        }

        ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "");
    }

    /**
     * Edit self evaluation
     */
    public function editSelfEvaluation()
    {
        $this->startSelfEvaluation("edit");
    }

    /**
     * Update self evaluation and go one step back
     */
    public function updateBackSelfEvaluation()
    {
        $this->updateSelfEvaluation(true);
    }
    
    
    
    /**
     * Update self evaluation
     *
     * @param
     */
    public function updateSelfEvaluation($a_back = false)
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $se = new ilSkillSelfEvaluation($this->requested_se_id);

        if ($se->getUserId() == $ilUser->getId()) {
            $steps = ilSkillSelfEvaluation::determineSteps($this->sn_id);
            $cstep = $this->requested_step;

            if (is_array($_POST["se_sk"])) {
                $se->setLevels($_POST["se_sk"], true);
            }
            $se->update();

            if ($a_back) {
                $ilCtrl->setParameter($this, "step", $this->requested_step - 1);
                $ilCtrl->setParameter($this, "se_id", $se->getId());
                $ilCtrl->redirect($this, "editSelfEvaluation");
            } elseif (count($steps) - 1 > $cstep) {
                $ilCtrl->setParameter($this, "step", $this->requested_step + 1);
                $ilCtrl->setParameter($this, "se_id", $se->getId());
                $ilCtrl->redirect($this, "editSelfEvaluation");
            }

            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "");
    }

    ////
    //// Presentation view
    ////

    /**
     * Get presentation view
     *
     * @param
     * @return
     */
    public function getPresentationView($a_user_id)
    {
        $ses = ilSkillSelfEvaluation::getAllSelfEvaluationsOfUser($a_user_id);

        $html = "";
        foreach ($ses as $se) {
            $this->setSelfEvaluationPresentationForm($se);
            $html .= $this->form->getHTML() . "<br /><br />";
        }

        return $html;
    }

    /**
     * Set self evaluation presentation form
     */
    public function setSelfEvaluationPresentationForm($se)
    {
        $lng = $this->lng;

        $this->form = new ilPropertyFormGUI();

        ilDatePresentation::setUseRelativeDates(false);
        $dates = ", " .
            $lng->txt("created") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($se["created"], IL_CAL_DATETIME)
            );
        if ($se["created"] != $se["last_update"]) {
            $dates .= ", " . $lng->txt("last_update") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($se["last_update"], IL_CAL_DATETIME)
            );
        }
        ilDatePresentation::setUseRelativeDates(true);

        $se = new ilSkillSelfEvaluation($se["id"]);
        $levels = $se->getLevels();

        $this->form->setTitle($lng->txt("skmg_self_evaluation") . $dates);

        $stree = new ilSkillTree();

        if ($stree->isInTree($se->getTopSkillId())) {
            $cnode = $stree->getNodeData($se->getTopSkillId());
            $childs = $stree->getSubTree($cnode);
            foreach ($childs as $child) {
                if ($child["type"] == "skll") {
                    // build title
                    $path = $stree->getPathFull($child["child"]);
                    $title = $sep = "";
                    foreach ($path as $p) {
                        if ($p["type"] != "skrt") {
                            $title .= $sep . $p["title"];
                            $sep = " > ";
                        }
                    }

                    $sk = new ilBasicSkill($child["child"]);
                    $ls = $sk->getLevelData();

                    $ne = new ilNonEditableValueGUI($title, "");
                    foreach ($ls as $ld) {
                        if ($ld["id"] == $levels[$child["child"]]) {
                            $ne->setValue($ld["title"]);
                        }
                    }
                    $this->form->addItem($ne);
                }
            }
        }
    }
}
