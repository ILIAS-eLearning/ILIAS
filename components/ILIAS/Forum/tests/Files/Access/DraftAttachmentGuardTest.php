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
    private const int OBJ_ID = 1;
    private const int DRAFT_ID = 1;
    private const int AUTHOR_ID = 1;
    private const int OTHER_OBJ_ID = 815;
    private const int OTHER_AUTHOR_ID = 2;

    public function testSmallestDraftIsAccepted(): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->once())
            ->method('findDraftOwnership')
            ->with(self::DRAFT_ID)
            ->willReturn($this->ownership());

        $decision = (new DraftAttachmentGuard($repository, self::AUTHOR_ID))->decide(self::OBJ_ID, self::DRAFT_ID);

        self::assertTrue($decision->isGranted());
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: DenialReason}>
     */
    public static function mismatchProvider(): array
    {
        return [
            'other forum' => [self::OTHER_OBJ_ID, self::AUTHOR_ID, DenialReason::FOREIGN_FORUM],
            'other author' => [self::OBJ_ID, self::OTHER_AUTHOR_ID, DenialReason::FOREIGN_AUTHOR],
        ];
    }

    #[DataProvider('mismatchProvider')]
    public function testDraftOutsideTheRequestIsRejected(int $obj_id, int $author_id, DenialReason $denial): void
    {
        $decision = (new DraftAttachmentGuard($this->repositoryReturningOwnership(), $author_id))
            ->decide($obj_id, self::DRAFT_ID);

        self::assertSame($denial, $decision->denialReason());
    }

    public function testMissingDraftIsRejected(): void
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findDraftOwnership')->willReturn(null);

        $decision = (new DraftAttachmentGuard($repository, self::AUTHOR_ID))->decide(self::OBJ_ID, self::DRAFT_ID);

        self::assertSame(DenialReason::UNKNOWN_CONTAINER, $decision->denialReason());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function incompleteSelectorProvider(): array
    {
        return [
            'forum object zero' => [0, self::DRAFT_ID],
            'forum object negative' => [-1, self::DRAFT_ID],
            'draft zero' => [self::OBJ_ID, 0],
            'draft negative' => [self::OBJ_ID, -1],
        ];
    }

    #[DataProvider('incompleteSelectorProvider')]
    public function testIncompleteSelectorsAreRejectedWithoutAQuery(int $obj_id, int $draft_id): void
    {
        $repository = $this->createMock(OwnershipRepository::class);
        $repository->expects($this->never())->method('findDraftOwnership');

        $decision = (new DraftAttachmentGuard($repository, self::AUTHOR_ID))->decide($obj_id, $draft_id);

        self::assertSame(DenialReason::INCOMPLETE_SELECTORS, $decision->denialReason());
    }

    private function ownership(): AttachmentOwnership
    {
        return new AttachmentOwnership(self::DRAFT_ID, 12, self::OBJ_ID, 1, self::AUTHOR_ID);
    }

    private function repositoryReturningOwnership(): OwnershipRepository
    {
        $repository = $this->createStub(OwnershipRepository::class);
        $repository->method('findDraftOwnership')->willReturn($this->ownership());

        return $repository;
    }
}
