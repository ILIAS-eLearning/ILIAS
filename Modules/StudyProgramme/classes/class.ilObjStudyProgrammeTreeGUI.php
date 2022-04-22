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
 * Class ilObjStudyProgrammeTreeGUI
 * Generates the manage view for ilTrainingProgramme-Repository objects. Handles all the async requests.
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjStudyProgrammeTreeGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilComponentLogger $log;
    protected Ilias $ilias;
    protected ilSetting $ilSetting;
    protected ilTree $ilTree;
    protected ilRbacAdmin $rbacadmin;
    protected ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    /**
     * CSS-ID of the modal windows
     */
    protected string $modal_id;
    protected ilAsyncOutputHandler $async_output_handler;

    /**
     * Ref-ID of the object
     */
    protected int $ref_id;
    protected ilObjStudyProgrammeTreeExplorerGUI $tree;
    protected ilObjStudyProgramme $object;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccess $ilAccess,
        ilToolbarGUI $ilToolbar,
        ilLanguage $lng,
        ilComponentLogger $ilLog,
        ILIAS $ilias,
        ilSetting $ilSetting,
        ilTree $ilTree,
        ilRbacAdmin $rbacadmin,
        ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->toolbar = $ilToolbar;
        $this->log = $ilLog;
        $this->ilias = $ilias;
        $this->lng = $lng;
        $this->ilSetting = $ilSetting;
        $this->ilTree = $ilTree;
        $this->rbacadmin = $rbacadmin;
        $this->http_wrapper = $http_wrapper;
        $this->refinery = $refinery;

        $this->modal_id = "tree_modal";
        $this->async_output_handler = new ilAsyncOutputHandler();

        $lng->loadLanguageModule("prg");
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
    }

    /**
     * Initialize Tree
     * Creates tree instance and set tree configuration
     */
    protected function initTree() : void
    {
        $this->tree = new ilObjStudyProgrammeTreeExplorerGUI($this->ref_id, $this->modal_id, "prg_tree", $this, 'view');

        $js_url = rawurldecode($this->ctrl->getLinkTarget($this, 'saveTreeOrder', '', true));
        $this->tree->addJsConf('save_tree_url', $js_url);
        $this->tree->addJsConf('save_button_id', 'save_order_button');
        $this->tree->addJsConf('cancel_button_id', 'cancel_order_button');
    }

    /**
     * Execute GUI-commands
     * If there is a async request the response is sent as a json string
     *
     * @throws ilException
     */
    public function executeCommand() : void
    {
        $this->initTree();
        $cmd = $this->ctrl->getCmd();

        $this->getToolbar();

        if ($cmd === "" || $cmd === null) {
            $cmd = "view";
        }

        // handles tree commands ("openNode", "closeNode", "getNodeAsync")
        if ($this->tree->handleCommand()) {
            exit();
        }

        switch ($cmd) {
            case "view":
            case "create":
            case "save":
            case "cancel":
            case "delete":
            case "confirmedDelete":
            case "cancelDelete":
            case "getContainerSelectionExplorer":
            case "saveTreeOrder":
            case "createNewLeaf":

                $content = $this->$cmd();
                break;
            default:
                throw new ilException("ilObjStudyProgrammeTreeGUI: " .
                    "Command not supported: $cmd");
        }

        ilAsyncOutputHandler::handleAsyncOutput($content);
    }

    /**
     * Display the tree view
     */
    protected function view() : string
    {
        $output = $this->tree->getHTML();
        $output .= $this->initAsyncUIElements();

        return $output;
    }

    /**
     * Cancel operation
     */
    protected function cancel() : string
    {
        return ilAsyncOutputHandler::encodeAsyncResponse();
    }

    /**
     * Saves tree node order
     * Data is json encoded from the jstree component
     *
     * @throws ilException
     */
    protected function saveTreeOrder() : string
    {
        $this->checkAccessOrFail('write');

        $tree = "";
        if ($this->http_wrapper->post()->has("tree")) {
            $tree = $this->http_wrapper->post()->retrieve("tree", $this->refinery->kindlyTo()->string());
        }
        $treeAsJson = ilUtil::stripSlashes($tree);
        $treeData = json_decode($treeAsJson, false, 512, JSON_THROW_ON_ERROR);

        if (!is_array($treeData) || [] === $treeData) {
            throw new ilStudyProgrammeTreeException("There is no tree data to save!");
        }

        // saves order recursive
        $this->storeTreeOrder($treeData);

        return ilAsyncOutputHandler::encodeAsyncResponse(
            ['success' => true, 'message' => $this->lng->txt('prg_saved_order_successful')]
        );
    }

    /**
     * Recursive function for saving the tree order
     */
    protected function storeTreeOrder(
        array $nodes,
        ilContainerSorting $container_sorting = null,
        int $parent_ref_id = null
    ) : void {
        $sorting_position = array();
        $position_count = 10;

        $ref_id = $parent_ref_id;
        if (is_null($ref_id)) {
            $ref_id = $this->ref_id;
        }

        /** @var ilObjStudyProgramme $parent_node */
        $parent_node = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($ref_id);

        if (is_null($container_sorting)) {
            $container_sorting = ilContainerSorting::_getInstance(ilObject::_lookupObjectId($this->ref_id));
        }

        foreach ($nodes as $node) {
            // get ref_id from json
            $id = $node->id;
            $id = substr($id, strrpos($id, "_") + 1);

            $sorting_position[$id] = $position_count;
            $position_count += 10;

            $node_obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId((int) $id);
            if ($node_obj instanceof ilObjStudyProgramme) {
                $node_obj->moveTo($parent_node);
            } else {
                // TODO: implement a method on ilObjStudyProgramme to move leafs
                $this->ilTree->moveTree($node_obj->getRefId(), $parent_node->getRefId());
                $this->rbacadmin->adjustMovedObjectPermissions($node_obj->getRefId(), $parent_node->getRefId());
            }

            // recursion if there are children
            if (isset($node->children)) {
                $this->storeTreeOrder(
                    $node->children,
                    ilContainerSorting::_getInstance(ilObject::_lookupObjectId((int) $id)),
                    (int) $id
                );
            }
        }
        $container_sorting->savePost($sorting_position);
    }

    /**
     * Creates a new leaf
     * Currently only course references can be created
     *
     * @throws ilException
     */
    protected function createNewLeaf() : string
    {
        $this->checkAccessOrFail('create', $this->http_wrapper->post()->retrieve("parent_id", $this->refinery->kindlyTo()->int()));

        if (
            $this->http_wrapper->post()->has("target_id") &&
            $this->http_wrapper->post()->has("type") &&
            $this->http_wrapper->post()->has("parent_id")
        ) {
            $target_id = $this->http_wrapper->post()->retrieve("target_id", $this->refinery->kindlyTo()->int());
            $parent_id = $this->http_wrapper->post()->retrieve("parent_id", $this->refinery->kindlyTo()->int());

            // TODO: more generic way for implementing different type of leafs
            $course_ref = new ilObjCourseReference();
            $course_ref->setTitleType(ilContainerReference::TITLE_TYPE_REUSE);
            $course_ref->setTargetRefId($target_id);

            $course_ref->create();
            $course_ref->createReference();

            $course_ref->putInTree($parent_id);

            // This is how it's done in ILIAS. If you set the target ID before the creation, it won't work
            $course_ref->setTargetId(ilObject::_lookupObjectId($target_id));
            $course_ref->update();
        }

        return ilAsyncOutputHandler::encodeAsyncResponse(
            ['success' => true, 'message' => $this->lng->txt('prg_added_course_ref_successful')]
        );
    }


    /**
     * Initialize the Course Explorer for creating a leaf
     *
     * @param bool $convert_to_string If set to true, the getOutput function is already called
     * @return ilAsyncContainerSelectionExplorer|string
     */
    protected function getContainerSelectionExplorer(bool $convert_to_string = true)
    {
        $create_leaf_form = new ilAsyncContainerSelectionExplorer(
            rawurldecode($this->ctrl->getLinkTarget($this, 'createNewLeaf', '', true)),
            $this->refinery,
            $this->http_wrapper->query()
        );
        $create_leaf_form->setId("select_course_explorer");

        $ref_expand = ROOT_FOLDER_ID;
        if ($this->http_wrapper->post()->has("ref_repexpand")) {
            $ref_expand = $this->http_wrapper->query()->retrieve("ref_repexpand", $this->refinery->kindlyTo()->int());
        }

        $create_leaf_form->setExpand($ref_expand);
        $create_leaf_form->setExpandTarget($this->ctrl->getLinkTarget($this, 'getContainerSelectionExplorer'));
        $create_leaf_form->setAsynchExpanding(true);
        $create_leaf_form->setTargetGet('target_id');
        $create_leaf_form->setFrameTarget("_self");
        $create_leaf_form->setClickable('crs', true);
        $create_leaf_form->setTargetType('crs');
        $create_leaf_form->setOutput(0);

        if ($convert_to_string) {
            return $create_leaf_form->getOutput();
        }

        return $create_leaf_form;
    }

    /**
     * Returns the async creation form for StudyProgrammes
     */
    protected function getCreationForm() : ilAsyncPropertyFormGUI
    {
        $tmp_obj = new ilObjStudyProgrammeGUI();

        $create_node_form = $tmp_obj->getAsyncCreationForm();
        $create_node_form->setTitle("");
        $this->ctrl->setParameterByClass("ilobjstudyprogrammegui", "new_type", "prg");
        $create_node_form->setFormAction($this->ctrl->getFormActionByClass("ilobjstudyprogrammegui", "save"));

        return $create_node_form;
    }

    /**
     * Generates the modal window content for the creation form of nodes or leafs
     * If there are already StudyProgramme-nodes in the parent, leaf creation is disabled and if there are already leafs, nodes can't be created
     *
     * @throws ilException
     */
    protected function create() : void
    {
        $parent_id = null;
        if ($this->http_wrapper->query()->has("ref_id")) {
            $this->http_wrapper->query()->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        }
        $this->checkAccessOrFail('create', $parent_id);

        $parent = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($parent_id);// TODO PHP8-REVIEW `$parent_id` is NULL, an `int` is expected
        $accordion = new ilAccordionGUI();

        $added_slides = 0;
        $content = "";
        if ($parent instanceof ilObjStudyProgramme) {
            // only allow adding new StudyProgramme-Node if there are no lp-children
            if (!$parent->hasLPChildren()) {
                $content_new_node = $this->getCreationForm()->getHTML();
                $accordion->addItem($this->lng->txt('prg_create_new_node'), $content_new_node);
                $added_slides++;
            }

            /* only allow adding new LP-Children if there are no other StudyProgrammes
             * AND creating crs references is activated in administration
             */
            if (!$parent->hasChildren() && $this->ilSetting->get("obj_dis_creation_crsr") === "") {
                $content_new_leaf = ilUtil::getSystemMessageHTML(
                    $this->lng->txt('prg_please_select_a_course_for_creating_a_leaf')
                );
                $content_new_leaf .= $this->getContainerSelectionExplorer();

                $accordion->addItem($this->lng->txt('prg_create_new_leaf'), $content_new_leaf);
                $added_slides++;
            }

            if ($added_slides === 1) {
                $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);
            }

            $content = $accordion->getHTML();
        }

        // creating modal window output
        $this->async_output_handler->setHeading($this->lng->txt("prg_async_" . $this->ctrl->getCmd()));
        $this->async_output_handler->setContent($content);
        $this->async_output_handler->terminate();
    }

    /**
     * Show to delete confirmation dialog for objects in the tree
     *
     * @throws ilException
     */
    protected function delete() : void
    {
        $this->checkAccessOrFail("delete");

        if (!$this->http_wrapper->query()->has("ref_id") || !$this->http_wrapper->query()->has("item_ref_id")) {
            throw new ilException("Nothing to delete!");
        }

        $element_ref_id = $this->http_wrapper->query()->retrieve("ref_id", $this->refinery->kindlyTo()->int());

        $cgui = new ilConfirmationGUI();

        $msg = $this->lng->txt("info_delete_sure");

        if (!$this->ilSetting->get('enable_trash')) {
            $msg .= "<br/>" . $this->lng->txt("info_delete_warning_no_trash");
        }
        $cgui->setFormAction($this->ctrl->getFormAction($this, 'confirmedDelete', '', true));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");
        $cgui->setFormName('async_form');

        $obj_id = ilObject::_lookupObjectId((int) $element_ref_id);
        $type = ilObject::_lookupType($obj_id);
        $title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);
        $alt = $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $type);

        $cgui->addItem(
            "id[]",
            $element_ref_id,
            $title,
            ilObject::_getIcon($obj_id, "small", $type),
            $alt
        );
        $cgui->addHiddenItem(
            'item_ref_id',
            $this->http_wrapper->query()->retrieve("item_ref_id", $this->refinery->kindlyTo()->int())
        );

        $content = $cgui->getHTML();

        // creating the modal window output
        $this->async_output_handler->setHeading($msg);
        $this->async_output_handler->setContent($content);
        $this->async_output_handler->terminate();
    }

    /**
     * Deletes a node or a leaf in the tree
     *
     * @throws ilException
     */
    protected function confirmedDelete() : string
    {
        $this->checkAccessOrFail("delete");

        if (
            (!$this->http_wrapper->post()->has("id") || !$this->http_wrapper->post()->has("item_ref_id")) &&
            is_array($this->http_wrapper->post()->retrieve(
                "id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            ))
        ) {
            throw new ilException("No item select for deletion!");
        }

        $ids = $this->http_wrapper->post()->retrieve(
            "id",
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );
        $current_node = $this->http_wrapper->post()->retrieve("item_ref_id", $this->refinery->kindlyTo()->int());
        $result = true;
        $msg = '';

        foreach ($ids as $id) {
            $obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($id);

            $not_parent_of_current = true;
            $not_root = true;

            // do some additional validation if it is a StudyProgramme
            if ($obj instanceof ilObjStudyProgramme) {

                //check if you are not deleting a parent element of the current element
                $children_of_node = ilObjStudyProgramme::getAllChildren($obj->getRefId());
                $get_ref_ids = static function (ilObjStudyProgramme $obj) : int {
                    return $obj->getRefId();
                };

                $children_ref_ids = array_map($get_ref_ids, $children_of_node);
                $not_parent_of_current = (!in_array($current_node, $children_ref_ids));

                $not_root = ($obj->getRoot() != null);// TODO PHP8-REVIEW This is always true
            }

            if (
                $current_node != $id &&
                $not_root &&
                $not_parent_of_current &&
                $this->checkAccess('delete', $obj->getRefId())
            ) {
                ilRepUtil::deleteObjects(0, $id);

                // deletes the tree-open-node-session storage
                if (isset($children_of_node)) {
                    $this->tree->closeCertainNode($id);
                    foreach ($children_of_node as $child) {
                        $this->tree->closeCertainNode($child->getRefId());
                    }
                }

                $msg = $this->lng->txt("prg_deleted_safely");
            } else {
                $msg = $this->lng->txt("prg_not_allowed_node_to_delete");
                $result = false;
            }
        }

        return ilAsyncOutputHandler::encodeAsyncResponse(array('success' => $result, 'message' => $msg));
    }

    /**
     * Cancel deletion
     * Return a json string for the async handling
     */
    protected function cancelDelete() : string
    {
        return ilAsyncOutputHandler::encodeAsyncResponse();
    }

    /**
     * Initializes all elements used for async-interaction
     * Adds HTML-skeleton for the bootstrap modal dialog, the notification mechanism and the Selection container
     */
    protected function initAsyncUIElements() : string
    {
        // add  js files
        ilAccordionGUI::addJavaScript();
        ilAsyncPropertyFormGUI::addJavaScript(true);
        ilAsyncContainerSelectionExplorer::addJavascript();

        // add bootstrap modal
        $settings_modal = ilModalGUI::getInstance();
        $settings_modal->setId($this->modal_id);
        $settings_modal->setType(ilModalGUI::TYPE_LARGE);
        $this->tpl->addOnLoadCode('$("#' . $this->modal_id . '").study_programme_modal();');

        $content = $settings_modal->getHTML();

        // init js notifications
        $notifications = new ilAsyncNotifications();
        $notifications->addJsConfig('events', array('success' => array('study_programme-show_success')));
        $notifications->initJs();

        // init tree selection explorer
        $async_explorer = new ilAsyncContainerSelectionExplorer(
            rawurldecode($this->ctrl->getLinkTarget($this, 'createNewLeaf', '', true)),
            $this->refinery,
            $this->http_wrapper->query()
        );
        $async_explorer->initJs();

        return $content;
    }

    /**
     * Setup the toolbar
     */
    protected function getToolbar() : void
    {
        $save_order_btn = ilLinkButton::getInstance();
        $save_order_btn->setId('save_order_button');
        $save_order_btn->setUrl("javascript:void(0);");
        $save_order_btn->setOnClick("$('body').trigger('study_programme-save_order');");
        $save_order_btn->setCaption('prg_save_tree_order');

        $cancel_order_btn = ilLinkButton::getInstance();
        $cancel_order_btn->setId('cancel_order_button');
        $cancel_order_btn->setUrl("javascript:void(0);");
        $cancel_order_btn->setOnClick("$('body').trigger('study_programme-cancel_order');");
        $cancel_order_btn->setCaption('prg_cancel_tree_order');

        $this->toolbar->addButtonInstance($save_order_btn);
        $this->toolbar->addButtonInstance($cancel_order_btn);
    }

    /**
     * Checks permission of current tree or certain child of it
     */
    protected function checkAccess(string $permission, int $ref_id = null) : bool
    {
        if (is_null($ref_id)) {
            $ref_id = $this->ref_id;
        }
        return $this->access->checkAccess($permission, '', $ref_id);
    }

    /**
     * Checks permission of a object and throws an exception if they are not granted
     *
     * @throws ilException
     */
    protected function checkAccessOrFail(string $permission, int $ref_id = null) : void
    {
        if (!$this->checkAccess($permission, $ref_id)) {
            throw new ilException("You have no permission for " . $permission . " Object with ref_id " . $ref_id . "!");
        }
    }
}
