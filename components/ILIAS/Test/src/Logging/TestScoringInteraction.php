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

namespace ILIAS\Test\Logging;

class TestScoringInteraction implements TestUserInteraction
{
    /**
    * @param array<string label_lang_var => mixed value> $additional_data
    */
    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly int $test_ref_id,
        private readonly int $question_id,
        private readonly \ilObjUser $administrator,
        private readonly \ilObjUser $participant,
        private readonly TestScoringInteractionTypes $interaction_types,
        private readonly int $modification_timestamp,
        private readonly array $additional_data
    ) {

    }

    public function getTestRefId(): int
    {
        return $this->test_ref_id;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getAdministratorId(): int
    {
        return $this->administrator->getId();
    }

    public function getParticipantId(): int
    {
        return $this->participant->getId();
    }

    public function getInteractionType(): TestScoringInteractionTypes
    {
        return $this->interaction_types;
    }

    public function getModificationTimestamp(): int
    {
        return $this->modification_timestamp;
    }

    public function getLogEntryAsDataTableRow(): array
    {

    }

    public function getLogEntryAsCsvRow(): string
    {

    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->getTestRefId()],
            'qst_id' => [\ilDBConstants::T_INTEGER , $this->getQuestionId()],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->getAdministratorId()],
            'pax_id' => [\ilDBConstants::T_INTEGER , $this->getParticipantId()],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->getInteractionType()->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->getModificationTimestamp()],
            'additional_data' => [\ilDBConstants::T_CLOB , serialize($this->additional_data)]
        ];
    }
}
