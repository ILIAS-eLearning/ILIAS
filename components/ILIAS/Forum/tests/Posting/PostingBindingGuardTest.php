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
use ILIAS\Forum\Posting\PostingBindingGuard;
use ILIAS\Forum\Posting\PostingPlacement;
use ILIAS\Forum\Posting\PostingPlacementRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostingBindingGuardTest extends TestCase
{
    private const int OBJ_ID = 1;
    private const int THREAD_ID = 1;
    private const int POSTING_ID = 1;
    private const int OTHER_OBJ_ID = 815;
    private const int OTHER_THREAD_ID = 2;

    public function testSmallestAddressedPostingIsAccepted(): void
    {
        $postings = $this->createMock(PostingPlacementRepository::class);
        $postings->expects($this->once())
            ->method('findPosting')
            ->with(self::POSTING_ID)
            ->willReturn(new PostingPlacement(self::POSTING_ID, self::OBJ_ID, self::THREAD_ID));

        $decision = (new PostingBindingGuard($postings))->decide(self::OBJ_ID, self::THREAD_ID, self::POSTING_ID);

        self::assertTrue($decision->isGranted());
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: BindingDenial}>
     */
    public static function mismatchProvider(): array
    {
        return [
            'other forum' => [self::OTHER_OBJ_ID, self::THREAD_ID, BindingDenial::FOREIGN_FORUM],
            'other thread' => [self::OBJ_ID, self::OTHER_THREAD_ID, BindingDenial::FOREIGN_THREAD],
        ];
    }

    #[DataProvider('mismatchProvider')]
    public function testPostingOutsideTheRequestIsRejected(int $obj_id, int $thread_id, BindingDenial $denial): void
    {
        $decision = (new PostingBindingGuard($this->repositoryReturningPosting()))
            ->decide($obj_id, $thread_id, self::POSTING_ID);

        self::assertSame($denial, $decision->denialReason());
    }

    public function testMissingPostingIsRejected(): void
    {
        $postings = $this->createStub(PostingPlacementRepository::class);
        $postings->method('findPosting')->willReturn(null);

        $decision = (new PostingBindingGuard($postings))->decide(self::OBJ_ID, self::THREAD_ID, self::POSTING_ID);

        self::assertSame(BindingDenial::UNKNOWN_POSTING, $decision->denialReason());
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: int}>
     */
    public static function incompleteSelectorProvider(): array
    {
        return [
            'forum object zero' => [0, self::THREAD_ID, self::POSTING_ID],
            'forum object negative' => [-1, self::THREAD_ID, self::POSTING_ID],
            'thread zero' => [self::OBJ_ID, 0, self::POSTING_ID],
            'thread negative' => [self::OBJ_ID, -1, self::POSTING_ID],
            'posting zero' => [self::OBJ_ID, self::THREAD_ID, 0],
            'posting negative' => [self::OBJ_ID, self::THREAD_ID, -1],
        ];
    }

    #[DataProvider('incompleteSelectorProvider')]
    public function testIncompleteSelectorsAreRejectedWithoutAQuery(
        int $obj_id,
        int $thread_id,
        int $posting_id
    ): void {
        $postings = $this->createMock(PostingPlacementRepository::class);
        $postings->expects($this->never())->method('findPosting');

        $decision = (new PostingBindingGuard($postings))->decide($obj_id, $thread_id, $posting_id);

        self::assertSame(BindingDenial::INCOMPLETE_SELECTORS, $decision->denialReason());
    }

    private function repositoryReturningPosting(): PostingPlacementRepository
    {
        $postings = $this->createStub(PostingPlacementRepository::class);
        $postings->method('findPosting')->willReturn(
            new PostingPlacement(self::POSTING_ID, self::OBJ_ID, self::THREAD_ID)
        );

        return $postings;
    }
}
