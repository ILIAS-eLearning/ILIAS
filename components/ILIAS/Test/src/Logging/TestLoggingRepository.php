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

use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 *
 * @admin skergomard
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
        array $valid_types,
        ?array $test_filter,
        ?Range $range = null,
        ?Order $order = null,
        ?int $from_filter = null,
        ?int $to_filter = null,
        ?array $admin_filter = null,
        ?array $pax_filter = null,
        ?array $question_filter = null,
        ?string $ip_filter = null,
        ?array $log_entry_type_filter = null,
        ?array $interaction_type_filter = null
    ): \Generator;

    public function getLogsCount(
        array $valid_types,
        ?array $test_filter,
        ?int $from_filter = null,
        ?int $to_filter = null,
        ?array $admin_filter = null,
        ?array $pax_filter = null,
        ?array $question_filter = null,
        ?string $ip_filter = null,
        ?array $log_entry_type_filter = null,
        ?array $interaction_type_filter = null
    ): int;

    /**
     * @param array<string> $unique_identifiers
     * @return array<\ILIAS\Test\Logging\TestUserInteraction>
     */
    public function getLogsByUniqueIdentifiers(array $unique_identifiers): \Generator;

    public function getLog(string $unique_identifier): ?TestUserInteraction;

    /**
     * @param array<string> $unique_identifiers
     */
    public function deleteLogs(array $unique_identifiers): void;

    public function testHasParticipantInteractions(int $ref_id): bool;
    public function deleteParticipantInteractionsForTest(int $ref_id): void;

    public function getLegacyLogsForObjId(?int $obj_id): array;
}
