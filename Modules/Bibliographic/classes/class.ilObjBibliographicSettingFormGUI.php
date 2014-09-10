<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
include_once('./Modules/Bibliographic/classes/class.ilObjBibliographicAdminGUI.php');


/**
 * Bibliographic Settings Form.
 *
 * @author Theodor Truffer
 *
 * @ilCtrl_Calls ilObjBibliographicSettingFormGUI: ilObjBibliographicAdminGUI
 *
 * @ingroup ModulesBibliographic
 */
class ilObjBibliographicSettingFormGUI extends ilPropertyFormGUI{

    protected $bibl_setting;
    protected $parent_gui;
    protected $ctrl;
    protected $lng;
    protected $lib_id;
    protected $cmd;

    /**Constructor
     *
     */
    public function __construct($parent_gui, $bibl_setting)
    {
        global $ilCtrl, $lng;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->parent_gui = $parent_gui;
        $this->bibl_setting = $bibl_setting;
        if($bibl_setting->getId() > 0){
            $this->cmd = 'update';
        }else{
            $this->cmd = 'create';
        }
        $this->initForm();
        $this->ctrl->saveParameter($parent_gui, 'lib_id');
    }


    /**
     * Init settings property form
     *
     * @access private
     */
    private function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        $name = new ilTextInputGUI($this->lng->txt("bibl_library_name"), 'name');
        $name->setRequired(true);
        $name->setValue('');
        $this->addItem($name);

        $url = new ilTextInputGUI($this->lng->txt("bibl_library_url"), 'url');
        $url->setRequired(true);
        $url->setValue('');
        $this->addItem($url);

        $img = new ilTextInputGUI($this->lng->txt("bibl_library_img"), 'img');
        $img->setValue('');
        $this->addItem($img);

        switch($this->cmd){
            case 'create':
                $this->setTitle($this->lng->txt("bibl_settings_new"));
                $this->addCommandButton('create', $this->lng->txt('save'));
                break;
            case 'update':
                $this->addCommandButton('update', $this->lng->txt('save'));
                $this->fillForm();
                $this->setTitle($this->lng->txt("bibl_settings_edit"));
                break;
        }

        $this->addCommandButton('cancel', $this->lng->txt("cancel"));


    }

    private function fillForm(){
        $this->setValuesByArray(array('name' => $this->bibl_setting->getName(),
            'url' => $this->bibl_setting->getBaseUrl(),
            'img' => $this->bibl_setting->getImageUrl()));
    }

    public function saveObject()
    {
        if(!$this->checkInput()){
            return false;
        }
        $this->bibl_setting->setName($this->getInput("name"));
        $this->bibl_setting->setBaseUrl($this->getInput("url"));
        $this->bibl_setting->setImageUrl($this->getInput("img"));

        switch($this->cmd){
            case 'create':
                $this->bibl_setting->create();
                break;
            case 'update':
                $this->bibl_setting->update();
                break;
        }

        return true;
    }


} 