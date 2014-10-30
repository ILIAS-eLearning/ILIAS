<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionTableEditGUI
{
	/**
	 * @var int
	 */
	private $table_id;
	/**
	 * @var ilDataCollectionTable
	 */
	private $table;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

	/**
	 * Constructor
	 *
	 * @param	ilObjDataCollectionGUI	$a_parent_obj
	 */
	public function __construct(ilObjDataCollectionGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->parent_object = $a_parent_obj;
		$this->obj_id = $a_parent_obj->obj_id;
		$this->table_id = $_GET['table_id'];
		$this->table = ilDataCollectionCache::getTableCache($this->table_id);
        if ( ! $this->checkPermission()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildatacollectionrecordlistgui', 'listRecords');
        }
	}

	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$this->tpl->getStandardTemplate();
		
		switch($cmd)
		{
			case 'update':
				$this->save("update");
				break;
			default:
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	 * create table add form
	 */
	public function create()
	{
		$this->initForm();
		$this->getStandardValues();
		$this->tpl->setContent($this->form->getHTML());
	}

	/**
	 * create field edit form
	 */
	public function edit()
	{
		if(!$this->table_id)
		{
			$this->ctrl->redirectByClass("ildatacollectionfieldeditgui", "listFields");
			return;
		}
		else
		{
			$this->table = ilDataCollectionCache::getTableCache($this->table_id);
		}
		$this->initForm("edit");
		$this->getValues();
		$this->tpl->setContent($this->form->getHTML());
	}

	/**
	 * getFieldValues
	 */
	public function getValues()
	{
		$values = array(
			'title'		=>	$this->table->getTitle(),
			'add_perm'		=>	$this->table->getAddPerm(),
			'edit_perm'		=>	$this->table->getEditPerm(),
			'delete_perm'		=>	$this->table->getDeletePerm(),
			'edit_by_owner'		=>	$this->table->getEditByOwner(),
            'export_enabled'    =>  $this->table->getExportEnabled(),
			'limited'		=>	$this->table->getLimited(),
			'limit_start'		=>	array("date" => substr($this->table->getLimitStart(),0,10), "time" => substr($this->table->getLimitStart(),-8)),
			'limit_end'		=>	array("date" => substr($this->table->getLimitEnd(),0,10), "time" => substr($this->table->getLimitEnd(),-8)),
			'is_visible'		=>	$this->table->getIsVisible(),
            'default_sort_field' => $this->table->getDefaultSortField(),
            'default_sort_field_order' => $this->table->getDefaultSortFieldOrder(),
            'description' => $this->table->getDescription(),
            'public_comments' => $this->table->getPublicCommentsEnabled(),
            'view_own_records_perm' => $this->table->getViewOwnRecordsPerm(),
		);
		if(!$this->table->getLimitStart())
			$values['limit_start'] = NULL;
		if(!$this->table->getLimitEnd())
			$values['limit_end'] = NULL;
		$this->form->setValuesByArray($values);
	}
	
	/**
	 * getStandardValues
	 */
	public function getStandardValues()
	{
		$values =  array(
			'title'		=>	"",
			'is_visible'		=>	1,
			'add_perm'		=>	1,
			'edit_perm'		=>	1,
			'delete_perm'		=>	1,
			'edit_by_owner'		=>	1,
			'export_enabled'		=>	0,
			'limited'		=>	0,
			'limit_start'		=>	NULL,
			'limit_end'		=>	NULL
		);
		$this->form->setValuesByArray($values);
	}
	
	/*
	 * cancel
	 */
	public function cancel()
	{
		$this->ctrl->redirectByClass("ilDataCollectionFieldListGUI", "listFields");
	}

	/**
	 * initEditCustomForm
	 *
	 * @param string $a_mode
	 */
	public function initForm($a_mode = "create")
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$item = new ilTextInputGUI($this->lng->txt('title'),'title');
		$item->setRequired(true);
		$this->form->addItem($item);
		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_visible'),'is_visible');
		$this->form->addItem($item);

        // Show default order field and direction only in edit mode, because table id is not yet given and there are no fields to select
        if ($a_mode != 'create') {
            $item = new ilSelectInputGUI($this->lng->txt('dcl_default_sort_field'), 'default_sort_field');
            $fields = $this->table->getVisibleFields();
            $options = array(0 => $this->lng->txt('dcl_please_select'));
            foreach ($fields as $field) {
                $options[$field->getId()] = $field->getTitle();
            }
            $item->setOptions($options);
            $this->form->addItem($item);

            $item = new ilSelectInputGUI($this->lng->txt('dcl_default_sort_field_order'), 'default_sort_field_order');
            $options = array('asc' => $this->lng->txt('dcl_asc'), 'desc' => $this->lng->txt('dcl_desc'));
            $item->setOptions($options);
            $this->form->addItem($item);
        }

        $item = new ilTextAreaInputGUI($this->lng->txt('additional_info'), 'description');
        $item->setUseRte(true);
//        $item->setRTESupport($this->table->getId(), 'dcl', 'table_settings');
        $item->setRteTagSet('mini');
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_public_comments'),'public_comments');
        $this->form->addItem($item);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('dcl_permissions_form'));
        $this->form->addItem($section);

        $item = new ilCustomInputGUI();
        $item->setHtml($this->lng->txt('dcl_table_info'));
        $item->setTitle($this->lng->txt('dcl_table_info_title'));
        $this->form->addItem($item);

		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_add_perm'),'add_perm');
//		$item->setInfo($this->lng->txt("dcl_add_perm_info"));
		$this->form->addItem($item);
		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_edit_perm'),'edit_perm');
//		$item->setInfo($this->lng->txt("dcl_edit_perm_info"));
		$this->form->addItem($item);
		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_delete_perm'),'delete_perm');
//		$item->setInfo($this->lng->txt("dcl_delete_perm_info"));
		$this->form->addItem($item);
		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_edit_by_owner'),'edit_by_owner');
//		$item->setInfo($this->lng->txt("dcl_edit_by_owner_info"));
		$this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_view_own_records_perm'),'view_own_records_perm');
//		$item->setInfo($this->lng->txt("dcl_edit_by_owner_info"));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_export_enabled'), 'export_enabled');
        $this->form->addItem($item);

		$item = new ilCheckboxInputGUI($this->lng->txt('dcl_limited'),'limited');
		$sitem1 = new ilDateTimeInputGUI($this->lng->txt('dcl_limit_start'),'limit_start');
        $sitem1->setShowTime(true);
		$sitem2 = new ilDateTimeInputGUI($this->lng->txt('dcl_limit_end'),'limit_end');
        $sitem2->setShowTime(true);
//		$item->setInfo($this->lng->txt("dcl_limited_info"));
		$item->addSubItem($sitem1);
		$item->addSubItem($sitem2);
		$this->form->addItem($item);

		if($a_mode == "edit")
		{
			$this->form->addCommandButton('update', 	$this->lng->txt('dcl_table_'.$a_mode));
		}
		else
		{
			$this->form->addCommandButton('save', 	$this->lng->txt('dcl_table_'.$a_mode));
		}
			
		$this->form->addCommandButton('cancel', 	$this->lng->txt('cancel'));
		$this->form->setFormAction($this->ctrl->getFormAction($this, $a_mode));
		if($a_mode == "edit")
		{
			$this->form->setTitle($this->lng->txt('dcl_edit_table'));
		}
		else
		{
			$this->form->setTitle($this->lng->txt('dcl_new_table'));
		}
	}

	
	/**
	 * save
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function save($a_mode = "create")
	{
		global $ilTabs;
		
		if(!ilObjDataCollection::_checkAccess($this->obj_id))
		{
			$this->accessDenied();
			return;
		}

		$ilTabs->activateTab("id_fields");
		
		$this->initForm($a_mode);
		
		if($this->checkInput($a_mode))
		{
            if($a_mode != "update")
            {
				$this->table = ilDataCollectionCache::getTableCache();
            }
			elseif($this->table_id)
            {
				$this->table = ilDataCollectionCache::getTableCache($this->table_id);
            }
			else
            {
				$this->ctrl->redirectByClass("ildatacollectionfieldeditgui", "listFields");
            }


			$this->table->setTitle($this->form->getInput("title"));
			$this->table->setObjId($this->obj_id);
			$this->table->setIsVisible($this->form->getInput("is_visible"));
			$this->table->setAddPerm($this->form->getInput("add_perm"));
			$this->table->setEditPerm($this->form->getInput("edit_perm"));
			$this->table->setDeletePerm($this->form->getInput("delete_perm"));
			$this->table->setEditByOwner($this->form->getInput("edit_by_owner"));
            $this->table->setViewOwnRecordsPerm($this->form->getInput('view_own_records_perm'));
            $this->table->setExportEnabled($this->form->getInput("export_enabled"));
            $this->table->setDefaultSortField($this->form->getInput("default_sort_field"));
            $this->table->setDefaultSortFieldOrder($this->form->getInput("default_sort_field_order"));
            $this->table->setPublicCommentsEnabled($this->form->getInput('public_comments'));
			$this->table->setLimited($this->form->getInput("limited"));
            $this->table->setDescription($this->form->getInput('description'));
			$limit_start = $this->form->getInput("limit_start");
			$limit_end = $this->form->getInput("limit_end");
			$this->table->setLimitStart($limit_start["date"]." ".$limit_start["time"]);
			$this->table->setLimitEnd($limit_end["date"]." ".$limit_end["time"]);

			if(!$this->table->hasPermissionToAddTable($this->parent_object->ref_id))
			{
				$this->accessDenied();
				return;
			}
			if($a_mode == "update")
			{
				$this->table->doUpdate();
				ilUtil::sendSuccess($this->lng->txt("dcl_msg_table_edited"), true);
				$this->ctrl->redirectByClass("ildatacollectiontableeditgui", "edit");
			}
			else
			{
				$this->table->doCreate();
				ilUtil::sendSuccess($this->lng->txt("dcl_msg_table_created"), true);
				$this->ctrl->setParameterByClass("ildatacollectionfieldlistgui","table_id", $this->table->getId());
				$this->ctrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
			}
		}
		else
		{
			$this->form->setValuesByPost();
			$this->tpl->setContent($this->form->getHTML());
		}
	}

    /**
     * Custom checks for the form input
     * @param $a_mode 'create' | 'update'
     * @return bool
     */
    protected function checkInput($a_mode) {
        $return = $this->form->checkInput();

        // Title of table must be unique in one DC
        if ($a_mode == 'create') {
            if ($title = $this->form->getInput('title')) {
                if (ilObjDataCollection::_hasTableByTitle($title, $this->obj_id)) {
                    $inputObj = $this->form->getItemByPostVar('title');
                    $inputObj->setAlert($this->lng->txt("dcl_table_title_unique"));
                    $return = false;
                }
            }
        }

        if (!$return) ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
        return $return;
    }

	/*
	 * accessDenied
	 */
	public function accessDenied()
	{
		$this->tpl->setContent("Access denied.");
	}

	/**
	 * confirmDelete
	 */
	public function confirmDelete()
	{
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('dcl_confirm_delete_table'));

		$conf->addItem('table', (int) $this->table->getId(), $this->table->getTitle());

		$conf->setConfirm($this->lng->txt('delete'), 'delete');
		$conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');

		$this->tpl->setContent($conf->getHTML());
	}

	/**
	 * cancelDelete
	 */
	public function cancelDelete()
	{
		$this->ctrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
	}

	/*
	  * delete
	  */
	public function delete()
	{
		$mainTableId = $this->table->getCollectionObject()->getMainTableId();
		if($mainTableId == $this->table->getId()){
			ilUtil::sendFailure($this->lng->txt("dcl_cant_delete_main_table"), true);
		}
		else{
			$this->ctrl->setParameterByClass("ildatacollectionfieldlistgui", "table_id", $mainTableId);
		}

		$this->table->doDelete();
		$this->ctrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
	}

    /**
     * @return bool
     */
    protected function checkPermission()
    {
        $ref_id = $this->parent_object->getDataCollectionObject()->getRefId();
        return ilObjDataCollection::_hasWriteAccess($ref_id);
    }

}

?>