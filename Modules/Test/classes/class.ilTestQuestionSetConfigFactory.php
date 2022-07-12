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
     */
    public function getQuestionSetConfig() : ilTestQuestionSetConfig
    {
        return $this->getQuestionSetConfigByType();
    }
    
    /**
     * creates and returns an instance of a test question set config
     * that corresponds to the passed question set type (test mode)
     */
    public function getQuestionSetConfigByType() : ilTestQuestionSetConfig
    {
        if ($this->testQuestionSetConfig === null) {
            if ($this->testOBJ->isFixedTest()) {
                $this->testQuestionSetConfig = new ilTestFixedQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->pluginAdmin,
                    $this->testOBJ
                );
            }
            if ($this->testOBJ->isRandomTest()) {
                $this->testQuestionSetConfig = new ilTestRandomQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->pluginAdmin,
                    $this->testOBJ
                );
            }
            
            if ($this->testOBJ->isDynamicTest()) {
                $this->testQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->pluginAdmin,
                    $this->testOBJ
                );
            }

            $this->testQuestionSetConfig->loadFromDb();
        }

        return $this->testQuestionSetConfig;
    }
}
