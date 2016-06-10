<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php");
require_once ("./Modules/DataCollection/classes/class.ilDclTable.php");
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php");
require_once "class.ilDclCache.php";
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once('./Modules/DataCollection/classes/class.ilDclFieldListTableGUI.php');

/**
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Oskar Truffer <ot@studer-raimann.ch>
* @author Stefan Wanzenried <sw@studer-raimann.ch>
* @version $Id: 
*
*
* @ingroup ModulesDataCollection
*/
class ilDclFieldListGUI
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
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
	 * Constructor
	 *
	 * @param	ilObjDataCollectionGUI	$a_parent_obj
	 * @param	int $table_id
	 */
	public function  __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id)
	{
        global $ilCtrl, $lng, $ilToolbar, $tpl, $ilTabs;

		$this->table_id = $table_id;
		$this->parent_obj = $a_parent_obj;
		$this->obj_id = $a_parent_obj->obj_id;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;
        if ( ! $this->checkAccess()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
	}
	
	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			default:
				$this->$cmd();
				break;
		}
	}

    /**
     * Delete multiple fields
     */
    public function deleteFields()
    {
        $field_ids = isset($_POST['dcl_field_ids']) ? $_POST['dcl_field_ids'] : array();
        $table = ilDclCache::getTableCache($this->table_id);
        foreach ($field_ids as $field_id) {
            $table->deleteField($field_id);
        }
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_fields_deleted'), true);
        $this->ctrl->redirect($this, 'listFields');
    }

    /**
     * Confirm deletion of multiple fields
     */
    public function confirmDeleteFields() {
        $this->tabs->clearSubTabs();
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_fields'));
        $field_ids = isset($_POST['dcl_field_ids']) ? $_POST['dcl_field_ids'] : array();
        foreach ($field_ids as $field_id) {
            /** @var ilDclBaseFieldModel $field */
            $field = ilDclCache::getFieldCache($field_id);
            $conf->addItem('dcl_field_ids[]', $field_id, $field->getTitle());
        }
        $conf->setConfirm($this->lng->txt('delete'), 'deleteFields');
        $conf->setCancel($this->lng->txt('cancel'), 'listFields');
        $this->tpl->setContent($conf->getHTML());
    }

    /*
     * save
     */
	public function save()
	{
		$table = ilDclCache::getTableCache($_GET['table_id']);
		$fields = $table->getFields();

		foreach($fields as $field)
		{
			$field->setLocked($_POST['locked'][$field->getId()] == "on");
			$field->setExportable($_POST['exportable'][$field->getId()] == "on");
			$field->setOrder($_POST['order'][$field->getId()]);
			$field->doUpdate();
		}
		$table->buildOrderFields();
		ilUtil::sendSuccess($this->lng->txt("dcl_table_settings_saved"));
		$this->listFields();
	}
	
	/**
	 * list fields
	 */
	public function listFields()
	{
		// Show tables
		require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
		$tables = $this->parent_obj->object->getTables();

		foreach($tables as $table)
		{
				$options[$table->getId()] = $table->getTitle();
		}
		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		$table_selection = new ilSelectInputGUI('', 'table_id');
		$table_selection->setOptions($options);
		$table_selection->setValue($this->table_id);

		$this->toolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclFieldListGUI", "doTableSwitch"));
        $this->toolbar->addText($this->lng->txt("dcl_table"));
		$this->toolbar->addInputItem($table_selection);
		$this->toolbar->addFormButton($this->lng->txt('change'),'doTableSwitch');
        $this->toolbar->addSeparator();
		$this->toolbar->addButton($this->lng->txt("dcl_add_new_table"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "create"));
        $this->toolbar->addSeparator();
        $this->ctrl->setParameterByClass("ildcltableeditgui", "table_id", $this->table_id);
		$this->toolbar->addButton($this->lng->txt("dcl_table_settings"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "edit"));
		$this->toolbar->addSeparator();
		$this->toolbar->addButton($this->lng->txt("dcl_delete_table"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "confirmDelete"));
		$this->toolbar->addSeparator();
		$this->toolbar->addButton($this->lng->txt("dcl_add_new_field"), $this->ctrl->getLinkTargetByClass("ildclfieldeditgui", "create"));
		$list = new ilDclFieldListTableGUI($this, $this->ctrl->getCmd(), $this->table_id);
		$this->tpl->setContent($list->getHTML());

	}
	
	/*
	 * doTableSwitch
	 */
	public function doTableSwitch()
	{
		$this->ctrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
		$this->ctrl->redirectByClass("ilDclFieldListGUI", "listFields");
	}

    /**
     * @return bool
     */
    protected function checkAccess()
    {
        $ref_id = $this->parent_obj->getDataCollectionObject()->getRefId();
        return ilObjDataCollection::_hasWriteAccess($ref_id);
    }

}

?>