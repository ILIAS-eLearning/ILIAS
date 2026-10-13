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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

class ilLearningSequenceConditionsRetrieval implements DataRetrieval
{
    private array $conditions = [];
    private ConditionFactory $condition_factory;
    private ilLanguage $lng;

    public function __construct(
        protected int $lso_ref_id,
        protected int $item_ref_id
    ) {
        global $DIC;
        $this->lng = $DIC->language();
        $discover = new ilObjLearningSequenceConditionDiscover();
        $this->conditions = $discover->getAllConditionIdsForItem($this->item_ref_id);
        $this->condition_factory = new ConditionFactory($discover, $DIC->database());
    }

    /**
     * @throws ilException
     * @throws ReflectionException
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $input_conditions = [];
        $output_conditions = [];

        foreach ($this->conditions as $condition_id) {
            $condition = $this->condition_factory->getConditionInstanceById($condition_id);

            if ($condition instanceof InputConditionInterface) {
                $input_conditions[(int) $condition_id] = $condition;
            } else {
                $output_conditions[(int) $condition_id] = $condition;
            }
        }

        ksort($input_conditions, SORT_NUMERIC);
        ksort($output_conditions, SORT_NUMERIC);

        foreach ([$input_conditions, $output_conditions] as $grouped_conditions) {
            foreach ($grouped_conditions as $condition_id => $condition) {
                $row = $row_builder->buildDataRow(
                    (string) $condition_id,
                    [
                        'type' => $this->resolveTypeLabel($condition),
                        'name' => $this->lng->txt($condition->getName()),
                        'subtype' => $this->resolveSubtypeLabel($condition),
                        'details' => $this->buildDetails($condition),
                    ]
                );

                if ($condition->getAdditionalForm() === null) {
                    $row = $row->withDisabledAction('edit');
                }

                yield $row;
            }
        }
    }

    /**
     * @param mixed $additional_viewcontrol_data
     * @param mixed $filter_data
     * @param mixed $additional_parameters
     * @return int|null
     */
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->conditions);
    }

    private function resolveTypeLabel(AbstractCondition $condition): string
    {
        if ($condition instanceof InputConditionInterface) {
            return $this->lng->txt('input_conditions');
        }

        return $this->lng->txt('output_conditions');
    }

    private function resolveSubtypeLabel(AbstractCondition $condition): string
    {
        if ($condition instanceof SubtypeAwareInterface) {
            return $condition->getSubtypeLabel($condition->getSubtype());
        }

        return '';
    }

    private function buildDetails(AbstractCondition $condition): string
    {
        $summary = trim($condition->getAdditionalDisplayInformation());
        $targets = $condition->getAdditionalDisplayObjectTitles();

        if ($summary === '' && $targets === []) {
            return '';
        }

        $parts = [];
        if ($summary !== '') {
            $parts[] = '<div>' . htmlspecialchars($summary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
        }

        if ($targets !== []) {
            $targets_html = array_map(
                static fn(string $target): string =>
                    '<li>' . htmlspecialchars($target, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>',
                $targets
            );

            $parts[] = '<div>' . htmlspecialchars($this->lng->txt('condition_targets') . ':', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
            $parts[] = '<ul>' . implode('', $targets_html) . '</ul>';
        }

        return implode('', $parts);
    }
}
