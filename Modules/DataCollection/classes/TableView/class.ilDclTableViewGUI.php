<?php

/**
 * Class ilDclTableViewGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewGUI
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

        $this->main_table_id = $a_parent_obj->object->getMainTableId();
        $this->table_id = $table_id;
        $this->parent_obj = $a_parent_obj;
        $this->obj_id = $a_parent_obj->obj_id;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("show");
        switch($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     *
     */
    public function show() {
        // Show tables
        require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
        $tables = $this->parent_obj->object->getTables();

        foreach($tables as $table)
        {
            $options[$table->getId()] = $table->getTitle(); //TODO order tables
        }
        include_once './Services/Form/classes/class.ilSelectInputGUI.php';
        $table_selection = new ilSelectInputGUI('', 'table_id');
        $table_selection->setOptions($options);
        $table_selection->setValue($this->table_id);

        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclTableViewGUI", "doTableSwitch"));
        $this->toolbar->addText($this->lng->txt("dcl_table"));
        $this->toolbar->addInputItem($table_selection);
        $button = ilSubmitButton::getInstance();
//        ilSubmitButton::getInstance();
//        $button->setUrl($this->ctrl->getLinkTarget($this, 'doTableSwitch'));
        $button->setCommand("doTableSwitch");
        $button->setCaption($this->lng->txt('change'));
        $this->toolbar->addButtonInstance($button);
//        $this->toolbar->addFormButton($this->lng->txt('change'),'doTableSwitch');
//        $this->toolbar->addSeparator();
//        $this->toolbar->addButton($this->lng->txt("dcl_add_new_table"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "create"));
//        $this->toolbar->addSeparator();
//        $this->ctrl->setParameterByClass("ildcltableeditgui", "table_id", $this->table_id);
//        $this->toolbar->addButton($this->lng->txt("dcl_table_settings"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "edit"));
//        $this->toolbar->addSeparator();
//        $this->toolbar->addButton($this->lng->txt("dcl_delete_table"), $this->ctrl->getLinkTargetByClass("ildcltableeditgui", "confirmDelete"));
//        $this->toolbar->addSeparator();
//        $this->toolbar->addButton($this->lng->txt("dcl_add_new_field"), $this->ctrl->getLinkTargetByClass("ildclfieldeditgui", "create"));
    }

    /*
     * doTableSwitch
     */
    public function doTableSwitch()
    {
        $this->ctrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
        $this->ctrl->redirectByClass("ilDclTableViewGUI", "show");
    }


}