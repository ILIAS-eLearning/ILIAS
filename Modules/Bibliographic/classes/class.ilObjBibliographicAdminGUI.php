<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once('./Modules/Bibliographic/classes/class.ilObjBibliographicSettingFormGUI.php');
include_once('./Modules/Bibliographic/classes/class.ilObjBibliographicAdminTableGUI.php');
include_once('./Modules/Bibliographic/classes/class.ilBibliographicSetting.php');


/**
 * Bibliographic Administration Settings.
 *
 * @author Theodor Truffer
 *
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilPermissionGUI, ilObjBibliographicSettingFormGUI
 *
 * @ingroup ModulesBibliographic
 */
class ilObjBibliographicAdminGUI extends ilObjectGUI {

    /**Constructor
     *
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = "bibs";
        parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("bibl");
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch($next_class)
        {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if(!$cmd || $cmd == 'view')
                {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        global $rbacsystem;

        if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
        {
            $this->tabs_gui->addTarget("settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view"));
        }

        if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
        {
            $this->tabs_gui->addTarget("perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
                array(),'ilpermissiongui');
        }
    }


    /**
     * Edit settings.
     */
    public function editSettings($a_form = null)
    {
        $this->tabs_gui->setTabActive('settings');

        if(!$a_form)
        {
            $a_form = $this->initFormSettings();
            $a_table = $this->initTableSettings();
        }
        $this->tpl->setContent($a_table->getHTML().$a_form->getHTML());
        return true;
    }

    /**
     * add library
     */
    public function add()
    {
        global $ilCtrl;
        $this->checkPermission("write");
        $form = new ilObjBibliographicSettingFormGUI($this, new ilBibliographicSetting());

        $this->tpl->setContent($form->getHTML());
        $this->tabs_gui->setTabActive('settings');
    }

    /**
     * edit library
     */
    public function edit(){
        global $ilCtrl;

        $this->checkPermission("write");
        $this->ctrl->saveParameter($this, 'lib_id');
        $form = new ilObjBibliographicSettingFormGUI($this, new ilBibliographicSetting($_REQUEST["lib_id"]));

        $this->tpl->setContent($form->getHTML());
        $this->tabs_gui->setTabActive('settings');
    }

    /**
     * create library
     */
    public function create()
    {
        $this->checkPermission("write");
        $form = new ilObjBibliographicSettingFormGUI($this, new ilBibliographicSetting());
        $form->setValuesByPost();
        if($form->saveObject()){
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, 'view');
        }

        $this->tpl->setContent($form->getHTML());
        $this->tabs_gui->setTabActive('settings');
    }

    /**
     * save changes in library
     */
    public function update(){
        $this->checkPermission("write");
        $form = new ilObjBibliographicSettingFormGUI($this, new ilBibliographicSetting($_REQUEST["lib_id"]));
        $form->setValuesByPost();
        if($form->saveObject()){
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, 'view');
        }

        $this->tpl->setContent($form->getHTML());
        $this->tabs_gui->setTabActive('settings');
    }

    /**
     * delete library
     */
    public function delete(){
        $this->checkPermission("write");

        global $ilDB, $ilCtrl;
        $ilDB->manipulate("DELETE FROM il_bibl_settings WHERE id = ".$ilDB->quote($_REQUEST["lib_id"], "integer"));
        $ilCtrl->redirect($this, 'view');
    }

    public function cancel(){
        $this->ctrl->redirect($this, 'view');
    }

    /**
     * Save settings in Form
     *
     */
    public function saveForm()
    {
        global $ilCtrl;

        $this->checkPermission("write");

        $form = $this->initFormSettings();
        if($form->checkInput())
        {
            $bibl_set = new ilSetting("bibl");
            $bibl_set->set("bib_ord", $form->getInput("bib_ord"));
            $bibl_set->set("ris_ord", $form->getInput("ris_ord"));


            ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }

    /**
     * Init settings property form
     *
     * @access protected
     */
    protected function initFormSettings()
    {
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('bibl_admin_settings'));

        $bibl_set = new ilSetting("bibl");

        $bibtex_order = new ilTextInputGUI($this->lng->txt("attr_order_bibtex"), 'bib_ord');
        $bibtex_order->setValue($bibl_set->get("bib_ord"), true);
        $bibtex_order->setInfo($this->lng->txt("attr_order_bibtex_info"));
        $form->addItem($bibtex_order);

        $ris_order = new ilTextInputGUI($this->lng->txt("attr_order_ris"), 'ris_ord');
        $ris_order->setValue($bibl_set->get("ris_ord"), true);
        $ris_order->setInfo($this->lng->txt("attr_order_ris_info"));
        $form->addItem($ris_order);

        $form->addCommandButton('saveForm', $this->lng->txt("save"));

        return $form;
    }

    /**
     * Init Table with library entries
     *
     * @access protected
     */
    protected function initTableSettings(){
        global $ilDB;
        $table = new ilObjBibliographicAdminTableGUI($this, 'library');

        $settings = ilBibliographicSetting::getAll();
        $result = array();
        foreach($settings as $set){
            $result[] = array(
                "id" => $set->getId(),
                "name" => $set->getName(),
                "url" => $set->getBaseUrl(),
                "img" => $set->getImageUrl()
            );
        }
        $table->setData($result);

        return $table;
    }
}