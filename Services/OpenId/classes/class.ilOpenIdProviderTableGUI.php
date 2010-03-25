<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');

/**
 * @classDescription Open ID provider table
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdProviderTableGUI extends ilTable2GUI
{
	private $ctrl = null;
	private $lng = null;

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_parent_class,$a_parent_cmd)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;

		parent::__construct($a_parent_class,$a_parent_cmd);
		
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->lng->txt('name'),'name',"20%");
	 	$this->addColumn($this->lng->txt('ldap_ilias_role'),'role',"30%");
	 	$this->addColumn($this->lng->txt('ldap_rule_condition'),'condition',"20%");
		$this->addColumn($this->lng->txt('ldap_add_remove'),'add_remove','30%');
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_role_assignment_row.html","Services/LDAP");
		$this->setDefaultOrderField('type');
		$this->setDefaultOrderDirection("desc");
		
	}
}
?>
