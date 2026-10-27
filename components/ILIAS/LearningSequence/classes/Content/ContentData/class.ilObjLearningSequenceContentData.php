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

/**
 * Data transfer object for an item in the learning sequence content table.
 */
final readonly class ilObjLearningSequenceContentData
{
    /**
     * Creates content data.
     *
     * @param int $ref_id Repository reference ID.
     * @param int $obj_id Object ID.
     * @param string $title Object title.
     * @param string $description Object description.
     * @param string $type Object type.
     * @param string $icon_path Path to the object icon.
     * @param string $href Object link.
     * @param bool $is_online Whether the object is online.
     * @param string $start_object Start object label.
     * @param string $end_object End object label.
     * @param string $previous_objects Previous object label.
     * @param string $next_objects Next object label.
     * @param ilObjLearningSequenceConditionData[] $input_conditions Input conditions.
     * @param ilObjLearningSequenceConditionData[] $output_conditions Output conditions.
     * @param bool $has_conflicting_input_configuration Whether the input conditions contradict each other.
     * @param \ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail[] $static_input_configuration_issue_details
     * @param bool $has_structural_predecessor Whether a structural predecessor exists.
     * @param bool $has_structural_successor Whether a structural successor exists.
     * @param ilObjLearningSequenceActionData[] $actions Available actions.
     */
    public function __construct(
        /** Repository reference ID. */
        public int $ref_id,
        /** Object ID. */
        public int $obj_id,
        /** Object title. */
        public string $title,
        /** Object description. */
        public string $description,
        /** Object type. */
        public string $type,
        /** Path to the object icon. */
        public string $icon_path,
        /** Object link. */
        public string $href,
        /** Whether the object is online. */
        public bool $is_online,
        /** Start object label. */
        public string $start_object,
        /** End object label. */
        public string $end_object,
        /** Previous object label. */
        public string $previous_objects,
        /** Next object label. */
        public string $next_objects,
        /** @var ilObjLearningSequenceConditionData[] Input conditions. */
        public array $input_conditions,
        /** @var ilObjLearningSequenceConditionData[] Output conditions. */
        public array $output_conditions,
        /** Whether the input conditions contradict each other. */
        public bool $has_conflicting_input_configuration,
        /** @var \ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail[] */
        public array $static_input_configuration_issue_details,
        /** Whether a structural predecessor exists. */
        public bool $has_structural_predecessor,
        /** Whether a structural successor exists. */
        public bool $has_structural_successor,
        /** Available actions. */
        public array $actions = []
    ) {
    }
}
