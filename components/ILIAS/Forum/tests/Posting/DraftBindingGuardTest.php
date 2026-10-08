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

use ILIAS\Forum\Posting\BindingDenial;
use ILIAS\Forum\Posting\DraftBindingGuard;
use ILIAS\Forum\Posting\DraftPlacement;
use ILIAS\Forum\Posting\DraftPlacementRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DraftBindingGuardTest extends TestCase
{
    private const int OBJ_ID = 1;
    private const int DRAFT_ID = 1;
    private const int AUTHOR_ID = 1;
    private const int THREAD_ID = 1;
    private const int POSTING_ID = 1;
    private const int OTHER_OBJ_ID = 815;
    private const int OTHER_AUTHOR_ID = 2;
    private const int OTHER_THREAD_ID = 2;
    private const int OTHER_POSTING_ID = 2;

    public function testSmallestReplyDraftIsAccepted(): void
    {
        $drafts = $this->createMock(DraftPlacementRepository::class);
        $drafts->expects($this->once())
            ->method('findDraft')
            ->with(self::DRAFT_ID)
            ->willReturn($this->placement());

        $decision = (new DraftBindingGuard($drafts, self::AUTHOR_ID))
            ->decideReply(self::OBJ_ID, self::THREAD_ID, self::POSTING_ID, self::DRAFT_ID);

        self::assertTrue($decision->isGranted());
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: int, 3: int, 4: BindingDenial}>
     */
    public static function replyMismatchProvider(): array
    {
        return [
            'other forum' => [self::OTHER_OBJ_ID, self::AUTHOR_ID, self::THREAD_ID, self::POSTING_ID, BindingDenial::FOREIGN_FORUM],
            'other author' => [self::OBJ_ID, self::OTHER_AUTHOR_ID, self::THREAD_ID, self::POSTING_ID, BindingDenial::FOREIGN_AUTHOR],
            'other thread' => [self::OBJ_ID, self::AUTHOR_ID, self::OTHER_THREAD_ID, self::POSTING_ID, BindingDenial::FOREIGN_THREAD],
            'other posting' => [self::OBJ_ID, self::AUTHOR_ID, self::THREAD_ID, self::OTHER_POSTING_ID, BindingDenial::FOREIGN_POSTING],
        ];
    }

    #[DataProvider('replyMismatchProvider')]
    public function testReplyDraftOutsideTheRequestIsRejected(
        int $obj_id,
        int $author_id,
        int $thread_id,
        int $posting_id,
        BindingDenial $denial
    ): void {
        $decision = (new DraftBindingGuard($this->repositoryReturning($this->placement()), $author_id))
            ->decideReply($obj_id, $thread_id, $posting_id, self::DRAFT_ID);

        self::assertSame($denial, $decision->denialReason());
    }

    public function testMissingReplyDraftIsRejected(): void
    {
        $drafts = $this->createStub(DraftPlacementRepository::class);
        $drafts->method('findDraft')->willReturn(null);

        $decision = (new DraftBindingGuard($drafts, self::AUTHOR_ID))
            ->decideReply(self::OBJ_ID, self::THREAD_ID, self::POSTING_ID, self::DRAFT_ID);

        self::assertSame(BindingDenial::UNKNOWN_DRAFT, $decision->denialReason());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteReplyProvider(): array
    {
        return [
            'thread zero' => [0, self::POSTING_ID],
            'thread negative' => [-1, self::POSTING_ID],
            'posting zero' => [self::THREAD_ID, 0],
            'posting negative' => [self::THREAD_ID, -1],
        ];
    }

    #[DataProvider('incompleteReplyProvider')]
    public function testReplyWithoutThreadOrPostingIsRejectedWithoutAQuery(int $thread_id, int $posting_id): void
    {
        $drafts = $this->createMock(DraftPlacementRepository::class);
        $drafts->expects($this->never())->method('findDraft');

        $decision = (new DraftBindingGuard($drafts, self::AUTHOR_ID))
            ->decideReply(self::OBJ_ID, $thread_id, $posting_id, self::DRAFT_ID);

        self::assertSame(BindingDenial::INCOMPLETE_SELECTORS, $decision->denialReason());
    }

    public function testSmallestUnboundThreadDraftIsAccepted(): void
    {
        $guard = new DraftBindingGuard(
            $this->repositoryReturning($this->placement(0, 0)),
            self::AUTHOR_ID
        );

        $decision = $guard->decideUnboundThread(self::OBJ_ID, self::DRAFT_ID);

        self::assertTrue($decision->isGranted());
    }

    public function testThreadDraftBoundToAThreadIsRejected(): void
    {
        $decision = (new DraftBindingGuard($this->repositoryReturning($this->placement()), self::AUTHOR_ID))
            ->decideUnboundThread(self::OBJ_ID, self::DRAFT_ID);

        self::assertSame(BindingDenial::BOUND_TO_THREAD, $decision->denialReason());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteDraftProvider(): array
    {
        return [
            'forum object zero' => [0, self::DRAFT_ID],
            'forum object negative' => [-1, self::DRAFT_ID],
            'draft zero' => [self::OBJ_ID, 0],
            'draft negative' => [self::OBJ_ID, -1],
        ];
    }

    #[DataProvider('incompleteDraftProvider')]
    public function testIncompleteDraftSelectorsAreRejectedWithoutAQuery(int $obj_id, int $draft_id): void
    {
        $drafts = $this->createMock(DraftPlacementRepository::class);
        $drafts->expects($this->never())->method('findDraft');

        $decision = (new DraftBindingGuard($drafts, self::AUTHOR_ID))->decideUnboundThread($obj_id, $draft_id);

        self::assertSame(BindingDenial::INCOMPLETE_SELECTORS, $decision->denialReason());
    }

    private function placement(int $thread_id = self::THREAD_ID, int $posting_id = self::POSTING_ID): DraftPlacement
    {
        return new DraftPlacement(self::DRAFT_ID, self::OBJ_ID, $thread_id, self::AUTHOR_ID, $posting_id);
    }

    private function repositoryReturning(DraftPlacement $placement): DraftPlacementRepository
    {
        $drafts = $this->createStub(DraftPlacementRepository::class);
        $drafts->method('findDraft')->willReturn($placement);

        return $drafts;
    }
}
