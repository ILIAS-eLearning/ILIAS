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
	protected $contributor_role_id; // [int]	
	protected $contributor_ids; // [array]	
	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param int $a_contributor_role_id
	 * @param array $a_contributor_ids
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, $a_contributor_role_id, array $a_contributor_ids = null)
	{
		global $ilCtrl;
				
		$this->contributor_role_id = $a_contributor_role_id;
		$this->contributor_ids = $a_contributor_ids;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn("", "", 1);
		$this->addColumn($this->lng->txt("name"), "name");
		
		$this->setDefaultOrderField("name");
						
		$this->setRowTemplate("tpl.contributor_row.html", "Modules/Blog");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		if($this->contributor_ids)
		{
			$this->setTitle($this->lng->txt("blog_contributor_container_add"));
			$this->addMultiCommand("addContributorContainerAction", $this->lng->txt("add"));
		}
		else
		{
			$this->setTitle($this->lng->txt("blog_contributors"));
			$this->addMultiCommand("confirmRemoveContributor", $this->lng->txt("remove"));
		}
		
		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{			
		global $rbacreview;
		
		if($this->contributor_ids)
		{
			$assigned =$rbacreview->assignedUsers($this->contributor_role_id);
		}
		else
		{			
			$assigned = array();						
			$this->contributor_ids = $rbacreview->assignedUsers($this->contributor_role_id);	
		}
		
		include_once "Services/User/classes/class.ilUserUtil.php";
	
		$data = array();
		foreach($this->contributor_ids as $id)
		{
			if(!in_array($id, $assigned))
			{
				$data[] = array("id" => $id,
					"name" => ilUserUtil::getNamePresentation($id, false, false, "", true));		
			}
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
	}
}

?>