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

namespace ILIAS\Forum\Files\Access\Test;

use ILIAS\Forum\Files\Access\AttachmentOwnership;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttachmentOwnershipTest extends TestCase
{
    /**
     * @return array<string, array{0: int, 1: int, 2: bool}>
     */
    public static function identifierProvider(): array
    {
        return [
            'addressed identifier matches' => [4711, 4711, true],
            'addressed identifier differs' => [4711, 815, false],
            'both identifiers are unset' => [0, 0, false],
            'both identifiers are negative' => [-4711, -4711, false],
        ];
    }

    #[DataProvider('identifierProvider')]
    public function testForumObjectMatchesOnlyOnEqualPositiveIdentifiers(
        int $stored_obj_id,
        int $addressed_obj_id,
        bool $expected
    ): void {
        $ownership = new AttachmentOwnership(23, 12, $stored_obj_id, 77, 6);

        $this->assertSame($expected, $ownership->isOwnedByForumObject($addressed_obj_id));
    }

    #[DataProvider('identifierProvider')]
    public function testThreadMatchesOnlyOnEqualPositiveIdentifiers(
        int $stored_thread_id,
        int $addressed_thread_id,
        bool $expected
    ): void {
        $ownership = new AttachmentOwnership(23, 12, 4711, $stored_thread_id, 6);

        $this->assertSame($expected, $ownership->isPartOfThread($addressed_thread_id));
    }

    #[DataProvider('identifierProvider')]
    public function testAuthorshipMatchesOnlyOnEqualPositiveIdentifiers(
        int $stored_author_id,
        int $acting_usr_id,
        bool $expected
    ): void {
        $ownership = new AttachmentOwnership(23, 12, 4711, 77, $stored_author_id);

        $this->assertSame($expected, $ownership->isAuthoredBy($acting_usr_id));
    }
}
