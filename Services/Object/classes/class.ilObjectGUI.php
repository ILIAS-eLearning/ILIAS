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
 
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory;

/**
 * Class ilObjectGUI
 * Basic methods of all Output classes
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjectGUI
{
    const ADMIN_MODE_NONE = "";
    const ADMIN_MODE_SETTINGS = "settings";
    const ADMIN_MODE_REPOSITORY = "repository";
    const UPLOAD_TYPE_LOCAL = 1;
    const UPLOAD_TYPE_UPLOAD_DIRECTORY = 2;
    const CFORM_NEW = 1;
    const CFORM_IMPORT = 2;
    const CFORM_CLONE = 3;
    private \ILIAS\Notes\Service $notes_service;

    protected ServerRequestInterface $request;
    protected ilLocatorGUI $locator;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected ilToolbarGUI $toolbar;
    protected ilRbacAdmin $rbac_admin;
    protected ilRbacSystem $rbac_system;
    protected ilRbacReview $rbac_review;
    protected ilObjectService $object_service;
    protected ilObjectDefinition $obj_definition;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilErrorHandling $error;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ILIAS $ilias;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected RequestWrapper $request_wrapper;
    protected Factory $refinery;
    protected ilFavouritesManager $favourites;
    protected ilObjectCustomIconFactory $custom_icon_factory;
    private ilObjectRequestRetriever $retriever;

    protected ?ilObject $object = null;
    protected bool $creation_mode = false;
    protected $data;
    protected int $id;
    protected bool $call_by_reference;
    protected bool $prepare_output;
    protected int $ref_id;
    protected int $obj_id;
    protected int $maxcount;			// contains number of child objects
    protected array $form_action = [];		// special formation (array "cmd" => "formaction")
    protected array $return_location = [];	// special return location (array "cmd" => "location")
    protected array $target_frame = [];	// special target frame (array "cmd" => "location")
    protected string $tmp_import_dir;	// directory used during import
    protected string $sub_objects = "";
    protected bool $omit_locator = false;
    protected string $type = "";
    protected string $admin_mode = self::ADMIN_MODE_NONE;
    protected int $requested_ref_id = 0;
    protected int $requested_crtptrefid = 0;
    protected int $requested_crtcb = 0;
    protected string $requested_new_type = "";
    protected string $link_params;
    protected string $html = "";

    /**
     * @param mixed $data
     * @param int $id
     * @param bool $call_by_reference
     * @param bool $prepare_output
     * @throws ilCtrlException
     */
    public function __construct($data, int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        global $DIC;

        $this->request = $DIC->http()->request();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->toolbar = $DIC->toolbar();
        $this->rbac_admin = $DIC->rbac()->admin();
        $this->rbac_system = $DIC->rbac()->system();
        $this->rbac_review = $DIC->rbac()->review();
        $this->object_service = $DIC->object();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tpl = $DIC["tpl"];
        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->tabs_gui = $DIC->tabs();
        $this->ilias = $DIC["ilias"];
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $this->refinery);
        $this->favourites = new ilFavouritesManager();
        $this->custom_icon_factory = $DIC['object.customicons.factory'];

        $this->data = $data;
        $this->id = $id;
        $this->call_by_reference = $call_by_reference;
        $this->prepare_output = $prepare_output;

        $params = array("ref_id");
        if (!$call_by_reference) {
            $params = array("ref_id","obj_id");
        }
        $this->ctrl->saveParameter($this, $params);

        if ($this->request_wrapper->has("ref_id")) {
            $this->requested_ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        }

        $this->obj_id = $this->id;
        $this->ref_id = $this->requested_ref_id;

        if ($call_by_reference) {
            $this->ref_id = $this->id;
            $this->obj_id = 0;
            if ($this->request_wrapper->has("obj_id")) {
                $this->obj_id = $this->request_wrapper->retrieve("obj_id", $this->refinery->kindlyTo()->int());
            }
        }

        // TODO: refactor this with post_wrapper or request_wrapper
        // callback after creation
        $this->requested_crtptrefid = $this->retriever->getMaybeInt('crtptrefid', 0);
        $this->requested_crtcb = $this->retriever->getMaybeInt("crtcb", 0);
        $this->requested_new_type = $this->retriever->getMaybeString("new_type", "");


        if ($this->id != 0) {
            $this->link_params = "ref_id=" . $this->ref_id;
        }

        $this->assignObject();
        
        if (is_object($this->object)) {
            if ($this->call_by_reference && $this->ref_id == $this->requested_ref_id) {
                $this->ctrl->setContextObject(
                    $this->object->getId(),
                    $this->object->getType()
                );
            }
        }

        if ($prepare_output) {
            $this->prepareOutput();
        }

        $this->notes_service = $DIC->notes();
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function setAdminMode(string $mode) : void
    {
        if (!in_array($mode, [
            self::ADMIN_MODE_NONE,
            self::ADMIN_MODE_REPOSITORY,
            self::ADMIN_MODE_SETTINGS
        ])) {
            throw new ilObjectException("Unknown Admin Mode $mode.");
        }
        $this->admin_mode = $mode;
    }

    public function getAdminMode() : string
    {
        return $this->admin_mode;
    }

    protected function getObjectService() : ilObjectService
    {
        return $this->object_service;
    }

    public function getObject() : ilObject
    {
        return $this->object;
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        if (!$cmd) {
            $cmd = "view";
        }
        $cmd .= "Object";
        $this->$cmd();
    }

    /**
    * determines whether objects are referenced or not (got ref ids or not)
    */
    public function withReferences() : bool
    {
        return $this->call_by_reference;
    }
    
    /**
    * if true, a creation screen is displayed
    * the current [ref_id] don't belong
    * to the current class!
    * The mode is determined in ilRepositoryGUI
    */
    public function setCreationMode(bool $mode = true) : void
    {
        $this->creation_mode = $mode;
    }
    
    public function getCreationMode() : bool
    {
        return $this->creation_mode;
    }

    protected function assignObject() : void
    {
        // TODO: it seems that we always have to pass only the ref_id
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = ilObjectFactory::getInstanceByRefId($this->id);
            } else {
                $this->object = ilObjectFactory::getInstanceByObjId($this->id);
            }
        }
    }

    public function prepareOutput(bool $show_sub_objects = true) : bool
    {
        $this->tpl->loadStandardTemplate();
        // administration prepare output
        $base_class = $this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string());
        if (strtolower($base_class) == "iladministrationgui") {
            $this->addAdminLocatorItems();
            $this->tpl->setLocator();

            $this->setTitleAndDescription();

            if ($this->getCreationMode() != true) {
                $this->setAdminTabs();
            }
            
            return false;
        }
        $this->setLocator();

        // in creation mode (parent) object and gui object do not fit
        if ($this->getCreationMode() == true) {
            // repository vs. workspace
            if ($this->call_by_reference) {
                // get gui class of parent and call their title and description method
                $obj_type = ilObject::_lookupType($this->requested_ref_id, true);
                $class_name = $this->obj_definition->getClassName($obj_type);
                $class = strtolower("ilObj" . $class_name . "GUI");
                $class_path = $this->ctrl->lookupClassPath($class);
                $class_name = $this->ctrl->getClassForClasspath($class_path);
                $parent_gui_obj = new $class_name("", $this->requested_ref_id, true, false);
                // the next line prevents the header action menu being shown
                $parent_gui_obj->setCreationMode(true);
                $parent_gui_obj->setTitleAndDescription();
            }
        } else {
            $this->setTitleAndDescription();

            // set tabs
            $this->setTabs();

            $file_upload_dropzone = new ilObjFileUploadDropzone($this->ref_id);
            if ($file_upload_dropzone->isUploadAllowed($this->object->getType())) {
                $this->enableDragDropFileUpload();
            }
        }
        
        return true;
    }

    protected function setTitleAndDescription() : void
    {
        if (!is_object($this->object)) {
            if ($this->requested_crtptrefid > 0) {
                $cr_obj_id = ilObject::_lookupObjId($this->requested_crtcb);
                $this->tpl->setTitle(ilObject::_lookupTitle($cr_obj_id));
                $this->tpl->setTitleIcon(ilObject::_getIcon($cr_obj_id));
            }
            return;
        }
        $this->tpl->setTitle($this->object->getPresentationTitle());
        $this->tpl->setDescription($this->object->getLongDescription());

        $base_class = $this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string());
        if (strtolower($base_class) == "iladministrationgui") {
            // alt text would be same as heading -> empty alt text
            $this->tpl->setTitleIcon(ilObject::_getIcon(0, "big", $this->object->getType()));
        } else {
            $this->tpl->setTitleIcon(
                ilObject::_getIcon(0, "big", $this->object->getType()),
                $this->lng->txt("obj_" . $this->object->getType())
            );
        }
        if (!$this->obj_definition->isAdministrationObject($this->object->getType())) {
            $lgui = ilObjectListGUIFactory::_getListGUIByType($this->object->getType());
            $lgui->initItem($this->object->getRefId(), $this->object->getId(), $this->object->getType());
            $this->tpl->setAlertProperties($lgui->getAlertProperties());
        }
    }
    
    /**
     * Add header action menu
     */
    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null) : ?ilObjectListGUI
    {
        if (!$this->creation_mode && $this->object) {
            $dispatcher = new ilCommonActionDispatcherGUI(
                ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
                $this->access,
                $this->object->getType(),
                $this->ref_id,
                $this->object->getId()
            );
            
            $dispatcher->setSubObject($sub_type, $sub_id);
            
            ilObjectListGUI::prepareJsLinks(
                $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
                "",
                $this->ctrl->getLinkTargetByClass(["ilcommonactiondispatchergui", "iltagginggui"], "", "", true)
            );
            
            $lg = $dispatcher->initHeaderAction();
            
            if (is_object($lg)) {
                // to enable add to desktop / remove from desktop
                if ($this instanceof ilDesktopItemHandling) {
                    $lg->setContainerObject($this);
                }
                
                // enable multi download
                $lg->enableMultiDownload(true);
                
                // comments settings are always on (for the repository)
                // should only be shown if active or permission to toggle
                if (
                    $this->access->checkAccess("write", "", $this->ref_id) ||
                    $this->access->checkAccess("edit_permissions", "", $this->ref_id) ||
                    $this->notes_service->domain()->commentsActive($this->object->getId())
                ) {
                    $lg->enableComments(true);
                }
                
                $lg->enableNotes(true);
                $lg->enableTags(true);
            }
            
            return $lg;
        }
        return null;
    }
    
    /**
     * Insert header action into main template
     */
    protected function insertHeaderAction(?ilObjectListGUI $list_gui = null) : void
    {
        if (
            !is_object($this->object) ||
            ilContainer::_lookupContainerSetting($this->object->getId(), "hide_top_actions")
        ) {
            return;
        }

        if (is_object($list_gui)) {
            $this->tpl->setHeaderActionMenu($list_gui->getHeaderAction());
        }
    }
    
    /**
     * Add header action menu
     */
    protected function addHeaderAction() : void
    {
        $this->insertHeaderAction($this->initHeaderAction());
    }

    /**
     * Ajax call: redraw action header only
     */
    protected function redrawHeaderActionObject() : void
    {
        $lg = $this->initHeaderAction();
        echo $lg->getHeaderAction();
        
        // we need to add onload code manually (rating, comments, etc.)
        echo $this->tpl->getOnLoadCodeForAsynch();
        exit;
    }

    /**
    * set admin tabs
    */
    protected function setTabs() : void
    {
        $this->getTabs();
    }

    /**
    * set admin tabs
    */
    final protected function setAdminTabs() : void
    {
        $this->getAdminTabs();
    }

    /**
    * administration tabs show only permissions and trash folder
    */
    public function getAdminTabs() : void
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTarget(
                "view",
                $this->ctrl->getLinkTarget($this, "view"),
                array("", "view"),
                get_class($this)
            );
        }
        
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), "perm"),
                "",
                "ilpermissiongui"
            );
        }
    }

    public function getHTML() : string
    {
        return $this->html;
    }

    protected function setLocator() : void
    {
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        
        if ($this->omit_locator) {
            return;
        }

        // repository vs. workspace
        if ($this->call_by_reference) {
            // todo: admin workaround
            // in the future, object gui classes should not be called in
            // admin section anymore (rbac/trash handling in own classes)
            $ref_id = $this->requested_ref_id;
            if ($this->requested_ref_id === 0) {
                $ref_id = $this->object->getRefId();
            }
            $ilLocator->addRepositoryItems($ref_id);
        }
        
        if (!$this->creation_mode) {
            $this->addLocatorItems();
        }

        $tpl->setLocator();
    }
    
    /**
    * should be overwritten to add object specific items
    * (repository items are preloaded)
    */
    protected function addLocatorItems() : void
    {
    }
    
    protected function omitLocator(bool $omit = true) : void
    {
        $this->omit_locator = $omit;
    }

    /**
     * should be overwritten to add object specific items
     * (repository items are preloaded)
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false) : void
    {
        if ($this->admin_mode == self::ADMIN_MODE_SETTINGS) {
            $this->ctrl->setParameterByClass(
                "ilobjsystemfoldergui",
                "ref_id",
                SYSTEM_FOLDER_ID
            );
            $this->locator->addItem(
                $this->lng->txt("administration"),
                $this->ctrl->getLinkTargetByClass(["iladministrationgui", "ilobjsystemfoldergui"], "")
            );
            if ($this->object && ($this->object->getRefId() != SYSTEM_FOLDER_ID && !$do_not_add_object)) {
                $this->locator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, "view")
                );
            }
        } else {
            $this->ctrl->setParameterByClass(
                "iladministrationgui",
                "ref_id",
                ""
            );
            $this->ctrl->setParameterByClass(
                "iladministrationgui",
                "admin_mode",
                "settings"
            );
            $this->ctrl->clearParametersByClass("iladministrationgui");
            $this->locator->addAdministrationItems();
        }
    }

    /**
    * confirmed deletion of object -> objects are moved to trash or deleted
    * immediately, if trash is disabled
    */
    public function confirmedDeleteObject() : void
    {
        if ($this->post_wrapper->has("mref_id")) {
            $mref_id = $this->post_wrapper->retrieve(
                "mref_id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            $_SESSION["saved_post"] = array_unique(array_merge($_SESSION["saved_post"], $mref_id));
        }
        
        $ru = new ilRepositoryTrashGUI($this);
        $ru->deleteObjects($this->requested_ref_id, ilSession::get("saved_post"));
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }

    /**
    * cancel deletion of object
    */
    public function cancelDeleteObject() : void
    {
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }


    /**
     * cancel action and go back to previous page
     */
    public function cancelObject() : void
    {
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }

    /**
     * create new object form
     */
    public function createObject() : void
    {
        $new_type = $this->requested_new_type;

        // add new object to custom parent container
        $this->ctrl->saveParameter($this, "crtptrefid");
        // use forced callback after object creation
        $this->ctrl->saveParameter($this, "crtcb");
        
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        } else {
            $this->lng->loadLanguageModule($new_type);
            $this->ctrl->setParameter($this, "new_type", $new_type);
            
            $forms = $this->initCreationForms($new_type);
            
            // copy form validation error: do not show other creation forms
            if ($this->request_wrapper->has("cpfl") && isset($forms[self::CFORM_CLONE])) {
                $forms = array(self::CFORM_CLONE => $forms[self::CFORM_CLONE]);
            }
            $this->tpl->setContent($this->getCreationFormsHTML($forms));
        }
    }

    /**
     * Init creation forms.
     * This will create the default creation forms: new, import, clone
     * @return array<int, ilPropertyFormGUI>
     */
    protected function initCreationForms(string $new_type) : array
    {
        $forms = [
            self::CFORM_NEW => $this->initCreateForm($new_type),
            self::CFORM_IMPORT => $this->initImportForm($new_type),
            self::CFORM_CLONE => $this->fillCloneTemplate(null, $new_type)
        ];
        
        return $forms;
    }

    /**
     * Get HTML for creation forms (accordion)
     * @param array<int, ilPropertyFormGUI> $forms
     */
    protected function getCreationFormsHTML(array $forms) : string
    {
        // #13168- sanity check
        foreach ($forms as $id => $form) {
            if (!$form instanceof ilPropertyFormGUI) {
                unset($forms[$id]);
            }
        }
        
        // no accordion if there is just one form
        if (sizeof($forms) == 1) {
            $form_type = key($forms);
            $forms = array_shift($forms);

            // see bug #0016217
            if (method_exists($this, "getCreationFormTitle")) {
                $form_title = $this->getCreationFormTitle($form_type);
                if ($form_title != "") {
                    $forms->setTitle($form_title);
                }
            }
            return $forms->getHTML();
        } else {
            $acc = new ilAccordionGUI();
            $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
            $cnt = 1;
            foreach ($forms as $form_type => $cf) {
                $htpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");

                // using custom form titles (used for repository plugins)
                $form_title = "";
                if (method_exists($this, "getCreationFormTitle")) {
                    $form_title = $this->getCreationFormTitle($form_type);
                }
                if (!$form_title) {
                    $form_title = $cf->getTitle();
                }

                // move title from form to accordion
                $htpl->setVariable("TITLE", $this->lng->txt("option") . " " . $cnt . ": " . $form_title);
                $cf->setTitle('');
                $cf->setTitleIcon('');
                $cf->setTableWidth("100%");

                $acc->addItem($htpl->get(), $cf->getHTML());

                $cnt++;
            }

            return "<div class='ilCreationFormSection'>" . $acc->getHTML() . "</div>";
        }
    }

    protected function initCreateForm(string $new_type) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($new_type . "_new"));

        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form = $this->initDidacticTemplate($form);

        $form->addCommandButton("save", $this->lng->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    protected function initDidacticTemplate(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('didactic');
        $existing_exclusive = false;
        $options = [];
        $options['dtpl_0'] = [
            $this->lng->txt('didactic_default_type'),
            sprintf(
                $this->lng->txt('didactic_default_type_info'),
                $this->lng->txt('objs_' . $this->type)
            )
        ];
        
        $templates = ilDidacticTemplateSettings::getInstanceByObjectType($this->type)->getTemplates();
        if ($templates) {
            foreach ($templates as $template) {
                if ($template->isEffective((int) $this->requested_ref_id)) {
                    $options["dtpl_" . $template->getId()] = array(
                        $template->getPresentationTitle(),
                        $template->getPresentationDescription()
                    );

                    if ($template->isExclusive()) {
                        $existing_exclusive = true;
                    }
                }
            }
        }
        
        $this->addDidacticTemplateOptions($options);

        if (sizeof($options) > 1) {
            $type = new ilRadioGroupInputGUI(
                $this->lng->txt('type'),
                'didactic_type'
            );
            // workaround for containers in edit mode
            if (!$this->getCreationMode()) {
                $value = 'dtpl_' . ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

                $type->setValue($value);

                if (!in_array($value, array_keys($options)) || ($existing_exclusive && $value == "dtpl_0")) {
                    //add or rename actual value to not available
                    $options[$value] = [$this->lng->txt('not_available')];
                }
            } else {
                if ($existing_exclusive) {
                    //if an exclusive template exists use the second template as default value
                    $keys = array_keys($options);
                    $type->setValue($keys[1]);
                } else {
                    $type->setValue('dtpl_0');
                }
            }
            $form->addItem($type);

            foreach ($options as $id => $data) {
                $option = new ilRadioOption($data[0], $id, $data[1]);

                if ($existing_exclusive && $id == 'dtpl_0') {
                    //set default disabled if an exclusive template exists
                    $option->setDisabled(true);
                }

                $type->addOption($option);
            }
        }
        
        return $form;
    }
    
    /**
     * Add custom templates
     */
    protected function addDidacticTemplateOptions(array &$a_options) : void
    {
    }

    /**
     * cancel create action and go back to repository parent
     */
    public function cancelCreation() : void
    {
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    public function saveObject() : void
    {
        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $this->requested_new_type)) {
            $this->error->raiseError($this->lng->txt("no_create_permission"), $this->error->MESSAGE);
        }

        $this->lng->loadLanguageModule($this->requested_new_type);
        $this->ctrl->setParameter($this, "new_type", $this->requested_new_type);
        
        $form = $this->initCreateForm($this->requested_new_type);
        if ($form->checkInput()) {
            $this->ctrl->setParameter($this, "new_type", "");

            $class_name = "ilObj" . $this->obj_definition->getClassName($this->requested_new_type);
            $newObj = new $class_name();
            $newObj->setType($this->requested_new_type);
            $newObj->setTitle($form->getInput("title"));
            $newObj->setDescription($form->getInput("desc"));
            $newObj->create();
            
            $this->putObjectInTree($newObj);

            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }
            
            $this->handleAutoRating($newObj);
            $this->afterSave($newObj);
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Get didactic template setting from creation screen
     */
    public function getDidacticTemplateVar(string $type) : int
    {
        if (!$this->post_wrapper->has("didactic_type")) {
            return 0;
        }

        $tpl = $this->post_wrapper->retrieve("didactic_type", $this->refinery->kindlyTo()->string());
        if (substr($tpl, 0, strlen($type) + 1) != $type . "_") {
            return 0;
        }

        return (int) substr($tpl, strlen($type) + 1);
    }

    /**
     * Add object to tree at given position
     */
    public function putObjectInTree(ilObject $obj, int $parent_node_id = null) : void
    {
        if (!$parent_node_id) {
            $parent_node_id = $this->requested_ref_id;
        }
        
        // add new object to custom parent container
        if ($this->requested_crtptrefid > 0) {
            $parent_node_id = $this->requested_crtptrefid;
        }

        $obj->createReference();
        $obj->putInTree($parent_node_id);
        $obj->setPermissions($parent_node_id);

        $this->obj_id = $obj->getId();
        $this->ref_id = $obj->getRefId();

        // BEGIN ChangeEvent: Record save object.
        ilChangeEvent::_recordWriteEvent($this->obj_id, $this->user->getId(), 'create');
        // END ChangeEvent: Record save object.

        // rbac log
        $rbac_log_roles = $this->rbac_review->getParentRoleIds($this->ref_id, false);
        $rbac_log = ilRbacLog::gatherFaPa($this->ref_id, array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $this->ref_id, $rbac_log);
        
        // use forced callback after object creation
        if ($this->requested_crtcb > 0) {
            $callback_type = ilObject::_lookupType($this->requested_crtcb, true);
            $class_name = "ilObj" . $this->obj_definition->getClassName($callback_type) . "GUI";
            if (strtolower($class_name) == "ilobjitemgroupgui") {
                $callback_obj = new $class_name($this->requested_crtcb);
            } else {
                // #10368
                $callback_obj = new $class_name(null, $this->requested_crtcb, true, false);
            }
            $callback_obj->afterSaveCallback($obj);
        }
    }

    /**
     * Post (successful) object creation hook
     */
    protected function afterSave(ilObject $new_object) : void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    public function editObject() : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateTab("settings");

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values);
        }
        
        $this->addExternalEditFormCustom($form);

        $this->tpl->setContent($form->getHTML());
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $form) : void
    {
        // has to be done AFTER setValuesByArray() ...
    }
    
    protected function initEditForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;

        $lng->loadLanguageModule($this->object->getType());

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $this->initEditCustomForm($form);

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Add custom fields to update form
     */
    protected function initEditCustomForm(ilPropertyFormGUI $a_form) : void
    {
    }

    protected function getEditFormValues() : array
    {
        $values["title"] = $this->object->getTitle();
        $values["desc"] = $this->object->getLongDescription();
        $this->getEditFormCustomValues($values);
        return $values;
    }

    /**
     * Add values to custom edit fields
     */
    protected function getEditFormCustomValues(array &$a_values) : void
    {
    }

    /**
     * updates object entry in object_data
     */
    public function updateObject() : void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        $form = $this->initEditForm();
        if ($form->checkInput() && $this->validateCustom($form)) {
            $this->object->setTitle($form->getInput("title"));
            $this->object->setDescription($form->getInput("desc"));
            $this->updateCustom($form);
            $this->object->update();
            
            $this->afterUpdate();
            return;
        }

        // display form again to correct errors
        $this->tabs_gui->activateTab("settings");
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Validate custom values (if not possible with checkInput())
     */
    protected function validateCustom(ilPropertyFormGUI $form) : bool
    {
        return true;
    }

    /**
     * Insert custom update form values into object
     */
    protected function updateCustom(ilPropertyFormGUI $form) : void
    {
    }

    /**
     * Post (successful) object update hook
     */
    protected function afterUpdate() : void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }

    protected function initImportForm(string $new_type) : ilPropertyFormGUI
    {
        $import_directory_factory = new ilImportDirectoryFactory();
        $export_directory = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_EXPORT);
        $upload_files = $export_directory->getFilesFor($this->user->getId(), $new_type);
        $has_upload_files = false;
        if (count($upload_files)) {
            $has_upload_files = true;
        }

        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "importFile"));
        $form->setTitle($this->lng->txt($new_type . "_import"));

        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        if ($has_upload_files) {
            $this->lng->loadLanguageModule('content');
            $option = new ilRadioGroupInputGUI(
                $this->lng->txt('cont_choose_file_source'),
                'upload_type'
            );
            $option->setValue((string) self::UPLOAD_TYPE_LOCAL);
            $form->addItem($option);

            $direct = new ilRadioOption($this->lng->txt('cont_choose_local'), (string) self::UPLOAD_TYPE_LOCAL);
            $option->addOption($direct);

            $direct->addSubItem($fi);
            $upload = new ilRadioOption(
                $this->lng->txt('cont_choose_upload_dir'),
                (string) self::UPLOAD_TYPE_UPLOAD_DIRECTORY
            );
            $option->addOption($upload);
            $files = new ilSelectInputGUI(
                $this->lng->txt('cont_choose_upload_dir'),
                'uploadFile'
            );
            $upload_files[''] = $this->lng->txt('cont_select_from_upload_dir');
            $files->setOptions($upload_files);
            $files->setRequired(true);
            $upload->addSubItem($files);
        }

        if (!$has_upload_files) {
            $form->addItem($fi);
        }

        $form->addCommandButton("importFile", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
    
        return $form;
    }

    protected function importFileObject(int $parent_id = null, bool $catch_errors = true) : void
    {
        if (!$parent_id) {
            $parent_id = $this->requested_ref_id;
        }
        $new_type = $this->requested_new_type;
        $upload_type = self::UPLOAD_TYPE_LOCAL;
        if ($this->post_wrapper->has("upload_type")) {
            $upload_type = $this->post_wrapper->retrieve("upload_type", $this->refinery->kindlyTo()->int());
        }

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $this->error->raiseError($this->lng->txt("no_create_permission"));
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);
        
        $form = $this->initImportForm($new_type);
        if ($form->checkInput()) {
            // :todo: make some check on manifest file
            if ($this->obj_definition->isContainer($new_type)) {
                $imp = new ilImportContainer((int) $parent_id);
            } else {
                $imp = new ilImport((int) $parent_id);
            }

            try {
                if ($upload_type == self::UPLOAD_TYPE_LOCAL) {
                    $new_id = $imp->importObject(
                        null,
                        $_FILES["importfile"]["tmp_name"],
                        $_FILES["importfile"]["name"],
                        $new_type
                    );
                } else {
                    $hash = $this->request->getParsedBody()['uploadFile'] ?? '';
                    $upload_factory = new ilImportDirectoryFactory();
                    $export_upload = $upload_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_EXPORT);
                    $file = $export_upload->getAbsolutePathForHash($this->user->getId(), $new_type, $hash);

                    $new_id = $imp->importObject(
                        null,
                        $file,
                        basename($file),
                        $new_type,
                        '',
                        true
                    );
                }
            } catch (ilException $e) {
                $this->tmp_import_dir = $imp->getTemporaryImportDir();
                if (!$catch_errors) {
                    throw $e;
                }
                // display message and form again
                $this->tpl->setOnScreenMessage(
                    "failure",
                    $this->lng->txt("obj_import_file_error") . " <br />" . $e->getMessage()
                );
                $form->setValuesByPost();
                $this->tpl->setContent($form->getHTML());
                return;
            }

            if ($new_id > 0) {
                $this->ctrl->setParameter($this, "new_type", "");

                $newObj = ilObjectFactory::getInstanceByObjId($new_id);

                // put new object id into tree - already done in import for containers
                if (!$this->obj_definition->isContainer($new_type)) {
                    $this->putObjectInTree($newObj);
                }
                
                $this->afterImport($newObj);
            } else {
                if ($this->obj_definition->isContainer($new_type)) {
                    $this->tpl->setOnScreenMessage("failure", $this->lng->txt("container_import_zip_file_invalid"));
                } else {
                    // not enough information here...
                    return;
                }
            }
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Post (successful) object import hook
     */
    protected function afterImport(ilObject $new_object) : void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Get form action for command (command is method name without "Object", e.g. "perm").
     */
    public function getFormAction(string $cmd, string $default_form_action = "") : string
    {
        if ($this->form_action[$cmd] != "") {
            return $this->form_action[$cmd];
        }

        return $default_form_action;
    }

    protected function setFormAction(string $cmd, string $form_action) : void
    {
        $this->form_action[$cmd] = $form_action;
    }

    /**
     * Get return location for command (command is method name without "Object", e.g. "perm")
     */
    protected function getReturnLocation(string $cmd, string $default_location = "") : string
    {
        if ($this->return_location[$cmd] != "") {
            return $this->return_location[$cmd];
        } else {
            return $default_location;
        }
    }

    /**
     * set specific return location for command
     */
    protected function setReturnLocation(string $cmd, string $location) : void
    {
        $this->return_location[$cmd] = $location;
    }

    /**
     * get target frame for command (command is method name without "Object", e.g. "perm")
     */
    protected function getTargetFrame(string $cmd, string $default_target_frame = "") : string
    {
        if (isset($this->target_frame[$cmd]) && $this->target_frame[$cmd] != "") {
            return $this->target_frame[$cmd];
        }

        if (!empty($default_target_frame)) {
            return "target=\"" . $default_target_frame . "\"";
        }

        return "";
    }

    /**
     * Set specific target frame for command
     */
    protected function setTargetFrame(string $cmd, string $target_frame) : void
    {
        $this->target_frame[$cmd] = "target=\"" . $target_frame . "\"";
    }

    public function isVisible(int $ref_id, string $type) : bool
    {
        $visible = $this->checkPermissionBool("visible,read", "", "", $ref_id);
        
        if ($visible && $type == 'crs') {
            $tree = $this->tree;
            if ($crs_id = $tree->checkForParentType($ref_id, 'crs')) {
                if (!$this->checkPermissionBool("write", "", "", $crs_id)) {
                    // Show only activated courses
                    $tmp_obj = ilObjectFactory::getInstanceByRefId($crs_id, false);
    
                    if (!$tmp_obj->isActivated()) {
                        unset($tmp_obj);
                        $visible = false;
                    }
                }
            }
        }
        
        return $visible;
    }

    /**
     * viewObject container presentation for "administration -> repository, trash, permissions"
     */
    public function viewObject() : void
    {
        $this->checkPermission('visible') && $this->checkPermission('read');

        $this->tabs_gui->activateTab('view');
        
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );

        if (!$this->withReferences()) {
            $this->ctrl->setParameter($this, 'obj_id', $this->obj_id);
        }

        $itab = new ilAdminSubItemsTableGUI(
            $this,
            "view",
            $this->requested_ref_id,
            $this->checkPermissionBool('write')
        );
        
        $this->tpl->setContent($itab->getHTML());
    }

    /**
    * Display deletion confirmation screen.
    * Only for referenced objects. For user,role & rolt overwrite this function in the appropriate
    * Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
    */
    public function deleteObject(bool $error = false) : void
    {
        $request_ids = [];
        if ($this->post_wrapper->has("id")) {
            $request_ids = $this->post_wrapper->retrieve(
                "id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if (
            $this->request_wrapper->has("item_ref_id") &&
            $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->string()) != ""
        ) {
            $request_ids = [$this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int())];
        }

        $ids = [];
        if (count($request_ids) > 0) {
            foreach ($request_ids as $idx => $id) {
                $ids["id"][$idx] = $id;
            }
        }

        // SAVE POST VALUES (get rid of this
        ilSession::set("saved_post", $ids["id"]);

        $ru = new ilRepositoryTrashGUI($this);
        if (!$ru->showDeleteConfirmation($ids["id"], $error)) {
            $this->ctrl->returnToParent($this);
        }
    }

    /**
    * show possible sub objects (pull down menu)
    */
    protected function showPossibleSubObjects() : void
    {
        if ($this->sub_objects == "") {
            $sub_objects = $this->obj_definition->getCreatableSubObjects(
                $this->object->getType(),
                ilObjectDefinition::MODE_REPOSITORY,
                $this->ref_id
            );
        } else {
            $sub_objects = $this->sub_objects;
        }

        $subobj = [];
        if (count($sub_objects) > 0) {
            foreach ($sub_objects as $row) {
                $count = 0;
                if ($row["max"] > 0) {
                    //how many elements are present?
                    for ($i = 0; $i < count($this->data["ctrl"]); $i++) {
                        if ($this->data["ctrl"][$i]["type"] == $row["name"]) {
                            $count++;
                        }
                    }
                }

                if ($row["max"] == "" || $count < $row["max"]) {
                    $subobj[] = $row["name"];
                }
            }
        }

        if (count($subobj) > 0) {
            $opts = ilLegacyFormElementsUtil::formSelect(12, "new_type", $subobj);
            $this->tpl->setCurrentBlock("add_object");
            $this->tpl->setVariable("SELECT_OBJTYPE", $opts);
            $this->tpl->setVariable("BTN_NAME", "create");
            $this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * @abstract	overwrite in derived GUI class of your object type
    */
    protected function getTabs() : void
    {
    }

    /**
    * redirects to (repository) view per ref id
    * usually to a container and usually used at
    * the end of a save/import method where the object gui
    * type (of the new object) doesn't match with the type
    * of the current ["ref_id"] value of the request
    */
    protected function redirectToRefId(int $ref_id, string $cmd = "") : void
    {
        $obj_type = ilObject::_lookupType($ref_id, true);
        $class_name = $this->obj_definition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        $this->ctrl->redirectByClass(array("ilrepositorygui", $class), $cmd);
    }
    
    /**
     * Fill object clone template
     * This method can be called from any object GUI class that wants to offer object cloning.
     *
     * @param ?string template variable name that will be filled
     * @param string type of new object
     * @return ?ilPropertyFormGUI
     */
    protected function fillCloneTemplate(?string $tpl_name, string $type) : ?ilPropertyFormGUI
    {
        $cp = new ilObjectCopyGUI($this);
        $cp->setType($type);
        $cp->setTarget($this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int()));
        if ($tpl_name) {
            $cp->showSourceSearch($tpl_name);
        }

        return $cp->showSourceSearch(null);
    }
    
    /**
    * Get center column
    */
    protected function getCenterColumnHTML() : string
    {
        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if ($this->ctrl->getNextClass() != "ilcolumngui") {
            // normal command processing
            return $this->getContent();
        } else {
            if (!$this->ctrl->isAsynch()) {
                //if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
                if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE) {
                    // right column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $this->ctrl->forwardCommand($column_gui);
                    }
                    // left column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_LEFT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $this->ctrl->forwardCommand($column_gui);
                    }
                } else {
                    // normal command processing
                    return $this->getContent();
                }
            }
        }
        return "";
    }
    
    /**
    * Display right column
    */
    protected function getRightColumnHTML() : string
    {
        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
        
        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }
        
        $this->setColumnSettings($column_gui);

        $html = "";
        if (
            $this->ctrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE
        ) {
            return $this->ctrl->forwardCommand($column_gui);
        }

        if (!$this->ctrl->isAsynch()) {
            return $this->ctrl->getHTML($column_gui);
        }

        return $html;
    }

    public function setColumnSettings(ilColumnGUI $column_gui) : void
    {
        $column_gui->setRepositoryMode(true);
        $column_gui->setEnableEdit(false);
        if ($this->checkPermissionBool("write")) {
            $column_gui->setEnableEdit(true);
        }
    }

    protected function checkPermission(string $perm, string $cmd = "", string $type = "", ?int $ref_id = null) : void
    {
        if (!$this->checkPermissionBool($perm, $cmd, $type, $ref_id)) {
            if (!is_int(strpos($_SERVER["PHP_SELF"], "goto.php"))) {
                if ($perm != "create" && !is_object($this->object)) {
                    return;
                }
                throw new ilObjectException($this->lng->txt("permission_denied"));
            }
        }
    }

    protected function checkPermissionBool(string $perm, string $cmd = "", string $type = "", ?int $ref_id = null) : bool
    {
        if ($perm == "create") {
            if (!$ref_id) {
                $ref_id = $this->requested_ref_id;
            }
            return $this->access->checkAccess($perm . "_" . $type, $cmd, $ref_id);
        }

        if (!is_object($this->object)) {
            return false;
        }

        if (!$ref_id) {
            $ref_id = $this->object->getRefId();
        }

        return $this->access->checkAccess($perm, $cmd, $ref_id);
    }
    
    /**
     * Goto repository root
     *
     * @param
     * @return
     */
    public static function _gotoRepositoryRoot(bool $raise_error = false) : void
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();
        
        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", ROOT_FOLDER_ID);
            $ctrl->redirectByClass("ilRepositoryGUI");
        }

        if ($raise_error) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }
    }
    
    public static function _gotoRepositoryNode(int $ref_id, string $cmd = "") : void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", $ref_id);
        $ctrl->redirectByClass("ilRepositoryGUI", $cmd);
    }
    
    /**
     * Enables the file upload into this object by dropping files.
     */
    protected function enableDragDropFileUpload() : void
    {
        $this->tpl->setFileUploadRefId($this->ref_id);
    }
    
    /**
     * Activate rating automatically if parent container setting
     */
    protected function handleAutoRating(ilObject $new_obj) : void
    {
        if (
            ilObject::hasAutoRating($new_obj->getType(), $new_obj->getRefId()) &&
            method_exists($new_obj, "setRating")
        ) {
            $new_obj->setRating(true);
            $new_obj->update();
        }
    }

    /**
     * show edit section of custom icons for container
     */
    protected function showCustomIconsEditing(
        $input_colspan = 1,
        ilPropertyFormGUI $form = null,
        $as_section = true
    ) : void {
        if ($this->settings->get("custom_icons")) {
            if ($form) {
                $customIcon = $this->custom_icon_factory->getByObjId($this->object->getId(), $this->object->getType());

                if ($as_section) {
                    $title = new ilFormSectionHeaderGUI();
                    $title->setTitle($this->lng->txt("icon_settings"));
                } else {
                    $title = new ilCustomInputGUI($this->lng->txt("icon_settings"), "");
                }
                $form->addItem($title);

                $caption = $this->lng->txt("cont_custom_icon");
                $icon = new ilImageFileInputGUI($caption, "cont_icon");

                $icon->setSuffixes($customIcon->getSupportedFileExtensions());
                $icon->setUseCache(false);
                if ($customIcon->exists()) {
                    $icon->setImage($customIcon->getFullPath());
                } else {
                    $icon->setImage('');
                }
                if ($as_section) {
                    $form->addItem($icon);
                } else {
                    $title->addSubItem($icon);
                }
            }
        }
    }

    /**
     * Redirect after creation, see https://docu.ilias.de/goto_docu_wiki_wpage_5035_1357.html
     * Should be overwritten and redirect to settings screen.
     */
    public function redirectAfterCreation() : void
    {
        $link = ilLink::_getLink($this->object->getRefId());
        $this->ctrl->redirectToURL($link);
    }

    public function addToDeskObject() : void
    {
        $this->favourites->add(
            $this->user->getId(),
            $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int())
        );
        $this->lng->loadLanguageModule("rep");
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("rep_added_to_favourites"), true);
        $this->ctrl->redirectToURL(ilLink::_getLink($this->requested_ref_id));
    }

    public function removeFromDeskObject() : void
    {
        $this->lng->loadLanguageModule("rep");
        $item_ref_id = $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int());
        $this->favourites->remove($this->user->getId(), $item_ref_id);
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("rep_removed_from_favourites"), true);
        $this->ctrl->redirectToURL(ilLink::_getLink($this->requested_ref_id));
    }
}
