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
    private const int OWNER_OBJ_ID = 4711;
    private const int DECOY_OBJ_ID = 815;
    private const int POSTING_ID = 23;
    private const int THREAD_ID = 77;

    public function testPostingIsDeliverableThroughItsOwningForum(): void
    {
        $guard = new PostingAttachmentGuard($this->repositoryReturningOwnedPosting());

        $this->assertTrue($guard->decide(self::OWNER_OBJ_ID, self::POSTING_ID)->isGranted());
    }

    public function testPostingIsNotDeliverableThroughADecoyForum(): void
    {
        $guard = new PostingAttachmentGuard($this->repositoryReturningOwnedPosting());

        $this->assertSame(
            DenialReason::FOREIGN_FORUM,
            $guard->decide(self::DECOY_OBJ_ID, self::POSTING_ID)->denialReason()
        );
    }

    public function testPostingIsDeliverableWhenTheRoutedThreadMatches(): void
    {
        $guard = new PostingAttachmentGuard($this->repositoryReturningOwnedPosting(), self::THREAD_ID);

        $this->assertTrue($guard->decide(self::OWNER_OBJ_ID, self::POSTING_ID)->isGranted());
    }

    public function testPostingOfAnotherThreadIsRejectedWhenTheRequestIsRoutedThroughAThread(): void
    {
        $guard = new PostingAttachmentGuard($this->repositoryReturningOwnedPosting(), self::THREAD_ID + 1);

        $this->assertSame(
            DenialReason::FOREIGN_THREAD,
            $guard->decide(self::OWNER_OBJ_ID, self::POSTING_ID)->denialReason()
        );
    }

    public function testUnknownPostingIsRejected(): void
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findPostingOwnership')->willReturn(null);

        $guard = new PostingAttachmentGuard($repository);

        $this->assertSame(
            DenialReason::UNKNOWN_CONTAINER,
            $guard->decide(self::OWNER_OBJ_ID, self::POSTING_ID)->denialReason()
        );
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteSelectorProvider(): array
    {
        return [
            'unset forum object' => [0, self::POSTING_ID],
            'negative forum object' => [-1, self::POSTING_ID],
            'unset posting' => [self::OWNER_OBJ_ID, 0],
            'negative posting' => [self::OWNER_OBJ_ID, -1],
        ];
    }

    #[DataProvider('incompleteSelectorProvider')]
    public function testIncompleteSelectorsAreRejectedWithoutQueryingTheRepository(
        int $routed_obj_id,
        int $posting_id
    ): void {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->never())->method('findPostingOwnership');

        $guard = new PostingAttachmentGuard($repository, self::THREAD_ID);

        $this->assertSame(
            DenialReason::INCOMPLETE_SELECTORS,
            $guard->decide($routed_obj_id, $posting_id)->denialReason()
        );
    }

    public function testOwnershipIsResolvedForTheAddressedPosting(): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->once())
                   ->method('findPostingOwnership')
                   ->with(self::POSTING_ID)
                   ->willReturn(null);

        (new PostingAttachmentGuard($repository))->decide(self::OWNER_OBJ_ID, self::POSTING_ID);
    }

    private function repositoryReturningOwnedPosting(): OwnershipRepository
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findPostingOwnership')->willReturn(
            new AttachmentOwnership(self::POSTING_ID, 12, self::OWNER_OBJ_ID, self::THREAD_ID, 6)
        );

        return $repository;
    }
}
