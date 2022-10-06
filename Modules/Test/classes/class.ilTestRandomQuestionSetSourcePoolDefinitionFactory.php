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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionFactory
{
    /**
     * @var ilDBInterface
     */
    private $db = null;

    /**
     * @var ilObjTest
     */
    private $testOBJ = null;

    /**
     * @param ilDBInterface $db
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ)
    {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function getSourcePoolDefinitionByOriginalPoolData($originalPoolData): ilTestRandomQuestionSetSourcePoolDefinition
    {
        $sourcePoolDefinition = $this->buildDefinitionInstance();

        $sourcePoolDefinition->setPoolId($originalPoolData['qpl_id']);
        $sourcePoolDefinition->setPoolRefId($originalPoolData['qpl_ref_id']);
        $sourcePoolDefinition->setPoolTitle($originalPoolData['qpl_title']);
        $sourcePoolDefinition->setPoolPath($originalPoolData['qpl_path']);
        $sourcePoolDefinition->setPoolQuestionCount($originalPoolData['count']);

        return $sourcePoolDefinition;
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function getSourcePoolDefinitionByDefinitionId($definitionId): ilTestRandomQuestionSetSourcePoolDefinition
    {
        $sourcePoolDefinition = $this->buildDefinitionInstance();

        $sourcePoolDefinition->loadFromDb($definitionId);

        return $sourcePoolDefinition;
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function getEmptySourcePoolDefinition(): ilTestRandomQuestionSetSourcePoolDefinition
    {
        return $this->buildDefinitionInstance();
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    private function buildDefinitionInstance(): ilTestRandomQuestionSetSourcePoolDefinition
    {
        return new ilTestRandomQuestionSetSourcePoolDefinition($this->db, $this->testOBJ);
    }
}
