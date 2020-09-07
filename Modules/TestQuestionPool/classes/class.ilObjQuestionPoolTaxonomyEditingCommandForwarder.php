<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * class can be used as forwarder for taxonomy editing context
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilObjQuestionPoolTaxonomyEditingCommandForwarder
{
    /**
     * object instance of current question
     *
     * @var ilObjQuestionPool
     */
    protected $poolOBJ = null;
    
    /**
     * global $db
     *
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * global $pluginAdmin
     *
     * @var ilPluginAdmin
     */
    protected $pluginAdmin = null;
    
    /**
     * global $ilCtrl
     *
     * @var ilCtrl
     */
    protected $ctrl = null;

    /**
     * global $ilCtrl
     *
     * @var ilCtrl
     */
    protected $tabs = null;

    /**
     * global $ilCtrl
     *
     * @var ilCtrl
     */
    protected $lng = null;
    
    /**
     * Constructor
     *
     * @param ilObjQuestionPool $poolOBJ
     * @param ilDBInterface $db
     * @param ilPluginAdmin $pluginAdmin
     * @param ilCtrl $ctrl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     */
    public function __construct(ilObjQuestionPool $poolOBJ, ilDBInterface $db, ilPluginAdmin $pluginAdmin, ilCtrl $ctrl, ilTabsGUI $tabs, ilLanguage $lng)
    {
        $this->poolOBJ = $poolOBJ;
        $this->db = $db;
        $this->pluginAdmin = $pluginAdmin;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
    }
    
    /**
     * forward method
     */
    public function forward()
    {
        $this->tabs->setTabActive('settings');
        $this->lng->loadLanguageModule('tax');

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->pluginAdmin);

        $questionList->setParentObjId($this->poolOBJ->getId());

        $questionList->load();

        require_once 'Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php';
        $taxGUI = new ilObjTaxonomyGUI();
        
        $taxGUI->setAssignedObject($this->poolOBJ->getId());
        $taxGUI->setMultiple(true);
            
        $taxGUI->activateAssignedItemSorting($questionList, 'qpl', $this->poolOBJ->getId(), 'quest');

        $this->ctrl->forwardCommand($taxGUI);
    }
}
