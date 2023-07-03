<?php

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

use ILIAS\Object\ImplementsCreationCallback;

/**
 * GUI class for the workflow of copying objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjectCopyGUI:
 *
 * @ingroup ServicesObject
 */
class ilObjectCopyGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    const SOURCE_SELECTION = 1;
    const TARGET_SELECTION = 2;
    const SEARCH_SOURCE = 3;

    const SUBMODE_COMPLETE = 1;
    const SUBMODE_CONTENT_ONLY = 2;

    // tabs
    const TAB_SELECTION_TARGET_TREE = 1;
    const TAB_SELECTION_SOURCE_TREE = 2;
    const TAB_SELECTION_MEMBERSHIP = 3;

    // group selection of source or target
    const TAB_GROUP_SC_SELECTION = 1;


    private $mode = 0;
    private $sub_mode = self::SUBMODE_COMPLETE;

    private $lng;

    private $parent_obj = null;

    private $type = '';

    private $sources = array();

    // begin-patch multi copy
    private $targets = array();
    private $targets_copy_id = array();
    // end-patch multi copy

    /**
     * @var ilLogger
     */
    private $log = null;

    public function __construct(ImplementsCreationCallback $a_parent_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->rbacreview = $DIC->rbac()->review();
        $this->log = $DIC["ilLog"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->lng = $lng;
        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('obj');
        $this->ctrl->saveParameter($this, "crtcb");

        $this->parent_obj = $a_parent_gui;

        $this->log = ilLoggerFactory::getLogger('obj');
    }

    /**
     * Control class handling
     * @return
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $this->init();
        $this->initTabs();

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Init return, mode
     * @return
     */
    protected function init()
    {
        $ilCtrl = $this->ctrl;

        if ((int) $_REQUEST['smode']) {
            $this->setSubMode((int) $_REQUEST['smode']);
            $ilCtrl->setParameter($this, 'smode', $this->getSubMode());
            ilLoggerFactory::getLogger('obj')->debug('Submode is: ' . $this->getSubMode());
        }

        // save sources
        if ($_REQUEST['source_ids']) {
            $this->setSource(explode('_', $_REQUEST['source_ids']));
            $ilCtrl->setParameter($this, 'source_ids', implode('_', $this->getSources()));
            ilLoggerFactory::getLogger('obj')->debug('Multiple sources: ' . implode('_', $this->getSources()));
        }
        if ($_REQUEST['source_id']) {
            $this->setSource(array((int) $_REQUEST['source_id']));
            $ilCtrl->setParameter($this, 'source_ids', implode('_', $this->getSources()));
            ilLoggerFactory::getLogger('obj')->debug('source_id is set: ' . implode('_', $this->getSources()));
        }
        if ($this->getFirstSource()) {
            $this->setType(
                ilObject::_lookupType(ilObject::_lookupObjId($this->getFirstSource()))
            );
        }

        // creation screen: copy section
        if ($_REQUEST['new_type']) {
            $this->setMode(self::SEARCH_SOURCE);
            $this->setType($_REQUEST['new_type']);
            $this->setTarget((int) $_GET['ref_id']);

            $ilCtrl->setParameter($this, 'new_type', $this->getType());
            $ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'new_type', $this->getType());
            $ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'cpfl', 1);
            $ilCtrl->setReturnByClass(get_class($this->getParentObject()), 'create');

            ilLoggerFactory::getLogger('obj')->debug('Copy from object creation for type: ' . $this->getType());
            return true;
        }
        // adopt content, and others?
        elseif ($_REQUEST['selectMode'] == self::SOURCE_SELECTION) {
            $this->setMode(self::SOURCE_SELECTION);

            $ilCtrl->setParameterByClass(get_class($this->parent_obj), 'selectMode', self::SOURCE_SELECTION);
            $this->setTarget((int) $_GET['ref_id']);
            $ilCtrl->setReturnByClass(get_class($this->parent_obj), '');

            ilLoggerFactory::getLogger('obj')->debug('Source selection mode. Target is: ' . $this->getFirstTarget());
        } elseif ($_REQUEST['selectMode'] == self::TARGET_SELECTION) {
            $this->setMode(self::TARGET_SELECTION);
            $ilCtrl->setReturnByClass(get_class($this->parent_obj), '');
            ilLoggerFactory::getLogger('obj')->debug('Target selection mode.');
        }


        // save targets
        if ($_REQUEST['target_ids']) {
            $this->setTargets(explode('_', $_REQUEST['target_ids']));
            ilLoggerFactory::getLogger('obj')->debug('targets are: ' . print_r($this->getTargets(), true));
        }
    }

    /**
     * Init tabs
     * General
     */
    protected function initTabs()
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule('cntr');
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt('tab_back_to_repository'),
            $ilCtrl->getParentReturn($this->parent_obj)
        );
    }

    /**
     * Set tabs
     * @param type $a_tab_group
     * @param type $a_active_tab
     */
    protected function setTabs($a_tab_group, $a_active_tab)
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        if ($a_tab_group == self::TAB_GROUP_SC_SELECTION) {
            if ($this->getSubMode() == self::SUBMODE_CONTENT_ONLY) {
                if ($this->getMode() == self::SOURCE_SELECTION) {
                    $ilTabs->addTab(
                        self::TAB_SELECTION_SOURCE_TREE,
                        $lng->txt('cntr_copy_repo_tree'),
                        $ilCtrl->getLinkTarget($this, 'initSourceSelection')
                    );
                    $ilTabs->addTab(
                        self::TAB_SELECTION_MEMBERSHIP,
                        $lng->txt('cntr_copy_crs_grp'),
                        $ilCtrl->getLinkTarget($this, 'showSourceSelectionMembership')
                    );
                }
            }
        }
        $ilTabs->activateTab($a_active_tab);
    }


    /**
     * Adopt content (crs in crs, grp in grp, crs in grp or grp in crs)
     * @return type
     */
    protected function adoptContent()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, 'smode', self::SUBMODE_CONTENT_ONLY);
        $ilCtrl->setParameter($this, 'selectMode', self::SOURCE_SELECTION);


        $this->setSubMode(self::SUBMODE_CONTENT_ONLY);
        $this->setMode(self::SOURCE_SELECTION);
        $this->setTarget((int) $_GET['ref_id']);


        return $this->initSourceSelection();
    }

    /**
     * Init copy from repository/search list commands
     * @return
     */
    protected function initTargetSelection()
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $ilCtrl->setParameter($this, 'selectMode', self::TARGET_SELECTION);
        // empty session on init
        $_SESSION['paste_copy_repexpand'] = array();

        // copy opened nodes from repository explorer
        $_SESSION['paste_copy_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();

        // begin-patch mc
        $this->setTargets(array());
        // cognos-blu-patch: end

        // open current position

        foreach ($this->getSources() as $source_id) {
            if ($source_id) {
                $path = $tree->getPathId($source_id);
                foreach ((array) $path as $node_id) {
                    if (!in_array($node_id, $_SESSION['paste_copy_repexpand'])) {
                        $_SESSION['paste_copy_repexpand'][] = $node_id;
                    }
                }
            }
        }

        $ilCtrl->setReturnByClass(get_class($this->parent_obj), '');
        $this->showTargetSelectionTree();
    }

    /**
     * Init source selection
     * @return
     */
    protected function initSourceSelection()
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;

        // empty session on init
        $_SESSION['paste_copy_repexpand'] = array();

        // copy opened nodes from repository explorer
        $_SESSION['paste_copy_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();

        $this->setTabs(self::TAB_GROUP_SC_SELECTION, self::TAB_SELECTION_SOURCE_TREE);


        // open current position
        // begin-patch mc
        foreach ($this->getTargets() as $target_ref_id) {
            $path = $tree->getPathId($target_ref_id);
            foreach ((array) $path as $node_id) {
                if (!in_array($node_id, $_SESSION['paste_copy_repexpand'])) {
                    $_SESSION['paste_copy_repexpand'][] = $node_id;
                }
            }
        }
        // end-patch multi copy
        $ilCtrl->setReturnByClass(get_class($this->parent_obj), '');
        $this->showSourceSelectionTree();
    }


    /**
     * show target selection membership
     */
    protected function showSourceSelectionMembership()
    {
        $user = $this->user;
        $tpl = $this->tpl;

        ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard_source'));
        $this->setTabs(self::TAB_GROUP_SC_SELECTION, self::TAB_SELECTION_MEMBERSHIP);

        include_once './Services/Object/classes/class.ilObjectCopyCourseGroupSelectionTableGUI.php';
        $cgs = new ilObjectCopyCourseGroupSelectionTableGUI($this, 'showSourceSelectionMembership', 'copy_selection_membership');
        $cgs->init();
        $cgs->setObjects(
            array_merge(
                ilParticipants::_getMembershipByType($user->getId(), 'crs', false),
                ilParticipants::_getMembershipByType($user->getId(), 'grp', false)
            )
        );
        $cgs->parse();

        $tpl->setContent($cgs->getHTML());
    }


    /**
     * Show target selection
     */
    public function showTargetSelectionTree()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $tpl = $this->tpl;
        $objDefinition = $this->obj_definition;
        $lng = $this->lng;

        $this->tpl = $tpl;

        if ($objDefinition->isContainer($this->getType())) {
            ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard_container'));
        } else {
            ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard'));
        }

        //
        include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
        $exp = new ilRepositorySelectorExplorerGUI($this, "showTargetSelectionTree");
        $exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "fold", "lso", "prg"));
        $exp->setSelectMode("target", true);
        if ($exp->handleCommand()) {
            return;
        }
        $output = $exp->getHTML();

        // toolbars
        $t = new ilToolbarGUI();
        $t->setFormAction($ilCtrl->getFormAction($this, "saveTarget"));
        if ($objDefinition->isContainer($this->getType())) {
            $btn = ilSubmitButton::getInstance();
            $btn->setCaption('btn_next');
            $btn->setCommand('saveTarget');
            $btn->setPrimary(true);
            $t->addButtonInstance($btn);
        } else {
            $btn = ilSubmitButton::getInstance();
            $btn->setCaption('paste');
            $btn->setCommand('saveTarget');
            $btn->setPrimary(true);
            $t->addButtonInstance($btn);
        }
        $t->addSeparator();
        $clipboard_btn = ilSubmitButton::getInstance();
        $clipboard_btn->setCaption('obj_insert_into_clipboard');
        $clipboard_btn->setCommand('keepObjectsInClipboard');
        $t->addButtonInstance($clipboard_btn);
        $cancel_btn = ilSubmitButton::getInstance();
        $cancel_btn->setCaption('cancel');
        $cancel_btn->setCommand('cancel');
        $t->addButtonInstance($cancel_btn);
        $t->setCloseFormTag(false);
        $t->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), " ");
        $output = $t->getHTML() . $output;
        $t->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), " ");
        $t->setCloseFormTag(true);
        $t->setOpenFormTag(false);
        $output .= "<br />" . $t->getHTML();

        $this->tpl->setContent($output);

        return;
    }

    /**
     * Show target selection
     */
    public function showSourceSelectionTree()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $tpl = $this->tpl;
        $objDefinition = $this->obj_definition;

        $this->tpl = $tpl;
        $this->tpl->addBlockfile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.paste_into_multiple_objects.html',
            "Services/Object"
        );

        ilUtil::sendInfo($this->lng->txt('msg_copy_clipboard_source'));
        include_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
        $exp = new ilPasteIntoMultipleItemsExplorer(
            ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
            'ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto',
            'paste_copy_repexpand'
        );
        $exp->setRequiredFormItemPermission('visible,read,copy');

        $ilCtrl->setParameter($this, 'selectMode', self::SOURCE_SELECTION);
        $exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showSourceSelectionTree'));
        $exp->setTargetGet('ref_id');
        $exp->setPostVar('source');
        $exp->setCheckedItems($this->getSources());

        // Filter to container
        foreach (array('cat','root','fold') as $container) {
            $exp->removeFormItemForType($container);
        }


        if ($_GET['paste_copy_repexpand'] == '') {
            $expanded = $tree->readRootId();
        } else {
            $expanded = $_GET['paste_copy_repexpand'];
        }

        $this->tpl->setVariable('FORM_TARGET', '_self');
        $this->tpl->setVariable('FORM_ACTION', $ilCtrl->getFormAction($this, 'copySelection'));

        $exp->setExpand($expanded);
        // build html-output
        $exp->setOutput(0);
        $output = $exp->getOutput();

        $this->tpl->setVariable('OBJECT_TREE', $output);

        $this->tpl->setVariable('CMD_SUBMIT', 'saveSource');
        $this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('btn_next'));

        $ilToolbar->addButton($this->lng->txt('cancel'), $ilCtrl->getLinkTarget($this, 'cancel'));
    }

    /**
     * Save target selection
     * @return
     */
    protected function saveTarget()
    {
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;


        // begin-patch mc
        if (is_array($_REQUEST['target']) and $_REQUEST['target']) {
            $this->setTargets($_REQUEST['target']);
            $ilCtrl->setParameter($this, 'target_ids', implode('_', $this->getTargets()));
        }
        // paste from clipboard
        elseif ((int) $_REQUEST['target']) {
            $this->setTarget($_REQUEST['target']);
            $ilCtrl->setParameter($this, 'target_ids', implode('_', $this->getTargets()));
        }
        // end-patch multi copy
        else {
            $ilCtrl->setParameter($this, 'selectMode', self::TARGET_SELECTION);
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showTargetSelectionTree();
            return false;
        }

        // validate allowed subtypes
        foreach ($this->getSources() as $source_ref_id) {
            foreach ((array) $this->getTargets() as $target_ref_id) {
                $target_type = ilObject::_lookupType($target_ref_id, true);
                $target_class_name = ilObjectFactory::getClassByType($target_type);
                $target_object = new $target_class_name($target_ref_id);
                $possible_subtypes = $target_object->getPossibleSubObjects();

                $source_type = ilObject::_lookupType($source_ref_id, true);

                if (!array_key_exists($source_type, (array) $possible_subtypes)) {
                    ilUtil::sendFailure(
                        sprintf(
                            $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                            $this->lng->txt('obj_' . $target_type),
                            $this->lng->txt('obj_' . $source_type)
                        )
                    );
                    $this->showTargetSelectionTree();
                    return false;
                }
            }
        }

        if (count($this->getSources()) == 1 && $objDefinition->isContainer($this->getType())) {
            // check, if object should be copied into itself
            // begin-patch mc
            $is_child = array();
            foreach ($this->getTargets() as $target_ref_id) {
                if ($tree->isGrandChild($this->getFirstSource(), $target_ref_id)) {
                    $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getFirstSource()));
                }
                if ($this->getFirstSource() == $target_ref_id) {
                    $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getFirstSource()));
                }
            }
            // end-patch multi copy
            if (count($is_child) > 0) {
                ilUtil::sendFailure($this->lng->txt("msg_not_in_itself") . " " . implode(',', $is_child));
                $this->showTargetSelectionTree();
                return false;
            }

            $this->showItemSelection();
        } else {
            if (count($this->getSources()) == 1) {
                $this->copySingleObject();
            } else {
                $this->copyMultipleNonContainer($this->getSources());
            }
        }
    }

    /**
     * set copy mode
     * @param int $a_mode
     * @return
     */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    /**
     * get copy mode
     * @return
     */
    public function getMode()
    {
        return $this->mode;
    }

    public function setSubMode($a_mode)
    {
        $this->sub_mode = $a_mode;
    }

    public function getSubMode()
    {
        return $this->sub_mode;
    }

    /**
     * Get parent gui object
     * @return object	parent gui
     */
    public function getParentObject()
    {
        return $this->parent_obj;
    }

    /**
     * Returns $type.
     *
     * @see ilObjectCopyGUI::$type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets $type.
     *
     * @param object $type
     * @see ilObjectCopyGUI::$type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set source id
     * @param array $a_source_id
     * @return
     */
    public function setSource(array $a_source_ids)
    {
        $this->sources = $a_source_ids;
    }

    /**
     * Get sources
     * @return array
     */
    public function getSources()
    {
        return (array) $this->sources;
    }

    public function getFirstSource()
    {
        if (count($this->sources)) {
            return $this->sources[0];
        }
        return 0;
    }

    // begin-patch mc

    /**
     * Set single object target
     * @param type $a_ref_id
     */
    public function setTarget($a_ref_id)
    {
        $this->setTargets(array($a_ref_id));
    }


    /**
     * Set target id
     * @param int $a_target
     * @return
     */
    public function setTargets(array $a_target)
    {
        $this->targets = $a_target;
    }

    /**
     * Get copy target
     * @return
     */
    public function getTargets()
    {
        return (array) $this->targets;
    }

    /**
     * Get first target
     * @return int
     */
    public function getFirstTarget()
    {
        if (array_key_exists(0, $this->getTargets())) {
            $targets = $this->getTargets();
            return $targets[0];
        }
        return 0;
    }
    // end-patch multi copy

    /**
     * Cancel workflow
     */
    protected function cancel()
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setReturnByClass(get_class($this->parent_obj), 'cancel');
        $ilCtrl->returnToParent($this);
    }

    /**
     * Keep objects in clipboard
     */
    public function keepObjectsInClipboard()
    {
        ilUtil::sendSuccess($this->lng->txt("obj_inserted_clipboard"), true);
        $ilCtrl = $this->ctrl;
        $_SESSION['clipboard']['cmd'] = "copy";
        $_SESSION['clipboard']['ref_ids'] = $this->getSources();
        $ilCtrl->returnToParent($this);
    }


    /**
     * Search source
     * @return
     */
    protected function searchSource()
    {
        $tree = $this->tree;
        $ilObjDataCache = $this->obj_data_cache;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (isset($_POST['tit'])) {
            ilUtil::sendInfo($this->lng->txt('wizard_search_list'));
            $_SESSION['source_query'] = $_POST['tit'];
        } else {
            $_POST['tit'] = $_SESSION['source_query'];
        }

        $this->initFormSearch();
        $this->form->setValuesByPost();

        if (!$this->form->checkInput()) {
            ilUtil::sendFailure($lng->txt('msg_no_search_string'), true);
            $ilCtrl->returnToParent($this);
            return false;
        }

        include_once './Services/Search/classes/class.ilQueryParser.php';
        $query_parser = new ilQueryParser($this->form->getInput('tit'));
        $query_parser->setMinWordLength(1, true);
        $query_parser->setCombination(QP_COMBINATION_AND);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            ilUtil::sendFailure($query_parser->getMessage(), true);
            $ilCtrl->returnToParent($this);
        }

        // only like search since fulltext does not support search with less than 3 characters
        include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array($_REQUEST['new_type']));
        $res = $object_search->performSearch();
        $res->setRequiredPermission('copy');
        $res->filter(ROOT_FOLDER_ID, true);

        if (!count($results = $res->getResultsByObjId())) {
            ilUtil::sendFailure($this->lng->txt('search_no_match'), true);
            $ilCtrl->returnToParent($this);
        }


        include_once './Services/Object/classes/class.ilObjectCopySearchResultTableGUI.php';
        $table = new ilObjectCopySearchResultTableGUI($this, 'searchSource', $this->getType());
        $table->setFormAction($ilCtrl->getFormAction($this));
        $table->setSelectedReference($this->getFirstSource());
        $table->parseSearchResults($results);
        $tpl->setContent($table->getHTML());
    }

    /**
     * select source object
     * @return
     */
    protected function saveSource()
    {
        $objDefinition = $this->obj_definition;
        $ilCtrl = $this->ctrl;

        if (isset($_POST['source'])) {
            $this->setSource(array((int) $_REQUEST['source']));
            $this->setType(ilObject::_lookupType((int) $_REQUEST['source'], true));
            $ilCtrl->setParameter($this, 'source_id', (int) $_REQUEST['source']);
        } else {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->searchSource();
            return false;
        }

        // validate allowed subtypes
        foreach ($this->getSources() as $source_ref_id) {
            foreach ((array) $this->getTargets() as $target_ref_id) {
                $target_type = ilObject::_lookupType($target_ref_id, true);
                $target_class_name = ilObjectFactory::getClassByType($target_type);
                $target_object = new $target_class_name($target_ref_id);
                $possible_subtypes = $target_object->getPossibleSubObjects();

                $source_type = ilObject::_lookupType($source_ref_id, true);

                if (!array_key_exists($source_type, $possible_subtypes)) {
                    #ilLoggerFactory::getLogger('obj')->debug('Source type: '.  $source_type);
                    #ilLoggerFactory::getLogger('obj')->debug('Target type: '.  $target_type);
                    #ilLoggerFactory::getLogger('obj')->debug('Submode: '.  $this->getSubMode());

                    // adopt content mode
                    if (
                        $this->getSubMode() != self::SUBMODE_CONTENT_ONLY and
                        ($source_type != 'crs' or $target_type != 'crs')
                    ) {
                        ilUtil::sendFailure(
                            sprintf(
                                $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                                $this->lng->txt('obj_' . $target_type),
                                $this->lng->txt('obj_' . $source_type)
                            )
                        );
                        $this->searchSource();
                        return false;
                    }
                }
            }
        }


        if ($objDefinition->isContainer($this->getType())) {
            $this->showItemSelection();
        } else {
            $this->copySingleObject();
        }
    }

    /**
     * Save selected source from membership screen
     */
    protected function saveSourceMembership()
    {
        $objDefinition = $this->obj_definition;
        $ilCtrl = $this->ctrl;

        if (!isset($_REQUEST['source'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $ilCtrl->redirect($this, 'showSourceSelectionMembership');
            return false;
        }

        $this->setSource(array((int) $_REQUEST['source']));
        $this->setType(ilObject::_lookupType((int) $this->getFirstSource(), true));
        $ilCtrl->setParameter($this, 'source_id', (int) $_REQUEST['source']);

        if ($objDefinition->isContainer($this->getType())) {
            $this->showItemSelection();
        } else {
            $this->copySingleObject();
        }
    }

    /**
     *
     * @return
     */
    protected function showItemSelection()
    {
        $tpl = $this->tpl;

        if (!count($this->getSources())) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->searchSource();
            return false;
        }

        ilLoggerFactory::getLogger('obj')->debug('Source(s): ' . print_r($this->getSources(), true));
        ilLoggerFactory::getLogger('obj')->debug('Target(s): ' . print_r($this->getTargets(), true));

        ilUtil::sendInfo($this->lng->txt($this->getType() . '_copy_threads_info'));
        include_once './Services/Object/classes/class.ilObjectCopySelectionTableGUI.php';

        $tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
        $tpl->setVariable('BODY_ATTRIBUTES', 'onload="ilDisableChilds(\'cmd\');"');

        switch ($this->getMode()) {
            case self::SOURCE_SELECTION:
                $back_cmd = 'showSourceSelectionTree';
                break;

            case self::TARGET_SELECTION:
                $back_cmd = 'showTargetSelectionTree';
                break;

            case self::SEARCH_SOURCE:
                $back_cmd = 'searchSource';
                break;
        }

        $table = new ilObjectCopySelectionTableGUI($this, 'showItemSelection', $this->getType(), $back_cmd);
        $table->parseSource($this->getFirstSource());

        $tpl->setContent($table->getHTML());
    }

    /**
     * Start cloning a single (not container) object
     * @return
     */
    protected function copySingleObject()
    {
        include_once('./Services/Link/classes/class.ilLink.php');
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

        $ilAccess = $this->access;
        $ilErr = $this->error;
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $rbacreview = $this->rbacreview;

        // Source defined
        if (!count($this->getSources())) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->returnToParent($this);
        }

        $this->copyMultipleNonContainer($this->getSources());
        return;
    }

    /**
     * Copy multiple non container
     *
     * @param array $a_sources array of source ref ids
     */
    public function copyMultipleNonContainer($a_sources)
    {
        $ilAccess = $this->access;
        $objDefinition = $this->obj_definition;
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $rbacreview = $this->rbacreview;

        // check permissions
        foreach ($a_sources as $source_ref_id) {
            $source_type = ilObject::_lookupType($source_ref_id, true);

            // Create permission
            // begin-patch mc
            foreach ($this->getTargets() as $target_ref_id) {
                if (!$rbacsystem->checkAccess('create', $target_ref_id, $source_type)) {
                    $this->log->notice('Permission denied for target_id: ' . $target_ref_id . ' source_type: ' . $source_type . ' CREATE');
                    ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                    $ilCtrl->returnToParent($this);
                }
            }

            // Copy permission
            if (!$ilAccess->checkAccess('copy', '', $source_ref_id)) {
                $this->log->notice('Permission denied for source_ref_id: ' . $source_ref_id . ' COPY');
                ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                $ilCtrl->returnToParent($this);
            }

            // check that these objects are really not containers
            if ($objDefinition->isContainer($source_type) and $this->getSubMode() != self::SUBMODE_CONTENT_ONLY) {
                ilUtil::sendFailure($this->lng->txt('cntr_container_only_on_their_own'), true);
                $ilCtrl->returnToParent($this);
            }
        }

        reset($a_sources);

        ilLoggerFactory::getLogger('obj')->debug('Copy multiple non containers. Sources: ' . print_r($a_sources, true));

        // clone
        foreach ($a_sources as $source_ref_id) {
            ilLoggerFactory::getLogger('obj')->debug('Copying source ref_id : ' . $source_ref_id);

            // begin-patch mc
            foreach ($this->getTargets() as $target_ref_id) {
                // Save wizard options
                $copy_id = ilCopyWizardOptions::_allocateCopyId();
                $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
                $wizard_options->saveOwner($ilUser->getId());
                $wizard_options->saveRoot((int) $source_ref_id);
                $wizard_options->read();

                $orig = ilObjectFactory::getInstanceByRefId((int) $source_ref_id);
                $new_obj = $orig->cloneObject($target_ref_id, $copy_id);

                // Delete wizard options
                $wizard_options->deleteAll();
                $this->parent_obj->callCreationCallback(
                    $new_obj,
                    $this->obj_definition,
                    $_GET['crtcb'] ?? 0
                );

                // rbac log
                if (ilRbacLog::isActive()) {
                    $rbac_log_roles = $rbacreview->getParentRoleIds($new_obj->getRefId(), false);
                    $rbac_log = ilRbacLog::gatherFaPa($new_obj->getRefId(), array_keys($rbac_log_roles), true);
                    ilRbacLog::add(ilRbacLog::COPY_OBJECT, $new_obj->getRefId(), $rbac_log, (int) $source_ref_id);
                }
            }
        }

        unset($_SESSION["clipboard"]["ref_ids"]);
        unset($_SESSION["clipboard"]["cmd"]);

        if (count($a_sources) == 1) {
            ilLoggerFactory::getLogger('obj')->info('Object copy completed.');
            ilUtil::sendSuccess($this->lng->txt("object_duplicated"), true);
            $ref_id = $new_obj->getRefId();
        } else {
            ilLoggerFactory::getLogger('obj')->info('Object copy completed.');
            ilUtil::sendSuccess($this->lng->txt("objects_duplicated"), true);
            $ref_id = $this->getFirstTarget();
        }

        ilUtil::sendSuccess($this->lng->txt("objects_duplicated"), true);
        ilUtil::redirect(ilLink::_getLink($ref_id));

        // see bug discussion 24472
        /*
        $gui_fac = new ilObjectGUIFactory();
        $obj_gui = $gui_fac->getInstanceByRefId($ref_id);
        $obj_gui->redirectAfterCreation();
        */
    }

    /**
     * Copy to multiple targets
     */
    protected function copyContainerToTargets()
    {
        $ilCtrl = $this->ctrl;

        ilLoggerFactory::getLogger('obj')->debug('Copy container to targets: ' . print_r($_REQUEST, true));
        ilLoggerFactory::getLogger('obj')->debug('Source(s): ' . print_r($this->getSources(), true));
        ilLoggerFactory::getLogger('obj')->debug('Target(s): ' . print_r($this->getTargets(), true));


        $last_target = 0;
        $result = 1;
        foreach ($this->getTargets() as $target_ref_id) {
            $result = $this->copyContainer($target_ref_id);
            $last_target = $target_ref_id;
        }

        unset($_SESSION["clipboard"]["ref_ids"]);
        unset($_SESSION["clipboard"]["cmd"]);

        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        if (ilCopyWizardOptions::_isFinished($result['copy_id'])) {
            ilLoggerFactory::getLogger('obj')->info('Object copy completed.');
            ilUtil::sendSuccess($this->lng->txt("object_duplicated"), true);
            if ($this->getSubMode() == self::SUBMODE_CONTENT_ONLY) {
                // return to parent container
                return $this->ctrl->returnToParent($this);
            }
            // return to last target
            $link = ilLink::_getLink($result['ref_id']);
            $ilCtrl->redirectToUrl($link);
        } else {
            // show progress
            ilLoggerFactory::getLogger('obj')->debug('Object copy in progress.');
            return $this->showCopyProgress();
        }
    }

    /**
     * Show progress for copying
     */
    protected function showCopyProgress()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        include_once './Services/Object/classes/class.ilObjectCopyProgressTableGUI.php';
        $progress = new ilObjectCopyProgressTableGUI(
            $this,
            'showCopyProgress',
            (int) $_GET['ref_id']
        );
        $progress->setObjectInfo($this->targets_copy_id);
        $progress->parse();
        $progress->init();
        $progress->setRedirectionUrl($ilCtrl->getParentReturn($this->parent_obj));

        $tpl->setContent($progress->getHTML());
    }

    /**
     * Update progress
     */
    protected function updateProgress()
    {
        $json = new stdClass();
        $json->percentage = null;
        $json->performed_steps = null;

        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $options = ilCopyWizardOptions::_getInstance((int) $_REQUEST['copy_id']);
        $json->required_steps = $options->getRequiredSteps();
        $json->id = (int) $_REQUEST['copy_id'];

        ilLoggerFactory::getLogger('obj')->debug('Update copy progress: ' . json_encode($json));

        echo json_encode($json);
        exit;
    }


    /**
     * Copy a container
     * @return
     */
    protected function copyContainer($a_target)
    {
        $ilLog = $this->log;
        $ilCtrl = $this->ctrl;

        include_once('./Services/Link/classes/class.ilLink.php');
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

        $ilAccess = $this->access;
        $ilErr = $this->error;
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        // Workaround for course in course copy

        $target_type = ilObject::_lookupType(ilObject::_lookupObjId($a_target));
        $source_type = ilObject::_lookupType(ilObject::_lookupObjId($this->getFirstSource()));

        if ($this->getSubMode() != self::SUBMODE_CONTENT_ONLY) {
            if (!$rbacsystem->checkAccess('create', $a_target, $this->getType())) {
                $this->log->notice('Permission denied for target: ' . $a_target . ' type: ' . $this->getType() . ' CREATE');
                ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                $ilCtrl->returnToParent($this);
            }
        }
        if (!$this->getFirstSource()) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->returnToParent($this);
            return false;
        }

        $options = $_POST['cp_options'] ? $_POST['cp_options'] : array();


        ilLoggerFactory::getLogger('obj')->debug('Copy container (sources): ' . print_r($this->getSources(), true));

        $orig = ilObjectFactory::getInstanceByRefId($this->getFirstSource());
        $result = $orig->cloneAllObject(
            $_COOKIE[session_name()],
            $_COOKIE['ilClientId'],
            $this->getType(),
            $a_target,
            $this->getFirstSource(),
            $options,
            false,
            $this->getSubMode()
        );
        $this->targets_copy_id[$a_target] = $result['copy_id'];
        $new_ref_id = (int) $result['ref_id'];
        if ($new_ref_id > 0) {
            $new_obj = ilObjectFactory::getInstanceByRefId((int) $result['ref_id'], false);
            if ($new_obj instanceof ilObject) {
                $this->parent_obj->callCreationCallback(
                    $new_obj,
                    $this->obj_definition,
                    $_GET['crtcb'] ?? 0
                );
            }
        }
        return $result;
    }



    /**
     * Show init screen
     * Normally shown below the create and import form when creating a new object
     *
     * @param string $a_tplvar The tpl variable to fill
     * @return
     */
    public function showSourceSearch($a_tplvar)
    {
        $tpl = $this->tpl;

        // Disabled for performance
        #if(!$this->sourceExists())
        #{
        #	return false;
        #}

        $this->unsetSession();
        $this->initFormSearch();

        if ($a_tplvar) {
            $tpl->setVariable($a_tplvar, $this->form->getHTML());
        } else {
            return $this->form;
        }
    }


    /**
     * Check if there is any source object
     * @return bool
     */
    protected function sourceExists()
    {
        $ilUser = $this->user;

        return (bool) ilUtil::_getObjectsByOperations($this->getType(), 'copy', $ilUser->getId(), 1);
    }

    /**
     * Init search form
     * @return
     */
    protected function initFormSearch()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $this->form = new ilPropertyFormGUI();
        $this->form->setTableWidth('600px');

        $ilCtrl->setParameter($this, 'new_type', $this->getType());

        #$ilCtrl->setParameter($this, 'cp_mode', self::SOURCE_SELECTION);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTitle($lng->txt($this->getType() . '_copy'));

        $this->form->addCommandButton('searchSource', $lng->txt('btn_next'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $tit = new ilTextInputGUI($lng->txt('title'), 'tit');
        $tit->setSize(40);
        $tit->setMaxLength(70);
        $tit->setRequired(true);
        $tit->setInfo($lng->txt('wizard_title_info'));
        $this->form->addItem($tit);
    }

    /**
     * Unset session variables
     * @return
     */
    protected function unsetSession()
    {
        unset($_SESSION['source_query']);
        $this->setSource(array());
    }
}
