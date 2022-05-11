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
 
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory;

/**
 * New implementation of ilObjectGUI. (beta)
 *
 * Differences to the ilObject implementation:
 * - no $this->tree anymore
 * - no $this->formaction anymore
 * - no $this->return_location anymore
 * - no $this->target_frame anymore
 * - no $this->actions anymore
 * - no $this->sub_objects anymore
 * - no $this->data anymore
 * - no $this->prepare_output anymore
 *
 *
 * All new modules should derive from this class.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
abstract class ilObject2GUI extends ilObjectGUI
{
    const OBJECT_ID = 0;
    const REPOSITORY_NODE_ID = 1;
    const WORKSPACE_NODE_ID = 2;
    const REPOSITORY_OBJECT_ID = 3;
    const WORKSPACE_OBJECT_ID = 4;
    const PORTFOLIO_OBJECT_ID = 5;

    protected ilObjectDefinition $obj_definition;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilObjectService $object_service;
    protected ilFavouritesManager $favourites;
    protected ilErrorHandling $error;
    protected ilLocatorGUI $locator;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected RequestWrapper $request_wrapper;
    protected Factory $refinery;
    protected ilRbacAdmin $rbac_admin;
    protected ilRbacSystem $rbac_system;
    protected ilRbacReview $rbac_review;

    protected int $request_ref_id;
    protected int $id_type;
    protected int $parent_id;
    protected string $type;
    protected string $html;

    protected int $object_id;
    protected ?int $node_id = null;
    protected array $creation_forms = array();
    /**
     * @var ilDummyAccessHandler|ilPortfolioAccessHandler|ilWorkspaceAccessHandler|mixed
     */
    protected $access_handler;
    private ilObjectRequestRetriever $retriever;
    
    public function __construct(int $id = 0, int $id_type = self::REPOSITORY_NODE_ID, int $parent_node_id = 0)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC["ilCtrl"];
        $this->lng = $DIC["lng"];
        $this->tabs_gui = $DIC["ilTabs"];
        $this->object_service = $DIC->object();
        $this->favourites = new ilFavouritesManager();
        $this->error = $DIC['ilErr'];
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $this->refinery);
        $this->rbac_admin = $DIC->rbac()->admin();
        $this->rbac_system = $DIC->rbac()->system();
        $this->rbac_review = $DIC->rbac()->review();

        $tree = $DIC["tree"];

        $this->requested_ref_id = 0;
        if ($this->request_wrapper->has("ref_id")) {
            $this->requested_ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        }
        $this->id_type = $id_type;
        $this->parent_id = $parent_node_id;
        $this->type = $this->getType();
        $this->html = "";

        $this->requested_new_type = '';
        if ($this->request_wrapper->has("new_type")) {
            $this->requested_new_type = $this->request_wrapper->retrieve(
                "new_type",
                $this->refinery->kindlyTo()->string()
            );
        } elseif ($this->post_wrapper->has("new_type")) {
            $this->requested_new_type = $this->post_wrapper->retrieve(
                "new_type",
                $this->refinery->kindlyTo()->string()
            );
        }

        $params = array();
        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
                $this->node_id = $id;
                $this->object_id = ilObject::_lookupObjectId($this->node_id);
                $this->tree = $tree;
                $this->access_handler = $this->access;
                $this->call_by_reference = true;  // needed for prepareOutput()
                $params[] = "ref_id";
                break;

            case self::REPOSITORY_OBJECT_ID:
                $this->object_id = $id;
                $this->tree = $tree;
                $this->access_handler = $this->access;
                $params[] = "obj_id"; // ???
                break;

            case self::WORKSPACE_NODE_ID:
                $ilUser = $DIC["ilUser"];
                $this->node_id = $id;
                $this->tree = new ilWorkspaceTree($ilUser->getId());
                $this->object_id = $this->tree->lookupObjectId($this->node_id);
                $this->access_handler = new ilWorkspaceAccessHandler($this->tree);
                $params[] = "wsp_id";
                break;

            case self::WORKSPACE_OBJECT_ID:
                $ilUser = $DIC["ilUser"];
                $this->object_id = $id;
                $this->tree = new ilWorkspaceTree($ilUser->getId());
                $this->access_handler = new ilWorkspaceAccessHandler($this->tree);
                $params[] = "obj_id"; // ???
                break;
            
            case self::PORTFOLIO_OBJECT_ID:
                $this->object_id = $id;
                $this->access_handler = new ilPortfolioAccessHandler();
                $params[] = "prt_id";
                break;

            case self::OBJECT_ID:
                $this->object_id = $id;
                $this->access_handler = new ilDummyAccessHandler();
                $params[] = "obj_id";
                break;
        }
        $this->ctrl->saveParameter($this, $params);


        
        // old stuff for legacy code (obsolete?)
        if (!$this->object_id) {
            $this->creation_mode = true;
        }
        if ($this->node_id) {
            // add parent node id if missing
            if (!$this->parent_id && $this->tree) {
                $this->parent_id = $this->tree->getParentId($this->node_id);
            }
        }
        $this->ref_id = $this->node_id ?? 0;
        $this->obj_id = $this->object_id;

        $this->assignObject();
        
        // set context
        if (is_object($this->object)) {
            $this->ctrl->setContextObject($this->object->getId(), $this->object->getType());
        }
        
        $this->afterConstructor();
    }
    
    /**
     * Do anything that should be done after constructor in here.
     */
    protected function afterConstructor() : void
    {
    }
    
    /**
     * execute command
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->prepareOutput();

        switch ($next_class) {
            case "ilworkspaceaccessgui":
                if ($this->node_id) {
                    $this->tabs_gui->activateTab("id_permissions");
                    $wspacc = new ilWorkspaceAccessGUI($this->node_id, $this->getAccessHandler());
                    $this->ctrl->forwardCommand($wspacc);
                } else {
                    if (!$cmd) {
                        $cmd = "render";
                    }
                    $this->$cmd();
                }
                break;

            default:
                if (!$cmd) {
                    $cmd = "render";
                }
                $this->$cmd();
        }
    }

    public function getIdType() : int
    {
        return $this->id_type;
    }

    /**
     * create object instance as internal property (repository/workspace switch)
     */
    final protected function assignObject() : void
    {
        if ($this->object_id != 0) {
            switch ($this->id_type) {
                case self::OBJECT_ID:
                case self::REPOSITORY_OBJECT_ID:
                case self::WORKSPACE_OBJECT_ID:
                case self::PORTFOLIO_OBJECT_ID:
                    $this->object = ilObjectFactory::getInstanceByObjId($this->object_id);
                    break;

                case self::REPOSITORY_NODE_ID:
                    $this->object = ilObjectFactory::getInstanceByRefId($this->node_id);
                    break;

                case self::WORKSPACE_NODE_ID:
                    // to be discussed
                    $this->object = ilObjectFactory::getInstanceByObjId($this->object_id);
                    break;
            }
        }
    }
    
    /**
     * @return mixed
     */
    protected function getAccessHandler()
    {
        return $this->access_handler;
    }

    /**
     * set Locator
     */
    protected function setLocator() : void
    {
        if ($this->omit_locator) {
            return;
        }

        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
                $ref_id = $this->node_id ?: $this->parent_id;
                $this->locator->addRepositoryItems($ref_id);
                
                // not so nice workaround: todo: handle locator as tabs in ilTemplate
                if ($this->admin_mode == self::ADMIN_MODE_NONE &&
                    strtolower($this->ctrl->getCmdClass()) == "ilobjrolegui") {
                    $this->ctrl->setParameterByClass(
                        "ilobjrolegui",
                        "rolf_ref_id",
                        $this->request_wrapper->retrieve("rolf_ref_id", $this->refinery->kindlyTo()->string())
                    );
                    $this->ctrl->setParameterByClass(
                        "ilobjrolegui",
                        "obj_id",
                        $this->request_wrapper->retrieve("obj_id", $this->refinery->kindlyTo()->string())
                    );
                    $this->locator->addItem(
                        $this->lng->txt("role"),
                        $this->ctrl->getLinkTargetByClass(array("ilpermissiongui",
                            "ilobjrolegui"), "perm")
                    );
                }
                
                // in workspace this is done in ilPersonalWorkspaceGUI
                if ($this->object_id) {
                    $this->addLocatorItems();
                }
                $this->tpl->setLocator();
                
                break;

            case self::WORKSPACE_NODE_ID:
                // :TODO:
                break;
        }
    }

    /**
     * Display delete confirmation form (repository/workspace switch)
     */
    public function delete() : void
    {
        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
            case self::REPOSITORY_OBJECT_ID:
                parent::deleteObject();

                // no break
            case self::WORKSPACE_NODE_ID:
            case self::WORKSPACE_OBJECT_ID:
                $this->deleteConfirmation();

                // no break
            case self::OBJECT_ID:
            case self::PORTFOLIO_OBJECT_ID:
                // :TODO: should this ever occur?
                break;
        }
    }

    /**
     * Delete objects (repository/workspace switch)
     */
    public function confirmedDelete() : void
    {
        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
            case self::REPOSITORY_OBJECT_ID:
                parent::confirmedDeleteObject();

                // no break
            case self::WORKSPACE_NODE_ID:
            case self::WORKSPACE_OBJECT_ID:
                $this->deleteConfirmedObjects();

                // no break
            case self::OBJECT_ID:
            case self::PORTFOLIO_OBJECT_ID:
                // :TODO: should this ever occur?
                break;
        }
    }

    /**
     * Delete objects (workspace specific)
     * This should probably be moved elsewhere as done with RepUtil
     */
    protected function deleteConfirmedObjects() : void
    {
        if (!$this->post_wrapper->has("id")) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
            return;
        }

        $ids = $this->post_wrapper->retrieve(
            "id",
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );

        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
            return;
        }

        // #18797 - because of parent/child relations gather all nodes first
        $del_nodes = array();
        foreach ($ids as $node_id) {
            $del_nodes[$node_id] = $this->tree->getNodeData($node_id);
        }

        foreach ($del_nodes as $node_id => $node) {
            $this->tree->deleteReference($node_id);
            if ($this->tree->isInTree($node_id)) {
                $this->tree->deleteTree($node);
            }

            $this->getAccessHandler()->removePermission($node_id);

            $object = ilObjectFactory::getInstanceByObjId($node["obj_id"], false);
            if ($object) {
                $object->delete();
            }
        }

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_removed"), true);
        $this->ctrl->redirect($this, "");
    }
    
    public function getHTML() : string
    {
        return parent::getHTML();
    }

    /**
     * Final/Private declaration of unchanged parent methods
     */
    final public function withReferences() : bool
    {
        return parent::withReferences();
    }
    final public function setCreationMode(bool $mode = true) : void
    {
        parent::setCreationMode($mode);
    }
    final public function getCreationMode() : bool
    {
        return parent::getCreationMode();
    }
    final public function prepareOutput(bool $show_sub_objects = true) : bool
    {
        return parent::prepareOutput($show_sub_objects);
    }
    protected function setTitleAndDescription() : void
    {
        parent::setTitleAndDescription();
    }
    final protected function omitLocator(bool $omit = true) : void
    {
        parent::omitLocator($omit);
    }
    final protected function getTargetFrame(string $cmd, string $target_frame = "") : string
    {
        return parent::getTargetFrame($cmd, $target_frame);
    }
    final protected function setTargetFrame(string $cmd, string $target_frame) : void
    {
        parent::setTargetFrame($cmd, $target_frame);
    }
    final public function isVisible(int $ref_id, string $type) : bool
    {
        return parent::isVisible($ref_id, $type);
    }
    final protected function getCenterColumnHTML() : string
    {
        return parent::getCenterColumnHTML();
    }
    final protected function getRightColumnHTML() : string
    {
        return parent::getRightColumnHTML();
    }
    final public function setColumnSettings(ilColumnGUI $column_gui) : void
    {
        parent::setColumnSettings($column_gui);
    }
    final protected function checkPermission(
        string $perm,
        string $cmd = "",
        string $type = "",
        int $ref_id = null
    ) : void {
        parent::checkPermission($perm, $cmd, $type, $ref_id);
    }
    final protected function showPossibleSubObjects() : void
    {
        parent::showPossibleSubObjects();
    }
    final public function cancelDelete() : void
    {
        parent::cancelDeleteObject();
    }
    final protected function redirectToRefId(int $ref_id, string $cmd = "") : void
    {
        parent::redirectToRefId($ref_id, $cmd);
    }
    final protected function fillCloneTemplate(?string $tpl_varname, string $type) : ?ilPropertyFormGUI
    {
        return parent::fillCloneTemplate($tpl_varname, $type);
    }

    //	private function setAdminTabs() { return parent::setAdminTabs(); }
    //	final public function getAdminTabs() { return parent::getAdminTabs(); }
    final protected function addAdminLocatorItems(bool $do_not_add_object = false) : void
    {
        parent::addAdminLocatorItems($do_not_add_object);
    }

    /**
     * view object content (repository/workspace switch)
     */
    public function view() : void
    {
        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
            case self::REPOSITORY_OBJECT_ID:
                parent::viewObject();

                // no break
            case self::WORKSPACE_NODE_ID:
            case self::WORKSPACE_OBJECT_ID:
                $this->render();

                // no break
            case self::OBJECT_ID:
            case self::PORTFOLIO_OBJECT_ID:
                // :TODO: should this ever occur?  do nothing or edit() ?!
                break;
        }
    }

    /**
     * create tabs (repository/workspace switch)
     *
     * this had to be moved here because of the context-specific permission tab
     */
    protected function setTabs() : void
    {
        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
            case self::REPOSITORY_OBJECT_ID:
                if ($this->checkPermissionBool("edit_permission")) {
                    $this->tabs_gui->addTab(
                        "id_permissions",
                        $this->lng->txt("perm_settings"),
                        $this->ctrl->getLinkTargetByClass(array(get_class($this), "ilpermissiongui"), "perm")
                    );
                }
                break;

            case self::WORKSPACE_NODE_ID:
            case self::WORKSPACE_OBJECT_ID:
                // only files and blogs can be shared for now
                if (
                    $this->checkPermissionBool("edit_permission") &&
                    in_array($this->type, array("file", "blog")) &&
                    $this->node_id
                ) {
                    $this->tabs_gui->addTab(
                        "id_permissions",
                        $this->lng->txt("wsp_permissions"),
                        $this->ctrl->getLinkTargetByClass(array(get_class($this), "ilworkspaceaccessgui"), "share")
                    );
                }
                break;
        }
    }
    
    /**
     * Deprecated functions
     */
    //	private function setSubObjects($a_sub_objects = "") { die("ilObject2GUI::setSubObjects() is deprecated."); }
    //	final public function getFormAction($a_cmd, $a_formaction = "") { die("ilObject2GUI::getFormAction() is deprecated."); }
    //	final protected  function setFormAction($a_cmd, $a_formaction) { die("ilObject2GUI::setFormAction() is deprecated."); }
    final protected function getReturnLocation(string $cmd, string $location = "") : string
    {
        die("ilObject2GUI::getReturnLocation() is deprecated.");
    }
    final protected function setReturnLocation(string $cmd, string $location) : void
    {
        die("ilObject2GUI::setReturnLocation() is deprecated.");
    }
    final protected function showActions() : void
    {
        die("ilObject2GUI::showActions() is deprecated.");
    }
    final protected function getTabs() : void
    {
        die("ilObject2GUI::getTabs() is deprecated.");
    }

    /**
     * Functions to be overwritten
     */
    protected function addLocatorItems() : void
    {
    }
    
    /**
     * Functions that must be overwritten
     */
    abstract public function getType() : string;

    /**
     * CRUD
     */
    public function create() : void
    {
        parent::createObject();
    }
    public function save() : void
    {
        parent::saveObject();
    }
    public function edit() : void
    {
        parent::editObject();
    }
    public function update() : void
    {
        parent::updateObject();
    }
    public function cancel() : void
    {
        parent::cancelObject();
    }

    /**
     * Init creation forms.
     * This will create the default creation forms: new, import, clone
     * @return \ilPropertyFormGUI[]
     */
    protected function initCreationForms(string $new_type) : array
    {
        $forms = parent::initCreationForms($new_type);
        
        // cloning doesn't work in workspace yet
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            unset($forms[self::CFORM_CLONE]);
        }

        return $forms;
    }
    
    public function importFile() : void
    {
        parent::importFileObject($this->parent_id);
    }

    /**
     * Add object to tree at given position
     */
    public function putObjectInTree(ilObject $obj, int $parent_node_id = null) : void
    {
        $this->object_id = $obj->getId();

        if (!$parent_node_id) {
            $parent_node_id = $this->parent_id;
        }
    
        // add new object to custom parent container
        if ($this->retriever->has('crtptrefid')) {
            $parent_node_id = $this->retriever->getMaybeInt('crtptrefid') ?? 0;
        }

        switch ($this->id_type) {
            case self::REPOSITORY_NODE_ID:
            case self::REPOSITORY_OBJECT_ID:
                if (!$this->node_id) {
                    $obj->createReference();
                    $this->node_id = $obj->getRefId();
                }
                $obj->putInTree($parent_node_id);
                $obj->setPermissions($parent_node_id);

                // rbac log
                $rbac_log_roles = $this->rbac_review->getParentRoleIds($this->node_id, false);
                $rbac_log = ilRbacLog::gatherFaPa($this->node_id, array_keys($rbac_log_roles), true);
                ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $this->node_id, $rbac_log);

                $this->ctrl->setParameter($this, "ref_id", $this->node_id);
                break;

            case self::WORKSPACE_NODE_ID:
            case self::WORKSPACE_OBJECT_ID:
                if (!$this->node_id) {
                    $this->node_id = $this->tree->insertObject($parent_node_id, $this->object_id);
                }
                $this->getAccessHandler()->setPermissions($parent_node_id, $this->node_id);

                $this->ctrl->setParameter($this, "wsp_id", $this->node_id);
                break;

            case self::OBJECT_ID:
            case self::PORTFOLIO_OBJECT_ID:
                // do nothing
                break;
        }
        
        // BEGIN ChangeEvent: Record save object.
        ilChangeEvent::_recordWriteEvent($this->object_id, $this->user->getId(), 'create');
        // END ChangeEvent: Record save object.
        
        // use forced callback after object creation
        self::handleAfterSaveCallback($obj, $this->retriever->getMaybeInt('crtcb'));
    }
    
    /**
     * After creation callback
     */
    public static function handleAfterSaveCallback(ilObject $obj, ?int $callback_ref_id)
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];

        $callback_ref_id = (int) $callback_ref_id;
        if ($callback_ref_id) {
            $callback_type = ilObject::_lookupType($callback_ref_id, true);
            $class_name = "ilObj" . $objDefinition->getClassName($callback_type) . "GUI";
            if (strtolower($class_name) == "ilobjitemgroupgui") {
                $callback_obj = new $class_name($callback_ref_id);
            } else {
                $callback_obj = new $class_name(null, $callback_ref_id, true, false);
            }
            $callback_obj->afterSaveCallback($obj);
        }
    }

    protected function checkPermissionBool(
        string $perm,
        string $cmd = "",
        string $type = "",
        ?int $node_id = null
    ) : bool {
        if ($perm == "create") {
            if (!$node_id) {
                $node_id = $this->parent_id;
            }
            if ($node_id) {
                return $this->getAccessHandler()->checkAccess($perm . "_" . $type, $cmd, $node_id);
            }
        } else {
            if (!$node_id) {
                $node_id = $this->node_id;
            }
            if ($node_id) {
                return $this->getAccessHandler()->checkAccess($perm, $cmd, $node_id);
            }
        }
        
        // if we do not have a node id, check if current user is owner
        if ($this->obj_id && $this->object->getOwner() == $this->user->getId()) {
            return true;
        }
        return false;
    }
    
    /**
     * Add header action menu
     */
    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null) : ?ilObjectListGUI
    {
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            if (!$this->creation_mode && $this->object_id) {
                $dispatcher = new ilCommonActionDispatcherGUI(
                    ilCommonActionDispatcherGUI::TYPE_WORKSPACE,
                    $this->getAccessHandler(),
                    $this->getType(),
                    $this->node_id,
                    $this->object_id
                );
                
                $dispatcher->setSubObject($sub_type, $sub_id);
                
                ilObjectListGUI::prepareJsLinks(
                    $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
                    "",
                    $this->ctrl->getLinkTargetByClass(["ilcommonactiondispatchergui", "iltagginggui"], "")
                );
                
                $lg = $dispatcher->initHeaderAction();
                
                if (is_object($lg)) {
                    // to enable add to desktop / remove from desktop
                    if ($this instanceof ilDesktopItemHandling) {
                        $lg->setContainerObject($this);
                    }

                    // for activation checks see ilObjectGUI
                    // $lg->enableComments(true);
                    $lg->enableNotes(true);
                    // $lg->enableTags(true);
                }

                return $lg;
            }
        }

        return parent::initHeaderAction();
    }
    
    /**
     * Updating icons after ajax call
     */
    protected function redrawHeaderAction() : void
    {
        parent::redrawHeaderActionObject();
    }
    
    protected function getPermanentLinkWidget(string $append = null, bool $center = false) : string
    {
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $append .= "_wsp";
        }
        
        $plink = new ilPermanentLinkGUI($this->getType(), $this->node_id, $append);
        $plink->setIncludePermanentLinkText(false);
        return $plink->getHTML();
    }

    protected function handleAutoRating(ilObject $new_obj) : void
    {
        // only needed in repository
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            parent::handleAutoRating($new_obj);
        }
    }
}
