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

use ILIAS\Forum\Files\Access\AccessDecision;
use ILIAS\Forum\Files\Access\AddressedAttachmentDelivery;
use ILIAS\Forum\Files\Access\AttachmentAccessDenied;
use ILIAS\Forum\Files\Access\AttachmentAccessGuard;
use ILIAS\Forum\Files\Access\DenialReason;
use ILIAS\Forum\Files\Access\GuardedAttachmentDelivery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuardedAttachmentDeliveryTest extends TestCase
{
    private const int OBJ_ID = 4711;
    private const int POSTING_ID = 23;
    private const string REVISION_TOKEN = 'd41d8cd98f00b204e9800998ecf8427e';

    /**
     * @return array<string, array{0: bool}>
     */
    public static function zipResultProvider(): array
    {
        return [
            'archive was delivered' => [true],
            'archive was not delivered' => [false],
        ];
    }

    public function testGrantedRequestForwardsTheAddressedRevision(): void
    {
        $storage = $this->storageMock();
        $storage->expects($this->once())->method('deliverFile')->with(self::REVISION_TOKEN);

        $delivery = new GuardedAttachmentDelivery($storage, $this->guard(AccessDecision::granted()));
        $delivery->deliverFile(self::REVISION_TOKEN);
    }

    #[DataProvider('zipResultProvider')]
    public function testGrantedRequestReturnsTheZipResultOfTheStorage(bool $delivered): void
    {
        $storage = $this->storageMock();
        $storage->expects($this->once())->method('deliverZipFile')->willReturn($delivered);

        $delivery = new GuardedAttachmentDelivery($storage, $this->guard(AccessDecision::granted()));

        $this->assertSame($delivered, $delivery->deliverZipFile());
    }

    public function testDeniedRequestNamesItsReasonAndNeverReachesTheStorage(): void
    {
        $storage = $this->storageMock();
        $storage->expects($this->never())->method('deliverFile');

        $delivery = new GuardedAttachmentDelivery(
            $storage,
            $this->guard(AccessDecision::denied(DenialReason::UNKNOWN_CONTAINER))
        );

        $this->expectExceptionObject(AttachmentAccessDenied::because(DenialReason::UNKNOWN_CONTAINER));

        $delivery->deliverFile(self::REVISION_TOKEN);
    }

    public function testDeniedZipRequestNamesItsReasonAndNeverReachesTheStorage(): void
    {
        $storage = $this->storageMock();
        $storage->expects($this->never())->method('deliverZipFile');

        $delivery = new GuardedAttachmentDelivery(
            $storage,
            $this->guard(AccessDecision::denied(DenialReason::FOREIGN_FORUM))
        );

        $this->expectExceptionObject(AttachmentAccessDenied::because(DenialReason::FOREIGN_FORUM));

        $delivery->deliverZipFile();
    }

    public function testGuardJudgesTheIdentifiersAddressedByTheStorage(): void
    {
        $guard = $this->createMock(AttachmentAccessGuard::class);
        $guard->expects($this->once())
              ->method('decide')
              ->with(self::OBJ_ID, self::POSTING_ID)
              ->willReturn(AccessDecision::granted());

        (new GuardedAttachmentDelivery($this->storage(), $guard))->deliverFile(self::REVISION_TOKEN);
    }

    public function testDecoratorExposesTheIdentifiersOfTheWrappedDelivery(): void
    {
        $delivery = new GuardedAttachmentDelivery($this->storage(), $this->guard(AccessDecision::granted()));

        $this->assertSame(self::OBJ_ID, $delivery->forumObjId());
        $this->assertSame(self::POSTING_ID, $delivery->containerId());
    }

    private function storage(): AddressedAttachmentDelivery
    {
        $storage = $this->createStub(AddressedAttachmentDelivery::class);
        $storage->method('forumObjId')->willReturn(self::OBJ_ID);
        $storage->method('containerId')->willReturn(self::POSTING_ID);

        return $storage;
    }

    /**
     * @return MockObject&AddressedAttachmentDelivery
     */
    private function storageMock(): MockObject
    {
        $storage = $this->createMock(AddressedAttachmentDelivery::class);
        $storage->method('forumObjId')->willReturn(self::OBJ_ID);
        $storage->method('containerId')->willReturn(self::POSTING_ID);

        return $storage;
    }

    private function guard(AccessDecision $decision): AttachmentAccessGuard
    {
        $guard = $this->createStub(AttachmentAccessGuard::class);
        $guard->method('decide')->willReturn($decision);

        return $guard;
    }
}
