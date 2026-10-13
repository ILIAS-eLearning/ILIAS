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

namespace ILIAS\LearningSequence\Content\Condition\OutputCondition\PointsOutputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\AccruedValueOutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ilLPStatus;
use ilObject;
use LogicException;

final class PointsOutputCondition extends AbstractCondition implements OutputConditionInterface, AccruedValueOutputConditionInterface
{
    protected const string NAME = 'points_output';
    private const string SETTINGS_TABLE = 'lso_c_points_output';
    private const string POINTS_FIELD = 'points';

    private ?int $points = null;

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
                    self::POINTS_FIELD => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id']
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return ilLPStatus::_hasUserCompleted(
            $this->resolveObjId(),
            $this->dic->user()->getId()
        );
    }

    public function getAccumulationIdentifier(): string
    {
        return 'points';
    }

    public function getAccumulatedValue(): int
    {
        return $this->getPoints();
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalForm(): FormStandard
    {
        $input = $this->ui_factory->input()->field()->numeric(
            $this->lang->txt('points_output'),
            $this->lang->txt('points_output_byline')
        )->withRequired(true);

        if ($this->condition_id !== null) {
            $input = $input->withValue($this->getPoints());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [$input]
        );
    }

    public function getAdditionalDisplayInformation(): string
    {
        return sprintf('%s: %d', $this->lang->txt('condition_points'), $this->getPoints());
    }

    /**
     * @param array $data
     */
    public function applyAdditionalFormData(array $data): void
    {
        $points = array_shift($data);
        if (is_array($points) || !is_numeric($points)) {
            $this->addValidationMessage($this->lang->txt('lso_exception_points_invalid'));
            return;
        }

        $this->setPoints((int) $points);
    }

    /**
     * Returns the points that are awarded when this object is completed.
     *
     * @return int
     */
    public function getPoints(): int
    {
        if ($this->points !== null) {
            return $this->points;
        }

        if ($this->condition_id === null) {
            return 0;
        }

        $res = $this->getDatabase()->queryF(
            'SELECT ' . self::POINTS_FIELD . ' FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );
        $row = $this->getDatabase()->fetchAssoc($res);
        if ($row === null || !isset($row[self::POINTS_FIELD])) {
            throw new LogicException('Points are not stored.');
        }

        $this->points = (int) $row[self::POINTS_FIELD];
        return $this->points;
    }

    /**
     * Sets the points that are awarded when this object is completed.
     *
     * @param int $points
     * @throws LogicException if the points are negative
     */
    public function setPoints(int $points): void
    {
        if ($points < 0) {
            $this->addValidationMessage($this->lang->txt('lso_exception_points_negative'));
            return;
        }

        $this->points = $points;
    }

    /**
     * @inheritDoc
     */
    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            self::POINTS_FIELD => ['integer', $this->requirePoints()]
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
                self::POINTS_FIELD => ['integer', $this->requirePoints()]
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
    protected function requiresConfiguration(): bool
    {
        return true;
    }

    /**
     * Returns the points that are required to be set.
     *
     * @return int
     * @throws LogicException if the points are not set
     */
    private function requirePoints(): int
    {
        if ($this->points === null) {
            throw new LogicException('Points are not set.');
        }

        return $this->points;
    }

    /**
     * Resolves the obj_id for the condition's object. The condition stores a
     * ref_id in obj_ref_id, but ilLPStatus expects an obj_id.
     */
    private function resolveObjId(): int
    {
        return ilObject::_lookupObjId((int) $this->obj_ref_id);
    }

    /**
     * @inheritDoc
     */
    public function allowMultipleConditionsOfSameType(): bool
    {
        return false;
    }
}
