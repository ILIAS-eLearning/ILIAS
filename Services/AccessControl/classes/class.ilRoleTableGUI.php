<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * TableGUI for the presentation og roles and role templates
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilRoleTableGUI extends ilTable2GUI
{
	private $path_gui = null;

	/**
	 * Constructor
	 * @param object $a_parent_gui
	 * @param string $a_parent_cmd
	 */
	public function __construct($a_parent_gui,$a_parent_cmd)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::__construct($a_parent_gui, $a_parent_cmd);
		$this->setId('rolf_role_tbl');
	}

	/**
	 * Get path gui
	 * @return ilPathGUI $path
	 */
	protected function getPathGUI()
	{
		return $this->path_gui;
	}


	
	/**
	 * Init table
	 */
	public function init()
	{
	 
		$this->lng->loadLanguageModule('rbac');

		$this->addColumn('','f','1px');
		$this->lng->loadLanguageModule('search');
	 	$this->addColumn($this->lng->txt('search_title_description'),'title','40%');
	 	$this->addColumn($this->lng->txt('context'),'','60%');

		$this->setRowTemplate('tpl.role_row.html','Services/AccessControl');
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
		$this->setSelectAllCheckbox('roles');
		$this->setTitle($this->lng->txt('objs_role'));

		$this->addMultiCommand('confirmDelete',$this->lng->txt('delete'));

		include_once './Services/Tree/classes/class.ilPathGUI.php';
		$this->path_gui = new ilPathGUI();
		$this->getPathGUI()->enableTextOnly(false);
		

		// Filter initialisation
		$this->initFilter();
		
	}

	/**
	 * Init filter
	 */
	public function  initFilter()
	{
		$this->setDisableFilterHiding(true);

		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		$roles = new ilSelectInputGUI($this->lng->txt('rbac_role_selection'), 'role_type');

		$action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
		$action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
		$action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
		$action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
		$action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
		$action[ilRbacReview::FILTER_TEMPLATES] = $this->lng->txt('role_templates_only');

		$roles->setOptions($action);

		$this->addFilterItem($roles);

		$roles->readFromSession();
		if(!$roles->getValue())
		{
			$roles->setValue(ilRbacReview::FILTER_ALL);
		}

		$this->filter['role_type'] = $roles->getValue();
	}

	/**
	 *
	 * @param array $set
	 */
	public function fillRow($set)
	{
		global $rbacreview,$tree;

		if($set['type'] == 'role')
		{
			if($set['obj_id'] != 8)
			{
				$this->ctrl->setParameterByClass(
					"ilobjrolegui",
					"rolf_ref_id",
					$rbacreview->getRoleFolderIdOfObject($set['parent'])
				);
			}

			$this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $set["obj_id"]);
			$link = $this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm");
			$this->ctrl->setParameterByClass("ilobjrolegui", "rolf_ref_id", "");
		}
		else
		{
			$this->ctrl->setParameterByClass("ilobjroletemplategui", "obj_id", $set["obj_id"]);
			$link = $this->ctrl->getLinkTargetByClass("ilobjroletemplategui", "perm");
		}

		if($set['obj_id'] != ANONYMOUS_ROLE_ID and $set['obj_id'] != SYSTEM_ROLE_ID)
		{
			$this->tpl->setVariable('VAL_ID', $set['obj_id']);
		}
		$this->tpl->setVariable('VAL_TITLE_LINKED', $set['title']);
		$this->tpl->setVariable('VAL_LINK', $link);
		if(strlen($set['description']))
		{
			$this->tpl->setVariable('VAL_DESC', $set['description']);
		}

		if((substr($set['title_orig'],0,3) == 'il_') and ($set['type'] == 'rolt'))
		{
			$this->tpl->setVariable('VAL_PRE',$this->lng->txt('predefined_template'));
		}

		$ref = $set['parent'];

		if($set['type'] == 'rolt')
		{
			$this->lng->loadLanguageModule('rbac');
			$this->tpl->setVariable('CONTEXT', $this->lng->txt('rbac_global_rolt'));
		}
		elseif($ref == 8)
		{
			$this->lng->loadLanguageModule('user');
			$this->tpl->setVariable('CONTEXT', $this->lng->txt('user_global_role'));
		}
		else
		{
			$this->tpl->setVariable(
				'CONTEXT',
				$this->getPathGUI()->getPath(ROOT_FOLDER_ID,$ref)
			);
		}
	}

	/**
	 * Parse role list
	 * @param array $role_list
	 */
	public function parse($role_folder_id)
	{
		global $rbacreview;

		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		$role_list = $rbacreview->getRolesByFilter(
			$this->getFilterItemByPostVar('role_type')->getValue(),
			$role_folder_id
		);


		$counter = 0;
		$rows = array();
		foreach((array) $role_list as $role)
		{
			$rows[$counter]['title_orig'] = $role['title'];
			$rows[$counter]['title'] = ilObjRole::_getTranslation($role['title']);
			$rows[$counter]['description'] = $role['description'];
			$rows[$counter]['obj_id'] = $role['obj_id'];
			$rows[$counter]['parent'] = $role['parent'];
			$rows[$counter]['type'] = $role['type'];

			++$counter;
		}
		$this->setData($rows);
	}


}

?>
