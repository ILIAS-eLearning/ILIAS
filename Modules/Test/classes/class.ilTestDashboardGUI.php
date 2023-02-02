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
 * Class ilTestDashboardGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestDashboardGUI: ilTestParticipantsGUI
 * @ilCtrl_Calls ilTestDashboardGUI: ilTestParticipantsTimeExtensionGUI
 */
class ilTestDashboardGUI
{
    /**
     * @var ilObjTest
     */
    protected $testObj;

    /**
     * @var ilTestQuestionSetConfig
     */
    protected $questionSetConfig;

    /**
     * @var ilTestAccess
     */
    protected $testAccess;

    /**
     * @var ilTestTabsManager
     */
    protected $testTabs;

    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;

    /**
     * ilTestDashboardGUI constructor.
     * @param ilObjTest $testObj
     */
    public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
    {
        $this->testObj = $testObj;
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return ilObjTest
     */
    public function getTestObj(): ilObjTest
    {
        return $this->testObj;
    }

    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }

    /**
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->questionSetConfig;
    }

    /**
     * @param ilTestQuestionSetConfig $questionSetConfig
     */
    public function setQuestionSetConfig($questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return ilTestAccess
     */
    public function getTestAccess(): ilTestAccess
    {
        return $this->testAccess;
    }

    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess($testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * @return ilTestTabsManager
     */
    public function getTestTabs(): ilTestTabsManager
    {
        return $this->testTabs;
    }

    /**
     * @param ilTestTabsManager $testTabs
     */
    public function setTestTabs($testTabs)
    {
        $this->testTabs = $testTabs;
    }

    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
    {
        return $this->objectiveParent;
    }

    /**
     * @param ilTestObjectiveOrientedContainer $objectiveParent
     */
    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objectiveParent)
    {
        $this->objectiveParent = $objectiveParent;
    }

    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        if (!$this->getTestAccess()->checkManageParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->getTestTabs()->getDashboardSubTabs();

        switch ($DIC->ctrl()->getNextClass()) {
            case 'iltestparticipantsgui':

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);

                require_once 'Modules/Test/classes/class.ilTestParticipantsGUI.php';
                $gui = new ilTestParticipantsGUI($this->getTestObj(), $this->getQuestionSetConfig());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'iltestparticipantstimeextensiongui':

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_TIME_EXTENSION);

                require_once 'Modules/Test/classes/class.ilTestParticipantsTimeExtensionGUI.php';
                $gui = new ilTestParticipantsTimeExtensionGUI($this->getTestObj());
                $DIC->ctrl()->forwardCommand($gui);
                break;
        }
    }
}
