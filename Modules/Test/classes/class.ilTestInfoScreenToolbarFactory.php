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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestInfoScreenToolbarFactory
{
    /**
     * @var integer
     */
    private $testRefId;

    /**
     * @var ilObjTest
     */
    private $testOBJ;

    /**
     * @var ilTestQuestionSetConfigFactory
     */
    private $testQuestionSetConfigFactory;

    /**
     * @var ilTestPlayerFactory
     */
    private $testPlayerFactory;

    /**
     * @var ilTestSessionFactory
     */
    private $testSessionFactory;

    /**
     * @var ilTestSequenceFactory
     */
    private $testSequenceFactory;

    /**
     * @return int
     */
    public function getTestRefId(): int
    {
        return $this->testRefId;
    }

    /**
     * @param int $testRefId
     */
    public function setTestRefId($testRefId)
    {
        $this->testRefId = $testRefId;
    }

    /**
     * @return ilObjTest
     */
    public function getTestOBJ(): ilObjTest
    {
        return $this->testOBJ;
    }

    /**
     * @param ilObjTest $testOBJ
     */
    public function setTestOBJ($testOBJ)
    {
        $this->testOBJ = $testOBJ;
    }

    protected function ensureInitialised()
    {
        $this->ensureTestObjectInitialised();

        $d = $GLOBALS['DIC'];

        $this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory(
            $d['tree'],
            $d['ilDB'],
            $d['component.repository'],
            $this->getTestOBJ()
        );

        $this->testPlayerFactory = new ilTestPlayerFactory($this->getTestOBJ());
        $this->testSessionFactory = new ilTestSessionFactory($this->getTestOBJ());

        $this->testSequenceFactory = new ilTestSequenceFactory(
            $d['ilDB'],
            $d['lng'],
            $d['component.repository'],
            $this->getTestOBJ()
        );
    }

    private function ensureTestObjectInitialised()
    {
        if (!($this->testOBJ instanceof ilObjTest)) {
            $this->testOBJ = ilObjectFactory::getInstanceByRefId($this->testRefId);
        }
    }

    public function getToolbarInstance(): ilTestInfoScreenToolbarGUI
    {
        $this->ensureInitialised();

        $d = $GLOBALS['DIC'];

        $toolbar = new ilTestInfoScreenToolbarGUI($d['ilDB'], $d['ilAccess'], $d['ilCtrl'], $d['lng'], $d['component.repository']);

        $toolbar->setTestOBJ($this->getTestOBJ());
        $toolbar->setTestPlayerGUI($this->testPlayerFactory->getPlayerGUI());

        $testQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
        $testSession = $this->testSessionFactory->getSession();
        $testSequence = $this->testSequenceFactory->getSequenceByTestSession($testSession);
        $testSequence->loadFromDb();
        $testSequence->loadQuestions($testQuestionSetConfig, new ilTestDynamicQuestionSetFilterSelection());

        $toolbar->setTestQuestionSetConfig($testQuestionSetConfig);
        $toolbar->setTestSession($testSession);
        $toolbar->setTestSequence($testSequence);

        return $toolbar;
    }
}
