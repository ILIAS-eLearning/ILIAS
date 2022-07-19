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
 * Factory for test question set config
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestQuestionSetConfigFactory
{
    private ilTree $tree;
    private ilDBInterface $db;
    private ilComponentRepository $component_repository;
    private ilObjTest $testOBJ;
    private ?ilTestQuestionSetConfig $testQuestionSetConfig = null;

    public function __construct(
        ilTree $tree,
        ilDBInterface $db,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ
    ) {
        $this->tree = $tree;
        $this->db = $db;
        $this->component_repository = $component_repository;
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
                    $this->component_repository,
                    $this->testOBJ
                );
            }
            if ($this->testOBJ->isRandomTest()) {
                $this->testQuestionSetConfig = new ilTestRandomQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->component_repository,
                    $this->testOBJ
                );
            }
            
            if ($this->testOBJ->isDynamicTest()) {
                $this->testQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->component_repository,
                    $this->testOBJ
                );
            }

            $this->testQuestionSetConfig->loadFromDb();
        }

        return $this->testQuestionSetConfig;
    }
}
