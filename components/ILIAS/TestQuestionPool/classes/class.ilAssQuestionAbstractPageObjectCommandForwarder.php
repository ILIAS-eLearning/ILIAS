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
    protected \ILIAS\TestQuestionPool\InternalRequestService $request;
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

        global $DIC;
        $this->request = $DIC->testQuestionPool()->internal()->request();
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
    abstract protected function ensurePageObjectExists($pageObjectType, $pageObjectId): void;

    /**
     * instantiates, initialises and returns a page object gui object
     */
    abstract protected function getPageObjectGUI($pageObjectType, $pageObjectId);
}
