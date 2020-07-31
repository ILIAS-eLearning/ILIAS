<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for competence profiles in containers
 *
 * @author Thomas Famula <famula@leifos.de>
 *
 * @ingroup ServicesContainer
 */
class ilContProfileTableGUI extends ilTable2GUI
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
     * @var ilContainerProfiles
     */
    protected $container_profiles;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilContainerProfiles $a_cont_profiles)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->container_profiles = $a_cont_profiles;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getProfiles());
        $this->setTitle($this->lng->txt("cont_skill_profiles"));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("cont_skill_profile"), "", "1");
        $this->addColumn($this->lng->txt("actions"), "", "1");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.cont_profile_row.html", "Services/Container/Skills");
        $this->setSelectAllCheckbox("id");

        $this->addMultiCommand("confirmRemoveSelectedProfile", $this->lng->txt("remove"));
    }

    /**
     * Get profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        $profiles = array();
        foreach ($this->container_profiles->getProfiles() as $p) {
            $profiles[] = array(
                "profile_id" => $p["profile_id"],
                "title" => ilSkillProfile::lookupTitle($p["profile_id"])
            );
        }

        return $profiles;
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tpl->setVariable("TITLE", $a_set["title"]);
        $tpl->setVariable("ID", $a_set["profile_id"]);
        $tpl->setVariable("CMD", $lng->txt("cont_skill_remove_profile"));
        $ctrl->setParameter($this->parent_obj, "profile_id", $a_set["profile_id"]);
        $tpl->setVariable("CMD_HREF", $ctrl->getLinkTarget($this->parent_obj, "confirmRemoveSingleProfile"));
    }
}
