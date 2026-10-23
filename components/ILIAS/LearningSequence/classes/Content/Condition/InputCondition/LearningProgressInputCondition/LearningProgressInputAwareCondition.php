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

namespace ILIAS\LearningSequence\Content\Condition\InputCondition\LearningProgressInputConditions;

use ilCtrlException;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\LSOObjectPicker;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;
use ILIAS\LearningSequence\Content\Condition\TableDefinition;
use ILIAS\UI\Component\Input\Container\Form\Standard as FormStandard;
use ILIAS\UI\Component\Link\Bulky;
use ilLPStatus;
use ilObject;
use LogicException;

final class LearningProgressInputAwareCondition extends AbstractCondition implements
    InputConditionInterface,
    SubtypeAwareInterface,
    InputConditionNavigationAwareInterface
{
    protected const string NAME = 'learning_progress_input';
    private const string SETTINGS_TABLE = 'lso_c_learning_progress_input';
    private const string SUBTYPE_NOT_ATTEMPTED = 'not_attempted';
    private const string SUBTYPE_IN_PROGRESS = 'in_progress';
    private const string SUBTYPE_COMPLETED = 'completed';
    private const string SUBTYPE_FAILED = 'failed';
    private ?int $condition_target_ref_id = null;

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
        $target_obj_id = ilObject::_lookupObjId($this->getConditionTargetRefId());
        if ($target_obj_id === 0) {
            return false;
        }

        return match ($this->getSubtype()) {
            self::SUBTYPE_NOT_ATTEMPTED => $this->isNotAttempted($target_obj_id),
            self::SUBTYPE_IN_PROGRESS => $this->isInProgress($target_obj_id),
            self::SUBTYPE_COMPLETED => $this->isCompleted($target_obj_id),
            self::SUBTYPE_FAILED => $this->isFailed($target_obj_id),
            default => throw new LogicException("Unknown learning progress subtype.")
        };
    }

    public function getNavigationMode(): string
    {
        return InputConditionNavigationAwareInterface::NAVIGATION_MODE_EDGE;
    }

    public function getNavigationSourceRefIds(): array
    {
        return [$this->getConditionTargetRefId()];
    }

    public function supportsDuplicateCheck(): bool
    {
        return true;
    }

    /**
     * Two learning progress input conditions are identical if they require the
     * same learning progress status on the same target object.
     */
    public function isDuplicateOf(AbstractCondition $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        try {
            return $this->getSubtype() === $other->getSubtype()
                && $this->getConditionTargetRefId() === $other->getConditionTargetRefId();
        } catch (LogicException) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function hasStaticInputConfigurationConflict(array $context = []): bool
    {
        return $this->referencesMissingLsoItems([$this->getConditionTargetRefId()], $context);
    }

    /**
     * @param array<string, mixed> $context
     * @return StaticInputConfigurationIssue[]
     */
    public function getStaticInputConfigurationIssues(array $context = []): array
    {
        $missing_ref_ids = $this->getMissingReferencedLsoRefIds([$this->getConditionTargetRefId()], $context);
        if ($missing_ref_ids === []) {
            return [];
        }

        $affected_ref_ids = $this->getStaticInputConfigurationConflictAffectedRefIds();
        if ($affected_ref_ids === []) {
            return [];
        }

        return [new StaticInputConfigurationIssue(
            'learning_progress_input_missing_reference',
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
    public static function migrate(): array
    {
        return [
            new TableDefinition(
                tableName: self::SETTINGS_TABLE,
                fields: [
                    'condition_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                    'subtype' => ['type' => 'text', 'length' => 32, 'notnull' => true],
                    'item_ref_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                ],
                primaryKeys: ['condition_id']
            )
        ];
    }

    /**
     * @inheritDoc
     */
    protected function requiresConfiguration(): bool
    {
        return true;
    }

    /**
     * Returns a picker to select the target ref id for the learning progress input condition.
     *
     * @return FormStandard
     * @throws ilCtrlException
     */
    public function getAdditionalForm(): FormStandard
    {
        $input = new LSOObjectPicker($this->ui_factory, (int) $this->lso_ref_id, (int) $this->getObjRefId())->getPicker(
            $this->lang->txt('lso_condition_simple_choice_target'),
            false,
        )
            ->withRequired(false)
            ->withAdditionalTransformation(
                $this->dic->refinery()->custom()->constraint(
                    static fn($value): bool => is_array($value)
                        && count($value) === 1
                        && isset($value[0])
                        && $value[0] !== '',
                    $this->lang->txt('lso_msg_choose_one_object')
                )
            );

        if ($this->condition_id !== null) {
            $input = $input->withValue((string) $this->getConditionTargetRefId());
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->buildUrl(self::CREATE_COMMAND, true)->__toString(),
            [$input]
        );
    }

    /**
     * @param array<mixed> $data
     */
    public function applyAdditionalFormData(array $data): void
    {
        $target_ref_ids = array_shift($data);
        if (
            !is_array($target_ref_ids)
            || count($target_ref_ids) !== 1
            || !isset($target_ref_ids[0])
            || $target_ref_ids[0] === ''
        ) {
            $this->addValidationMessage($this->lang->txt('lso_msg_choose_one_object'));
            return;
        }

        $this->setConditionTargetRefId((int) $target_ref_ids[0]);
    }

    /**
     * @return string[]
     */
    public function getAdditionalDisplayObjectTitles(): array
    {
        return array_map(
            fn(int $ref_id): string => $this->getObjectTitleByRefId($ref_id),
            [$this->getConditionTargetRefId()]
        );
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
            throw new LogicException("Learning progress subtype is not set.");
        }

        $res = $this->getDatabase()->queryF(
            'SELECT subtype FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );
        $row = $this->getDatabase()->fetchAssoc($res);

        if ($row === null || !is_string($row['subtype'])) {
            throw new LogicException("Learning progress subtype is not stored.");
        }

        $this->setSubtype($row['subtype']);
        return (string) $this->subtype;
    }

    /**
     * Get the target ref id for the learning progress condition.
     *
     * @return int
     * @throws LogicException if the target ref id is not set or not stored
     */
    public function getConditionTargetRefId(): int
    {
        if ($this->condition_target_ref_id !== null) {
            return $this->condition_target_ref_id;
        }

        if ($this->condition_id === null) {
            throw new LogicException("Learning progress target ref id is not set.");
        }

        $res = $this->getDatabase()->queryF(
            'SELECT item_ref_id FROM ' . self::SETTINGS_TABLE . ' WHERE condition_id = %s',
            ['integer'],
            [$this->condition_id]
        );
        /** @var string[]|null $row */
        $row = $this->getDatabase()->fetchAssoc($res);

        if ($row === null) {
            throw new LogicException("Learning progress target ref id is not stored.");
        }

        $this->condition_target_ref_id = (int) $row['item_ref_id'];
        return $this->condition_target_ref_id;
    }

    /**
     * Set the target ref id for the learning progress condition.
     *
     * @param int $condition_target_ref_id
     */
    public function setConditionTargetRefId(int $condition_target_ref_id): void
    {
        $this->condition_target_ref_id = $condition_target_ref_id;
    }

    /**
     * @inheritDoc
     */
    protected function createConditionData(int $condition_id): void
    {
        $this->getDatabase()->insert(self::SETTINGS_TABLE, [
            'condition_id' => ['integer', $condition_id],
            'subtype' => ['text', $this->requireSubtype()],
            'item_ref_id' => ['integer', $this->getConditionTargetRefId()]
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
                'subtype' => ['text', $this->requireSubtype()],
                'item_ref_id' => ['integer', $this->getConditionTargetRefId()]
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
     * @throws ilCtrlException
     */
    public function buildSubtypeStep(string $subtype): Bulky
    {
        return $this->buildStep(
            ['subtype' => $subtype],
            $this->getSubtypeLabel($subtype)
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
            default => throw new LogicException("Unknown learning progress subtype.")
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
            throw new LogicException("Learning progress subtype is not set.");
        }

        return $this->subtype;
    }

    /**
     * Check if the learning progress status is "not attempted" for the current user and object.
     *
     * @param int $target_obj_id
     * @return bool
     */
    private function isNotAttempted(int $target_obj_id): bool
    {
        return ilLPStatus::_lookupStatus(
            $target_obj_id,
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    /**
     * Check if the learning progress status is "in progress" for the current user and object.
     *
     * @param int $target_obj_id
     * @return bool
     */
    private function isInProgress(int $target_obj_id): bool
    {
        return ilLPStatus::_lookupStatus(
            $target_obj_id,
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
    }

    /**
     * Check if the learning progress status is "completed" for the current user and object.
     *
     * @param int $target_obj_id
     * @return bool
     */
    private function isCompleted(int $target_obj_id): bool
    {
        return ilLPStatus::_hasUserCompleted(
            $target_obj_id,
            $this->dic->user()->getId()
        );
    }

    /**
     * Check if the learning progress status is "failed" for the current user and object.
     *
     * @param int $target_obj_id
     * @return bool
     */
    private function isFailed(int $target_obj_id): bool
    {
        return ilLPStatus::_lookupStatus(
            $target_obj_id,
            $this->dic->user()->getId()
        ) === ilLPStatus::LP_STATUS_FAILED_NUM;
    }
}
