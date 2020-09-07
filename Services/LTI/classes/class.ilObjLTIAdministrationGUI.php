<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
// require_once 'Services/LTI/classes/ActiveRecord/class.ilLTIExternalConsumer.php';
require_once 'Services/LTI/classes/InternalProvider/class.ilLTIToolConsumer.php';
require_once 'Services/LTI/classes/class.ilLTIDataConnector.php';


/**
 * Class ilObjLTIAdministrationGUI
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesLTI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{
    /**
     * Data connector object or string.
     *
     * @var mixed $dataConnector
     */
    private $dataConnector = null;
    
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = "ltis";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->dataConnector = new ilLTIDataConnector();
        
        $GLOBALS['DIC']->language()->loadLanguageModule('lti');
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $GLOBALS['ilTabs']->activateTab('perm_settings');
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = 'listConsumers';
                } elseif ($cmd == 'createconsumer') {
                    $cmd = "initConsumerForm";
                }
                $this->$cmd();
                break;
        }
    }

    public function getType()
    {
        return "ltis";
    }

    public function getAdminTabs()
    {
        global $rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            // currently no general settings.
            //			$this->tabs_gui->addTab("settings",
            //				$this->lng->txt("settings"),
            //				$this->ctrl->getLinkTarget($this, "initSettingsForm"));

            $this->tabs_gui->addTab(
                "consumers",
                $this->lng->txt("consumers"),
                $this->ctrl->getLinkTarget($this, "listConsumers")
            );
        }
        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "releasedObjects",
                $this->lng->txt("lti_released_objects"),
                $this->ctrl->getLinkTarget($this, "releasedObjects")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    protected function initSettingsForm(ilPropertyFormGUI $form = null)
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getSettingsForm();
        }
        $this->tabs_gui->activateTab("settings");
        $this->tpl->setContent($form->getHTML());
    }


    protected function getSettingsForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        /*
        $form->setFormAction($this->ctrl->getFormAction($this,'saveSettingsForm'));
        $form->setTitle($this->lng->txt("lti_settings"));

        // object types
        $cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

        $valid_obj_types = $this->object->getLTIObjectTypes();
        foreach($valid_obj_types as $obj_type_id => $obj_name)
        {
            $cb_obj_types->addOption(new ilCheckboxOption($obj_name, $obj_type_id));
        }
        $objs_active = $this->object->getActiveObjectTypes();
        $cb_obj_types->setValue($objs_active);
        $form->addItem($cb_obj_types);

        // test roles
        $roles = $this->object->getLTIRoles();
        foreach($roles as $role_id => $role_name)
        {
            $options[$role_id] = $role_name;
        }
        $si_roles = new ilSelectInputGUI($this->lng->txt("gbl_roles_to_users"), 'roles');
        $si_roles->setOptions($options);
        $si_roles->setValue($this->object->getCurrentRole());
        $form->addItem($si_roles);

        $form->addCommandButton("saveSettingsForm", $this->lng->txt("save"));
        */
        return $form;
    }

    /*
    protected function saveSettingsForm()
    {
        global $ilCtrl;

        $this->checkPermission("write");

        $form = $this->getSettingsForm();
        if($form->checkInput())
        {
            $obj_types = $form->getInput('types');

            $role = $form->getInput('role');

            $this->object->saveData($obj_types, $role);

            ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
        }

        $form->setValuesByPost();
        $this->initSettingsForm($form);
    }
    */

    // consumers

    protected function initConsumerForm(ilPropertyFormGUI $form = null)
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getConsumerForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param string $a_mode
     * @return ilPropertyFormGUI
     */
    protected function getConsumerForm($a_mode = '')
    {
        $this->tabs_gui->activateTab("consumers");

        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();

        $ti_title = new ilTextInputGUI($this->lng->txt("title"), 'title');
        $ti_title->setRequired(true);
        $ti_description = new ilTextInputGUI($this->lng->txt("description"), 'description');
        $ti_prefix = new ilTextInputGUI($this->lng->txt("prefix"), 'prefix');
        $ti_prefix->setRequired(true);
        #$ti_key = new ilTextInputGUI($this->lng->txt("lti_consumer_key"), 'key');
        #$ti_key->setRequired(true);
        #$ti_secret = new ilTextInputGUI($this->lng->txt("lti_consumer_secret"), 'secret');
        #$ti_secret->setRequired(true);

        $languages = $this->lng->getInstalledLanguages();
        $array_lang = array();
        foreach ($languages as $lang_key) {
            $array_lang[$lang_key] = ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_" . $lang_key);
        }

        $si_language = new ilSelectInputGUI($this->lng->txt("language"), "language");
        $si_language->setOptions($array_lang);
        
        $cb_active = new ilCheckboxInputGUI($this->lng->txt('active'), 'active');

        $form->addItem($cb_active);
        $form->addItem($ti_title);
        $form->addItem($ti_description);
        $form->addItem($ti_prefix);
        #$form->addItem($ti_key);
        #$form->addItem($ti_secret);
        $form->addItem($si_language);

        // object types
        $cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

        $valid_obj_types = $this->object->getLTIObjectTypes();
        foreach ($valid_obj_types as $obj_type) {
            $object_name = $GLOBALS['DIC']->language()->txt('objs_' . $obj_type);
            $cb_obj_types->addOption(new ilCheckboxOption($object_name, $obj_type));
        }
        $form->addItem($cb_obj_types);

        // test roles
        $roles = $this->object->getLTIRoles();
        foreach ($roles as $role_id => $role_name) {
            $options[$role_id] = $role_name;
        }
        $si_roles = new ilSelectInputGUI($this->lng->txt("gbl_roles_to_users"), 'role');
        $si_roles->setOptions($options);
        $form->addItem($si_roles);

        if ($a_mode == 'edit') {
            $form->setFormAction($this->ctrl->getFormAction($this, 'editLTIConsumer'));
            $form->setTitle($this->lng->txt("lti_edit_consumer"));
            $form->addCommandButton("updateLTIConsumer", $this->lng->txt("save"));
        } else {
            $form->setFormAction($this->ctrl->getFormAction($this, 'createLTIConsumer'));
            $form->setTitle($this->lng->txt("lti_create_consumer"));
            $form->addCommandButton("createLTIConsumer", $this->lng->txt("save"));
            $form->addCommandButton('listConsumers', $this->lng->txt('cancel'));
        }

        return $form;
    }

    /**
     * Edit consumer
     * @global type $ilCtrl
     * @global type $tpl
     * @param ilPropertyFormGUI $a_form
     */
    protected function editConsumer(ilPropertyFormGUI $a_form = null)
    {
        global $ilCtrl, $tpl;
        $consumer_id = $_REQUEST["cid"];
        $ilCtrl->setParameter($this, "cid", $consumer_id);

        if (!$consumer_id) {
            $ilCtrl->redirect($this, "listConsumers");
        }

        $consumer = ilLTIToolConsumer::fromExternalConsumerId($consumer_id, $this->dataConnector);
        if (!$a_form instanceof ilPropertyFormGUI) {
            $a_form = $this->getConsumerForm('edit');
            $a_form->getItemByPostVar("title")->setValue($consumer->getTitle());
            $a_form->getItemByPostVar("description")->setValue($consumer->getDescription());
            $a_form->getItemByPostVar("prefix")->setValue($consumer->getPrefix());
            $a_form->getItemByPostVar("language")->setValue($consumer->getLanguage());
            $a_form->getItemByPostVar("active")->setChecked($consumer->getActive());
            $a_form->getItemByPostVar("role")->setValue($consumer->getRole());
            $a_form->getItemByPostVar("types")->setValue($this->object->getActiveObjectTypes($consumer_id));
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new lti consumer
     */
    protected function createLTIConsumer()
    {
        $this->checkPermission("write");

        $form = $this->getConsumerForm();
        
        if ($form->checkInput()) {
            // $consumer = new ilLTIExternalConsumer();
            // $dataConnector = new ilLTIDataConnector();
            $consumer = new ilLTIToolConsumer(null, $this->dataConnector);
            $consumer->setTitle($form->getInput('title'));
            $consumer->setDescription($form->getInput('description'));
            $consumer->setPrefix($form->getInput('prefix'));
            $consumer->setLanguage($form->getInput('language'));
            $consumer->setActive($form->getInput('active'));
            $consumer->setRole($form->getInput('role'));
            $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
            
            $this->object->saveConsumerObjectTypes(
                $consumer->getExtConsumerId(),
                $form->getInput('types')
            );
            ilUtil::sendSuccess($this->lng->txt("lti_consumer_created"), true);
            $GLOBALS['DIC']->ctrl()->redirect($this, 'listConsumers');
        }

        $form->setValuesByPost();
        $this->listConsumers();
        return;
    }

    /**
     * Update lti consumer settings
     * @global ilCtrl $ilCtrl
     */
    protected function updateLTIConsumer()
    {
        global $ilCtrl;

        $this->checkPermission("write");

        $consumer_id = $_REQUEST["cid"];
        if (!$consumer_id) {
            $ilCtrl->redirect($this, "listConsumers");
        }

        $ilCtrl->setParameter($this, "cid", $consumer_id);

        $consumer = ilLTIToolConsumer::fromExternalConsumerId($consumer_id, $this->dataConnector);
        $form = $this->getConsumerForm('edit');
        if ($form->checkInput()) {
            $consumer->setTitle($form->getInput('title'));
            $consumer->setDescription($form->getInput('description'));
            $consumer->setPrefix($form->getInput('prefix'));
            $consumer->setLanguage($form->getInput('language'));
            $consumer->setActive($form->getInput('active'));
            $consumer->setRole($form->getInput('role'));
            $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
            $this->object->saveConsumerObjectTypes($consumer_id, $form->getInput('types'));

            ilUtil::sendSuccess($this->lng->txt("lti_consumer_updated"), true);
        }
        $this->listConsumers();
    }

    /**
     * Delete consumers
     * @global type $ilCtrl
     */
    protected function deleteLTIConsumer()
    {
        global $ilCtrl;

        $consumer_id = $_REQUEST['cid'];

        if (!$consumer_id) {
            $ilCtrl->redirect($this, "listConsumers");
        }
        $consumer = ilLTIToolConsumer::fromExternalConsumerId($consumer_id, $this->dataConnector);
        $consumer->deleteGlobalToolConsumerSettings($this->dataConnector);
        ilUtil::sendSuccess($this->lng->txt("lti_consumer_deleted"), true);
        $GLOBALS['DIC']->ctrl()->redirect($this, 'listConsumers');
    }


    /**
     * List consumers
     * @global type $ilAccess
     * @global type $ilToolbar
     */
    protected function listConsumers()
    {
        global $ilAccess, $ilToolbar;

        if ($this->checkPermissionBool('write')) {
            $ilToolbar->addButton(
                $this->lng->txt('lti_create_consumer'),
                $this->ctrl->getLinkTarget($this, 'createconsumer')
            );
        }

        $this->tabs_gui->activateTab("consumers");

        include_once "Services/LTI/classes/Consumer/class.ilLTIConsumerTableGUI.php";
        $tbl = new ilObjectConsumerTableGUI(
            $this,
            "listConsumers"
        );
        $tbl->setEditable($this->checkPermissionBool('write'));
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Change activation status
     * @global type $ilCtrl
     */
    protected function changeStatusLTIConsumer()
    {
        global $ilCtrl;

        $consumer_id = $_REQUEST["cid"];

        if (!$consumer_id) {
            $ilCtrl->redirect($this, "listConsumers");
        }

        $consumer = ilLTIToolConsumer::fromExternalConsumerId($consumer_id, $this->dataConnector);
        if ($consumer->getActive()) {
            $consumer->setActive(0);
            $msg = "lti_consumer_set_inactive";
        } else {
            $consumer->setActive(1);
            $msg = "lti_consumer_set_active";
        }
        $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
        ilUtil::sendSuccess($this->lng->txt($msg), true);
        
        $GLOBALS['DIC']->ctrl()->redirect($this, 'listConsumers');
    }
    
    /**
     * Show relases objects
     */
    protected function releasedObjects()
    {
        $GLOBALS['DIC']->tabs()->activateTab('releasedObjects');
        
        $table = new ilLTIProviderReleasedObjectsTableGUI($this, 'releasedObjects', 'ltireleases');
        $table->init();
        $table->parse();
        
        $GLOBALS['DIC']->ui()->mainTemplate()->setContent($table->getHTML());
    }
}
