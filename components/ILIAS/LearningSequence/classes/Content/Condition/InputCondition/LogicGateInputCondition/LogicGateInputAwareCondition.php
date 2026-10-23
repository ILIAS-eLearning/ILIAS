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

namespace ILIAS\LearningSequence\Content\Condition\InputCondition;

use ilCtrlException;
use ilException;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;
use ILIAS\LearningSequence\Content\Condition\LSOObjectPicker;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use ILIAS\UI\Component\Link\Bulky;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;
use ReflectionException;

class LogicGateInputAwareCondition extends AbstractCondition implements
    InputConditionInterface,
    SubtypeAwareInterface,
    InputConditionNavigationAwareInterface
{
    final protected const string NAME = "logic_gate";
    private const string SETTINGS_TABLE = 'lso_c_logic_gate_input';
    private const string SETTINGS_TABLE_ITEMS = 'lso_c_logic_gate_items';

    private const string SUBTYPE_AND = 'logic_gate_and';
    private const string SUBTYPE_OR = 'logic_gate_or';
    private const string SUBTYPE_NOT = 'logic_gate_not';
    private array $items = [];
    private ConditionFactory $condition_factory;

    public function __construct(?int $condition_id = null)
    {
        parent::__construct($condition_id);

        $this->condition_factory = new ConditionFactory(
            $this->dic['lso.condition_types'],
            $this->dic->database(),
        );
    }

    /**
     * @return void
     */
    public function read(): void
    {
        parent::read();

        $s = self::SETTINGS_TABLE;
        $i = self::SETTINGS_TABLE_ITEMS;

        $db = $this->dic->database();
        $query = $db->queryF(
            <<<SQL
            SELECT item_ref_id FROM $s
            JOIN $i ON $s.condition_id = $i.condition_id
            WHERE $s.condition_id = %s
            SQL,
            ['integer'],
            [$this->condition_id]
        );
        while ($row = $db->fetchAssoc($query)) {
            $this->items[] = $row['item_ref_id'];
        }
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return void
     */
    public function setItems(array $items): void
    {
        if ($items === []) {
            $this->addValidationMessage($this->lang->txt('lso_subset_msg_choose_at_least_one'));
            return;
        }

        $this->items = $items;
    }

    /**
     * @return string
     */
    private function getItemsAsString(): string
    {
        return implode(', ', $this->items);
    }

    /**
     * @return TableDefinition[]
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
            ),
            new TableDefinition(
                tableName: self::SETTINGS_TABLE_ITEMS,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    'item_ref_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id', 'item_ref_id']
            )
        ];
    }

    /**
     * @return array|Bulky[]
     * @throws ilCtrlException
     */
    public function setupSteps(): array
    {
        $this->assertContextSet();

        return [
            $this->ui_factory->menu()->sub($this->lang->txt($this->getName()), [
                $this->buildSubtypeStep(self::SUBTYPE_AND),
                $this->buildSubtypeStep(self::SUBTYPE_OR),
                $this->buildSubtypeStep(self::SUBTYPE_NOT),
            ])
        ];
    }

    /**
     * @return bool
     * @throws ReflectionException
     * @throws ilException
     */
    public function check(): bool
    {
        return match ($this->getSubtype()) {
            self::SUBTYPE_AND => $this->areAllItemsCompleted(),
            self::SUBTYPE_OR => $this->isAnyItemCompleted(),
            self::SUBTYPE_NOT => $this->areNoItemsCompleted(),
            default => throw new \LogicException("Unknown logic gate subtype.")
        };
    }

    /**
     * @return string
     */
    public function getNavigationMode(): string
    {
        return InputConditionNavigationAwareInterface::NAVIGATION_MODE_DEPENDENCY;
    }

    /**
     * @return int[]
     */
    public function getNavigationSourceRefIds(): array
    {
        return $this->getItems();
    }

    public function getStaticInputConditionConstraints(): array
    {
        $ref_ids = $this->getNavigationSourceRefIds();

        return match ($this->getSubtype()) {
            self::SUBTYPE_AND => [['kind' => 'all_completed', 'ref_ids' => $ref_ids]],
            self::SUBTYPE_OR => [['kind' => 'any_completed', 'ref_ids' => $ref_ids]],
            self::SUBTYPE_NOT => [['kind' => 'none_completed', 'ref_ids' => $ref_ids]],
            default => []
        };
    }

    /**
     * @param array<string, mixed> $context
     */
    public function hasStaticInputConfigurationConflict(array $context = []): bool
    {
        if ($this->referencesMissingLsoItems($this->getNavigationSourceRefIds(), $context)) {
            return true;
        }

        $start_ref_id = $this->getConfiguredStartRefId($context);

        return $start_ref_id > 0
            && $this->getSubtype() === self::SUBTYPE_NOT
            && in_array($start_ref_id, $this->getNavigationSourceRefIds(), true);
    }

    /**
     * @param array<string, mixed> $context
     * @return StaticInputConfigurationIssue[]
     */
    public function getStaticInputConfigurationIssues(array $context = []): array
    {
        $details = [];
        $affected_ref_ids = $this->getStaticInputConfigurationConflictAffectedRefIds();
        if ($affected_ref_ids === []) {
            return [];
        }

        $missing_ref_ids = $this->getMissingReferencedLsoRefIds($this->getNavigationSourceRefIds(), $context);
        if ($missing_ref_ids !== []) {
            foreach ($affected_ref_ids as $affected_ref_id) {
                $details[] = new StaticInputConfigurationIssueDetail(
                    $affected_ref_id,
                    'lso_static_input_configuration_missing_references',
                    properties_by_language_var: [
                        'lso_static_input_configuration_referenced_objects' => $missing_ref_ids
                    ]
                );
            }
        }

        $start_ref_id = $this->getConfiguredStartRefId($context);
        if (
            $start_ref_id > 0
            && $this->getSubtype() === self::SUBTYPE_NOT
            && in_array($start_ref_id, $this->getNavigationSourceRefIds(), true)
        ) {
            foreach ($affected_ref_ids as $affected_ref_id) {
                $details[] = new StaticInputConfigurationIssueDetail(
                    $affected_ref_id,
                    'lso_logic_gate_input_not_start_reference',
                    properties_by_language_var: [
                        'lso_static_input_configuration_referenced_objects' => [$start_ref_id]
                    ]
                );
            }
        }

        if ($details === []) {
            return [];
        }

        return [new StaticInputConfigurationIssue(
            'logic_gate_input_configuration_conflict',
            $affected_ref_ids,
            details: $details
        )];
    }

    /**
     * Checks whether an item should count as completed for logic-gate evaluation.
     *
     * If an item defines output conditions, all of them must be fulfilled, mirroring
     * the navigator's canLeave() semantics. Without output conditions we fall back to
     * the item's regular learning-progress completion state.
     *
     * @throws ReflectionException
     * @throws ilException
     */
    private function isItemCompleted(int $item_ref_id): bool
    {
        $conditions_of_item = $this->condition_factory->getConditionIdsForItem($item_ref_id);

        $conditions = array_map(
            fn($condition_id) => $this->condition_factory->getConditionInstanceById($condition_id),
            $conditions_of_item
        );

        $output_conditions = array_filter(
            $conditions,
            fn($condition) => $condition instanceof OutputConditionInterface
        );

        if ($output_conditions !== []) {
            return array_all(
                $output_conditions,
                fn($condition) => $condition->check()
            );
        }

        $item_obj_id = \ilObject::_lookupObjId($item_ref_id);
        if ($item_obj_id <= 0) {
            return false;
        }

        return \ilLPStatus::_hasUserCompleted($item_obj_id, $this->dic->user()->getId());
    }

    /**
     * @return bool
     * @throws ReflectionException
     * @throws ilException
     */
    private function areAllItemsCompleted(): bool
    {
        $items = $this->getItems();

        if (empty($items)) {
            return true;
        }

        return array_all(
            $items,
            fn($item_ref_id) => $this->isItemCompleted($item_ref_id)
        );
    }

    /**
     * @return bool
     * @throws ReflectionException
     * @throws ilException
     */
    private function isAnyItemCompleted(): bool
    {
        return array_any(
            $this->getItems(),
            fn($item_ref_id) => $this->isItemCompleted($item_ref_id)
        );
    }

    /**
     * @return bool
     * @throws ReflectionException
     * @throws ilException
     */
    private function areNoItemsCompleted(): bool
    {
        return !$this->isAnyItemCompleted();
    }

    /**
     * @param string $subtype
     * @return string
     */
    public function getSubtypeLabel(string $subtype): string
    {
        return match ($subtype) {
            self::SUBTYPE_AND => $this->lang->txt('logic_gate_and'),
            self::SUBTYPE_OR => $this->lang->txt('logic_gate_or'),
            self::SUBTYPE_NOT => $this->lang->txt('logic_gate_not'),
            default => throw new \LogicException("Unknown logic gate subtype.")
        };
    }

    /**
     * @return string[]
     */
    public function getSupportedSubtypes(): array
    {
        return [
            self::SUBTYPE_AND,
            self::SUBTYPE_OR,
            self::SUBTYPE_NOT,
        ];
    }

    /**
     * @return string
     */
    public function getSubtype(): string
    {
        if ($this->subtype !== null) {
            return $this->subtype;
        }

        if ($this->condition_id === null) {
            throw new \LogicException("Cannot get subtype for a condition that has not been created yet.");
        }

        $s = self::SETTINGS_TABLE;
        $res = $this->getDatabase()->queryF(
            "SELECT subtype FROM $s WHERE condition_id = %s",
            ['integer'],
            [$this->condition_id]
        );
        $row = $this->getDatabase()->fetchAssoc($res);

        if ($row === null || !is_string($row['subtype'])) {
            throw new \LogicException("Could not retrieve subtype for condition with ID {$this->condition_id}.");
        }

        $this->setSubtype($row['subtype']);
        return (string) $this->subtype;
    }

    /**
     * @return bool
     */
    protected function requiresConfiguration(): bool
    {
        return true;
    }

    /**
     * @throws ilCtrlException
     */
    public function getAdditionalForm(): ?FormStandard
    {
        $input = new LSOObjectPicker($this->ui_factory, (int) $this->lso_ref_id, (int) $this->getObjRefId())->getPicker(
            $this->lang->txt('lso_condition_simple_multi_target'),
            true,
        )
            ->withRequired(false)
            ->withAdditionalTransformation(
                $this->dic->refinery()->custom()->constraint(
                    static fn($value): bool => is_array($value)
                        && count($value) >= 1,
                    $this->lang->txt('lso_subset_msg_choose_at_least_one')
                )
            )
            ->withByline($this->lang->txt($this->getSubtype() . '_byline'));

        if ($this->condition_id !== null) {
            $input = $input->withValue($this->getItems());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [ $input ]
        );
    }

    /**
     * @return string[]
     */
    public function getAdditionalDisplayObjectTitles(): array
    {
        return array_map(
            fn(int $ref_id): string => $this->getObjectTitleByRefId($ref_id),
            $this->getItems()
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function buildSubtypeStep(string $subtype): Bulky
    {
        return $this->buildStep(
            ['subtype' => $subtype],
            $this->getSubtypeLabel($subtype),
            self::CONFIGURE_COMMAND
        );
    }

    /**
     * @param array $data
     */
    public function applyAdditionalFormData(array $data): void
    {
        $this->setItems(array_filter(array_map('intval', $data[0] ?? [])));
    }

    /**
     * @param int $condition_id
     * @return void
     */
    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            'subtype' => ['text', $this->getSubtype()],
        ]);

        foreach ($this->items as $item) {
            $this->getDatabase()->insert(self::SETTINGS_TABLE_ITEMS, [
                'condition_id' => ['integer', $condition_id],
                'item_ref_id' => ['integer', $item],
            ]);
        }
    }

    /**
     * @param int $condition_id
     * @return void
     */
    protected function editConditionData(int $condition_id): void
    {
        $this->getDatabase()->update(self::SETTINGS_TABLE, [
            'subtype' => ['text', $this->getSubtype()],
        ], [
            'condition_id' => ['integer', $condition_id],
        ]);

        $i = self::SETTINGS_TABLE_ITEMS;
        $this->getDatabase()->manipulateF(
            "DELETE FROM $i WHERE condition_id = %d",
            ['integer'],
            [$this->condition_id]
        );
        foreach ($this->items as $item) {
            $this->getDatabase()->insert(self::SETTINGS_TABLE_ITEMS, [
                'condition_id' => ['integer', $condition_id],
                'item_ref_id' => ['integer', $item],
            ]);
        }
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

        $i = self::SETTINGS_TABLE_ITEMS;
        $this->getDatabase()->manipulateF(
            "DELETE FROM $i WHERE condition_id = %d",
            ['integer'],
            [$this->condition_id]
        );
    }
}
