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

namespace ILIAS\LearningSequence\Content\Condition;

final readonly class StaticInputConfigurationIssue
{
    public string $kind;
    /**
     * @var int[]
     */
    public array $affected_ref_ids;
    public ?string $summary_message_language_var;
    /**
     * @var StaticInputConfigurationIssueDetail[]
     */
    public array $details;

    /**
     * @param int[] $affected_ref_ids
     * @param StaticInputConfigurationIssueDetail[] $details
     */
    public function __construct(
        string $kind,
        array $affected_ref_ids,
        ?string $summary_message_language_var = null,
        array $details = []
    ) {
        $this->kind = $kind;
        $this->affected_ref_ids = array_values(array_unique(array_filter(
            array_map('intval', $affected_ref_ids),
            static fn(int $ref_id): bool => $ref_id > 0
        )));
        $this->summary_message_language_var = $summary_message_language_var;
        $this->details = array_values(array_filter(
            $details,
            static fn(mixed $detail): bool => $detail instanceof StaticInputConfigurationIssueDetail
        ));
    }
}
