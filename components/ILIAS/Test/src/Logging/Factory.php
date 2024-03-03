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

class Factory
{
    private const NONEXISTENT_TYPE_MSG = 'Invalid Interaction Type in Database for id %s with type %s';
    public function __construct(
        private readonly \ilLanguage $lng
    ) {
    }

    public function buildTestAdministrationInteraction(
        int $ref_id,
        \ilObjUser $admin,
        TestAdministrationInteractionTypes $type,
        array $additional_data
    ): TestAdministrationInteraction {
        return new TestAdministrationInteraction(
            $this->lng,
            $ref_id,
            $admin,
            $type,
            time(),
            $additional_data
        );
    }

    public function buildTestAdministrationInteractionFromDBValues(
        \stdClass $db_values
    ): TestAdministrationInteraction {
        if (($type = TestAdministrationInteractionTypes::tryFrom($db_values->interaction_type)) === null) {
            throw new \ilTestException(
                sprintf(self::NONEXISTENT_TYPE_MSG, [$db_values->id, $db_values->interaction_type])
            );
        }
        return (new TestAdministrationInteraction(
            $this->lng,
            $db_values->ref_id,
            new \ilObjUser($db_values->admin_id),
            $type,
            $db_values->modification_timestamp,
            $db_values->additional_data
        )
        )->withId($db_values->id);
    }

    public function buildTestQuestionAdministrationInteraction(
        int $ref_id,
        int $qst_id,
        \ilObjUser $admin,
        TestQuestionAdministrationInteractionTypes $type,
        array $additional_data
    ): TestQuestionAdministrationInteraction {
        return new TestAdministrationInteraction(
            $this->lng,
            $ref_id,
            $qst_id,
            $admin,
            $type,
            time(),
            $additional_data
        );
    }

    public function buildQuestionAdministrationInteractionFromDBValues(
        \stdClass $db_values
    ): TestQuestionAdministrationInteraction {
        if (($type = TestQuestionAdministrationInteractionTypes::tryFrom($db_values->interaction_type)) === null) {
            throw new \ilTestException(
                sprintf(self::NONEXISTENT_TYPE_MSG, [$db_values->id, $db_values->interaction_type])
            );
        }
        return (new TestQuestionAdministrationInteraction(
            $this->lng,
            $db_values->ref_id,
            $db_values->qst_id,
            new \ilObjUser($db_values->admin_id),
            $type,
            $db_values->modification_timestamp,
            $db_values->additional_data
        ))->withId($db_values->id);
    }

    public function buildParticipantInteraction(
        int $ref_id,
        int $qst_id,
        \ilObjUser $pax,
        TestParticipantInteractionTypes $type,
        array $additional_data
    ): TestParticipantInteraction {
        return new TestParticipantInteraction(
            $this->lng,
            $ref_id,
            $qst_id,
            $pax,
            $_SERVER['REMOTE_ADDR'],
            $type,
            time(),
            $additional_data
        );
    }

    public function buildParticipantInteractionFromDBValues(
        \stdClass $db_values
    ): TestParticipantInteraction {
        if (($type = TestParticipantInteractionTypes::tryFrom($db_values->interaction_type)) === null) {
            throw new \ilTestException(
                sprintf(self::NONEXISTENT_TYPE_MSG, [$db_values->id, $db_values->interaction_type])
            );
        }
        return (new TestParticipantInteraction(
            $this->lng,
            $db_values->ref_id,
            $db_values->qst_id,
            new \ilObjUser($db_values->pax_id),
            $db_values->source_ip,
            $type,
            $db_values->modification_timestamp,
            $db_values->additional_data
        ))->withId($db_values->id);
    }

    public function buildScoringInteraction(
        int $ref_id,
        int $qst_id,
        \ilObjUser $admin,
        \ilObjUser $pax,
        TestScoringInteractionTypes $type,
        array $additional_data
    ): TestScoringInteraction {
        return new TestScoringInteraction(
            $this->lng,
            $ref_id,
            $qst_id,
            $admin,
            $pax,
            $type,
            time(),
            $additional_data
        );
    }

    public function buildScoringInteractionFromDBValues(
        \stdClass $db_values
    ): TestScoringInteraction {
        if (($type = TestScoringInteractionTypes::tryFrom($db_values->interaction_type)) === null) {
            throw new \ilTestException(
                sprintf(self::NONEXISTENT_TYPE_MSG, [$db_values->id, $db_values->interaction_type])
            );
        }
        return (new TestScoringInteraction(
            $this->lng,
            $db_values->ref_id,
            $db_values->qst_id,
            new \ilObjUser($db_values->admin_id),
            new \ilObjUser($db_values->pax_id),
            $type,
            $db_values->modification_timestamp,
            $db_values->additional_data
        ))->withId($db_values->id);
    }

    public function buildError(
        int $ref_id,
        ?int $qst_id,
        ?\ilObjUser $admin,
        ?\ilObjUser $pax,
        TestErrorTypes $type,
        string $error_message
    ): TestError {
        return new TestError(
            $this->lng,
            $ref_id,
            $qst_id,
            $admin,
            $pax,
            $type,
            time(),
            $error_message
        );
    }

    public function buildErrorFromDBValues(\stdClass $db_values): TestError
    {
        if (($type = TestErrorTypes::tryFrom($db_values->interaction_type)) === null) {
            throw new \ilTestException(
                sprintf(self::NONEXISTENT_TYPE_MSG, [$db_values->id, $db_values->interaction_type])
            );
        }

        $administrator = null;
        if ($db_values->admin_id !== null) {
            $administrator = new \ilObjUser($db_values->admin_id);
        }


        $participant = null;
        if ($db_values->admin_id !== null) {
            $participant = new \ilObjUser($db_values->pax_id);
        }

        return (new TestError(
            $this->lng,
            $db_values->ref_id,
            $db_values->qst_id,
            $administrator,
            $participant,
            $type,
            $db_values->modification_timestamp,
            $db_values->error_message
        ))->withId($db_values->id);
    }
}
