<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilObjTestDynamicQuestionSetConfigGUI: ilPropertyFormGUI
 */
class ilObjTestDynamicQuestionSetConfigGUI
{
    /**
     * command constants
     */
    const CMD_SHOW_FORM = 'showForm';
    const CMD_SAVE_FORM = 'saveForm';
    const CMD_GET_TAXONOMY_OPTIONS_ASYNC = 'getTaxonomyOptionsAsync';
    
    /**
     * global $ilCtrl object
     *
     * @var ilCtrl
     */
    protected $ctrl = null;
    
    /**
     * global $ilAccess object
     *
     * @var ilAccess
     */
    protected $access = null;
    
    /**
     * global $ilTabs object
     *
     * @var ilTabsGUI
     */
    protected $tabs = null;
    
    /**
     * global $lng object
     *
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * global $tpl object
     *
     * @var ilGlobalTemplateInterface
     */
    protected $tpl = null;
    
    /**
     * global $ilDB object
     *
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * global $tree object
     *
     * @var ilTree
     */
    protected $tree = null;
    
    /**
     * object instance for current test
     *
     * @var ilObjTest
     */
    protected $testOBJ = null;
    
    /**
     * object instance managing the dynamic question set config
     *
     * @var ilObjTestDynamicQuestionSetConfig
     */
    protected $questionSetConfig = null;
    
    const QUESTION_ORDERING_TYPE_UPDATE_DATE = 'ordering_by_date';
    const QUESTION_ORDERING_TYPE_TAXONOMY = 'ordering_by_tax';
    
    /**
     * Constructor
     */
    public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilLanguage $lng, ilGlobalTemplateInterface $tpl, ilDBInterface $db, ilTree $tree, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->tree = $tree;
        $this->pluginAdmin = $pluginAdmin;

        $this->testOBJ = $testOBJ;
        
        $this->questionSetConfig = new ilObjTestDynamicQuestionSetConfig($this->tree, $this->db, $this->pluginAdmin, $this->testOBJ);
    }
    
    /**
     * Command Execution
     */
    public function executeCommand()
    {
        // allow only write access
        
        if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
        }
        
        // activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)
        
        $this->tabs->activateTab('assQuestions');
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("ctm_cannot_be_changed"));
    }
}
