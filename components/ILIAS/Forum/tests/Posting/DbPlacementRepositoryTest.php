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

use ilDBConstants;
use ilDBInterface;
use ilDBStatement;
use ILIAS\Forum\Posting\DbPlacementRepository;
use PHPUnit\Framework\TestCase;

class DbPlacementRepositoryTest extends TestCase
{
    public function testPostingRowIsMappedToItsForumObjectAndThread(): void
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->callback(static fn(string $sql): bool => str_contains($sql, 'frm_posts posts')),
                [ilDBConstants::T_INTEGER],
                [23]
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('fetchAssoc')
            ->with($statement)
            ->willReturn([
                'pos_pk' => '23',
                'pos_thr_fk' => '77',
                'top_frm_fk' => '4711',
            ]);

        $placement = (new DbPlacementRepository($db))->findPosting(23);

        $this->assertNotNull($placement);
        $this->assertSame(23, $placement->posting_id);
        $this->assertSame(4711, $placement->obj_id);
        $this->assertSame(77, $placement->thread_id);
    }

    public function testMissingPostingRowIsAbsent(): void
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(null);

        $this->assertNull((new DbPlacementRepository($db))->findPosting(23));
    }

    public function testNonPositivePostingIdDoesNotQuery(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('queryF');

        $this->assertNull((new DbPlacementRepository($db))->findPosting(0));
    }

    public function testDraftRowKeepsTheParentPosting(): void
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())
            ->method('queryF')
            ->with(
                $this->callback(static fn(string $sql): bool => str_contains($sql, 'frm_posts_drafts drafts')),
                [ilDBConstants::T_INTEGER],
                [42]
            )
            ->willReturn($statement);
        $db->method('fetchAssoc')->willReturn([
            'draft_id' => '42',
            'thread_id' => '77',
            'post_id' => '23',
            'post_author_id' => '6',
            'top_frm_fk' => '4711',
        ]);

        $placement = (new DbPlacementRepository($db))->findDraft(42);

        $this->assertNotNull($placement);
        $this->assertSame(42, $placement->draft_id);
        $this->assertSame(4711, $placement->obj_id);
        $this->assertSame(77, $placement->thread_id);
        $this->assertSame(6, $placement->author_id);
        $this->assertSame(23, $placement->posting_id);
    }

    public function testNonPositiveDraftIdDoesNotQuery(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('queryF');

        $this->assertNull((new DbPlacementRepository($db))->findDraft(0));
    }
}
