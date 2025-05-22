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

declare(strict_types=1);

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;

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
    private ?ilTestQuestionSetConfig $testQuestionSetConfig = null;

    public function __construct(
        protected readonly ilTree $tree,
        protected readonly ilDBInterface $db,
        protected readonly ilLanguage $lng,
        protected readonly TestLogger $logger,
        protected readonly ilComponentRepository $component_repository,
        protected readonly ilObjTest $test_obj,
        protected readonly GeneralQuestionPropertiesRepository $questionrepository
    ) {
    }

    /**
     * creates and returns an instance of a test question set config
     * that corresponds to the test's current question set type (test mode)
     */
    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        if ($this->testQuestionSetConfig === null) {
            if ($this->test_obj->isFixedTest()) {
                $this->testQuestionSetConfig = new ilTestFixedQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->lng,
                    $this->logger,
                    $this->component_repository,
                    $this->test_obj,
                    $this->questionrepository
                );
            }
            if ($this->test_obj->isRandomTest()) {
                $this->testQuestionSetConfig = new ilTestRandomQuestionSetConfig(
                    $this->tree,
                    $this->db,
                    $this->lng,
                    $this->logger,
                    $this->component_repository,
                    $this->test_obj,
                    $this->questionrepository
                );
            }

            $this->testQuestionSetConfig->loadFromDb();
        }

        return $this->testQuestionSetConfig;
    }
}
