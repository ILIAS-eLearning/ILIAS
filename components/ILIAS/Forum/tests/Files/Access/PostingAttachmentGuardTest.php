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
use ILIAS\Forum\Files\Access\DenialReason;
use ILIAS\Forum\Files\Access\OwnershipRepository;
use ILIAS\Forum\Files\Access\PostingAttachmentGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostingAttachmentGuardTest extends TestCase
{
    private const int OBJ_ID = 1;
    private const int POSTING_ID = 1;
    private const int THREAD_ID = 1;
    private const int OTHER_OBJ_ID = 815;
    private const int OTHER_THREAD_ID = 2;

    public function testSmallestPostingIsAcceptedWithoutAThreadConstraint(): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->once())
            ->method('findPostingOwnership')
            ->with(self::POSTING_ID)
            ->willReturn($this->ownership());

        $decision = (new PostingAttachmentGuard($repository))->decide(self::OBJ_ID, self::POSTING_ID);

        self::assertTrue($decision->isGranted());
    }

    public function testPostingIsAcceptedWhenTheRoutedThreadMatches(): void
    {
        $decision = (new PostingAttachmentGuard($this->repositoryReturningOwnership(), self::THREAD_ID))
            ->decide(self::OBJ_ID, self::POSTING_ID);

        self::assertTrue($decision->isGranted());
    }

    /**
     * @return array<string, array{0: ?int, 1: int, 2: DenialReason}>
     */
    public static function mismatchProvider(): array
    {
        return [
            'other forum' => [null, self::OTHER_OBJ_ID, DenialReason::FOREIGN_FORUM],
            'other thread' => [self::OTHER_THREAD_ID, self::OBJ_ID, DenialReason::FOREIGN_THREAD],
        ];
    }

    #[DataProvider('mismatchProvider')]
    public function testPostingOutsideTheRequestIsRejected(?int $thread_id, int $obj_id, DenialReason $denial): void
    {
        $decision = (new PostingAttachmentGuard($this->repositoryReturningOwnership(), $thread_id))
            ->decide($obj_id, self::POSTING_ID);

        self::assertSame($denial, $decision->denialReason());
    }

    public function testMissingPostingIsRejected(): void
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findPostingOwnership')->willReturn(null);

        $decision = (new PostingAttachmentGuard($repository))->decide(self::OBJ_ID, self::POSTING_ID);

        self::assertSame(DenialReason::UNKNOWN_CONTAINER, $decision->denialReason());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteSelectorProvider(): array
    {
        return [
            'forum object zero' => [0, self::POSTING_ID],
            'forum object negative' => [-1, self::POSTING_ID],
            'posting zero' => [self::OBJ_ID, 0],
            'posting negative' => [self::OBJ_ID, -1],
        ];
    }

    #[DataProvider('incompleteSelectorProvider')]
    public function testIncompleteSelectorsAreRejectedWithoutAQuery(int $obj_id, int $posting_id): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->never())->method('findPostingOwnership');

        $decision = (new PostingAttachmentGuard($repository, self::THREAD_ID))->decide($obj_id, $posting_id);

        self::assertSame(DenialReason::INCOMPLETE_SELECTORS, $decision->denialReason());
    }

    private function ownership(): AttachmentOwnership
    {
        return new AttachmentOwnership(self::POSTING_ID, 12, self::OBJ_ID, self::THREAD_ID, 6);
    }

    private function repositoryReturningOwnership(): OwnershipRepository
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findPostingOwnership')->willReturn($this->ownership());

        return $repository;
    }
}
