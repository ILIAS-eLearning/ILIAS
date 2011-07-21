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
	const TYPE_VIEW = 1;
	const TYPE_SEARCH = 2;
	
	private $path_gui = null;

	private $type = self::TYPE_VIEW;

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
		$this->lng->loadLanguageModule('rbac');
		$this->lng->loadLanguageModule('search');
	}

	/**
	 * Set table type
	 * @param int $a_type
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Get table type
	 */
	public function getType()
	{
		return $this->type;
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
		$this->addColumn('','f','1px');

		switch($this->getType())
		{
			case self::TYPE_VIEW:
				$this->setId('rolf_role_tbl');
				$this->addColumn($this->lng->txt('search_title_description'),'title','40%');
				$this->addColumn($this->lng->txt('context'),'','50%');
				$this->addColumn($this->lng->txt('actions'),'','10%');
				$this->setTitle($this->lng->txt('objs_role'));
				$this->addMultiCommand('confirmDelete',$this->lng->txt('delete'));
				break;
			
			case self::TYPE_SEARCH:
				$this->setId('rolf_role_search_tbl');
				$this->addColumn('','f','1px');
				$this->addColumn($this->lng->txt('search_title_description'),'title','40%');
				$this->addColumn($this->lng->txt('context'),'','60%');
				$this->setTitle($this->lng->txt('rbac_role_rights_copy'));
				$this->addMultiCommand('copyPermOptions',$this->lng->txt('copy'));
				break;
		}


		$this->setRowTemplate('tpl.role_row.html','Services/AccessControl');
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
		$this->setSelectAllCheckbox('roles');


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

		switch($this->getType())
		{
			case self::TYPE_VIEW:
				$action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
				$action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
				$action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
				$action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
				$action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
				$action[ilRbacReview::FILTER_TEMPLATES] = $this->lng->txt('role_templates_only');
				break;

			case self::TYPE_SEARCH:
				$action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
				$action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
				$action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
				$action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
				$action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
				break;
		}

		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		$roles = new ilSelectInputGUI($this->lng->txt('rbac_role_selection'), 'role_type');

		$roles->setOptions($action);

		$this->addFilterItem($roles);

		$roles->readFromSession();
		if(!$roles->getValue())
		{
			$roles->setValue(ilRbacReview::FILTER_ALL);
		}

		// title filter
		include_once './Services/Form/classes/class.ilTextInputGUI.php';
		$title = new ilTextInputGUI($this->lng->txt('title'), 'role_title');
		$title->setSize(16);
		$title->setMaxLength(64);

		$this->addFilterItem($title);
		$title->readFromSession();

		$this->filter['role_type'] = $roles->getValue();
		$this->filter['role_title'] = $title->getValue();
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

		if($this->getType() == self::TYPE_VIEW)
		{
			// Copy role
			$this->tpl->setVariable('COPY_TEXT',$this->lng->txt('rbac_role_rights_copy'));
			$this->ctrl->setParameter($this->getParentObject(), "copy_source", $set["obj_id"]);
			$link = $this->ctrl->getLinkTarget($this->getParentObject(),'roleSearch');
			$this->tpl->setVariable(
				'COPY_LINK',
				$link
			);
		}

	}

	/**
	 * Parse role list
	 * @param array $role_list
	 */
	public function parse($role_folder_id)
	{
		global $rbacreview,$ilUser;

		include_once './Services/AccessControl/classes/class.ilObjRole.php';

		$filter = $this->getFilterItemByPostVar('role_title')->getValue();
		$type = $this->getFilterItemByPostVar('role_type')->getValue();
		
		if($type == ilRbacReview::FILTER_INTERNAL or $type == ilRbacReview::FILTER_ALL)
		{
			$filter = '';
		}

		$role_list = $rbacreview->getRolesByFilter(
			$type,
			0,
			$filter
		);


		$counter = 0;
		$rows = array();
		foreach((array) $role_list as $role)
		{
			$title = ilObjRole::_getTranslation($role['title']);

			if($type == ilRbacReview::FILTER_INTERNAL or $type == ilRbacReview::FILTER_ALL)
			{
				if(strlen($this->getFilterItemByPostVar('role_title')->getValue()))
				{
					if(stristr($title, $this->getFilterItemByPostVar('role_title')->getValue()) == FALSE)
					{
						continue;
					}
				}
			}
			$rows[$counter]['title_orig'] = $role['title'];
			$rows[$counter]['title'] = $title;
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
