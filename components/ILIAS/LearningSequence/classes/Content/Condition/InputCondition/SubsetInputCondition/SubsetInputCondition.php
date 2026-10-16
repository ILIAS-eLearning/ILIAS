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

namespace ILIAS\LearningSequence\Content\Condition\InputCondition\SubsetInputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\LSOObjectPicker;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;

final class SubsetInputCondition extends AbstractCondition implements
    InputConditionInterface,
    InputConditionNavigationAwareInterface
{
    protected const string NAME = 'subset';
    private const string SETTINGS_TABLE = 'lso_c_subset';
    private const string TARGETS_TABLE = 'lso_c_subset_items';
    private const string SUBSET_FIELD = 'subset';
    private const string SOURCE_REF_ID_FIELD = 'item_ref_id';

    /**
     * @var int[]|null
     */
    private ?array $source_ref_ids = null;
    private ?int $subset = null;

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
                    self::SUBSET_FIELD => ['type' => 'integer', 'length' => 4, 'notnull' => true],
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
        $user_id = $this->dic->user()->getId();
        $completed_counter = 0;
        foreach ($this->getSourceRefIds() as $source_ref_id) {
            $object_id = \ilObject::_lookupObjId($source_ref_id);
            if ($object_id > 0 && \ilLPStatus::_hasUserCompleted($object_id, $user_id)) {
                $completed_counter++;
            }
        }

        return $completed_counter >= $this->getSubset();
    }

    public function getNavigationMode(): string
    {
        return InputConditionNavigationAwareInterface::NAVIGATION_MODE_DEPENDENCY;
    }

    public function getNavigationSourceRefIds(): array
    {
        return $this->getSourceRefIds();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function hasStaticInputConfigurationConflict(array $context = []): bool
    {
        return $this->referencesMissingLsoItems($this->getSourceRefIds(), $context);
    }

    /**
     * @param array<string, mixed> $context
     * @return StaticInputConfigurationIssue[]
     */
    public function getStaticInputConfigurationIssues(array $context = []): array
    {
        $missing_ref_ids = $this->getMissingReferencedLsoRefIds($this->getSourceRefIds(), $context);
        if ($missing_ref_ids === []) {
            return [];
        }

        $affected_ref_ids = $this->getStaticInputConfigurationConflictAffectedRefIds();
        if ($affected_ref_ids === []) {
            return [];
        }

        return [new StaticInputConfigurationIssue(
            'subset_input_missing_references',
            $affected_ref_ids,
            details: array_map(
                static fn(int $affected_ref_id): StaticInputConfigurationIssueDetail => new StaticInputConfigurationIssueDetail(
                    $affected_ref_id,
                    'lso_static_input_configuration_missing_references',
                    properties_by_language_var: [
                        'lso_static_input_configuration_referenced_objects' => $missing_ref_ids
                    ]
                ),
                $affected_ref_ids
            )
        )];
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

        $required_amount = $this->ui_factory->input()->field()->numeric(
            $this->lang->txt('subset_amount'),
            $this->lang->txt('subset_amount_byline')
        )->withRequired(true);

        if ($this->condition_id !== null) {
            $multi_select = $multi_select->withValue(
                array_map(static fn(int $ref_id): string => (string) $ref_id, $this->getSourceRefIds())
            );
            $required_amount = $required_amount->withValue($this->getSubset());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [
                $multi_select,
                $required_amount,
            ]
        );
    }

    public function getAdditionalDisplayInformation(): string
    {
        return sprintf('%s: %d', $this->lang->txt('subset_amount'), $this->getSubset());
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

        $subset = $data[1] ?? null;
        if (is_array($subset) || !is_numeric($subset)) {
            $this->addValidationMessage($this->lang->txt('lso_exception_subset_invalid'));
            return;
        }

        $this->setSourceRefIds($source_ref_ids);
        if ($this->hasValidationMessages()) {
            return;
        }
        $this->setSubset((int) $subset);
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
            throw new \LogicException("No source ref ids found for condition id {$this->condition_id}");
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
     * @return int
     */
    public function getSubset(): int
    {
        if ($this->subset !== null) {
            return $this->subset;
        }

        if ($this->condition_id === null) {
            return 0;
        }

        $res = $this->getDatabase()->queryF(
            'SELECT ' . self::SUBSET_FIELD . ' FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );
        $row = $this->getDatabase()->fetchAssoc($res);
        if ($row === null || !isset($row[self::SUBSET_FIELD])) {
            throw new \LogicException("No subset value found for condition id {$this->condition_id}");
        }

        $this->subset = (int) $row[self::SUBSET_FIELD];
        return $this->subset;
    }

    /**
     * @param int $subset
     */
    public function setSubset(int $subset): void
    {
        if ($subset < 0) {
            $this->addValidationMessage($this->lang->txt('lso_exception_subset_negative'));
            return;
        }

        if ($this->source_ref_ids !== null && $subset > count($this->source_ref_ids)) {
            $this->addValidationMessage($this->lang->txt('lso_exception_subset_exceeds_objects'));
            return;
        }

        $this->subset = $subset;
    }

    /**
     * @inheritDoc
     */
    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            self::SUBSET_FIELD => ['integer', $this->requireSubset()]
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
                self::SUBSET_FIELD => ['integer', $this->requireSubset()]
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
     * @return int[]
     */
    private function requireSourceRefIds(): array
    {
        if ($this->source_ref_ids === null || $this->source_ref_ids === []) {
            throw new \LogicException('Source object ids are not set.');
        }

        return $this->source_ref_ids;
    }

    /**
     * @return int
     */
    private function requireSubset(): int
    {
        if ($this->subset === null) {
            throw new \LogicException('Subset is not set.');
        }

        return $this->subset;
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
}
