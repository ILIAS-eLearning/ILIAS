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

namespace ILIAS\LearningSequence\Content\Condition\InputCondition\PointsInputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\AccruedValueInputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\LSOObjectPicker;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\PointsOutputCondition\PointsOutputCondition;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

final class PointsInputCondition extends AbstractCondition implements
    InputConditionInterface,
    InputConditionNavigationAwareInterface,
    AccruedValueInputConditionInterface
{
    protected const string NAME = 'points_input';
    private const string SETTINGS_TABLE = 'lso_c_points_input';
    private const string TARGETS_TABLE = 'lso_c_points_input_items';
    private const string POINTS_FIELD = 'points';
    private const string SOURCE_REF_ID_FIELD = 'item_ref_id';

    private ?int $points = null;
    /**
     * @var int[]|null
     */
    private ?array $source_ref_ids = null;

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
            ),
            new TableDefinition(
                tableName: self::TARGETS_TABLE,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    self::SOURCE_REF_ID_FIELD => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id', self::SOURCE_REF_ID_FIELD]
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return $this->getAvailablePoints() >= $this->getPoints();
    }

    public function getNavigationMode(): string
    {
        return InputConditionNavigationAwareInterface::NAVIGATION_MODE_DEPENDENCY;
    }

    public function getNavigationSourceRefIds(): array
    {
        return $this->getSourceRefIds();
    }

    public function getAccumulationIdentifier(): string
    {
        return 'points';
    }

    public function getRequiredAccumulatedValue(): int
    {
        return $this->getPoints();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function hasStaticInputConfigurationConflict(array $context = []): bool
    {
        if ($this->referencesMissingLsoItems($this->getSourceRefIds(), $context)) {
            return true;
        }

        return $this->getMaximumReachablePoints($context) < $this->getPoints();
    }

    /**
     * @param array<string, mixed> $context
     * @return StaticInputConfigurationIssue[]
     */
    public function getStaticInputConfigurationIssues(array $context = []): array
    {
        $issues = [];
        $conflict_details = [];

        $missing_ref_ids = $this->getMissingReferencedLsoRefIds($this->getSourceRefIds(), $context);
        if ($missing_ref_ids !== []) {
            foreach ($this->getStaticInputConfigurationConflictAffectedRefIds() as $affected_ref_id) {
                $conflict_details[] = new StaticInputConfigurationIssueDetail(
                    $affected_ref_id,
                    'lso_static_input_configuration_missing_references',
                    properties_by_language_var: [
                        'lso_static_input_configuration_referenced_objects' => $missing_ref_ids
                    ]
                );
            }
        }

        if ($this->getMaximumReachablePoints($context) < $this->getPoints()) {
            foreach ($this->getStaticInputConfigurationConflictAffectedRefIds() as $affected_ref_id) {
                $conflict_details[] = new StaticInputConfigurationIssueDetail(
                    $affected_ref_id,
                    'lso_points_input_unreachable_threshold'
                );
            }
        }

        if ($conflict_details !== []) {
            $issues[] = new StaticInputConfigurationIssue(
                'points_input_configuration_conflict',
                $this->getStaticInputConfigurationConflictAffectedRefIds(),
                details: $conflict_details
            );
        }

        $source_ref_ids_without_points_output = $this->getSourceRefIdsWithoutPointsOutput($context);
        if ($source_ref_ids_without_points_output !== []) {
            $issues[] = new StaticInputConfigurationIssue(
                'points_input_source_without_points_output',
                $source_ref_ids_without_points_output,
                'lso_points_input_source_without_points_output_table',
                details: array_map(
                    fn(int $ref_id): StaticInputConfigurationIssueDetail => new StaticInputConfigurationIssueDetail(
                        $ref_id,
                        'lso_points_input_missing_output_on_object',
                        properties_by_language_var: [
                            'lso_static_input_configuration_referenced_by_objects' => $this->getRefIdsReferencingMissingPointsOutput($ref_id)
                        ]
                    ),
                    $source_ref_ids_without_points_output
                )
            );
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $context
     * @return int[]
     */
    public function getSourceRefIdsWithoutPointsOutput(array $context = []): array
    {
        $configured_output_ref_ids = $context['points_output_ref_ids'] ?? null;
        if (is_array($configured_output_ref_ids)) {
            $configured_output_ref_ids = array_values(array_unique(array_map('intval', $configured_output_ref_ids)));

            return array_values(array_filter(
                $this->getSourceRefIds(),
                static fn(int $ref_id): bool => !in_array($ref_id, $configured_output_ref_ids, true)
            ));
        }

        return array_values(array_filter(
            $this->getSourceRefIds(),
            fn(int $ref_id): bool => $this->getOutputConditionIdForItem($ref_id) === null
        ));
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalForm(): FormStandard
    {
        $multi_select = new LSOObjectPicker((int) $this->lso_ref_id, (int) $this->getObjRefId())->getPicker(
            $this->lang->txt('lso_condition_simple_multi_target'),
            true
        )
            ->withRequired(false)
            ->withAdditionalTransformation(
                $this->dic->refinery()->custom()->constraint(
                    static fn($value): bool => is_array($value)
                        && count($value) >= 1,
                    $this->lang->txt('lso_subset_msg_choose_at_least_one')
                )
            );
        $points_input = $this->ui_factory->input()->field()->numeric(
            $this->lang->txt('points_input'),
            $this->lang->txt('points_input_byline')
        );

        if ($this->condition_id !== null) {
            $multi_select = $multi_select->withValue(
                array_map(static fn(int $ref_id): string => (string) $ref_id, $this->getSourceRefIds())
            );
            $points_input = $points_input->withValue($this->getPoints());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [
                $multi_select,
                $points_input
            ]
        );
    }

    public function getAdditionalDisplayInformation(): string
    {
        return sprintf('%s: %d', $this->lang->txt('condition_points'), $this->getPoints());
    }

    /**
     * @return string[]
     */
    public function getAdditionalDisplayObjectTitles(): array
    {
        return array_map(
            fn(int $ref_id): string => $this->getObjectTitleByRefId($ref_id),
            $this->getSourceRefIds()
        );
    }

    /**
     * @param array<mixed> $data
     */
    public function applyAdditionalFormData(array $data): void
    {
        $source_ref_ids = array_values(array_unique(array_filter(
            array_map(
                static fn(mixed $value): int => (int) $value,
                is_array($data[0] ?? null) ? $data[0] : []
            ),
            static fn(int $value): bool => $value > 0
        )));
        if ($source_ref_ids === []) {
            $this->addValidationMessage($this->lang->txt('lso_subset_msg_choose_at_least_one'));
            return;
        }

        $points = $data[1] ?? null;
        if (is_array($points) || !is_numeric($points)) {
            $this->addValidationMessage($this->lang->txt('lso_exception_points_invalid'));
            return;
        }

        $this->setSourceRefIds($source_ref_ids);
        if ($this->hasValidationMessages()) {
            return;
        }
        $this->setPoints((int) $points);
    }

    /**
     * @return int[]
     */
    public function getSourceRefIds(): array
    {
        if ($this->source_ref_ids !== null) {
            return $this->source_ref_ids;
        }

        if ($this->condition_id === null) {
            return [];
        }

        $res = $this->getDatabase()->queryF(
            'SELECT ' . self::SOURCE_REF_ID_FIELD . ' FROM ' . self::TARGETS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );

        $source_ref_ids = [];
        while ($row = $this->getDatabase()->fetchAssoc($res)) {
            $source_ref_ids[] = (int) $row[self::SOURCE_REF_ID_FIELD];
        }

        $source_ref_ids = array_values(array_unique(array_filter(
            $source_ref_ids,
            static fn(int $value): bool => $value > 0
        )));
        if ($source_ref_ids === []) {
            throw new \LogicException("No source objects found for this condition.");
        }

        $this->source_ref_ids = $source_ref_ids;
        return $this->source_ref_ids;
    }

    /**
     * @param int[] $source_ref_ids
     */
    public function setSourceRefIds(array $source_ref_ids): void
    {
        $current_ref_id = $this->obj_ref_id;
        $source_ref_ids = array_values(array_unique(array_filter(
            array_map(static fn(mixed $value): int => (int) $value, $source_ref_ids),
            static fn(int $value): bool => $value > 0 && $value !== $current_ref_id
        )));
        if ($source_ref_ids === []) {
            $this->addValidationMessage($this->lang->txt('lso_subset_msg_choose_at_least_one'));
            return;
        }

        $this->source_ref_ids = $source_ref_ids;
    }

    /**
     * Retruns the points required to enter this step.
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
            throw new \LogicException("Points not stored for this condition.");
        }

        $this->points = (int) $row[self::POINTS_FIELD];
        return $this->points;
    }

    /**
     * Sets the points required to enter this step.
     *
     * @param int $points
     */
    public function setPoints(int $points): void
    {
        if ($points <= 0) {
            $this->addValidationMessage($this->lang->txt('lso_points_input_points_must_be_greater_zero'));
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
        $this->storeSourceRefIds($condition_id, $this->requireSourceRefIds());
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
        $this->getDatabase()->manipulateF(
            'DELETE FROM ' . self::TARGETS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
        $this->storeSourceRefIds($condition_id, $this->requireSourceRefIds());
    }

    /**
     * @inheritDoc
     */
    protected function deleteConditionData(int $condition_id): void
    {
        $this->getDatabase()->manipulateF(
            'DELETE FROM ' . self::TARGETS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
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
     * Returns the total points available from configured source objects.
     *
     * @return int
     */
    private function getAvailablePoints(): int
    {
        $points = 0;

        foreach ($this->getSourceRefIds() as $source_ref_id) {
            if ($source_ref_id === $this->obj_ref_id) {
                continue;
            }

            $points += $this->getPointsFromItem($source_ref_id);
        }

        return $points;
    }

    /**
     * Returns the points from a configured source object.
     *
     * @param int $ref_id
     * @return int
     */
    private function getPointsFromItem(int $ref_id): int
    {
        $condition_id = $this->getOutputConditionIdForItem($ref_id);
        if ($condition_id === null) {
            return 0;
        }

        $condition = new PointsOutputCondition();
        $condition->setLsoRefId($this->lso_ref_id);
        $condition->setObjRefId($ref_id);
        $condition->setConditionId($condition_id);

        try {
            return $condition->check() ? $condition->getPoints() : 0;
        } catch (\LogicException) {
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getMaximumReachablePoints(array $context = []): int
    {
        $configured_points_outputs = $context['configured_points_outputs_by_ref_id'] ?? null;
        if (is_array($configured_points_outputs)) {
            $maximum_points = 0;
            foreach ($this->getSourceRefIds() as $source_ref_id) {
                $maximum_points += max(0, (int) ($configured_points_outputs[$source_ref_id] ?? 0));
            }

            return $maximum_points;
        }

        $maximum_points = 0;
        foreach ($this->getSourceRefIds() as $source_ref_id) {
            $maximum_points += $this->getConfiguredPointsFromItem($source_ref_id);
        }

        return $maximum_points;
    }

    private function getConfiguredPointsFromItem(int $ref_id): int
    {
        $condition_id = $this->getOutputConditionIdForItem($ref_id);
        if ($condition_id === null) {
            return 0;
        }

        $condition = new PointsOutputCondition();
        $condition->setConditionId($condition_id);

        try {
            return $condition->getPoints();
        } catch (\LogicException) {
            return 0;
        }
    }

    /**
     * Returns the condition ID for the PointsOutputCondition of a configured source object.
     *
     * @param int $ref_id
     * @return int|null
     */
    private function getOutputConditionIdForItem(int $ref_id): ?int
    {
        $condition_name = $this->getIdentifierForClass(PointsOutputCondition::class);
        $res = $this->getDatabase()->queryF(
            'SELECT c.condition_id
                FROM lso_conditions c
                INNER JOIN lso_condition_types t ON c.type_id = t.type_id
                WHERE c.lso_ref_id = %s AND c.obj_ref_id = %s AND t.condition_name = %s',
            ['integer', 'integer', 'text'],
            [$this->lso_ref_id, $ref_id, $condition_name]
        );
        $row = $this->getDatabase()->fetchAssoc($res);
        if ($row === null) {
            return null;
        }

        return (int) $row['condition_id'];
    }

    /**
     * @return int[]
     */
    private function getRefIdsReferencingMissingPointsOutput(int $source_ref_id): array
    {
        $ref_id = (int) $this->getObjRefId();
        if ($ref_id <= 0 || !in_array($source_ref_id, $this->getSourceRefIds(), true)) {
            return [];
        }

        return [$ref_id];
    }

    /**
     * Returns the points that are required to enter this step, or throws an exception if they are not set.
     *
     * @return int
     * @throws \LogicException if the points are not set
     */
    private function requirePoints(): int
    {
        if ($this->points === null) {
            throw new \LogicException('Points are not set.');
        }

        return $this->points;
    }

    /**
     * @return int[]
     */
    private function requireSourceRefIds(): array
    {
        if ($this->source_ref_ids === null || $this->source_ref_ids === []) {
            throw new \LogicException('Source objects are not set.');
        }

        return $this->source_ref_ids;
    }

    /**
     * @param int[] $source_ref_ids
     */
    private function storeSourceRefIds(int $condition_id, array $source_ref_ids): void
    {
        foreach ($source_ref_ids as $source_ref_id) {
            $this->getDatabase()->insert(self::TARGETS_TABLE, [
                'condition_id' => ['integer', $condition_id],
                self::SOURCE_REF_ID_FIELD => ['integer', $source_ref_id]
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function allowMultipleConditionsOfSameType(): bool
    {
        return false;
    }
}
