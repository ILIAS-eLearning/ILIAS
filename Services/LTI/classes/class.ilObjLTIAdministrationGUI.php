<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilObjLTIAdministrationGUI
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilLTIConsumerAdministrationGUI
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesLTI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{
    private ?ilLTIDataConnector $dataConnector = null;

    private ?int $consumer_id = null;

    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('lti'); //&ltis
        $this->type = "ltis";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->dataConnector = new ilLTIDataConnector();

        if ($DIC->http()->wrapper()->query()->has("cid")) {
            $this->consumer_id = (int) $DIC->http()->wrapper()->query()->retrieve("cid", $DIC->refinery()->kindlyTo()->int());
        }
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $GLOBALS['ilTabs']->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'illticonsumeradministrationgui':
                $this->tabs_gui->activateTab('lti_consuming');
                $gui = new ilLTIConsumerAdministrationGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->tabs_gui->activateTab('lti_providing');
                if (!$cmd || $cmd == 'view') {
                    $cmd = 'listConsumers';
                } elseif ($cmd == 'createconsumer') {
                    $cmd = "initConsumerForm";
                }
                $this->$cmd();
                break;
        }
    }

    public function getType() : string
    {
        return "ltis";
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'lti_providing',
                $this->lng->txt("lti_providing_tab"),
                $this->ctrl->getLinkTarget($this, "listConsumers")
            );

            $this->tabs_gui->addTab(
                'lti_consuming',
                $this->lng->txt("lti_consuming_tab"),
                $this->ctrl->getLinkTargetByClass('ilLTIConsumerAdministrationGUI')
            );

            if ($this->ctrl->getCmdClass() == 'ilobjltiadministrationgui') {
                $this->addProvidingSubtabs();
            }
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    protected function addProvidingSubtabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            // currently no general settings.
            //			$this->tabs_gui->addTab("settings",
            //				$this->lng->txt("settings"),
            //				$this->ctrl->getLinkTarget($this, "initSettingsForm"));

            $this->tabs_gui->addSubTab(
                "consumers",
                $this->lng->txt("consumers"),
                $this->ctrl->getLinkTarget($this, "listConsumers")
            );
        }
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addSubTab(
                "releasedObjects",
                $this->lng->txt("lti_released_objects"),
                $this->ctrl->getLinkTarget($this, "releasedObjects")
            );
        }
    }

    protected function initSettingsForm(ilPropertyFormGUI $form = null) : void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getSettingsForm();
        }
        $this->tabs_gui->activateSubTab("settings");
        $this->tpl->setContent($form->getHTML());
    }


    protected function getSettingsForm() : \ilPropertyFormGUI
    {
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
    
    // create global role LTI-User
    protected function createLtiUserRole() : void
    {
        // include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $role = new ilObjRole();
        $role->setTitle("il_lti_global_role");
        $role->setDescription("This global role should only contain the permission 'read' for repository and categories.");
        $role->create();
        $this->rbac_admin->assignRoleToFolder($role->getId(), 8, 'y');
        $this->rbac_admin->setProtected(8, $role->getId(), 'y');
        $this->rbac_admin->setRolePermission($role->getId(), 'root', [3], 8);
        $this->rbac_admin->setRolePermission($role->getId(), 'cat', [3], 8);
        $this->rbac_admin->grantPermission($role->getId(), [3], ROOT_FOLDER_ID);
        $role->changeExistingObjects(
            ROOT_FOLDER_ID,
            ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
            array('cat'),
            array()
        );
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("lti_user_role_created"), true);
        $this->listConsumers();
    }
    

    // consumers

    protected function initConsumerForm(ilPropertyFormGUI $form = null) : void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getConsumerForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function getConsumerForm(string $a_mode = '') : \ilPropertyFormGUI
    {
        $this->tabs_gui->activateSubTab("consumers");

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
        $array_lang = [];
        $options = [];
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
            $object_name = $this->lng->txt('objs_' . $obj_type);
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
     * @param ilPropertyFormGUI $a_form
     */
    protected function editConsumer(ilPropertyFormGUI $a_form = null) : void
    {
        $this->ctrl->setParameter($this, "cid", $this->consumer_id);

        if (!$this->consumer_id) {
            $this->ctrl->redirect($this, "listConsumers");
        }

        $consumer = ilLTIPlatform::fromExternalConsumerId($this->consumer_id, $this->dataConnector);
        if (!$a_form instanceof ilPropertyFormGUI) {
            $a_form = $this->getConsumerForm('edit');
            $a_form->getItemByPostVar("title")->setValue($consumer->getTitle());
            $a_form->getItemByPostVar("description")->setValue($consumer->getDescription());
            $a_form->getItemByPostVar("prefix")->setValue($consumer->getPrefix());
            $a_form->getItemByPostVar("language")->setValue($consumer->getLanguage());
            $a_form->getItemByPostVar("active")->setChecked($consumer->getActive());
            $a_form->getItemByPostVar("role")->setValue($consumer->getRole());
            $a_form->getItemByPostVar("types")->setValue($this->object->getActiveObjectTypes($this->consumer_id));
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * Create new lti consumer
     */
    protected function createLTIConsumer() : void
    {
        $this->checkPermission("write");

        $form = $this->getConsumerForm();
        
        if ($form->checkInput()) {
            // $consumer = new ilLTIExternalConsumer();
            // $dataConnector = new ilLTIDataConnector();
            $consumer = new ilLTIPlatform(null, $this->dataConnector);
            $consumer->setTitle($form->getInput('title'));
            $consumer->setDescription($form->getInput('description'));
            $consumer->setPrefix($form->getInput('prefix'));
            $consumer->setLanguage($form->getInput('language'));
            $consumer->setActive((bool) $form->getInput('active'));
            $consumer->setRole((int) $form->getInput('role'));
            $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
            
            $this->object->saveConsumerObjectTypes(
                $consumer->getExtConsumerId(),
                $form->getInput('types')
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("lti_consumer_created"), true);
            $this->ctrl->redirect($this, 'listConsumers');
        }

        $form->setValuesByPost();
        $this->listConsumers();
        return;
    }

    /**
     * Update lti consumer settings
     */
    protected function updateLTIConsumer() : void
    {
        $this->checkPermission("write");

        if (!$this->consumer_id) {
            $this->ctrl->redirect($this, "listConsumers");
        }

        $this->ctrl->setParameter($this, "cid", $this->consumer_id);

        $consumer = ilLTIPlatform::fromExternalConsumerId($this->consumer_id, $this->dataConnector);
        $form = $this->getConsumerForm('edit');
        if ($form->checkInput()) {
            $consumer->setTitle($form->getInput('title'));
            $consumer->setDescription($form->getInput('description'));
            $consumer->setPrefix($form->getInput('prefix'));
            $consumer->setLanguage($form->getInput('language'));
            $consumer->setActive((bool) $form->getInput('active'));
            $consumer->setRole((int) $form->getInput('role'));
            $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
            $this->object->saveConsumerObjectTypes($this->consumer_id, $form->getInput('types'));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("lti_consumer_updated"), true);
        }
        $this->listConsumers();
    }

    /**
     * Delete consumers
     */
    protected function deleteLTIConsumer() : void
    {
        $consumer_id = 0;
        if ($this->request_wrapper->has('cid')) {
            $consumer_id = (int) $this->request_wrapper->retrieve('cid', $this->refinery->kindlyTo()->int());
        }

        if ($consumer_id == 0) {
            $this->ctrl->redirect($this, "listConsumers");
        }
        $consumer = ilLTIPlatform::fromExternalConsumerId($consumer_id, $this->dataConnector);
        $consumer->deleteGlobalToolConsumerSettings($this->dataConnector);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("lti_consumer_deleted"), true);
        $this->ctrl->redirect($this, 'listConsumers');
    }


    /**
     * List consumers
     */
    protected function listConsumers() : void
    {
        if ($this->checkPermissionBool('write')) {
            $this->toolbar->addButton(
                $this->lng->txt('lti_create_consumer'),
                $this->ctrl->getLinkTarget($this, 'createconsumer')
            );
            if (ilObject::_getIdsForTitle("il_lti_global_role", "role", false) == false) {
                $this->toolbar->addButton(
                    $this->lng->txt('lti_create_lti_user_role'),
                    $this->ctrl->getLinkTarget($this, 'createLtiUserRole')
                );
                $this->toolbar->addText($this->lng->txt('lti_user_role_info'));
            }
        }

        $this->tabs_gui->activateSubTab("consumers");
        $tbl = new ilObjectConsumerTableGUI(
            $this,
            "listConsumers"
        );
        $tbl->setEditable($this->checkPermissionBool('write'));
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Change activation status
     */
    protected function changeStatusLTIConsumer() : void
    {
        if (!$this->consumer_id) {
            $this->ctrl->redirect($this, "listConsumers");
        }

        $consumer = ilLTIPlatform::fromExternalConsumerId($this->consumer_id, $this->dataConnector);
        if ($consumer->getActive()) {
            $consumer->setActive(false);
            $msg = "lti_consumer_set_inactive";
        } else {
            $consumer->setActive(true);
            $msg = "lti_consumer_set_active";
        }
        $consumer->saveGlobalToolConsumerSettings($this->dataConnector);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt($msg), true);

        $this->ctrl->redirect($this, 'listConsumers');
    }
    
    /**
     * Show relases objects
     */
    protected function releasedObjects() : void
    {
        $this->tabs_gui->activateSubTab('releasedObjects');

        $table = new ilLTIProviderReleasedObjectsTableGUI($this, 'releasedObjects', 'ltireleases');
        $table->init();
        $table->parse();
        
        $this->tpl->setContent($table->getHTML());
    }
}
