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

namespace ILIAS\LearningSequence\Content\Condition\OutputCondition\LearningProgressOutputConditions;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Link\Bulky;
use ilLPStatus;
use ilObject;
use LogicException;

final class LearningProgressOutputAwareCondition extends AbstractCondition implements
    OutputConditionInterface,
    SubtypeAwareInterface
{
    protected const string NAME = 'learning_progress_output';
    private const string SETTINGS_TABLE = 'lso_c_learning_progress_output';
    private const string SUBTYPE_NOT_ATTEMPTED = 'not_attempted';
    private const string SUBTYPE_IN_PROGRESS = 'in_progress';
    private const string SUBTYPE_COMPLETED = 'completed';
    private const string SUBTYPE_FAILED = 'failed';

    /**
     * @inheritDoc
     */
    public function setupSteps(): array
    {
        $this->assertContextSet();

        return [
            $this->ui_factory->menu()->sub($this->lang->txt($this->getName()), [
                $this->buildSubtypeStep(self::SUBTYPE_NOT_ATTEMPTED),
                $this->buildSubtypeStep(self::SUBTYPE_IN_PROGRESS),
                $this->buildSubtypeStep(self::SUBTYPE_COMPLETED),
                $this->buildSubtypeStep(self::SUBTYPE_FAILED)
            ])
        ];
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return match ($this->getSubtype()) {
            self::SUBTYPE_NOT_ATTEMPTED => $this->isNotAttempted(),
            self::SUBTYPE_IN_PROGRESS => $this->isInProgress(),
            self::SUBTYPE_COMPLETED => $this->isCompleted(),
            self::SUBTYPE_FAILED => $this->isFailed(),
            default => throw new LogicException('Unknown learning progress subtype.')
        };
    }

    /**
     * @inheritDoc
     */
    public static function migrate(): array
    {
        return [
            new TableDefinition(
                tableName: self::SETTINGS_TABLE,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    'subtype' => ['type' => 'text', 'length' => 32, 'notnull' => true],
                ],
                primaryKeys: ['condition_id']
            )
        ];
    }

    /**
     * Get the subtype of the learning progress condition.
     *
     * @return string
     * @throws LogicException if the subtype is not set or not stored
     */
    public function getSubtype(): string
    {
        if ($this->subtype !== null) {
            return $this->subtype;
        }

        if ($this->condition_id === null) {
            throw new LogicException('Learning progress subtype is not set.');
        }

        $res = $this->getDatabase()->queryF(
            'SELECT subtype FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );
        $row = $this->getDatabase()->fetchAssoc($res);

        if ($row === null || !is_string($row['subtype'])) {
            throw new LogicException('Learning progress subtype is not stored.');
        }

        $this->setSubtype($row['subtype']);
        return (string) $this->subtype;
    }

    /**
     * @inheritDoc
     */
    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            'subtype' => ['text', $this->requireSubtype()]
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function editConditionData(int $condition_id): void
    {
        $this->getDatabase()->update(
            self::SETTINGS_TABLE,
            [
                'subtype' => ['text', $this->requireSubtype()]
            ],
            [
                'condition_id' => ['integer', $condition_id]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function deleteConditionData(int $condition_id): void
    {
        $this->getDatabase()->manipulateF(
            'DELETE FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
    }

    /**
     * @inheritDoc
     */
    public function buildSubtypeStep(string $subtype): Bulky
    {
        return $this->buildStep(
            ['subtype' => $subtype],
            $this->getSubtypeLabel($subtype),
            self::CREATE_COMMAND
        );
    }

    /**
     * Get the label for a given subtype.
     *
     * @param string $subtype
     * @return string
     * @throws LogicException if the subtype is unknown
     */
    public function getSubtypeLabel(string $subtype): string
    {
        return match ($subtype) {
            self::SUBTYPE_NOT_ATTEMPTED => $this->lang->txt('learning_progress_not_attempted'),
            self::SUBTYPE_IN_PROGRESS => $this->lang->txt('learning_progress_in_progress'),
            self::SUBTYPE_COMPLETED => $this->lang->txt('learning_progress_completed'),
            self::SUBTYPE_FAILED => $this->lang->txt('failed'),
            default => throw new LogicException('Unknown learning progress subtype.')
        };
    }

    /**
     * @return string[]
     */
    public function getSupportedSubtypes(): array
    {
        return [
            self::SUBTYPE_NOT_ATTEMPTED,
            self::SUBTYPE_IN_PROGRESS,
            self::SUBTYPE_COMPLETED,
            self::SUBTYPE_FAILED
        ];
    }

    /**
     * Require the subtype to be set, otherwise throw an exception.
     *
     * @return string
     * @throws LogicException if the subtype is not set
     */
    private function requireSubtype(): string
    {
        if ($this->subtype === null) {
            throw new LogicException('Learning progress subtype is not set.');
        }

        return $this->subtype;
    }

    /**
     * Check if the learning progress status is "not attempted" for the current user and object.
     *
     * @return bool
     */
    private function isNotAttempted(): bool
    {
        return ilLPStatus::_lookupStatus(
            $this->resolveObjId(),
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    /**
     * Check if the learning progress status is "in progress" for the current user and object.
     *
     * @return bool
     */
    private function isInProgress(): bool
    {
        return ilLPStatus::_lookupStatus(
            $this->resolveObjId(),
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
    }

    /**
     * Check if the learning progress status is "completed" for the current user and object.
     *
     * @return bool
     */
    private function isCompleted(): bool
    {
        return ilLPStatus::_hasUserCompleted(
            $this->resolveObjId(),
            $this->dic->user()->getId()
        );
    }

    /**
     * Check if the learning progress status is "failed" for the current user and object.
     *
     * @return bool
     */
    private function isFailed(): bool
    {
        return ilLPStatus::_lookupStatus(
            $this->resolveObjId(),
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_FAILED_NUM;
    }

    /**
     * Resolves the obj_id for the condition's object. The condition stores a
     * ref_id in obj_ref_id, but ilLPStatus expects an obj_id.
     */
    private function resolveObjId(): int
    {
        return ilObject::_lookupObjId((int) $this->obj_ref_id);
    }
}
