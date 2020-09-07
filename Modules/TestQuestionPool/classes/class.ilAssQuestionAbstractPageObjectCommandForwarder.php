<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * abstract parent class for page object forwarders
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
abstract class ilAssQuestionAbstractPageObjectCommandForwarder
{
    /**
     * object instance of current question
     *
     * @access protected
     * @var assQuestion
     */
    protected $questionOBJ = null;
    
    /**
     * global $ilCtrl
     *
     * @access protected
     * @var ilCtrl
     */
    protected $ctrl = null;

    /**
     * global $ilCtrl
     *
     * @access protected
     * @var ilCtrl
     */
    protected $tabs = null;

    /**
     * global $ilCtrl
     *
     * @access protected
     * @var ilCtrl
     */
    protected $lng = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param assQuestion $questionOBJ
     * @param ilCtrl $ctrl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     */
    public function __construct(assQuestion $questionOBJ, ilCtrl $ctrl, ilTabsGUI $tabs, ilLanguage $lng)
    {
        $this->questionOBJ = $questionOBJ;
        
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;

        $this->tabs->clearTargets();
        
        $this->lng->loadLanguageModule('content');
    }

    /**
     * this is the actual forward method that is to be implemented
     * by derived forwarder classes
     */
    abstract public function forward();
    
    /**
     * ensures an existing page object with giben type/id
     *
     * @access protected
     */
    abstract protected function ensurePageObjectExists($pageObjectType, $pageObjectId);
    
    /**
     * instantiates, initialises and returns a page object gui object
     *
     * @access protected
     * @return page object gui object
     */
    abstract protected function getPageObjectGUI($pageObjectType, $pageObjectId);
}
