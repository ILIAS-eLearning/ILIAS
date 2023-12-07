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

class TestQuestionAdministrationInteraction implements TestUserInteraction
{
    /**
    * @param array<string label_lang_var => mixed value> $additional_data
    */
    public function __construct(
        private ilLanguage $lng,
        private int $test_ref_id,
        private int $question_id,
        private \ilObjUser $administrator,
        private TestQuestionAdministrationInteractionTypes $interaction_types,
        private int $modification_timestamp,
        private array $additional_data
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

    public function getInteractionType(): TestQuestionAdministrationInteractionTypes
    {
        return $this->interaction_types;
    }

    public function getAdministratorId(): int
    {
        return $this->administrator->getId();
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
}
