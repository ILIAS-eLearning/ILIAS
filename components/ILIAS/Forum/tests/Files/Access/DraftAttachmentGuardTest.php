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
use ILIAS\Forum\Files\Access\DraftAttachmentGuard;
use ILIAS\Forum\Files\Access\OwnershipRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DraftAttachmentGuardTest extends TestCase
{
    private const int OWNER_OBJ_ID = 4711;
    private const int DECOY_OBJ_ID = 815;
    private const int DRAFT_ID = 42;
    private const int AUTHOR_ID = 6;

    public function testAuthorMayDownloadFromTheOwningForum(): void
    {
        $guard = new DraftAttachmentGuard($this->repositoryReturningDraft(), self::AUTHOR_ID);

        $this->assertTrue($guard->decide(self::OWNER_OBJ_ID, self::DRAFT_ID)->isGranted());
    }

    public function testDraftIsNotDeliverableThroughADecoyForum(): void
    {
        $guard = new DraftAttachmentGuard($this->repositoryReturningDraft(), self::AUTHOR_ID);

        $this->assertSame(
            DenialReason::FOREIGN_FORUM,
            $guard->decide(self::DECOY_OBJ_ID, self::DRAFT_ID)->denialReason()
        );
    }

    public function testDraftOfAnotherAuthorIsRejected(): void
    {
        $guard = new DraftAttachmentGuard($this->repositoryReturningDraft(), self::AUTHOR_ID + 1);

        $this->assertSame(
            DenialReason::FOREIGN_AUTHOR,
            $guard->decide(self::OWNER_OBJ_ID, self::DRAFT_ID)->denialReason()
        );
    }

    public function testAnonymousUserIsRejectedEvenIfTheDraftCarriesNoAuthor(): void
    {
        $guard = new DraftAttachmentGuard($this->repositoryReturningDraft(0), 0);

        $this->assertSame(
            DenialReason::FOREIGN_AUTHOR,
            $guard->decide(self::OWNER_OBJ_ID, self::DRAFT_ID)->denialReason()
        );
    }

    public function testUnknownDraftIsRejected(): void
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findDraftOwnership')->willReturn(null);

        $guard = new DraftAttachmentGuard($repository, self::AUTHOR_ID);

        $this->assertSame(
            DenialReason::UNKNOWN_CONTAINER,
            $guard->decide(self::OWNER_OBJ_ID, self::DRAFT_ID)->denialReason()
        );
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteSelectorProvider(): array
    {
        return [
            'unset forum object' => [0, self::DRAFT_ID],
            'negative forum object' => [-1, self::DRAFT_ID],
            'unset draft' => [self::OWNER_OBJ_ID, 0],
            'negative draft' => [self::OWNER_OBJ_ID, -1],
        ];
    }

    #[DataProvider('incompleteSelectorProvider')]
    public function testIncompleteSelectorsAreRejectedWithoutQueryingTheRepository(
        int $routed_obj_id,
        int $draft_id
    ): void {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->never())->method('findDraftOwnership');

        $guard = new DraftAttachmentGuard($repository, self::AUTHOR_ID);

        $this->assertSame(
            DenialReason::INCOMPLETE_SELECTORS,
            $guard->decide($routed_obj_id, $draft_id)->denialReason()
        );
    }

    public function testOwnershipIsResolvedForTheAddressedDraft(): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->once())
                   ->method('findDraftOwnership')
                   ->with(self::DRAFT_ID)
                   ->willReturn(null);

        (new DraftAttachmentGuard($repository, self::AUTHOR_ID))->decide(self::OWNER_OBJ_ID, self::DRAFT_ID);
    }

    private function repositoryReturningDraft(int $author_id = self::AUTHOR_ID): OwnershipRepository
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findDraftOwnership')->willReturn(
            new AttachmentOwnership(self::DRAFT_ID, 12, self::OWNER_OBJ_ID, 77, $author_id)
        );

        return $repository;
    }
}
