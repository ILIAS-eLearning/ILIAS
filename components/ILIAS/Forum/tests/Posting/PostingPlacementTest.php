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

namespace ILIAS\Forum\Posting\Test;

use ILIAS\Forum\Posting\PostingPlacement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostingPlacementTest extends TestCase
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
        ];
    }

    #[DataProvider('identifierProvider')]
    public function testForumObjectMatchesOnlyOnEqualPositiveIdentifiers(
        int $stored_obj_id,
        int $addressed_obj_id,
        bool $expected
    ): void {
        $placement = new PostingPlacement(23, $stored_obj_id, 77);

        $this->assertSame($expected, $placement->belongsToForumObject($addressed_obj_id));
    }

    #[DataProvider('identifierProvider')]
    public function testThreadMatchesOnlyOnEqualPositiveIdentifiers(
        int $stored_thread_id,
        int $addressed_thread_id,
        bool $expected
    ): void {
        $placement = new PostingPlacement(23, 4711, $stored_thread_id);

        $this->assertSame($expected, $placement->belongsToThread($addressed_thread_id));
    }
}
