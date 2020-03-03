<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeTreeExplorerGUI.php");
require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
require_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncContainerSelectionExplorer.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");
require_once('./Services/Container/classes/class.ilContainerSorting.php');
require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncNotifications.php");
require_once("./Modules/CourseReference/classes/class.ilObjCourseReference.php");

/**
 * Class ilObjStudyProgrammeTreeGUI
 * Generates the manage view for ilTrainingProgramme-Repository objects. Handles all the async requests.
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjStudyProgrammeTreeGUI
{
    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var ilTemplate
     */
    public $tpl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjStudyProgramme
     */
    public $object;
    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var Ilias
     */
    public $ilias;

    /**
     * @var ilLng
     */
    public $lng;

    /**
     * Ref-ID of the object
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilObjStudyProgrammeTreeExplorerGUI
     */
    protected $tree;

    /**
     * CSS-ID of the modal windows
     * @var string
     */
    protected $modal_id;

    /**
     * @var ilAsyncOutputHandler
     */
    protected $async_output_handler;

    /*
     * @var ilToolbar
     */
    public $toolbar;

    public function __construct($a_ref_id)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLocator = $DIC['ilLocator'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilias = $DIC['ilias'];
        $ilSetting = $DIC['ilSetting'];

        $this->ref_id = $a_ref_id;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->locator = $ilLocator;
        $this->tree = $tree;
        $this->toolbar = $ilToolbar;
        $this->log = $ilLog;
        $this->ilias = $ilias;
        $this->lng = $lng;
        $this->ilSetting = $ilSetting;
        $this->modal_id = "tree_modal";
        $this->async_output_handler = new ilAsyncOutputHandler();

        $this->initTree();

        $lng->loadLanguageModule("prg");
    }


    /**
     * Initialize Tree
     * Creates tree instance and set tree configuration
     */
    protected function initTree()
    {
        $this->tree = new ilObjStudyProgrammeTreeExplorerGUI($this->ref_id, $this->modal_id, "prg_tree", $this, 'view');

        $js_url = rawurldecode($this->ctrl->getLinkTarget($this, 'saveTreeOrder', '', true, false));
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
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        $this->getToolbar();

        if ($cmd == "") {
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
     *
     * @return string
     */
    protected function view()
    {
        $output = $this->tree->getHTML();
        $output .= $this->initAsyncUIElements();

        return $output;
    }


    /**
     * Cancel operation
     *
     * @return string
     */
    protected function cancel()
    {
        return ilAsyncOutputHandler::encodeAsyncResponse();
    }


    /**
     * Saves tree node order
     * Data is json encoded from the jstree component
     *
     * @return string json string
     * @throws ilException
     */
    protected function saveTreeOrder()
    {
        $this->checkAccessOrFail('write');

        if (!isset($_POST['tree']) || is_null(json_decode(stripslashes($_POST['tree'])))) {
            throw new ilStudyProgrammeTreeException("There is no tree data to save!");
        }

        // saves order recursive
        $data = json_decode(stripslashes($_POST['tree']));
        $this->storeTreeOrder($data);

        return ilAsyncOutputHandler::encodeAsyncResponse(array('success'=>true, 'message'=>$this->lng->txt('prg_saved_order_successful')));
    }


    /**
     * Recursive function for saving the tree order
     *
     * @param string[]						$nodes_ref_ids
     * @param ilContainerSorting|null       $container_sorting
     * @param int|null                      $parent_ref_id
     */
    protected function storeTreeOrder(array $nodes_ref_ids, $container_sorting = null, int $parent_ref_id = null)
    {
        $sorting_position = array();
        $position_count = 10;

        $parent_node = ($parent_ref_id === null)? ilObjectFactoryWrapper::singleton()->getInstanceByRefId($this->ref_id) : ilObjectFactoryWrapper::singleton()->getInstanceByRefId($parent_ref_id);
        $container_sorting = ($container_sorting === null) ? ilContainerSorting::_getInstance(ilObject::_lookupObjectId($this->ref_id)) : $container_sorting;

        foreach ($nodes_ref_ids as $node_ref) {
            // get ref_id from json
            $id = substr($node_ref, strrpos($node_ref, "_")+1);

            $sorting_position[$id] = $position_count;
            $position_count+= 10;

            $node_obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($id);
            if ($node_obj instanceof ilObjStudyProgramme) {
                $node_obj->moveTo($parent_node);
            } else {
                // TODO: implement a method on ilObjStudyProgramme to move leafs
                global $DIC;
                $tree = $DIC['tree'];
                $rbacadmin = $DIC['rbacadmin'];

                $tree->moveTree($node_obj->getRefId(), $parent_node->getRefId());
                $rbacadmin->adjustMovedObjectPermissions($node_obj->getRefId(), $parent_node->getRefId());
            }

            // recursion if there are children
            if (isset($node->children)) {
                $this->storeTreeOrder($node->children, ilContainerSorting::_getInstance(ilObject::_lookupObjectId($id)), $id);
            }
        }
        $container_sorting->savePost($sorting_position);
    }


    /**
     * Creates a new leaf
     * Currently only course references can be created
     *
     * @return string
     * @throws ilException
     */
    protected function createNewLeaf()
    {
        $this->checkAccessOrFail('create', (int) $_POST['parent_id']);

        if (isset($_POST['target_id'], $_POST['type'], $_POST['parent_id'])) {
            $target_id = (int) $_POST['target_id'];
            $parent_id = (int) $_POST['parent_id'];

            // TODO: more generic way for implementing different type of leafs
            $course_ref = new ilObjCourseReference();
            $course_ref->setTitleType(ilObjCourseReference::TITLE_TYPE_REUSE);
            $course_ref->setTargetRefId($target_id);

            $course_ref->create();
            $course_ref->createReference();

            $course_ref->putInTree($parent_id);

            // This is how its done in ILIAS. If you set the target ID before the creation, it won't work
            $course_ref->setTargetId(ilObject::_lookupObjectId($target_id));
            $course_ref->update();
        }

        return ilAsyncOutputHandler::encodeAsyncResponse(array('success'=>true, 'message'=>$this->lng->txt('prg_added_course_ref_successful')));
    }


    /**
     * Initialize the Course Explorer for creating a leaf
     *
     * @param bool $convert_to_string If set to true, the getOutput function is already called
     *
     * @return ilAsyncContainerSelectionExplorer|string
     */
    protected function getContainerSelectionExplorer($convert_to_string = true)
    {
        $create_leaf_form = new ilAsyncContainerSelectionExplorer(rawurldecode($this->ctrl->getLinkTarget($this, 'createNewLeaf', '', true, false)));
        $create_leaf_form->setId("select_course_explorer");

        $ref_expand = ROOT_FOLDER_ID;
        if (isset($_GET['ref_repexpand'])) {
            $ref_expand = (int) $_GET['ref_repexpand'];
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
        } else {
            return $create_leaf_form;
        }
    }


    /**
     * Returns the async creation form for StudyProgrammes
     *
     * @return ilAsyncPropertyFormGUI
     */
    protected function getCreationForm()
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
    protected function create()
    {
        $parent_id = (isset($_GET['ref_id']))? (int) $_GET['ref_id'] : null;
        $this->checkAccessOrFail('create', $parent_id);

        $parent = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($parent_id);
        $accordion = new ilAccordionGUI();

        $added_slides = 0;
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
                $content_new_leaf = $this->tpl->getMessageHTML($this->lng->txt('prg_please_select_a_course_for_creating_a_leaf'));
                $content_new_leaf .= $this->getContainerSelectionExplorer();

                $accordion->addItem($this->lng->txt('prg_create_new_leaf'), $content_new_leaf);
                $added_slides++;
            }

            if ($added_slides == 1) {
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
     * Show the delete confirmation dialog for objects in the tree
     *
     * @throws ilException
     */
    protected function delete()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];

        $this->checkAccessOrFail("delete");

        if (!isset($_GET['ref_id'], $_GET['item_ref_id'])) {
            throw new ilException("Nothing to delete!");
        }

        $element_ref_id = (int) $_GET['ref_id'];

        $cgui = new ilConfirmationGUI();

        $msg = $this->lng->txt("info_delete_sure");

        if (!$ilSetting->get('enable_trash')) {
            $msg .= "<br/>" . $this->lng->txt("info_delete_warning_no_trash");
        }
        $cgui->setFormAction($this->ctrl->getFormAction($this, 'confirmedDelete', '', true));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");
        $cgui->setFormName('async_form');

        $obj_id = ilObject::_lookupObjectId($element_ref_id);
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
        $cgui->addHiddenItem('item_ref_id', $_GET['item_ref_id']);

        $content = $cgui->getHTML();

        // creating the modal window output
        $this->async_output_handler->setHeading($msg);
        $this->async_output_handler->setContent($content);
        $this->async_output_handler->terminate();
    }


    /**
     * Deletes a node or a leaf in the tree
     *
     * @return string
     * @throws ilException
     */
    protected function confirmedDelete()
    {
        $this->checkAccessOrFail("delete");

        if (!isset($_POST['id'], $_POST['item_ref_id']) && is_array($_POST['id'])) {
            throw new ilException("No item select for deletion!");
        }

        $ids = $_POST['id'];
        $current_node = (int) $_POST['item_ref_id'];
        $result = true;

        foreach ($ids as $id) {
            $obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($id);

            $not_parent_of_current = true;
            $not_root = true;

            // do some additional validation if it is a StudyProgramme
            if ($obj instanceof ilObjStudyProgramme) {

                //check if you are not deleting a parent element of the current element
                $children_of_node = ilObjStudyProgramme::getAllChildren($obj->getRefId());
                $get_ref_ids = function ($obj) {
                    return $obj->getRefId();
                };

                $children_ref_ids = array_map($get_ref_ids, $children_of_node);
                $not_parent_of_current = (!in_array($current_node, $children_ref_ids));

                $not_root = ($obj->getRoot() != null);
            }

            if ($current_node != $id && $not_root && $not_parent_of_current && $this->checkAccess('delete', $obj->getRefId())) {
                ilRepUtil::deleteObjects(null, $id);

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

        return ilAsyncOutputHandler::encodeAsyncResponse(array('success'=>$result, 'message'=>$msg));
    }


    /**
     * Cancel deletion
     * Return a json string for the async handling
     *
     * @return string
     */
    protected function cancelDelete()
    {
        return ilAsyncOutputHandler::encodeAsyncResponse();
    }


    /**
     * Initializes all elements used for async-interaction
     * Adds HTML-skeleton for the bootstrap modal dialog, the notification mechanism and the Selection container
     *
     * @return string
     */
    protected function initAsyncUIElements()
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

        $content =  $settings_modal->getHTML();

        // init js notifications
        $notifications = new ilAsyncNotifications();
        $notifications->addJsConfig('events', array('success'=>array('study_programme-show_success')));
        $notifications->initJs();

        // init tree selection explorer
        $async_explorer = new ilAsyncContainerSelectionExplorer(rawurldecode($this->ctrl->getLinkTarget($this, 'createNewLeaf', '', true, false)));
        $async_explorer->initJs();

        return $content;
    }


    /**
     * Setup the toolbar
     */
    protected function getToolbar()
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
     *
     * @param string $permission
     * @param null $ref_id
     *
     * @return bool
     */
    protected function checkAccess($permission, $ref_id = null)
    {
        $ref_id = ($ref_id === null)? $this->ref_id : $ref_id;
        $checker = $this->access->checkAccess($permission, '', $ref_id);

        return $checker;
    }


    /**
     * Checks permission of a object and throws an exception if they are not granted
     *
     * @param string $permission
     * @param null $ref_id
     *
     * @throws ilException
     */
    protected function checkAccessOrFail($permission, $ref_id = null)
    {
        if (!$this->checkAccess($permission, $ref_id)) {
            throw new ilException("You have no permission for " . $permission . " Object with ref_id " . $ref_id . "!");
        }
    }
}
