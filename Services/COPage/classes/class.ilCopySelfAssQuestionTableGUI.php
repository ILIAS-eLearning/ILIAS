<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

/**
 * Table to select self assessment questions for copying into learning resources
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilCopySelfAssQuestionTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var int
     */
    protected $pool_ref_id;

    /**
     * @var int
     */
    protected $pool_obj_id;

    /**
     * ilCopySelfAssQuestionTableGUI constructor.
     * @param int $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $a_pool_ref_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pool_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();

        $this->setId("cont_qpl");
        $this->pool_ref_id = $a_pool_ref_id;
        $this->pool_obj_id = ilObject::_lookupObjId($a_pool_ref_id);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle(ilObject::_lookupTitle($this->pool_obj_id));

        $this->setFormName('sa_quest_browser');

        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("cont_question_type"), 'ttype', '');
        $this->addColumn($this->lng->txt("actions"), '', '');


        $this->setRowTemplate("tpl.copy_sa_quest_row.html", "Services/COPage");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->initFilter();
        
        $this->getQuestions();
    }

    /**
     * Get questions
     */
    public function getQuestions()
    {
        global $DIC;

        $access = $this->access;
        
        include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
        $all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
        $all_ids = array();
        foreach ($all_types as $k => $v) {
            $all_ids[] = $v["question_type_id"];
        }
        
        $questions = array();
        if ($access->checkAccess("read", "", $this->pool_ref_id)) {
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
            $questionList = new ilAssQuestionList(
                $DIC->database(),
                $DIC->language(),
                $DIC["ilPluginAdmin"]
            );
            $questionList->setParentObjId($this->pool_obj_id);
            $questionList->load();
            
            $data = $questionList->getQuestionDataArray();

            $questions = array();
            foreach ($data as $d) {
                // list only self assessment question types
                if (in_array($d["question_type_fi"], $all_ids)) {
                    $questions[] = $d;
                }
            }
        }
        $this->setData($questions);
    }

    /**
     * Fill row
     *
     * @param array $a_set data array
     */
    public function fillRow($a_set)
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        // action: copy
        $ctrl->setParameter($this->parent_obj, "q_id", $a_set["question_id"]);
        $ctrl->setParameter($this->parent_obj, "subCmd", "copyQuestion");
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable(
            "HREF_CMD",
            $ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd)
        );
        $this->tpl->setVariable(
            "TXT_CMD",
            $lng->txt("cont_copy_question_into_page")
        );
        $this->tpl->parseCurrentBlock();
        $ctrl->setParameter($this->parent_obj, "subCmd", "listPoolQuestions");
        
        // properties
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable(
            "TYPE",
            assQuestion::_getQuestionTypeName($a_set["type_tag"])
        );
    }
}
