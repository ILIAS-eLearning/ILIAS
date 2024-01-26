<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Object\ImplementsCreationCallback;
use ILIAS\Object\CreationCallbackTrait;

/**
* Class ilObjectGUI
* Basic methods of all Output classes
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilObjectGUI implements ImplementsCreationCallback
{
    use CreationCallbackTrait;

    protected const UPLOAD_TYPE_LOCAL = 1;
    protected const UPLOAD_TYPE_UPLOAD_DIRECTORY = 2;

    /**
     * @var ilErrorHandling
     */
    protected $ilErr;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    const COPY_WIZARD_NEEDS_PAGE = 1;


    /**
    * object Definition Object
    * @var		object ilias
    * @access	private
    */
    public $objDefinition;

    /**
    * template object
    * @var		ilGlobalTemplateInterface
    * @access	private
    */
    public $tpl;

    /**
    * tree object
    * @var		object ilias
    * @access	private
    */
    public $tree;

    /**
    * language object
    * @var		object language (of ilObject)
    * @access	private
    */
    public $lng;

    /**
    * output data
    * @var		data array
    * @access	private
    */
    public $data;

    /**
    * object
    * @var          \ilObject
    * @access       private
    */
    public $object;
    public $ref_id;
    public $obj_id;
    public $maxcount;			// contains number of child objects
    public $formaction;		// special formation (array "cmd" => "formaction")
    public $return_location;	// special return location (array "cmd" => "location")
    public $target_frame;	// special target frame (array "cmd" => "location")
    protected $tmp_import_dir;	// directory used during import

    public $tab_target_script;
    public $actions;
    public $sub_objects;
    public $omit_locator = false;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui = null;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjectService
     */
    protected $object_service;

    const CFORM_NEW = 1;
    const CFORM_IMPORT = 2;
    const CFORM_CLONE = 3;

    /**
     * @var ilFavouritesManager
     */
    protected $favourites;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
    * Constructor
    * @access	public
    * @param	array	??
    * @param	integer	object id
    * @param	boolean	call be reference
    */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->request = $DIC->http()->request();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->rbacreview = $DIC->rbac()->review();
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->object_service = $DIC->object();
        $objDefinition = $DIC["objDefinition"];
        $tpl = $DIC["tpl"];
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();

        $this->favourites = new ilFavouritesManager();

        $this->ilias = $DIC["ilias"];

        /**
         * @var ilTab
         */
        $this->tabs_gui = $ilTabs;

        if (!isset($ilErr)) {
            $ilErr = new ilErrorHandling();
            $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr,'errorHandler'));
        } else {
            $this->ilErr = $ilErr;
        }

        $this->objDefinition = $objDefinition;
        $this->tpl = $tpl;
        $this->html = "";
        $this->ctrl = $ilCtrl;

        $params = array("ref_id");

        if (!$a_call_by_reference) {
            $params = array("ref_id","obj_id");
        }

        $this->ctrl->saveParameter($this, $params);

        $this->lng = $lng;
        $this->tree = $tree;
        $this->formaction = array();
        $this->return_location = array();
        $this->target_frame = array();
        $this->actions = "";
        $this->sub_objects = "";

        $this->data = $a_data;
        $this->id = $a_id;
        $this->call_by_reference = $a_call_by_reference;
        $this->prepare_output = $a_prepare_output;
        $this->creation_mode = false;

        $this->ref_id = ($this->call_by_reference) ? $this->id : $_GET["ref_id"];
        $this->obj_id = ($this->call_by_reference) ? $_GET["obj_id"] : $this->id;

        if ($this->id != 0) {
            $this->link_params = "ref_id=" . $this->ref_id;
        }

        // get the object
        $this->assignObject();

        // set context
        if (is_object($this->object)) {
            if ($this->call_by_reference && $this->ref_id == $_GET["ref_id"]) {
                $this->ctrl->setContext(
                    $this->object->getId(),
                    $this->object->getType()
                );
            }
        }

        //prepare output
        if ($a_prepare_output) {
            $this->prepareOutput();
        }
    }

    /**
     * Get object service
     *
     * @return ilObjectService
     */
    protected function getObjectService()
    {
        return $this->object_service;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->prepareOutput();
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }

        return true;
    }


    /**
    * determines wether objects are referenced or not (got ref ids or not)
    */
    public function withReferences()
    {
        return $this->call_by_reference;
    }

    /**
    * if true, a creation screen is displayed
    * the current $_GET[ref_id] don't belong
    * to the current class!
    * the mode is determined in ilrepositorygui
    */
    public function setCreationMode($a_mode = true)
    {
        $this->creation_mode = $a_mode;
    }

    /**
    * get creation mode
    */
    public function getCreationMode()
    {
        return $this->creation_mode;
    }

    protected function assignObject()
    {
        // TODO: it seems that we always have to pass only the ref_id
        //echo "<br>ilObjectGUIassign:".get_class($this).":".$this->id.":<br>";
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = ilObjectFactory::getInstanceByRefId($this->id);
            } else {
                $this->object = ilObjectFactory::getInstanceByObjId($this->id);
            }
        }
    }

    /**
    * prepare output
    */
    public function prepareOutput($a_show_subobjects = true)
    {
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        $ilUser = $this->user;

        $this->tpl->loadStandardTemplate();
        // administration prepare output
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            $this->addAdminLocatorItems();
            $tpl->setLocator();

            //			ilUtil::sendInfo();
            ilUtil::infoPanel();

            $this->setTitleAndDescription();

            if ($this->getCreationMode() != true) {
                $this->setAdminTabs();
            }

            return false;
        }
        // set locator
        $this->setLocator();
        // catch feedback message
        //		ilUtil::sendInfo();
        ilUtil::infoPanel();

        // in creation mode (parent) object and gui object
        // do not fit
        if ($this->getCreationMode() == true) {
            // repository vs. workspace
            if ($this->call_by_reference) {
                // get gui class of parent and call their title and description method
                $obj_type = ilObject::_lookupType($_GET["ref_id"], true);
                $class_name = $this->objDefinition->getClassName($obj_type);
                $class = strtolower("ilObj" . $class_name . "GUI");
                $class_path = $this->ctrl->lookupClassPath($class);
                include_once($class_path);
                $class_name = $this->ctrl->getClassForClasspath($class_path);
                //echo "<br>instantiating parent for title and description";
                $this->parent_gui_obj = new $class_name("", $_GET["ref_id"], true, false);
                // the next line prevents the header action menu being shown
                $this->parent_gui_obj->setCreationMode(true);
                $this->parent_gui_obj->setTitleAndDescription();
            }
        } else {
            // set title and description and title icon
            $this->setTitleAndDescription();

            // set tabs
            $this->setTabs();


            // fileupload support
            require_once './Services/FileUpload/classes/class.ilFileUploadUtil.php';
            if (ilFileUploadUtil::isUploadAllowed($this->ref_id, $this->object->getType())) {
                $this->enableDragDropFileUpload();
            }
        }

        return true;
    }

    /**
    * called by prepare output
    */
    protected function setTitleAndDescription()
    {
        if (!is_object($this->object)) {
            if ((int) $_REQUEST["crtptrefid"] > 0) {
                $cr_obj_id = ilObject::_lookupObjId((int) $_REQUEST["crtcb"]);
                $this->tpl->setTitle(ilObject::_lookupTitle($cr_obj_id));
                $this->tpl->setTitleIcon(ilObject::_getIcon($cr_obj_id));
            }
            return;
        }
        $this->tpl->setTitle($this->object->getPresentationTitle());
        $this->tpl->setDescription($this->object->getLongDescription());

        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            // alt text would be same as heading -> empty alt text
            $this->tpl->setTitleIcon(ilObject::_getIcon("", "big", $this->object->getType()));
        } else {
            $this->tpl->setTitleIcon(
                ilObject::_getIcon("", "big", $this->object->getType()),
                $this->lng->txt("obj_" . $this->object->getType())
            );
        }

        include_once './Services/Object/classes/class.ilObjectListGUIFactory.php';
        $lgui = ilObjectListGUIFactory::_getListGUIByType($this->object->getType());
        $lgui->initItem($this->object->getRefId(), $this->object->getId(), $this->object->getType());
        $this->tpl->setAlertProperties($lgui->getAlertProperties());
    }

    /**
     * Add header action menu
     *
     * @param string $a_sub_type
     * @param int $a_sub_id
     * @return ilObjectListGUI
     */
    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        $ilAccess = $this->access;

        if (!$this->creation_mode && $this->object) {
            include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
            $dispatcher = new ilCommonActionDispatcherGUI(
                ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
                $ilAccess,
                $this->object->getType(),
                $this->ref_id,
                $this->object->getId()
            );

            $dispatcher->setSubObject($a_sub_type, $a_sub_id);

            include_once "Services/Object/classes/class.ilObjectListGUI.php";
            ilObjectListGUI::prepareJSLinks(
                $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
                $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false),
                $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false)
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
                include_once "Services/Notes/classes/class.ilNote.php";
                if ($ilAccess->checkAccess("write", "", $this->ref_id) ||
                    $ilAccess->checkAccess("edit_permissions", "", $this->ref_id) ||
                    ilNote::commentsActivated($this->object->getId(), 0, $this->object->getType())) {
                    $lg->enableComments(true);
                }

                $lg->enableNotes(true);
                $lg->enableTags(true);
            }

            return $lg;
        }
    }

    /**
     * Insert header action into main template
     *
     * @param ilObjectListGUI $a_list_gui
     */
    protected function insertHeaderAction($a_list_gui)
    {
        if (!is_object($this->object) || ilContainer::_lookupContainerSetting($this->object->getId(), "hide_top_actions")) {
            return;
        }

        if (is_object($a_list_gui)) {
            $this->tpl->setHeaderActionMenu($a_list_gui->getHeaderAction());
        }
    }

    /**
     * Add header action menu
     */
    protected function addHeaderAction()
    {
        $this->insertHeaderAction($this->initHeaderAction());
    }

    /**
     * Ajax call: redraw action header only
     */
    protected function redrawHeaderActionObject()
    {
        $tpl = $this->tpl;

        $lg = $this->initHeaderAction();
        echo $lg->getHeaderAction();

        // we need to add onload code manually (rating, comments, etc.)
        echo $tpl->getOnLoadCodeForAsynch();

        exit;
    }



    /**
    * set admin tabs
    * @access	public
    */
    protected function setTabs()
    {
        $this->getTabs();
    }

    /**
    * set admin tabs
    * @access	public
    */
    final protected function setAdminTabs()
    {
        $this->getAdminTabs();
    }

    /**
    * administration tabs show only permissions and trash folder
    */
    public function getAdminTabs()
    {
        $tree = $this->tree;

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
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                "",
                "ilpermissiongui"
            );
        }
    }


    public function getHTML()
    {
        return $this->html;
    }


    /**
    * set possible actions for objects in list. if actions are set
    * via this method, the values of objects.xml are ignored.
    *
    * @param	array		$a_actions		array with $command => $lang_var pairs
    */
    private function setActions($a_actions = "")
    {
        if (is_array($a_actions)) {
            foreach ($a_actions as $name => $lng) {
                $this->actions[$name] = array("name" => $name, "lng" => $lng);
            }
        } else {
            $this->actions = "";
        }
    }

    /**
    * set possible subobjects for this object. if subobjects are set
    * via this method, the values of objects.xml are ignored.
    *
    * @param	array		$a_actions		array with $command => $lang_var pairs
    */
    private function setSubObjects($a_sub_objects = "")
    {
        if (is_array($a_sub_objects)) {
            foreach ($a_sub_objects as $name => $options) {
                $this->sub_objects[$name] = array("name" => $name, "max" => $options["max"]);
            }
        } else {
            $this->sub_objects = "";
        }
    }

    /**
    * set Locator
    *
    * @param	object	tree object
    * @param	integer	reference id
    * @param	scriptanme that is used for linking;
    * @access	public
    */
    protected function setLocator()
    {
        $ilLocator = $this->locator;
        $tpl = $this->tpl;

        if ($this->omit_locator) {
            return;
        }

        // repository vs. workspace
        if ($this->call_by_reference) {
            // todo: admin workaround
            // in the future, objectgui classes should not be called in
            // admin section anymore (rbac/trash handling in own classes)
            $ref_id = ($_GET["ref_id"] != "")
                ? $_GET["ref_id"]
                : $this->object->getRefId();
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
    protected function addLocatorItems()
    {
    }

    protected function omitLocator($a_omit = true)
    {
        $this->omit_locator = $a_omit;
    }

    /**
    * should be overwritten to add object specific items
    * (repository items are preloaded)
    *
    * @param bool $a_do_not_add_object
    */
    protected function addAdminLocatorItems($a_do_not_add_object = false)
    {
        $ilLocator = $this->locator;

        if ($_GET["admin_mode"] == "settings") {	// system settings
            $this->ctrl->setParameterByClass(
                "ilobjsystemfoldergui",
                "ref_id",
                SYSTEM_FOLDER_ID
            );
            $ilLocator->addItem(
                $this->lng->txt("administration"),
                $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjsystemfoldergui"), "")
            );
            if ($this->object && ($this->object->getRefId() != SYSTEM_FOLDER_ID && !$a_do_not_add_object)) {
                $ilLocator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, "view")
                );
            }
        } else {							// repository administration
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
            //$ilLocator->addItem($this->lng->txt("administration"),
            //	$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
            //	ilFrameTargetInfo::_getFrame("MainContent"));
            $this->ctrl->clearParametersByClass("iladministrationgui");
            $ilLocator->addAdministrationItems();
        }
    }

    /**
    * confirmed deletion of object -> objects are moved to trash or deleted
    * immediately, if trash is disabled
    */
    public function confirmedDeleteObject()
    {
        if (isset($_POST["mref_id"])) {
            $_SESSION["saved_post"] = array_unique(array_merge($_SESSION["saved_post"], $_POST["mref_id"]));
        }

        include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
        $ru = new ilRepUtilGUI($this);
        $ru->deleteObjects($_GET["ref_id"], ilSession::get("saved_post"));
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }

    /**
    * cancel deletion of object
    *
    * @access	public
    */
    public function cancelDeleteObject()
    {
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }


    /**
    * cancel action and go back to previous page
    * @access	public
    *
    */
    public function cancelObject()
    {
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }

    /**
    * create new object form
    *
    * @access	public
    */
    public function createObject()
    {
        $tpl = $this->tpl;
        $ilErr = $this->ilErr;

        $new_type = $_REQUEST["new_type"];


        // add new object to custom parent container
        $this->ctrl->saveParameter($this, "crtptrefid");
        // use forced callback after object creation
        $this->ctrl->saveParameter($this, "crtcb");

        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        } else {
            $this->lng->loadLanguageModule($new_type);
            $this->ctrl->setParameter($this, "new_type", $new_type);

            $forms = $this->initCreationForms($new_type);

            // copy form validation error: do not show other creation forms
            if ($_GET["cpfl"] && isset($forms[self::CFORM_CLONE])) {
                $forms = array(self::CFORM_CLONE => $forms[self::CFORM_CLONE]);
            }
            $tpl->setContent($this->getCreationFormsHTML($forms));
        }
    }

    /**
     * Init creation froms
     *
     * this will create the default creation forms: new, import, clone
     *
     * @param	string	$a_new_type
     * @return	array
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm($a_new_type),
            self::CFORM_CLONE => $this->fillCloneTemplate(null, $a_new_type)
            );

        return $forms;
    }

    /**
     * Get HTML for creation forms (accordion)
     *
     * @param array $a_forms
     */
    final protected function getCreationFormsHTML(array $a_forms)
    {
        $tpl = $this->tpl;

        // #13168- sanity check
        foreach ($a_forms as $id => $form) {
            if (!$form instanceof ilPropertyFormGUI) {
                unset($a_forms[$id]);
            }
        }

        // no accordion if there is just one form
        if (sizeof($a_forms) == 1) {
            $form_type = key($a_forms);
            $a_forms = array_shift($a_forms);

            // see bug #0016217
            if (method_exists($this, "getCreationFormTitle")) {
                $form_title = $this->getCreationFormTitle($form_type);
                if ($form_title != "") {
                    $a_forms->setTitle($form_title);
                }
            }
            return $a_forms->getHTML();
        } else {
            include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");

            $acc = new ilAccordionGUI();
            $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
            $cnt = 1;
            foreach ($a_forms as $form_type => $cf) {
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
                $htpl->setVariable("TITLE", $this->lng->txt("option") . " " . $cnt . ": " .
                    $form_title);
                $cf->setTitle(null);
                $cf->setTitleIcon(null);
                $cf->setTableWidth("100%");

                $acc->addItem($htpl->get(), $cf->getHTML());

                $cnt++;
            }

            return "<div class='ilCreationFormSection'>" . $acc->getHTML() . "</div>";
        }
    }

    /**
     * Init object creation form
     *
     * @param	string	$a_new_type
     * @return	ilPropertyFormGUI
     */
    protected function initCreateForm($a_new_type)
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($a_new_type . "_new"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form = $this->initDidacticTemplate($form);

        $form->addCommandButton("save", $this->lng->txt($a_new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Show didactic template types
     * @param ilPropertyFormGUI $form
     * @return ilPropertyFormGUI $form
     */
    protected function initDidacticTemplate(ilPropertyFormGUI $form)
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('didactic');
        $existing_exclusive = false;
        $options = [];
        $options['dtpl_0'] = array($this->lng->txt('didactic_default_type'),
            sprintf(
                $this->lng->txt('didactic_default_type_info'),
                $this->lng->txt('objs_' . $this->type)
            ));

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettings.php';
        $templates = ilDidacticTemplateSettings::getInstanceByObjectType($this->type)->getTemplates();
        if ($templates) {
            foreach ($templates as $template) {
                if ($template->isEffective($_GET["ref_id"])) {
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
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
                $value = 'dtpl_' . ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

                $type->setValue($value);

                if (!in_array($value, array_keys($options)) || ($existing_exclusive && $value == "dtpl_0")) {
                    //add or rename actual value to not avaiable
                    $options[$value] = array($this->lng->txt('not_available'));
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
     *
     * @param array $a_options
     */
    protected function addDidacticTemplateOptions(array &$a_options)
    {
    }

    /**
     * cancel create action and go back to repository parent
     */
    public function cancelCreation()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilrepositorygui", "frameset");
    }

    /**
    * save object
    *
    * @access	public
    */
    public function saveObject()
    {
        $objDefinition = $this->objDefinition;
        $tpl = $this->tpl;
        $ilErr = $this->ilErr;

        $new_type = $_REQUEST["new_type"];

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->MESSAGE);
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);

        $form = $this->initCreateForm($new_type);
        if ($form->checkInput()) {
            $this->ctrl->setParameter($this, "new_type", "");

            // create instance
            $class_name = "ilObj" . $objDefinition->getClassName($new_type);
            $location = $objDefinition->getLocation($new_type);
            include_once($location . "/class." . $class_name . ".php");
            $newObj = new $class_name();
            $newObj->setType($new_type);
            $newObj->setTitle($form->getInput("title"));
            $newObj->setDescription($form->getInput("desc"));
            $newObj->create();

            $this->putObjectInTree($newObj);

            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }

            // auto rating
            $this->handleAutoRating($newObj);

            // additional paramters are added to afterSave()
            $args = func_get_args();
            if ($args) {
                $this->afterSave($newObj, $args);
            } else {
                $this->afterSave($newObj);
            }
            return;
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Get didactic template setting from creation screen
     *
     * @param string $a_type
     * @return string
     */
    public function getDidacticTemplateVar($a_type)
    {
        $tpl = $_POST["didactic_type"];
        if ($tpl && substr($tpl, 0, strlen($a_type) + 1) == $a_type . "_") {
            return (int) substr($tpl, strlen($a_type) + 1);
        }
        return 0;
    }

    /**
     * Add object to tree at given position
     *
     * @param ilObject $a_obj
     * @param int $a_parent_node_id
     */
    public function putObjectInTree(ilObject $a_obj, $a_parent_node_id = null)
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        $objDefinition = $this->objDefinition;

        if (!$a_parent_node_id) {
            $a_parent_node_id = $_GET["ref_id"];
        }

        // add new object to custom parent container
        if ((int) $_REQUEST["crtptrefid"]) {
            $a_parent_node_id = (int) $_REQUEST["crtptrefid"];
        }

        $a_obj->createReference();
        $a_obj->putInTree($a_parent_node_id);
        $a_obj->setPermissions($a_parent_node_id);

        $this->obj_id = $a_obj->getId();
        $this->ref_id = $a_obj->getRefId();

        // BEGIN ChangeEvent: Record save object.
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordWriteEvent($this->obj_id, $ilUser->getId(), 'create');
        // END ChangeEvent: Record save object.

        // rbac log
        include_once "Services/AccessControl/classes/class.ilRbacLog.php";
        $rbac_log_roles = $rbacreview->getParentRoleIds($this->ref_id, false);
        $rbac_log = ilRbacLog::gatherFaPa($this->ref_id, array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $this->ref_id, $rbac_log);

        // use forced callback after object creation
        $this->callCreationCallback($a_obj, $this->objDefinition, $_GET['crtcb'] ?? 0);
    }

    /**
     * Post (successful) object creation hook
     *
     * @param ilObject $a_new_object
     */
    protected function afterSave(ilObject $a_new_object)
    {
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * edit object
     *
     * @access	public
     */
    public function editObject()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;
        $ilErr = $this->ilErr;

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $ilTabs->activateTab("settings");

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values);
        }

        $this->addExternalEditFormCustom($form);

        $tpl->setContent($form->getHTML());
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $a_form)
    {
        // has to be done AFTER setValuesByArray() ...
    }

    /**
     * Init object edit form
     *
     * @return ilPropertyFormGUI
     */
    protected function initEditForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule($this->object->getType());

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $this->initEditCustomForm($form);

        $form->addCommandButton("update", $this->lng->txt("save"));
        //$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Add custom fields to update form
     *
     * @param	ilPropertyFormGUI	$a_form
     */
    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
    }

    /**
     * Get values for edit form
     *
     * @return array
     */
    protected function getEditFormValues()
    {
        $values["title"] = $this->object->getTitle();
        $values["desc"] = $this->object->getLongDescription();
        $this->getEditFormCustomValues($values);
        return $values;
    }

    /**
     * Add values to custom edit fields
     *
     * @param	array	$a_values
     */
    protected function getEditFormCustomValues(array &$a_values)
    {
    }

    /**
     * updates object entry in object_data
     */
    public function updateObject()
    {
        $ilTabs = $this->tabs_gui;
        $tpl = $this->tpl;
        $ilErr = $this->ilErr;

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $form = $this->initEditForm();
        if ($form->checkInput() &&
            $this->validateCustom($form)) {
            $this->object->setTitle($form->getInput("title"));
            $this->object->setDescription($form->getInput("desc"));
            $this->updateCustom($form);
            $this->object->update();

            $this->afterUpdate();
            return;
        }

        // display form again to correct errors
        $ilTabs->activateTab("settings");
        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Validate custom values (if not possible with checkInput())
     *
     * @param ilPropertyFormGUI $a_form
     * @return boolean
     */
    protected function validateCustom(ilPropertyFormGUI $a_form)
    {
        return true;
    }

    /**
     * Insert custom update form values into object
     *
     * @param	ilPropertyFormGUI	$a_form
     */
    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
    }

    /**
     * Post (successful) object update hook
     */
    protected function afterUpdate()
    {
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Init object import form
     *
     * @param	string	new type
     * @return	ilPropertyFormGUI
     */
    protected function initImportForm($a_new_type)
    {
        global $DIC;

        $import_directory_factory = new ilImportDirectoryFactory();
        $export_directory = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_EXPORT);
        $upload_files = $export_directory->getFilesFor((int) $DIC->user()->getId(), (string) $a_new_type);
        $has_upload_files = false;
        if (count($upload_files)) {
            $has_upload_files = true;
        }

        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "importFile"));
        $form->setTitle($this->lng->txt($a_new_type . "_import"));

        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        if ($has_upload_files) {
            $this->lng->loadLanguageModule('content');
            $option = new ilRadioGroupInputGUI(
                $this->lng->txt('cont_choose_file_source'),
                'upload_type'
            );
            $option->setValue(self::UPLOAD_TYPE_LOCAL);
            $form->addItem($option);

            $direct = new ilRadioOption(
                $this->lng->txt('cont_choose_local'),
                self::UPLOAD_TYPE_LOCAL
            );
            $option->addOption($direct);

            $direct->addSubItem($fi);
            $upload = new ilRadioOption(
                $this->lng->txt('cont_choose_upload_dir'),
                self::UPLOAD_TYPE_UPLOAD_DIRECTORY
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

    /**
     * Import
     */
    protected function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        global $DIC;

        $user = $DIC->user();

        $objDefinition = $this->objDefinition;
        $tpl = $this->tpl;
        $ilErr = $this->ilErr;

        if (!$parent_id) {
            $parent_id = $_GET["ref_id"];
        }
        $new_type = $_REQUEST["new_type"];
        $upload_type = $this->request->getParsedBody()['upload_type'] ?? self::UPLOAD_TYPE_LOCAL;

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"));
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);

        $form = $this->initImportForm($new_type);
        if ($form->checkInput()) {
            // :todo: make some check on manifest file

            if ($objDefinition->isContainer($new_type)) {
                include_once './Services/Export/classes/class.ilImportContainer.php';
                $imp = new ilImportContainer((int) $parent_id);
            } else {
                include_once("./Services/Export/classes/class.ilImport.php");
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
                    $file = $export_upload->getAbsolutePathForHash($user->getId(), $new_type, $hash);

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
                if (DEVMODE) {
                    throw $e;
                }
                $this->tmp_import_dir = $imp->getTemporaryImportDir();
                if (!$a_catch_errors) {
                    throw $e;
                }
                // display message and form again
                ilUtil::sendFailure($this->lng->txt("obj_import_file_error") . " <br />" . $e->getMessage());
                $form->setValuesByPost();
                $tpl->setContent($form->getHtml());
                return;
            }

            if ($new_id > 0) {
                $this->ctrl->setParameter($this, "new_type", "");

                $newObj = ilObjectFactory::getInstanceByObjId($new_id);
                // put new object id into tree - already done in import for containers
                if (!$objDefinition->isContainer($new_type)) {
                    $this->putObjectInTree($newObj);
                } else {
                    $ref_ids = ilObject::_getAllReferences($newObj->getId());
                    if (count($ref_ids) === 1) {
                        $newObj->setRefId((int) current($ref_ids));
                    }
                    $this->callCreationCallback($newObj, $this->objDefinition, $_GET['crtcb'] ?? 0);   // see #24244
                }

                $this->afterImport($newObj);
            }
            // import failed
            else {
                if ($objDefinition->isContainer($new_type)) {
                    ilUtil::sendFailure($this->lng->txt("container_import_zip_file_invalid"));
                } else {
                    // not enough information here...
                    return;
                }
            }
        }

        // display form to correct errors
        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Post (successful) object import hook
     *
     * @param ilObject $a_new_object
     */
    protected function afterImport(ilObject $a_new_object)
    {
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
    * get form action for command (command is method name without "Object", e.g. "perm")
    * @param	string		$a_cmd			command
    * @param	string		$a_formaction	default formaction (is returned, if no special
    *										formaction was set)
    * @access	public
    * @return	string
    */
    public function getFormAction($a_cmd, $a_formaction = "")
    {
        if ($this->formaction[$a_cmd] != "") {
            return $this->formaction[$a_cmd];
        } else {
            return $a_formaction;
        }
    }

    /**
    * set specific form action for command
    *
    * @param	string		$a_cmd			command
    * @param	string		$a_formaction	default formaction (is returned, if no special
    *										formaction was set)
    * @access	public
    */
    protected function setFormAction($a_cmd, $a_formaction)
    {
        $this->formaction[$a_cmd] = $a_formaction;
    }

    /**
    * get return location for command (command is method name without "Object", e.g. "perm")
    * @param	string		$a_cmd		command
    * @param	string		$a_location	default return location (is returned, if no special
    *									return location was set)
    * @access	public
    */
    protected function getReturnLocation($a_cmd, $a_location = "")
    {
        if ($this->return_location[$a_cmd] != "") {
            return $this->return_location[$a_cmd];
        } else {
            return $a_location;
        }
    }

    /**
    * set specific return location for command
    * @param	string		$a_cmd		command
    * @param	string		$a_location	default return location (is returned, if no special
    *									return location was set)
    * @access	public
    */
    protected function setReturnLocation($a_cmd, $a_location)
    {
        //echo "-".$a_cmd."-".$a_location."-";
        $this->return_location[$a_cmd] = $a_location;
    }

    /**
    * get target frame for command (command is method name without "Object", e.g. "perm")
    * @param	string		$a_cmd			command
    * @param	string		$a_target_frame	default target frame (is returned, if no special
    *										target frame was set)
    * @access	public
    */
    protected function getTargetFrame($a_cmd, $a_target_frame = "")
    {
        if ($this->target_frame[$a_cmd] != "") {
            return $this->target_frame[$a_cmd];
        } elseif (!empty($a_target_frame)) {
            return "target=\"" . $a_target_frame . "\"";
        } else {
            return;
        }
    }

    /**
    * set specific target frame for command
    * @param	string		$a_cmd			command
    * @param	string		$a_target_frame	default target frame (is returned, if no special
    *										target frame was set)
    * @access	public
    */
    protected function setTargetFrame($a_cmd, $a_target_frame)
    {
        $this->target_frame[$a_cmd] = "target=\"" . $a_target_frame . "\"";
    }

    // BEGIN Security: Hide objects which aren't accessible by the user.
    public function isVisible($a_ref_id, $a_type)
    {
        $visible = $this->checkPermissionBool("visible,read", "", "", $a_ref_id);

        if ($visible && $a_type == 'crs') {
            $tree = $this->tree;
            if ($crs_id = $tree->checkForParentType($a_ref_id, 'crs')) {
                if (!$this->checkPermissionBool("write", "", "", $crs_id)) {
                    // Show only activated courses
                    $tmp_obj = &ilObjectFactory::getInstanceByRefId($crs_id, false);

                    if (!$tmp_obj->isActivated()) {
                        unset($tmp_obj);
                        $visible = false;
                    }
                }
            }
        }

        return $visible;
    }
    // END Security: Hide objects which aren't accessible by the user.

    /**
     * viewObject container presentation for "administration -> repository, trash, permissions"
     * @throws \ilObjectException
    */
    public function viewObject()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $user = $DIC->user();

        $this->checkPermission('visible') && $this->checkPermission('read');

        $this->tabs_gui->activateTab('view');

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $user->getId()
        );

        if (!$this->withReferences()) {
            $this->ctrl->setParameter($this, 'obj_id', $this->obj_id);
        }
        $itab = new ilAdminSubItemsTableGUI(
            $this,
            "view",
            $_GET["ref_id"],
            $this->checkPermissionBool('write')
        );

        $tpl->setContent($itab->getHTML());
    }

    /**
    * Display deletion confirmation screen.
    * Only for referenced objects. For user,role & rolt overwrite this function in the appropriate
    * Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
    *
    * @access	public
    */
    public function deleteObject($a_error = false)
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["item_ref_id"] != "") {
            $_POST["id"] = array($_GET["item_ref_id"]);
        }

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $idx => $id) {
                $_POST["id"][$idx] = (int) $id;
            }
        }

        // SAVE POST VALUES (get rid of this
        ilSession::set("saved_post", $_POST["id"]);

        include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
        $ru = new ilRepUtilGUI($this);
        if (!$ru->showDeleteConfirmation($_POST["id"], $a_error)) {
            $ilCtrl->returnToParent($this);
        }
    }

    /**
    * show possible subobjects (pulldown menu)
    *
    * @access	public
    */
    protected function showPossibleSubObjects()
    {
        if ($this->sub_objects == "") {
            $d = $this->objDefinition->getCreatableSubObjects($this->object->getType(), ilObjectDefinition::MODE_REPOSITORY, $this->ref_id);
        } else {
            $d = $this->sub_objects;
        }

        $import = false;

        if (count($d) > 0) {
            foreach ($d as $row) {
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

        if (is_array($subobj)) {

            //build form
            $opts = ilUtil::formSelect(12, "new_type", $subobj);
            $this->tpl->setCurrentBlock("add_object");
            $this->tpl->setVariable("SELECT_OBJTYPE", $opts);
            $this->tpl->setVariable("BTN_NAME", "create");
            $this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * get a template blockfile
    * format: tpl.<objtype>_<command>.html
    *
    * @param	string	command
    * @param	string	object type definition
    * @access	public
    */
    final protected function getTemplateFile($a_cmd, $a_type = "")
    {
        mk();
        die("ilObjectGUI::getTemplateFile() is deprecated.");
    }

    /**
    * get tabs
    * abstract method.
    * @abstract	overwrite in derived GUI class of your object type
    * @access	public
    * @param	object	instance of ilTabsGUI
    */
    protected function getTabs()
    {
        // please define your tabs here
    }

    // PROTECTED
    protected function __showButton($a_cmd, $a_text, $a_target = '')
    {
        $ilToolbar = $this->toolbar;

        $ilToolbar->addButton($a_text, $this->ctrl->getLinkTarget($this, $a_cmd), $a_target);
    }

    protected function hitsperpageObject()
    {
        ilSession::set("tbl_limit", $_POST["hitsperpage"]);
        $_GET["limit"] = $_POST["hitsperpage"];
    }


    protected function &__initTableGUI()
    {
        include_once "./Services/Table/classes/class.ilTableGUI.php";

        return new ilTableGUI(0, false);
    }

    /**
     * standard implementation for tables
     * use 'from' variable use different initial setting of table
     *
     */
    protected function __setTableGUIBasicData(&$tbl, &$result_set, $a_from = "")
    {
        switch ($a_from) {
            case "clipboardObject":
                $offset = $_GET["offset"];
                $order = $_GET["sort_by"];
                $direction = $_GET["sort_order"];
                $tbl->disable("footer");
                break;

            default:
                $offset = $_GET["offset"];
                $order = $_GET["sort_by"];
                $direction = $_GET["sort_order"];
                break;
        }

        $tbl->setOrderColumn($order);
        $tbl->setOrderDirection($direction);
        $tbl->setOffset($offset);
        $tbl->setLimit($_GET["limit"]);
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        $tbl->setData($result_set);
    }

    /**
    * redirects to (repository) view per ref id
    * usually to a container and usually used at
    * the end of a save/import method where the object gui
    * type (of the new object) doesn't match with the type
    * of the current $_GET["ref_id"] value
    *
    * @param	int		$a_ref_id		reference id
    */
    protected function redirectToRefId($a_ref_id, $a_cmd = "")
    {
        $obj_type = ilObject::_lookupType($a_ref_id, true);
        $class_name = $this->objDefinition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $a_ref_id);
        $this->ctrl->redirectByClass(array("ilrepositorygui", $class), $a_cmd);
    }

    // Object Cloning
    /**
     * Fill object clone template
     * This method can be called from any object GUI class that wants to offer object cloning.
     *
     * @access public
     * @param string template variable name that will be filled
     * @param string type of new object
     *
     */
    protected function fillCloneTemplate($a_tpl_varname, $a_type)
    {
        include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
        $cp = new ilObjectCopyGUI($this);
        $cp->setType($a_type);
        $cp->setTarget($_GET['ref_id']);
        if ($a_tpl_varname) {
            $cp->showSourceSearch($a_tpl_varname);
        } else {
            return $cp->showSourceSearch(null);
        }
    }

    /**
     * Clone single (not container object)
     * Method is overwritten in ilContainerGUI
     *
     * @access public
     */
    public function cloneAllObject()
    {
        include_once('./Services/Link/classes/class.ilLink.php');
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

        $ilErr = $this->ilErr;
        $ilUser = $this->user;

        $new_type = $_REQUEST['new_type'];
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'));
        }
        if (!(int) $_REQUEST['clone_source']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->createObject();
            return false;
        }
        if (!$this->checkPermissionBool("write", "", $new_type, (int) $_REQUEST['clone_source'])) {
            $ilErr->raiseError($this->lng->txt('permission_denied'));
        }

        // Save wizard options
        $copy_id = ilCopyWizardOptions::_allocateCopyId();
        $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
        $wizard_options->saveOwner($ilUser->getId());
        $wizard_options->saveRoot((int) $_REQUEST['clone_source']);

        $options = $_POST['cp_options'] ? $_POST['cp_options'] : array();
        foreach ($options as $source_id => $option) {
            $wizard_options->addEntry($source_id, $option);
        }
        $wizard_options->read();

        $orig = ilObjectFactory::getInstanceByRefId((int) $_REQUEST['clone_source']);
        $new_obj = $orig->cloneObject((int) $_GET['ref_id'], $copy_id);

        // Delete wizard options
        $wizard_options->deleteAll();

        ilUtil::sendSuccess($this->lng->txt("object_duplicated"), true);
        ilUtil::redirect(ilLink::_getLink($new_obj->getRefId()));
    }


    /**
    * Get center column
    */
    protected function getCenterColumnHTML()
    {
        $ilCtrl = $this->ctrl;

        include_once("Services/Block/classes/class.ilColumnGUI.php");

        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if ($ilCtrl->getNextClass() != "ilcolumngui") {
            // normal command processing
            return $this->getContent();
        } else {
            if (!$ilCtrl->isAsynch()) {
                //if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
                if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE) {
                    // right column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $ilCtrl->forwardCommand($column_gui);
                    }
                    // left column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_LEFT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $ilCtrl->forwardCommand($column_gui);
                    }
                } else {
                    // normal command processing
                    return $this->getContent();
                }
            }
        }
    }

    /**
    * Display right column
    */
    protected function getRightColumnHTML()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        include_once("Services/Block/classes/class.ilColumnGUI.php");
        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        $this->setColumnSettings($column_gui);

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = $ilCtrl->getHTML($column_gui);
            }
        }

        return $html;
    }

    /**
    * May be overwritten in subclasses.
    */
    protected function setColumnSettings(ilColumnGUI $column_gui)
    {
        $column_gui->setRepositoryMode(true);
        $column_gui->setEnableEdit(false);
        if ($this->checkPermissionBool("write")) {
            $column_gui->setEnableEdit(true);
        }
    }

    /**
     * Check permission and redirect on error
     *
     * @param string $a_perm
     * @param string $a_cmd
     * @param string $a_type
     * @param int $a_ref_id
     * @throws ilObjectException
     * @return bool
     */
    protected function checkPermission($a_perm, $a_cmd = "", $a_type = "", $a_ref_id = null)
    {
        if (!$this->checkPermissionBool($a_perm, $a_cmd, $a_type, $a_ref_id)) {
            if (!is_int(strpos($_SERVER["PHP_SELF"], "goto.php"))) {
                // create: redirect to parent
                if ($a_perm == "create") {
                    if (!$a_ref_id) {
                        $a_ref_id = $_GET["ref_id"];
                    }
                    $type = ilObject::_lookupType($a_ref_id, true);
                } else {
                    // does this make sense?
                    if (!is_object($this->object)) {
                        return;
                    }
                    if (!$a_ref_id) {
                        $a_ref_id = $this->object->getRefId();
                    }
                    $type = $this->object->getType();
                }

                ilSession::clear("il_rep_ref_id");

                include_once "Services/Object/exceptions/class.ilObjectException.php";
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
                $parent_ref_id = $this->tree->getParentNodeData($this->object->getRefId())['ref_id'];
                $this->ctrl->redirectToURL(ilLink::_getLink($parent_ref_id));
            }
            // we should never be here
            else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
                self::_gotoRepositoryRoot();
            }
        }
    }

    /**
     * Check permission
     *
     * @param string $a_perm
     * @param string $a_cmd
     * @param string $a_type
     * @param int $a_ref_id
     * @return bool
     */
    protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_ref_id = null)
    {
        $ilAccess = $this->access;

        if ($a_perm == "create") {
            if (!$a_ref_id) {
                $a_ref_id = $_GET["ref_id"];
            }
            return $ilAccess->checkAccess($a_perm . "_" . $a_type, $a_cmd, $a_ref_id);
        } else {
            // does this make sense?
            if (!is_object($this->object)) {
                return false;
            }
            if (!$a_ref_id) {
                $a_ref_id = $this->object->getRefId();
            }
            return $ilAccess->checkAccess($a_perm, $a_cmd, $a_ref_id);
        }
    }

    /**
     * Goto repository root
     *
     * @param
     * @return
     */
    public static function _gotoRepositoryRoot($a_raise_error = false)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $_GET["cmd"] = "frameset";
            $_GET["target"] = "";
            $_GET["ref_id"] = ROOT_FOLDER_ID;
            $_GET["baseClass"] = "ilRepositoryGUI";
            include("ilias.php");
            exit;
        }

        if ($a_raise_error) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }
    }

    /**
     * Goto repository root
     *
     * @param
     * @return
     */
    public static function _gotoRepositoryNode($a_ref_id, $a_cmd = "frameset")
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];

        $_GET["cmd"] = $a_cmd;
        $_GET["target"] = "";
        $_GET["ref_id"] = $a_ref_id;
        $_GET["baseClass"] = "ilRepositoryGUI";
        include("ilias.php");
        exit;
    }

    /**
     * Enables the file upload into this object by dropping files.
     */
    protected function enableDragDropFileUpload()
    {
        include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
        ilFileUploadGUI::initFileUpload();

        $this->tpl->enableDragDropFileUpload($this->ref_id);
    }

    /**
     * Activate rating automatically if parent container setting
     *
     * @param ilObject $a_new_obj
     */
    protected function handleAutoRating(ilObject $a_new_obj)
    {
        if (ilObject::hasAutoRating($a_new_obj->getType(), $a_new_obj->getRefId()) &&
            method_exists($a_new_obj, "setRating")) {
            $a_new_obj->setRating(true);
            $a_new_obj->update();
        }
    }

    /**
     * show edit section of custom icons for container
     *
     */
    protected function showCustomIconsEditing($a_input_colspan = 1, ilPropertyFormGUI $a_form = null, $a_as_section = true)
    {
        if ($this->settings->get("custom_icons")) {
            if ($a_form) {
                global $DIC;
                /** @var \ilObjectCustomIconFactory  $customIconFactory */
                $customIconFactory = $DIC['object.customicons.factory'];

                $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

                if ($a_as_section) {
                    $title = new ilFormSectionHeaderGUI();
                    $title->setTitle($this->lng->txt("icon_settings"));
                } else {
                    $title = new ilCustomInputGUI($this->lng->txt("icon_settings"), "");
                }
                $a_form->addItem($title);

                $caption = $this->lng->txt("cont_custom_icon");
                $icon = new ilImageFileInputGUI($caption, "cont_icon");

                $icon->setSuffixes($customIcon->getSupportedFileExtensions());
                $icon->setUseCache(false);
                if ($customIcon->exists()) {
                    $icon->setImage($customIcon->getFullPath());
                } else {
                    $icon->setImage('');
                }
                if ($a_as_section) {
                    $a_form->addItem($icon);
                } else {
                    $title->addSubItem($icon);
                }
            }
        }
    }

    /**
     * Redirect after creation, see https://docu.ilias.de/goto_docu_wiki_wpage_5035_1357.html
     *
     * Should be overwritten and redirect to settings screen.
     */
    public function redirectAfterCreation()
    {
        $ctrl = $this->ctrl;
        $link = ilLink::_getLink($this->object->getRefId());
        $ctrl->redirectToURL($link);
    }

    /**
     * @inheritDoc
     */
    public function addToDeskObject()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $user = $this->user;
        $this->favourites->add($user->getId(), (int) $_GET["item_ref_id"]);
        $lng->loadLanguageModule("rep");
        ilUtil::sendSuccess($lng->txt("rep_added_to_favourites"), true);
        $ctrl->redirectToURL(ilLink::_getLink((int) $_GET["ref_id"]));
    }

    /**
     * @inheritDoc
     */
    public function removeFromDeskObject()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $user = $this->user;
        $lng->loadLanguageModule("rep");
        $this->favourites->remove($user->getId(), (int) $_GET["item_ref_id"]);
        ilUtil::sendSuccess($lng->txt("rep_removed_from_favourites"), true);
        $ctrl->redirectToURL(ilLink::_getLink((int) $_GET["ref_id"]));
    }
}
