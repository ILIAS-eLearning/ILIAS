<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
require_once 'Modules/Test/classes/class.ilTestPlayerFactory.php';
require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
require_once 'Modules/Test/classes/class.ilTestSequenceFactory.php';
require_once 'Modules/Test/classes/class.ilTestDynamicQuestionSetFilterSelection.php';
require_once 'Modules/Test/classes/toolbars/class.ilTestInfoScreenToolbarGUI.php';

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
    public function getTestRefId()
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
    public function getTestOBJ()
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
            $d['ilPluginAdmin'],
            $this->getTestOBJ()
        );
        
        $this->testPlayerFactory = new ilTestPlayerFactory($this->getTestOBJ());
        $this->testSessionFactory = new ilTestSessionFactory($this->getTestOBJ());
        
        $this->testSequenceFactory = new ilTestSequenceFactory(
            $d['ilDB'],
            $d['lng'],
            $d['ilPluginAdmin'],
            $this->getTestOBJ()
        );
    }
    
    private function ensureTestObjectInitialised()
    {
        if (!($this->testOBJ instanceof ilObjTest)) {
            $this->testOBJ = ilObjectFactory::getInstanceByRefId($this->testRefId);
        }
    }
    
    public function getToolbarInstance()
    {
        $this->ensureInitialised();
        
        $d = $GLOBALS['DIC'];
        
        $toolbar = new ilTestInfoScreenToolbarGUI($d['ilDB'], $d['ilAccess'], $d['ilCtrl'], $d['lng'], $d['ilPluginAdmin']);
        
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
