<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
* GUI class for course objective view
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerObjectiveGUI extends ilContainerContentGUI
{
    /**
     * @var \ilLogger |null
     */
    private $logger = null;


    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    protected $force_details = 0;

    /**
     * @var \ilLOSettings
     */
    protected $loc_settings;
    
    const MATERIALS_TESTS = 1;
    const MATERIALS_OTHER = 2;
    
    private $output_html = '';
    
    private $test_assignments = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param object container gui object
     * @return
     */
    public function __construct($a_container_gui)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $lng = $DIC->language();

        $this->logger = $DIC->logger()->crs();
        
        $this->lng = $lng;
        parent::__construct($a_container_gui);
        
        $this->initDetails();
        $this->initTestAssignments();
    }
    
    /**
     * Get test assignments object
     * @return ilLOTestAssignments
     */
    public function getTestAssignments()
    {
        return $this->test_assignments;
    }
    
    /**
     * @return \ilLOSettings
     */
    public function getSettings()
    {
        return $this->loc_settings;
    }
    
    
    
    /**
     * get details level
     *
     * @access public
     * @param
     * @return
     */
    public function getDetailsLevel($a_objective_id)
    {
        // no details anymore
        return self::DETAILS_ALL;
    }
    
    /**
     * Impementation of abstract method getMainContent
     *
     * @access public
     * @return
     */
    public function getMainContent()
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        // see bug #7452
        //		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');


        include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

        $tpl = new ilTemplate("tpl.container_page.html", true, true, "Services/Container");

        if ($ilAccess->checkAccess('write', '', $this->getContainerObject()->getRefId())) {
            // check for results
            include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
            if (ilLOUserResults::hasResults($this->getContainerObject()->getId(), $ilUser->getId())) {
                include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
                $ilToolbar = new ilToolbarGUI();
                $ilToolbar->addButton(
                    $lng->txt('crs_reset_results'),
                    $ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI()), 'reset')
                );
            }
        }

        // Feedback
        // @todo
        //		$this->__showFeedBack();

        $this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
    
        $is_manage = $this->getContainerGUI()->isActiveAdministrationPanel();
        $is_order = $this->getContainerGUI()->isActiveOrdering();
        
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $this->loc_settings = ilLOSettings::getInstanceByObjId($this->getContainerObject()->getId());
                    
        $this->initRenderer();
    
        if (!$is_manage && !$is_order) {
            // currently inactive
            // $this->showStatus($tpl);
        }
        if (!$is_manage) {
            $this->showObjectives($tpl, $is_order);
                        
            // $this->showMaterials($tpl,self::MATERIALS_TESTS, false, !$is_order);
            
            // check for results
            include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
            $has_results = ilLOUserResults::hasResults($this->getContainerObject()->getId(), $ilUser->getId());
            
            include_once './Modules/Test/classes/class.ilObjTestAccess.php';
            $tst_obj_id = ilObject::_lookupObjId($this->loc_settings->getInitialTest());
            
            if (
                $this->loc_settings->getInitialTest() &&
                $this->loc_settings->isGeneralInitialTestVisible() &&
                !$this->loc_settings->isInitialTestStart() &&
                !ilObjTestAccess::checkCondition($tst_obj_id, ilConditionHandler::OPERATOR_FINISHED, '', $ilUser->getId())
            ) {
                $this->output_html .= $this->renderTest($this->loc_settings->getInitialTest(), null, true, true);
            } elseif (
                $this->loc_settings->getQualifiedTest() &&
                $this->loc_settings->isGeneralQualifiedTestVisible()
            ) {
                $this->output_html .= $this->renderTest($this->loc_settings->getQualifiedTest(), null, false, true);
            }
            
            $this->showMaterials($tpl, self::MATERIALS_OTHER, false, !$is_order);
        } else {
            $this->showMaterials($tpl, null, $is_manage);
        }

        // reset results by setting or for admins
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        if (
            ilLOSettings::getInstanceByObjId($this->getContainerObject()->getId())->isResetResultsEnabled() or
            $ilAccess->checkAccess('write', '', $this->getContainerObject()->getRefId())
        ) {
            if ($has_results) {
                if (!$is_manage && !$is_order) {
                    $this->showButton('askReset', $lng->txt('crs_reset_results'));
                }
            }
        }

        $tpl->setVariable('CONTAINER_PAGE_CONTENT', $this->output_html);
        
        return $tpl->get();
    }
    
    /**
     * show status
     *
     * @access public
     * @param
     * @return
     */
    public function showStatus($tpl)
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveResultCache.php');
        
        $status = ilCourseObjectiveResultCache::getStatus($ilUser->getId(), $this->getContainerObject()->getId());
        if ($status == IL_OBJECTIVE_STATUS_EMPTY) {
            return;
        }
        $info_tpl = new ilTemplate('tpl.crs_objectives_view_info_table.html', true, true, 'Modules/Course');
        $info_tpl->setVariable("INFO_STRING", $lng->txt('crs_objectives_info_' . $status));

        $this->output_html .= $info_tpl->get();
    }
    
    /**
     * show objectives
     *
     * @access public
     * @param object $tpl template object
     * @return
     */
    public function showObjectives($a_tpl, $a_is_order = false)
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $tpl = $this->tpl;
        
        $this->clearAdminCommandsDetermination();
        
        // get embedded blocks
        $has_container_page = false;
        if (!$a_is_order) {
            $output_html = $this->getContainerGUI()->getContainerPageHTML();
            if ($output_html != "") {
                $has_container_page = true;
                $this->output_html .= $this->insertPageEmbeddedBlocks($output_html);
            }
            unset($output_html);
        }
        
        // All objectives
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        if (!count($objective_ids = ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId(), true))) {
            return false;
        }
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveListGUI.php');
        $this->objective_list_gui = new ilCourseObjectiveListGUI();
        $this->objective_list_gui->setContainerObject($this->getContainerGUI());
        if ($ilSetting->get("icon_position_in_lists") == "item_rows") {
            $this->objective_list_gui->enableIcon(true);
        }
        
        $acc = null;
        if (!$a_is_order) {
            include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
            $acc = new ilAccordionGUI();
            $acc->setUseSessionStorage(true);
            $acc->setAllowMultiOpened(true);
            $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
            $acc->setId("crsobjtv_" . $this->container_obj->getId());
        } else {
            $this->renderer->addCustomBlock('lobj', $lng->txt('crs_objectives'));
        }
        
        $lur_data = $this->parseLOUserResults();
        
        $has_initial = ilLOSettings::getInstanceByObjId($this->container_obj->getId())->worksWithInitialTest();
        
        $has_lo_page = false;
        $obj_cnt = 0;
        foreach ($objective_ids as $objective_id) {
            include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
            if (
                $has_initial &&
                (
                    !isset($lur_data[$objective_id]) or
                    ilLOUtils::hasActiveRun(
                        $this->container_obj->getId(),
                        ilLOSettings::getInstanceByObjId($this->container_obj->getId())->getInitialTest(),
                        $objective_id
                    )
                )
            ) {
                $lur_data[$objective_id] = array("type"=>ilLOSettings::TYPE_TEST_INITIAL);
            }

            if ($html = $this->renderObjective($objective_id, $has_lo_page, $acc, $lur_data[$objective_id])) {
                $this->renderer->addItemToBlock('lobj', 'lobj', $objective_id, $html);
            }
            $obj_cnt++;
        }

        // buttons for showing/hiding all objectives
        if (!$a_is_order && $obj_cnt > 1) {
            $this->showButton("", $lng->txt("crs_show_all_obj"), "", "crs_show_all_obj_btn");
            $this->showButton("", $lng->txt("crs_hide_all_obj"), "", "crs_hide_all_obj_btn");
            $acc->setShowAllElement("crs_show_all_obj_btn");
            $acc->setHideAllElement("crs_hide_all_obj_btn");
        }
        
        if (!$has_container_page && $has_lo_page) {
            // add core co page css
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath(0)
            );
            $tpl->setCurrentBlock("SyntaxStyle");
            $tpl->setVariable(
                "LOCATION_SYNTAX_STYLESHEET",
                ilObjStyleSheet::getSyntaxStylePath()
            );
            $tpl->parseCurrentBlock();
        }
    
        // order/block
        if ($a_is_order) {
            $this->addFooterRow();
        
            $this->output_html .= $output_html . $this->renderer->getHTML();

            $this->renderer->resetDetails();
        }
        // view/accordion
        else {
            $this->output_html .= "<div class='ilCrsObjAcc'>" . $acc->getHTML() . "</div>";
        }
    }
    
    /**
     * add footer row
     *
     * @access public
     * @param
     * @return
     */
    public function addFooterRow()
    {
        // no details
        return;
        
        /*
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getContainerObject()->getRefId());
        $ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "1");
        $url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
        $this->renderer->addDetailsLevel(2, $url, ($this->details_level == self::DETAILS_TITLE));

        $ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "2");
        $url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
        $this->renderer->addDetailsLevel(3, $url, ($this->details_level == self::DETAILS_ALL));

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
        */
    }
    
    protected function renderTest($a_test_ref_id, $a_objective_id, $a_is_initial = false, $a_add_border = false, $a_lo_result = array())
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $node_data = [];
        if ($a_test_ref_id) {
            $node_data = $tree->getNodeData($a_test_ref_id);
        }
        if (!$node_data['child']) {
            return '';
        }
        
        // update ti
        if ($a_objective_id) {
            if ($a_is_initial) {
                $title = sprintf($this->lng->txt('crs_loc_itst_for_objective'), ilCourseObjective::lookupObjectiveTitle($a_objective_id));
            } else {
                $title = sprintf($this->lng->txt('crs_loc_qtst_for_objective'), ilCourseObjective::lookupObjectiveTitle($a_objective_id));
            }
            $node_data['objective_id'] = $a_objective_id;
            $node_data['objective_status'] =
                (
                    $a_lo_result['status'] == ilLOUserResults::STATUS_COMPLETED ?
                    false :
                    false
                );
        } else {
            $obj_id = ilObject::_lookupObjId($a_test_ref_id);
            $title = ilObject::_lookupTitle($obj_id);
            
            $title .= (
                ' (' .
                    (
                        $a_is_initial
                            ? $this->lng->txt('crs_loc_itest_info')
                            : $this->lng->txt('crs_loc_qtest_info')
                    ) .
                    ')'
            );
            $node_data['objective_id'] = 0;
        }
        
        $node_data['title'] = $title;
        
        return "<div class='ilContObjectivesViewTestItem'>" . $this->renderItem($node_data) . "</div>";
    }
    
    /**
     * Show all other (no assigned tests, no assigned materials) materials
     *
     * @access public
     * @param object $tpl template object
     * @return void
     */
    public function showMaterials($a_tpl, $a_mode = null, $a_is_manage = false, $a_as_accordion = false)
    {
        $ilAccess = $this->access;
        $lng = $this->lng;

        $this->clearAdminCommandsDetermination();
        
        if (is_array($this->items["_all"])) {
            $this->objective_map = $this->buildObjectiveMap();
            
            // all rows
            $item_r = array();
            
            $position = 1;
            foreach ($this->items["_all"] as $k => $item_data) {
                if ($a_mode == self::MATERIALS_TESTS and $item_data['type'] != 'tst') {
                    continue;
                }
                if ($item_data['type'] == 'itgr') {
                    continue;
                }
                if (!$a_is_manage) {
                    // if test object is qualified or initial do not show here
                    include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
                    include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
                    $assignments = ilLOTestAssignments::getInstance($this->getContainerObject()->getId());
                    if ($assignments->getTypeByTest($item_data['child']) != ilLOSettings::TYPE_TEST_UNDEFINED) {
                        continue;
                    }
                }
                
                if ($this->rendered_items[$item_data["child"]] !== true &&
                    !$this->renderer->hasItem($item_data["child"])) {
                    $this->rendered_items[$item_data['child']] = true;
                    
                    // TODO: Position (DONE ?)
                    $html = $this->renderItem($item_data, $position++, $a_mode == self::MATERIALS_TESTS ? false : true);
                    if ($html != "") {
                        $item_r[] = array("html" => $html, "id" => $item_data["child"], "type" => $item_data["type"]);
                    }
                }
            }
            
            // if we have at least one item, output the block
            if (count($item_r) > 0) {
                if (!$a_as_accordion) {
                    $pos = 0;
                    
                    switch ($a_mode) {
                        case self::MATERIALS_TESTS:
                            $block_id = "tst";
                            $this->renderer->addTypeBlock($block_id);
                            break;

                        case self::MATERIALS_OTHER:
                            $block_id = "oth";
                            $this->renderer->addCustomBlock($block_id, $lng->txt('crs_other_resources'));
                            break;
                        
                        // manage
                        default:
                            $block_id = "all";
                            $this->renderer->addCustomBlock($block_id, $lng->txt('content'));
                            break;
                    }

                    // :TODO:
                    if ($a_mode != self::MATERIALS_TESTS) {
                        $pos = $this->getItemGroupsHTML();
                    }
                
                    foreach ($item_r as $h) {
                        if (!$this->renderer->hasItem($h["id"])) {
                            $this->renderer->addItemToBlock($block_id, $h["type"], $h["id"], $h["html"]);
                        }
                    }
                    
                    $this->output_html .= $this->renderer->getHTML();
                } else {
                    switch ($a_mode) {
                        case self::MATERIALS_TESTS:
                            $txt = $lng->txt('objs_tst');
                            break;

                        case self::MATERIALS_OTHER:
                            $txt = $lng->txt('crs_other_resources');
                            break;
                    }
                    
                    include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
                    $acc = new ilAccordionGUI();
                    $acc->setId("crsobjtvmat" . $a_mode . "_" . $this->container_obj->getId());
                    
                    $acc_content = array();
                    foreach ($item_r as $h) {
                        $acc_content[] = $h["html"];
                    }
                    $acc->addItem($txt, $this->buildAccordionContent($acc_content));
                    
                    $this->output_html .= $acc->getHTML();
                }
            }
        }
    }
    
    protected function buildObjectiveMap()
    {
        $objective_map = array();
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        // begin-patch lok
        if (count($objective_ids = ilCourseObjective::_getObjectiveIds($this->getContainerObject()->getId(), true))) {
            // end-patch lok
            include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
            foreach ($objective_ids as $objective_id) {
                foreach (ilCourseObjectiveMaterials::_getAssignedMaterials($objective_id) as $mat_ref_id) {
                    $objective_map["material"][$mat_ref_id][] = $objective_id;

                    if (!isset($objective_map["names"][$objective_id])) {
                        $objective = new ilCourseObjective($this->getContainerObject(), $objective_id);
                        $objective_map["names"][$objective_id] = $objective->getTitle();
                    }
                }
            }

            // initial/qualifying test
            $tst = $this->loc_settings->getInitialTest();
            if ($tst) {
                $objective_map["test_i"] = $tst;
            }
            $tst = $this->loc_settings->getQualifiedTest();
            if ($tst) {
                $objective_map["test_q"] = $tst;
            }
            
            // objective test assignments
            include_once 'Modules/Course/classes/Objectives/class.ilLOSettings.php';
            include_once 'Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
            $ass_test = new ilLOTestAssignments($this->getContainerObject()->getId());
            foreach ($ass_test->getAssignmentsByType(ilLOSettings::TYPE_TEST_INITIAL) as $ass) {
                $title = ilCourseObjective::lookupObjectiveTitle($ass->getObjectiveId());
                $objective_map["test_ass"][$ass->getTestRefId()][$ass->getAssignmentType()][] = $title;
            }
            foreach ($ass_test->getAssignmentsByType(ilLOSettings::TYPE_TEST_QUALIFIED) as $ass) {
                $title = ilCourseObjective::lookupObjectiveTitle($ass->getObjectiveId());
                $objective_map["test_ass"][$ass->getTestRefId()][$ass->getAssignmentType()][] = $title;
            }
        }
        
        return $objective_map;
    }
    
    protected function addItemDetails(ilObjectListGUI $a_item_list_gui, array $a_item)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
                        
        $item_ref_id = $a_item["ref_id"];
        
        if (is_array($this->objective_map)) {
            $details = array();
            if (isset($this->objective_map["material"][$item_ref_id])) {
                // #12965
                foreach ($this->objective_map["material"][$item_ref_id] as $objective_id) {
                    $ilCtrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', $objective_id);
                    $url = $ilCtrl->getLinkTargetByClass(array('illoeditorgui', 'ilcourseobjectivesgui'), 'edit');
                    $ilCtrl->setParameterByClass('ilcourseobjectivesgui', 'objective_id', '');

                    $details[] = array(
                        'desc' => $lng->txt('crs_loc_tab_materials') . ': ',
                        'target' => '_top',
                        'link' => $url,
                        'name' => $this->objective_map["names"][$objective_id]
                    );
                }
            }
            if ($this->objective_map["test_i"] == $item_ref_id) {
                $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 1);
                $details[] = array(
                    'desc' => '',
                    'target' => '_top',
                    'link' => $ilCtrl->getLinkTargetByClass('illoeditorgui', 'testOverview'),
                    'name' => $lng->txt('crs_loc_tab_itest')
                );
                $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 0);
            }
            if ($this->objective_map["test_q"] == $item_ref_id) {
                $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 2);
                $details[] = array(
                    'desc' => '',
                    'target' => '_top',
                    'link' => $ilCtrl->getLinkTargetByClass('illoeditorgui', 'testOverview'),
                    'name' => $lng->txt('crs_loc_tab_qtest')
                );
                $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 0);
            }
            
            // #15367
            if (is_array($this->objective_map["test_ass"][$item_ref_id])) {
                foreach ($this->objective_map["test_ass"][$item_ref_id] as $type => $items) {
                    if ($type == ilLOSettings::TYPE_TEST_INITIAL) {
                        $caption = $lng->txt('crs_loc_tab_itest');
                        $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 1);
                    } else {
                        $caption = $lng->txt('crs_loc_tab_qtest');
                        $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 2);
                    }
                    foreach ($items as $objtv_title) {
                        $details[] = array(
                            'desc' => '',
                            'target' => '_top',
                            'link' => $ilCtrl->getLinkTargetByClass('illoeditorgui', 'testsOverview'),
                            'name' => $caption . " (" . $this->lng->txt("crs_loc_learning_objective") . ": " . $objtv_title . ")"
                        );
                    }
                    $ilCtrl->setParameterByClass('illoeditorgui', 'tt', 0);
                }
            }
        
            if (sizeof($details)) {
                $a_item_list_gui->enableItemDetailLinks(true);
                $a_item_list_gui->setItemDetailLinks($details, $lng->txt('crs_loc_settings_tbl') . ': ');
            } else {
                $a_item_list_gui->enableItemDetailLinks(false);
            }
        }
        
        // order
        if ($this->getContainerGUI()->isActiveOrdering()) {
            $a_item_list_gui->enableCommands(true, true);
            $a_item_list_gui->enableProperties(false);
        }
        // view
        elseif (!$this->getContainerGUI()->isActiveAdministrationPanel()) {
            $a_item_list_gui->enableCommands(true, true);
            $a_item_list_gui->enableProperties(false);
        }
        
        if ($a_item['objective_id']) {
            $a_item_list_gui->setDefaultCommandParameters(array('objective_id' => $a_item['objective_id']));
            
            
            if ($this->loc_settings->getQualifiedTest() == $a_item['ref_id']) {
                $a_item_list_gui->setConditionTarget($this->getContainerObject()->getRefId(), $a_item['objective_id'], 'lobj');
                // check conditions of target
                include_once './Services/Conditions/classes/class.ilConditionHandler.php';
                $fullfilled = ilConditionHandler::_checkAllConditionsOfTarget($this->getContainerObject()->getRefId(), $a_item['objective_id'], 'lobj');
                if (!$fullfilled || $a_item['objective_status']) {
                    $a_item_list_gui->disableTitleLink(true);
                }
            }
            include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
            $res = ilLOUserResults::lookupResult(
                $this->getContainerObject()->getId(),
                $ilUser->getId(),
                $a_item['objective_id'],
                ilLOUserResults::TYPE_QUALIFIED
            );
            
            $res = $this->updateResult($res, $a_item['ref_id'], $a_item['objective_id'], $ilUser->getId());
            
            if ($res['is_final']) {
                $a_item_list_gui->disableTitleLink(true);
                $a_item_list_gui->enableProperties(true);
                $a_item_list_gui->addCustomProperty(
                    $this->lng->txt('crs_loc_passes_reached'),
                    '',
                    true
                );
            } elseif ($this->loc_settings->getQualifiedTest() == $a_item['ref_id']) {
                include_once './Modules/Course/classes/class.ilCourseObjective.php';
                $poss_pass = ilCourseObjective::lookupMaxPasses($a_item['objective_id']);
                
                if ($poss_pass) {
                    $a_item_list_gui->enableProperties(true);
                    $a_item_list_gui->addCustomProperty(
                        $this->lng->txt('crs_loc_passes_left'),
                        (($poss_pass - $res['tries']) > 0) ? ($poss_pass - $res['tries']) : 1,
                        false
                    );
                }
            }
        }
    }
    
    protected function updateResult($a_res, $a_item_ref_id, $a_objective_id, $a_user_id)
    {
        if ($this->loc_settings->getQualifiedTest() == $a_item_ref_id) {
            // Check for existing test run, and decrease tries, reset final if run exists
            include_once './Modules/Test/classes/class.ilObjTest.php';
            include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
            $active = ilObjTest::isParticipantsLastPassActive(
                $a_item_ref_id,
                $a_user_id
            );
            
            if ($active) {
                include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
                if (ilLOTestRun::lookupRunExistsForObjective(
                    ilObject::_lookupObjId($a_item_ref_id),
                    $a_objective_id,
                    $a_user_id
                )) {
                    ($a_res['tries'] > 0) ? --$a_res['tries'] : 0;
                    $a_res['is_final'] = 0;
                }
            }
        }
        return $a_res;
    }
    
    /**
     * render objective
     *
     * @access protected
     * @param int objective id
     * @param bool co page status
     * @param ilAccordionGUI $a_accordion
     * @param array $a_lo_result
     * @return string html
     */
    protected function renderObjective($a_objective_id, &$a_has_lo_page, ilAccordionGUI $a_accordion = null, array $a_lo_result = null)
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        include_once('./Modules/Course/classes/class.ilCourseObjective.php');
        $objective = new ilCourseObjective($this->getContainerObject(), $a_objective_id);
        
        include_once('./Services/Container/classes/class.ilContainerSorting.php');
        include_once('./Services/Object/classes/class.ilObjectActivation.php');
        $items = ilObjectActivation::getItemsByObjective($a_objective_id);
        
        // sorting is handled by ilCourseObjectiveMaterials
        // $items = ilContainerSorting::_getInstance($this->getContainerObject()->getId())->sortSubItems('lobj',$a_objective_id,$items);
        
        include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
        $objectives_lm_obj = new ilCourseObjectiveMaterials($a_objective_id);
        
        // #13381 - map material assignment to position
        $sort_map = array();
        foreach ($objectives_lm_obj->getMaterials() as $item) {
            $sort_map[$item["lm_ass_id"]] = $item["position"];
        }
    
        $is_manage = $this->getContainerGUI()->isActiveAdministrationPanel();
        $is_order = $this->getContainerGUI()->isActiveOrdering();
                
        $sort_content = array();
        
        foreach ($items as $item) {
            if ($this->getDetailsLevel($a_objective_id) < self::DETAILS_ALL) {
                continue;
            }
                                    
            $item_list_gui2 = $this->getItemGUI($item);
            $item_list_gui2->enableIcon(true);
            
            if ($is_order || $a_accordion) {
                $item_list_gui2->enableCommands(true, true);
                $item_list_gui2->enableProperties(false);
            }
                        
            $chapters = $objectives_lm_obj->getChapters();
            if (count($chapters)) {
                $has_sections = false;
                foreach ($chapters as $chapter) {
                    if ($chapter['ref_id'] != $item['child']) {
                        continue;
                    }
                    $has_sections = true;

                    include_once './Modules/LearningModule/classes/class.ilLMObject.php';
                    $title =  $item['title'] .
                        " &rsaquo; " . ilLMObject::_lookupTitle($chapter['obj_id']) .
                        " (" . $lng->txt('obj_' . $chapter['type']) . ")";

                    $item_list_gui2->setDefaultCommandParameters(array(
                        "obj_id" => $chapter['obj_id'],
                        "focus_id" => $chapter['obj_id'],
                        "focus_return" => $this->container_obj->getRefId()));

                    if ($is_order) {
                        $item_list_gui2->setPositionInputField(
                            "[lobj][" . $a_objective_id . "][" . $chapter['lm_ass_id'] . "]",
                            sprintf('%d', $chapter['position']*10)
                        );
                    }

                    $sub_item_html = $item_list_gui2->getListItemHTML(
                        $item['ref_id'],
                        $item['obj_id'],
                        $title,
                        $item['description']
                    );
                    
                    // #13381 - use materials order
                    $sort_key = str_pad($chapter['position'], 5, 0, STR_PAD_LEFT) . "_" . strtolower($title) . "_" . $chapter['lm_ass_id'];
                    $sort_content[$sort_key] = $sub_item_html;
                }
            }

            $this->rendered_items[$item['child']] = true;
            
            if ($lm_ass_id = $objectives_lm_obj->isAssigned($item['ref_id'], true)) {
                if ($is_order) {
                    $item_list_gui2->setPositionInputField(
                        "[lobj][" . $a_objective_id . "][" . $lm_ass_id . "]",
                        sprintf('%d', $sort_map[$lm_ass_id]*10)
                    );
                }
                                
                $sub_item_html = $item_list_gui2->getListItemHTML(
                    $item['ref_id'],
                    $item['obj_id'],
                    $item['title'],
                    $item['description']
                );
                                
                // #13381 - use materials order
                $sort_key = str_pad($sort_map[$lm_ass_id], 5, 0, STR_PAD_LEFT) . "_" . strtolower($item['title']) . "_" . $lm_ass_id;
                $sort_content[$sort_key]  = $sub_item_html;
            }
        }
        
        if ($this->getDetailsLevel($a_objective_id) == self::DETAILS_ALL) {
            $this->objective_list_gui->enableCommands(false);
        } else {
            $this->objective_list_gui->enableCommands(true);
        }
        
        if ($is_order) {
            $this->objective_list_gui->setPositionInputField(
                "[lobj][" . $a_objective_id . "][0]",
                $objective->__getPosition()*10
            );
        }
            
        ksort($sort_content);
        
        if (!$a_accordion) {
            foreach ($sort_content as $sub_item_html) {
                $this->objective_list_gui->addSubItemHTML($sub_item_html);
            }
            
            return $this->objective_list_gui->getObjectiveListItemHTML(
                0,
                $a_objective_id,
                $objective->getTitle(),
                $objective->getDescription(),
                ($is_manage || $is_order)
            );
        } else {
            $acc_content = $sort_content;
            
            $initial_shown = false;
            $initial_test_ref_id = $this->getTestAssignments()->getTestByObjective($a_objective_id, ilLOSettings::TYPE_TEST_INITIAL);
            $initial_test_obj_id = ilObject::_lookupObjId($initial_test_ref_id);
            include_once './Modules/Test/classes/class.ilObjTestAccess.php';
            
            if (
                $initial_test_obj_id &&
                $this->getSettings()->hasSeparateInitialTests() &&
                !ilObjTestAccess::checkCondition($initial_test_obj_id, ilConditionHandler::OPERATOR_FINISHED, '', $ilUser->getId())
            ) {
                $acc_content[] = $this->renderTest(
                    $this->getTestAssignments()->getTestByObjective($a_objective_id, ilLOSettings::TYPE_TEST_INITIAL),
                    $a_objective_id,
                    true,
                    false,
                    $a_lo_result
                );
                $initial_shown = true;
            } elseif ($this->getSettings()->hasSeparateQualifiedTests()) {
                $acc_content[] = $this->renderTest(
                    $this->getTestAssignments()->getTestByObjective($a_objective_id, ilLOSettings::TYPE_TEST_QUALIFIED),
                    $a_objective_id,
                    false,
                    false,
                    $a_lo_result
                );
            }
            
            
            /*
            if($this->loc_settings->getInitialTest() &&
                $this->loc_settings->getType() == ilLOSettings::LOC_INITIAL_SEL &&
                !$a_lo_risult["initial_status"])
            {
                $acc_content[] = $this->renderTest($this->loc_settings->getInitialTest(), $a_objective_id, true, false, $a_lo_result);
                $initial_shown = true;
            }
            if(!$initial_shown &&
                $this->loc_settings->getQualifiedTest() &&
                $this->loc_settings->isQualifiedTestPerObjectiveVisible())
            {
                $acc_content[] = $this->renderTest($this->loc_settings->getQualifiedTest(), $a_objective_id, false, false, $a_lo_result);
            }
            */
            
            $co_page = null;
            include_once("./Services/COPage/classes/class.ilPageUtil.php");
            if (ilPageUtil::_existsAndNotEmpty("lobj", $objective->getObjectiveId())) {
                $a_has_lo_page = true;
                
                include_once 'Modules/Course/classes/Objectives/class.ilLOPageGUI.php';
                $page_gui = new ilLOPageGUI($objective->getObjectiveId());
                
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));
                $page_gui->setPresentationTitle("");
                $page_gui->setTemplateOutput(false);
                $page_gui->setHeader("");
                
                $co_page = "<div class='ilContObjectiveIntro'>" . $page_gui->showPage() . "</div>";
            }
            
            $a_accordion->addItem(
                $this->buildAccordionTitle($objective, $a_lo_result),
                $co_page .
                    $this->buildAccordionContent($acc_content),
                (isset($_GET["oobj"]) && (int) $_GET["oobj"] == $objective->getObjectiveId())
            );
        }
    }
    
    /**
     * init details
     *
     * @access protected
     * @param
     * @return
     */
    protected function initDetails()
    {
        $ilUser = $this->user;
                
        // no details
        return;
    }
    
    protected function initTestAssignments()
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $this->test_assignments = ilLOTestAssignments::getInstance($this->getContainerObject()->getId());
    }
    
    /**
     * Parse learning objective results.
     * @return type
     */
    protected function parseLOUserResults()
    {
        $ilUser = $this->user;
        
        $res = array();
                
        include_once "Modules/Course/classes/Objectives/class.ilLOTestAssignments.php";
        $lo_ass = ilLOTestAssignments::getInstance($this->getContainerObject()->getId());
                
        include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
        $lur = new ilLOUserResults($this->getContainerObject()->getId(), $ilUser->getId());
        foreach ($lur->getCourseResultsForUserPresentation() as $objective_id => $types) {
            // show either initial or qualified for objective
            if (isset($types[ilLOUserResults::TYPE_INITIAL])) {
                $initial_status = $types[ilLOUserResults::TYPE_INITIAL]["status"];
            }
            
            // qualified test has priority
            if (isset($types[ilLOUserResults::TYPE_QUALIFIED])) {
                $result = $types[ilLOUserResults::TYPE_QUALIFIED];
                $result["type"] = ilLOUserResults::TYPE_QUALIFIED;
                $result["initial"] = $types[ilLOUserResults::TYPE_INITIAL];
            } else {
                $result = $types[ilLOUserResults::TYPE_INITIAL];
                $result["type"] = ilLOUserResults::TYPE_INITIAL;
            }
                        
            $result["initial_status"] = $initial_status;
                        
            $result["itest"] = $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
            $result["qtest"] = $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
                                                
            $res[$objective_id] = $result;
        }
        
        return $res;
    }
    
    /**
     * Render progress bar(s)
     *
     * @param int $a_perc_result
     * @param int $a_perc_limit
     * @param string $a_css
     * @param string $a_caption
     * @param string $a_url
     * @param string $a_tt_id
     * @param string $a_tt_txt
     * @param string $a_next_step
     * @param string $a_sub (html)
     * @param int $a_sub_style
     * @return type
     */
    public static function renderProgressBar(
        $a_perc_result = null,
        $a_perc_limit = null,
        $a_css = null,
        $a_caption = null,
        $a_url = null,
        $a_tt_id = null,
        $a_tt_txt = null,
        $a_next_step = null,
        $a_sub = false,
        $a_sub_style = 30
    ) {
        global $DIC;

        $tpl = new ilTemplate("tpl.objective_progressbar.html", true, true, "Services/Container");

        if ($a_perc_result !== null) {
            $tpl->setCurrentBlock("statusbar_bl");
            $tpl->setVariable("PERC_STATUS", $a_perc_result);
            $tpl->setVariable("PERC_WIDTH", $a_perc_result);
            $tpl->setVariable("PERC_COLOR", $a_css);
            if ($a_perc_limit) {
                // :TODO: magic?
                $limit_pos = (99-(int) $a_perc_limit)*-1;
                $tpl->setVariable("LIMIT_POS", $limit_pos);
            }
            if ($a_tt_txt &&
                $a_tt_id) {
                $tpl->setVariable("TT_ID", $a_tt_id);
            }
            $tpl->parseCurrentBlock();
        }

        if ($a_caption) {
            if ($a_url) {
                include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
                $button = ilLinkButton::getInstance();
                $button->setCaption($a_caption, false);
                $button->setUrl($a_url);
                
                $tpl->setCurrentBlock("statustxt_bl");
                $tpl->setVariable("TXT_PROGRESS_STATUS", $button->render());
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("statustxt_no_link_bl");
                $tpl->setVariable("TXT_PROGRESS_STATUS_NO_LINK", $a_caption);
                $tpl->parseCurrentBlock();
            }
        }


        if ($a_next_step) {
            $tpl->setCurrentBlock("nstep_bl");
            $tpl->setVariable("TXT_NEXT_STEP", $a_next_step);
            $tpl->parseCurrentBlock();
        }

        if ($a_tt_id &&
            $a_tt_txt) {
            include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
            ilTooltipGUI::addTooltip($a_tt_id, $a_tt_txt);
        }
        
        if ($a_sub) {
            $tpl->setVariable("SUB_STYLE", ' style="padding-left: ' . $a_sub_style . 'px;"');
            $tpl->setVariable("SUB_INIT", $a_sub);
        }
        
        return $tpl->get();
    }

    /**
     * Render progress bar(s)
     *
     * @param int $a_perc_result
     * @param int $a_perc_limit
     * @param int $a_compare_value
     * @param string $a_caption
     * @param string $a_url
     * @param string $a_tt_id
     * @param string $a_tt_txt
     * @param string $a_next_step
     * @param string $a_sub (html)
     * @param int $a_sub_style
     * @param string $a_main_text
     * @param string $a_required_text
     * @return type
     */
    public static function renderProgressMeter(
        $a_perc_result = null,
        $a_perc_limit = null,
        $a_compare_value = null,
        $a_caption = null,
        $a_url = null,
        $a_tt_id = null,
        $a_tt_txt = null,
        $a_next_step = null,
        $a_sub = false,
        $a_sub_style = 30,
        $a_main_text = '',
        $a_required_text = ''
    ) {
        global $DIC;

        $tpl = new ilTemplate("tpl.objective_progressmeter.html", true, true, "Services/Container");

        $lng = $DIC->language();
        $lng->loadLanguageModule('crs');



        if (is_numeric($a_perc_result)) {
            $uiFactory = $DIC->ui()->factory();
            $uiRenderer = $DIC->ui()->renderer();

            /*
            $pMeter = $uiFactory->chart()->progressMeter()->standard(
                100,
                (int) $a_perc_result,
                (int) $a_perc_limit
            );
            */

            $pMeter = $uiFactory->chart()->progressMeter()->standard(
                100,
                (int) $a_perc_result,
                (int) $a_perc_limit,
                (int) $a_compare_value
            );

            if (strlen($a_main_text)) {
                #$pMeter = $pMeter->withMainText($a_main_text);
            }
            if (strlen($a_required_text)) {
                #$pMeter = $pMeter->withRequiredText($a_required_text);
            }
            $tpl->setVariable('PROGRESS_METER', $uiRenderer->render($pMeter));
        }

        /*
        if($a_caption)
        {
            if($a_url)
            {
                include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
                $button = ilLinkButton::getInstance();
                $button->setCaption($a_caption, false);
                $button->setUrl($a_url);

                $tpl->setCurrentBlock("statustxt_bl");
                $tpl->setVariable("TXT_PROGRESS_STATUS", $button->render());
                $tpl->parseCurrentBlock();
            }
            else
            {
                $tpl->setCurrentBlock("statustxt_no_link_bl");
                $tpl->setVariable("TXT_PROGRESS_STATUS_NO_LINK", $a_caption);
                $tpl->parseCurrentBlock();
            }
        }
        */

        if ($a_tt_id &&
            $a_tt_txt) {
            include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
            ilTooltipGUI::addTooltip($a_tt_id, $a_tt_txt);
        }

        if ($a_sub) {
            $tpl->setVariable("SUB_STYLE", ' style="padding-left: ' . $a_sub_style . 'px;"');
            $tpl->setVariable("SUB_INIT", $a_sub);
        }

        return $tpl->get();
    }

    /**
     * Get objective result summary
     *
     * @param bool
     * @param int
     * @param array
     * @todo refactor to presentation class
     */
    public static function getObjectiveResultSummary($a_has_initial_test, $a_objective_id, $a_lo_result)
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule('crs');

        $is_qualified =
            ($a_lo_result["type"] == ilLOUserResults::TYPE_QUALIFIED);
        $is_qualified_initial =
            (
                $a_lo_result['type'] == ilLOUserResults::TYPE_INITIAL &&
                ilLOSettings::getInstanceByObjId($a_lo_result['course_id'])->isInitialTestQualifying()
            );
        $has_completed =
            ($a_lo_result["status"] == ilLOUserResults::STATUS_COMPLETED);

        $next_step = $progress_txt = $bar_color = $test_url = $initial_sub = null;

        if (
            $is_qualified ||
            $is_qualified_initial) {
            if ($has_completed) {
                $next_step = $lng->txt("crs_loc_progress_objective_complete");
            } else {
                $next_step = $lng->txt("crs_loc_progress_do_qualifying_again");
            }
        }
        // initial test
        else {
            if ($a_lo_result["status"]) {
                $next_step =
                    $has_completed ?
                        $lng->txt("crs_loc_progress_do_qualifying") :
                        $lng->txt("crs_loc_suggested");
            } else {
                $next_step = (bool) $a_has_initial_test ?
                    $lng->txt("crs_loc_progress_no_result_do_initial") :
                    $lng->txt("crs_loc_progress_no_result_no_initial");
            }
        }
        return $next_step;
    }

    /**
     * Render progressbar(s) for given objective and result data
     *
     * @param bool $a_has_initial_test
     * @param int $a_objective_id
     * @param bool $a_lo_result
     * @param bool $a_list_mode
     * @param bool $a_sub
     * @param mixed $a_tt_suffix
     * @return string
     */
    public static function buildObjectiveProgressBar($a_has_initial_test, $a_objective_id, array $a_lo_result, $a_list_mode = false, $a_sub = false, $a_tt_suffix = null)
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule('crs');
        
        // tooltip (has to be unique!)
        
        $tooltip_id = "crsobjtvusr_" . $a_objective_id . "_" . $a_lo_result["type"] . "_" . ((int) $a_sub);
        if ($a_tt_suffix !== null) {
            $tooltip_id .= "_" . $a_tt_suffix;
        }
                            
        $tt_txt = sprintf(
            $lng->txt("crs_loc_tt_info"),
            $a_lo_result["result_perc"],
            $a_lo_result["limit_perc"]
        );
        
        
        include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
                                
        $is_qualified = ($a_lo_result["type"] == ilLOUserResults::TYPE_QUALIFIED);
        $is_qualified_initial = ($a_lo_result['type'] == ilLOUserResults::TYPE_INITIAL &&
            ilLOSettings::getInstanceByObjId($a_lo_result['course_id'])->isInitialTestQualifying());
        $has_completed = ($a_lo_result["status"] == ilLOUserResults::STATUS_COMPLETED);
            
        $next_step = $progress_txt = $bar_color = $test_url = $initial_sub = null;

        $compare_value = null;

        if ($is_qualified ||
            $is_qualified_initial) {
            $progress_txt = $lng->txt("crs_loc_progress_result_qtest");
            $tt_txt = $lng->txt("crs_loc_tab_qtest") . ": " . $tt_txt;
                                                                                            
            if ($has_completed) {
                $next_step = $lng->txt("crs_loc_progress_objective_complete");
                $bar_color = "ilCourseObjectiveProgressBarCompleted";
                
                // render 2nd progressbar if there is also an initial test
                if ($is_qualified &&
                    $a_has_initial_test &&
                    is_array($a_lo_result["initial"])) {
                    $a_lo_result["initial"]["itest"] = $a_lo_result["itest"];
                    
                    // force list mode to get rid of next step
                    #$initial_sub = self::buildObjectiveProgressBar(true, $a_objective_id, $a_lo_result["initial"], true, true, $a_tt_suffix);
                    $compare_value = $a_lo_result['initial']['result_perc'];
                }
            } else {
                $next_step = $lng->txt("crs_loc_progress_do_qualifying_again");
                $bar_color = "ilCourseObjectiveProgressBarFailed";
            }
        }
        // initial test
        else {
            if ($a_lo_result["status"]) {
                $progress_txt = $lng->txt("crs_loc_progress_result_itest");
                $tt_txt = $lng->txt("crs_loc_tab_itest") . ": " . $tt_txt;
                
                $bar_color = "ilCourseObjectiveProgressBarNeutral";
                $next_step = $has_completed
                    ? $lng->txt("crs_loc_progress_do_qualifying")
                    : $lng->txt("crs_loc_suggested");
            }
            // not attempted: no progress bar
            else {
                $next_step = (bool) $a_has_initial_test
                    ? $lng->txt("crs_loc_progress_no_result_do_initial")
                    : $lng->txt("crs_loc_progress_no_result_no_initial");
            }
        }
        
        // link to test results
        // - first try to fetch a link for qualifying test results
        if ($a_lo_result["qtest"]) {
            $test_url = ilLOUtils::getTestResultLinkForUser($a_lo_result["qtest"], $a_lo_result["user_id"]);
        }
        // - when no qualifiying test results link was fetched, try for initial test
        if (!$test_url && $a_lo_result["itest"]) {
            $test_url = ilLOUtils::getTestResultLinkForUser($a_lo_result["itest"], $a_lo_result["user_id"]);
        }

        $main_text = $lng->txt('crs_loc_itest_info');
        if ($a_lo_result['type'] == ilLOSettings::TYPE_TEST_QUALIFIED) {
            $main_text = $lng->txt('crs_loc_qtest_info');
        }



        return self::renderProgressMeter(
            $a_lo_result["result_perc"],
            $a_lo_result["limit_perc"],
            $compare_value,
            $progress_txt,
            $test_url,
            $tooltip_id,
            $tt_txt,
            $a_list_mode
                ? null
                : $next_step,
            $initial_sub,
            $a_list_mode
                ? 30
                : 10,
            $main_text,
            $lng->txt('crs_lobj_pm_min_goal')
        );
    }

    /**
     * @param \ilCourseObjective $a_objective
     * @param array|null $a_lo_result
     * @return string
     * @throws \ilTemplateException
     */
    protected function buildAccordionTitle(ilCourseObjective $a_objective, array $a_lo_result = null)
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $ui_factory = $DIC->ui()->factory();

        $tpl = new ilTemplate("tpl.objective_accordion_title.html", true, true, "Services/Container");
            
        if ($a_lo_result) {
            $tpl->setVariable(
                "PROGRESS_BAR",
                self::buildObjectiveProgressBar(
                    (bool) $this->loc_settings->worksWithInitialTest(),
                    $a_objective->getObjectiveId(),
                    $a_lo_result
                )
            );
        }
        
        $tpl->setVariable("TITLE", $this->lng->txt("crs_loc_learning_objective") . ": " . trim($a_objective->getTitle()));
        $tpl->setVariable("DESCRIPTION", nl2br(trim($a_objective->getDescription())));

        $this->logger->dump($a_lo_result);

        $initial_res = null;
        $initial_lim = null;
        if ($this->loc_settings->worksWithInitialTest()) {
            if (array_key_exists('initial', $a_lo_result)) {
                $initial_res = (int) $a_lo_result['initial']['result_perc'];
                $initial_lim = (int) $a_lo_result['initial']['limit_perc'];
            }
            if (
                $a_lo_result['type'] == ilLOUserResults::TYPE_INITIAL &&
                isset($a_lo_result['result_perc'])
            ) {
                $initial_res = (int) $a_lo_result['result_perc'];
                $initial_lim = (int) $a_lo_result['limit_perc'];
            }
        }

        if ($initial_res !== null) {
            $link = \ilLOUtils::getTestResultLinkForUser(
                $a_lo_result["itest"],
                $a_lo_result["user_id"]
            );

            if (strlen($link)) {
                $tpl->setCurrentBlock('i_with_link');
                $tpl->setVariable(
                    'IBTN',
                    $renderer->render(
                        $ui_factory->button()->shy(
                            $this->lng->txt('crs_objective_result_details'),
                            $link
                        )
                    )
                );
                $tpl->parseCurrentBlock();
            }


            $tpl->setCurrentBlock('res_initial');
            $tpl->setVariable(
                'IRESULT',
                sprintf(
                    $this->lng->txt('crs_objective_result_summary_initial'),
                    (int) $initial_res . '%',
                    (int) $initial_lim . '%'
                )
            );
            $tpl->parseCurrentBlock();
        }

        $qual_res = null;
        $qual_lim = null;

        if ($a_lo_result['type'] == ilLOUserResults::TYPE_QUALIFIED) {
            $qual_res = (int) $a_lo_result['result_perc'];
            $qual_lim = (int) $a_lo_result['limit_perc'];
        }

        if ($qual_res !== null) {
            $link = \ilLOUtils::getTestResultLinkForUser(
                $a_lo_result["qtest"],
                $a_lo_result["user_id"]
            );

            if (strlen($link)) {
                $tpl->setCurrentBlock('q_with_link');
                $tpl->setVariable(
                    'QBTN',
                    $renderer->render(
                        $ui_factory->button()->shy(
                            $this->lng->txt('crs_objective_result_details'),
                            $link
                        )
                    )
                );
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('res_qualifying');
            $tpl->setVariable(
                'QRESULT',
                sprintf(
                    $this->lng->txt('crs_objective_result_summary_qualifying'),
                    (int) $qual_res . '%',
                    (int) $qual_lim . '%'
                )
            );
            $tpl->parseCurrentBlock();
        }

        $this->logger->dump($a_lo_result);


        $summary = self::getObjectiveResultSummary(
            (bool) $this->loc_settings->worksWithInitialTest(),
            $a_objective->getObjectiveId(),
            $a_lo_result
        );
        if (strlen($summary)) {
            $tpl->setCurrentBlock('objective_summary');
            $tpl->setVariable('SUMMARY_TXT', $summary);
            $tpl->parseCurrentBlock();
        }

        // #15510
        $tpl->setVariable("ANCHOR_ID", "objtv_acc_" . $a_objective->getObjectiveId());
                
        return $tpl->get();
    }
    
    protected function buildAccordionContent(array $a_items)
    {
        $tpl = new ilTemplate("tpl.objective_accordion_content.html", true, true, "Services/Container");
        foreach ($a_items as $item) {
            $tpl->setCurrentBlock("items_bl");
            $tpl->setVariable("ITEM", $item);
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }
    
    /**
     * show action button
     *
     * @access protected
     * @param
     * @return
     */
    protected function showButton($a_cmd, $a_text, $a_target = '', $a_id = "")
    {
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        
        // #11842
        $ilToolbar->addButton(
            $a_text,
            $ilCtrl->getLinkTarget($this->getContainerGUI(), $a_cmd),
            $a_target,
            "",
            '',
            $a_id
        );
    }
}
