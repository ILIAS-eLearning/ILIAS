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

namespace ILIAS\TestQuestionPool\ExportImport\Export;

use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\DataCollector;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilObjQuestionPool;

/**
 * Collector to aggregate data from the question pool for export.
 */
class QuestionPoolCollector implements DataCollector
{
    use CollectsQuestions;

    /** @var array<int, GeneralQuestionProperties> $questions */
    private ?array $questions = null;
    private ?ilObjQuestionPool $pool_object = null;

    public function __construct(
        private readonly GeneralQuestionPropertiesRepository $question_repository,
        private readonly ilDBInterface $db,
        private readonly ObjectId $pool_id
    ) {
    }

    /**
     * Get the ID of the question pool.
     *
     * @return ObjectId
     */
    public function getObjectId(): ObjectId
    {
        return $this->pool_id;
    }

    /**
     * Get the object of the question pool. It will be loaded from the database if not already loaded.
     */
    public function getObject(): ilObjQuestionPool
    {
        if ($this->pool_object === null) {
            $this->pool_object = new ilObjQuestionPool($this->pool_id->toInt(), false);
            $this->pool_object->read();
        }

        return $this->pool_object;
    }

    /**
     * Collect the question properties for all questions in the question pool.
     *
     * @return array<int, GeneralQuestionProperties>
     */
    public function getQuestionProperties(): array
    {
        if ($this->questions === null) {
            $this->questions = $this->question_repository->getForParentObjectId($this->pool_id->toInt());
        }
        return $this->questions;
    }

    private function database(): ilDBInterface
    {
        return $this->db;
    }
}
