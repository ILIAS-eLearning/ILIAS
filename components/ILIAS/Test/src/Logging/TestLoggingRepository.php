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

namespace ILIAS\Test\Logging;

/**
 *
 * @author skergomard
 */
interface TestLoggingRepository
{
    public function storeTestAdministrationInteraction(TestAdministrationInteraction $interaction): void;
    public function storeQuestionAdministrationInteraction(TestQuestionAdministrationInteraction $interaction): void;
    public function storeParticipantInteraction(TestParticipantInteraction $interaction): void;
    public function storeScoringInteraction(TestScoringInteraction $interaction): void;
    public function storeError(TestError $interaction): void;

    /**
     * @return array<\ILIAS\Test\Logging\TestUserInteraction>
     */
    public function getLogs(
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        array $filter_data,
        ?int $ref_id
    ): array;

    public function getLog(string $unique_identifier): TestUserInteraction;

    /**
     * @param array<string> $unique_identifiers
     */
    public function deleteLogs(array $unique_identifiers): void;

    public function getLegacyLogsForObjId(int $obj_id = null, bool $without_student_interactions = false): array;
}
