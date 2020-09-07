<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all contributors members of a blog
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesBlog
 */
class ilContributorTableGUI extends ilTable2GUI
{
    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    protected $local_roles; // [array]
    
    /**
     * Constructor
     *
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     * @param array $a_roles
     */
    public function __construct($a_parent_obj, $a_parent_cmd, array $a_roles)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->rbacreview = $DIC->rbac()->review();
        $ilCtrl = $DIC->ctrl();
                
        $this->local_roles = $a_roles;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("obj_role"), "role");
        
        $this->setDefaultOrderField("name");
                        
        $this->setRowTemplate("tpl.contributor_row.html", "Modules/Blog");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->setSelectAllCheckbox("id"); // #16472

        if ($this->contributor_ids) {
            $this->setTitle($this->lng->txt("blog_contributor_container_add"));
            $this->addMultiCommand("addContributorContainerAction", $this->lng->txt("add"));
        } else {
            $this->setTitle($this->lng->txt("blog_contributors"));
            $this->addMultiCommand("confirmRemoveContributor", $this->lng->txt("remove"));
        }
        
        $this->getItems();
    }

    protected function getItems()
    {
        $rbacreview = $this->rbacreview;
        
        $user_map = $assigned = array();
        foreach ($this->local_roles as $id => $title) {
            $local = $rbacreview->assignedUsers($id);
            $assigned = array_merge($assigned, $local);
            foreach ($local as $user_id) {
                $user_map[$user_id][] = $title;
            }
        }
        
        include_once "Services/User/classes/class.ilUserUtil.php";
    
        $data = array();
        foreach (array_unique($assigned) as $id) {
            $data[] = array("id" => $id,
                "name" => ilUserUtil::getNamePresentation($id, false, false, "", true),
                "role" => $user_map[$id]);
        }
        
        $this->setData($data);
    }

    /**
     * Fill template row
     *
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        $this->tpl->setVariable("TXT_ROLES", implode(", ", $a_set["role"]));
    }
}
