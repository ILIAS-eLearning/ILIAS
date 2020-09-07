<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for test question set config
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestQuestionSetConfigFactory
{
    /**
     * singleton instance of test question set config
     *
     * @var ilTestQuestionSetConfig
     */
    private $testQuestionSetConfig = null;
    
    /**
     * global $tree object instance
     *
     * @var ilTree
     */
    private $tree = null;
    
    /**
     * object instance of $ilDB
     *
     * @var ilDBInterface
     */
    private $db = null;

    /**
     * object instance of $ilPluginAdmin
     *
     * @var ilPluginAdmin
     */
    private $pluginAdmin = null;

    /**
     * object instance of current test
     *
     * @var ilObjTest
     */
    private $testOBJ = null;
    
    /**
     * constructor
     *
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilTree $tree, ilDBInterface $db, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->tree = $tree;
        $this->db = $db;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * creates and returns an instance of a test question set config
     * that corresponds to the test's current question set type (test mode)
     *
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfig()
    {
        return $this->getQuestionSetConfigByType($this->testOBJ->getQuestionSetType());
    }
    
    /**
     * creates and returns an instance of a test question set config
     * that corresponds to the passed question set type (test mode)
     *
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfigByType($questionSetType)
    {
        if ($this->testQuestionSetConfig === null) {
            switch ($questionSetType) {
                case ilObjTest::QUESTION_SET_TYPE_FIXED:

                    require_once 'Modules/Test/classes/class.ilTestFixedQuestionSetConfig.php';
                    $this->testQuestionSetConfig = new ilTestFixedQuestionSetConfig(
                        $this->tree,
                        $this->db,
                        $this->pluginAdmin,
                        $this->testOBJ
                    );
                    break;

                case ilObjTest::QUESTION_SET_TYPE_RANDOM:

                    require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
                    $this->testQuestionSetConfig = new ilTestRandomQuestionSetConfig(
                        $this->tree,
                        $this->db,
                        $this->pluginAdmin,
                        $this->testOBJ
                    );
                    break;

                case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:

                    require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
                    $this->testQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
                        $this->tree,
                        $this->db,
                        $this->pluginAdmin,
                        $this->testOBJ
                    );
                    break;
            }

            $this->testQuestionSetConfig->loadFromDb();
        }

        return $this->testQuestionSetConfig;
    }
}
