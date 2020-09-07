<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPCSkills.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * GUI class for learning history page content
 *
 * Handles user commands on skills data
 *
 * @author killin@leifos.com
 *
 * @ilCtrl_isCalledBy ilPCLearningHistoryGUI: ilPageEditorGUI
 * @ingroup ServicesLearningHistory
 */
class ilPCLearningHistoryGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilLearningHistoryService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(ilPageObject $a_pg_obj, ilPCLearningHistory $a_content_obj = null, $a_hier_id = "", $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("lhist");
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->service = $DIC->learningHistory();
        $this->ui = $DIC->ui();
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert learning history form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function insert(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit skills form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init learning history edit form
     *
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     */
    protected function initForm($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_create_lhist"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_lhist"));
        }

        // duration
        $du = new ilDateDurationInputGUI($lng->txt("lhist_period"), "period");
        if (!$a_insert) {
            if ($this->content_obj->getFrom() != "") {
                $du->setStart(new ilDate($this->content_obj->getFrom(), IL_CAL_DATE));
            }
            if ($this->content_obj->getTo() != "") {
                $du->setEnd(new ilDate($this->content_obj->getTo(), IL_CAL_DATE));
            }
        }
        $du->setAllowOpenIntervals(true);
        $form->addItem($du);

        //
        $radg = new ilRadioGroupInputGUI($lng->txt("lhist_type_of_achievement"), "mode");
        //$radg->setValue();
        $op1 = new ilRadioOption($lng->txt("lhist_all"), 0);
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("lhist_selected"), 1);
        $radg->addOption($op2);
        $form->addItem($radg);


        // select type
        $options = [];
        foreach ($this->service->provider()->getAllProviders(true) as $p) {
            $options[get_class($p)] = $p->getName();
        }
        $si = new ilMultiSelectInputGUI($lng->txt(""), "class");
        $si->setHeight(130);
        if (!$a_insert) {
            $si->setValue($this->content_obj->getClasses());
            if (count($this->content_obj->getClasses()) > 0) {
                $radg->setValue(1);
            }
        }
        $si->setOptions($options);
        $op2->addSubItem($si);



        if ($a_insert) {
            $form->addCommandButton("create_lhist", $this->lng->txt("insert"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }


    /**
     * Create new learning history component
     */
    public function create()
    {
        $valid = false;
        
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            //$data = $form->getInput("skill_id");
            $valid = true;
        }

        if ($valid) {
            $this->content_obj = new ilPCLearningHistory($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->setAttributesFromInput($form);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $form->setValuesByPost();
        return $this->insert($form);
    }

    /**
     * Update learning history component
     */
    public function update()
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->setAttributesFromInput($form);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $form->setValuesByPost();
        return $this->edit($form);
    }

    /**
     *
     *
     * @param
     * @return
     */
    protected function setAttributesFromInput($form)
    {
        /** @var ilDurationInputGUI $item */
        $item = $form->getItemByPostVar("period");
        $from = (is_null($item->getStart()))
            ? ""
            : $item->getStart()->get(IL_CAL_DATE);
        $to = (is_null($item->getEnd()))
            ? ""
            : $item->getEnd()->get(IL_CAL_DATE);

        $this->content_obj->setFrom($from);
        $this->content_obj->setTo($to);
        $classes = ($form->getInput("mode") == "1" && is_array($form->getInput("class")))
            ? $form->getInput("class")
            : array();
        $this->content_obj->setClasses($classes);
    }

    /**
     * Get placeholder presentation
     *
     * @param
     * @return
     */
    public static function getPlaceholderPresentation()
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("lhist");

        // @todo we need a ks element for this
        $content = '<div style="margin:5px" class="ilBox"><h3>' . $lng->txt("lhist_lhist") . '</h3><div class="il_Description_no_margin">' .
            $lng->txt("lhist_cont_placeholder_text") . '</div></div>';

        return $content;
    }
}
