<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Portfolio/classes/class.ilPCConsultationHours.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCConsultationHoursGUI
*
* Handles user commands on consultation hour data
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCConsultationHoursGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
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
                $ret = &$this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert consultation hours form
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
     * Edit consultation hours form
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
     * Init consultation hours form
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
            $form->setTitle($this->lng->txt("cont_insert_consultation_hours"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_consultation_hours"));
        }
        
        $mode = new ilRadioGroupInputGUI($this->lng->txt("cont_cach_mode"), "mode");
        $mode->setRequired(true);
        $form->addItem($mode);
        
        $opt_auto = new ilRadioOption($this->lng->txt("cont_cach_mode_automatic"), "auto");
        $opt_auto->setInfo($this->lng->txt("cont_cach_mode_automatic_info"));
        $mode->addOption($opt_auto);
        
        $opt_manual = new ilRadioOption($this->lng->txt("cont_cach_mode_manual"), "manual");
        $opt_manual->setInfo($this->lng->txt("cont_cach_mode_manual_info"));
        $mode->addOption($opt_manual);
        
        if (!$this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            include_once "Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php";
            $grp_ids = ilConsultationHourGroups::getGroupsOfUser($ilUser->getId());
            if (sizeof($grp_ids)) {
                $this->lng->loadLanguageModule("dateplaner");
                $groups = new ilCheckboxGroupInputGUI($this->lng->txt("cal_ch_app_grp"), "grp");
                $groups->setRequired(true);
                $opt_manual->addSubItem($groups);

                foreach ($grp_ids as $grp_obj) {
                    $groups->addOption(new ilCheckboxOption($grp_obj->getTitle(), $grp_obj->getGroupId()));
                }
            } else {
                $opt_manual->setDisabled(true);
            }
        } else {
            $opt_manual->setDisabled(true);
        }
        
        if ($a_insert) {
            $mode->setValue("auto");
            
            $form->addCommandButton("create_consultation_hours", $this->lng->txt("select"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            // set values
            $grp_ids = $this->content_obj->getGroupIds();
            if (sizeof($grp_ids)) {
                $mode->setValue("manual");
                $groups->setValue($grp_ids);
            } else {
                $mode->setValue("auto");
            }
            
            $form->addCommandButton("update", $this->lng->txt("select"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    /**
    * Create new consultation hours
    */
    public function create()
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $grp_ids = null;
            $mode = $form->getInput("mode");
            if ($mode == "manual") {
                $grp_ids = $form->getInput("grp");
            }
            
            $this->content_obj = new ilPCConsultationHours($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setData($mode, (array) $grp_ids);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $form->setValuesByPost();
        return $this->insert($form);
    }

    /**
    * Update consultation hours
    */
    public function update()
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $grp_ids = array();
            $mode = $form->getInput("mode");
            if ($mode == "manual") {
                $grp_ids = $form->getInput("grp");
            }
            
            $this->content_obj->setData($mode, $grp_ids);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $form->setValuesByPost();
        return $this->edit($form);
    }
}
