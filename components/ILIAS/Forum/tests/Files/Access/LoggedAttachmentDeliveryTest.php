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

use ILIAS\Forum\Files\Access\AddressedAttachmentDelivery;
use ILIAS\Forum\Files\Access\AttachmentAccessDenied;
use ILIAS\Forum\Files\Access\DenialReason;
use ILIAS\Forum\Files\Access\LoggedAttachmentDelivery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ilLogger;

class LoggedAttachmentDeliveryTest extends TestCase
{
    private const int OBJ_ID = 4711;
    private const int POSTING_ID = 23;
    private const int USR_ID = 6;
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

    public function testDeniedRequestIsRecordedWithItsSelectorsAndRethrown(): void
    {
        $inner = $this->deliveryMock();
        $inner->expects($this->once())->method('deliverFile')->willThrowException(
            AttachmentAccessDenied::because(DenialReason::FOREIGN_FORUM)
        );

        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())
               ->method('warning')
               ->with(
                   self::stringContains(DenialReason::FOREIGN_FORUM->logMessage()),
                   [
                       'reason' => DenialReason::FOREIGN_FORUM->value,
                       'forum_obj_id' => self::OBJ_ID,
                       'container_id' => self::POSTING_ID,
                       'usr_id' => self::USR_ID
                   ]
               );

        $this->expectExceptionObject(AttachmentAccessDenied::because(DenialReason::FOREIGN_FORUM));

        (new LoggedAttachmentDelivery($inner, $logger, self::USR_ID))->deliverFile(self::REVISION_TOKEN);
    }

    public function testDeniedZipRequestIsRecordedWithItsOwnReasonAndRethrown(): void
    {
        $inner = $this->deliveryMock();
        $inner->expects($this->once())->method('deliverZipFile')->willThrowException(
            AttachmentAccessDenied::because(DenialReason::UNKNOWN_CONTAINER)
        );

        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())
               ->method('warning')
               ->with(self::stringContains(DenialReason::UNKNOWN_CONTAINER->logMessage()), self::anything());

        $this->expectExceptionObject(AttachmentAccessDenied::because(DenialReason::UNKNOWN_CONTAINER));

        (new LoggedAttachmentDelivery($inner, $logger, self::USR_ID))->deliverZipFile();
    }

    public function testGrantedRequestForwardsTheAddressedRevisionWithoutBeingRecorded(): void
    {
        $inner = $this->deliveryMock();
        $inner->expects($this->once())->method('deliverFile')->with(self::REVISION_TOKEN);

        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->never())->method('warning');

        (new LoggedAttachmentDelivery($inner, $logger, self::USR_ID))->deliverFile(self::REVISION_TOKEN);
    }

    #[DataProvider('zipResultProvider')]
    public function testGrantedRequestKeepsTheZipResultOfTheWrappedDelivery(bool $delivered): void
    {
        $inner = $this->deliveryMock();
        $inner->expects($this->once())->method('deliverZipFile')->willReturn($delivered);

        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->never())->method('warning');

        $delivery = new LoggedAttachmentDelivery($inner, $logger, self::USR_ID);

        $this->assertSame($delivered, $delivery->deliverZipFile());
    }

    public function testFailuresOtherThanAccessDenialAreNeitherRecordedNorSwallowed(): void
    {
        $inner = $this->deliveryMock();
        $inner->expects($this->once())->method('deliverFile')->willThrowException(
            new RuntimeException('The storage is unavailable')
        );

        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->never())->method('warning');

        $this->expectException(RuntimeException::class);

        (new LoggedAttachmentDelivery($inner, $logger, self::USR_ID))->deliverFile(self::REVISION_TOKEN);
    }

    public function testDecoratorExposesTheIdentifiersOfTheWrappedDelivery(): void
    {
        $delivery = new LoggedAttachmentDelivery(
            $this->delivery(),
            $this->createStub(ilLogger::class),
            self::USR_ID
        );

        $this->assertSame(self::OBJ_ID, $delivery->forumObjId());
        $this->assertSame(self::POSTING_ID, $delivery->containerId());
    }

    private function delivery(): AddressedAttachmentDelivery
    {
        $delivery = $this->createStub(AddressedAttachmentDelivery::class);
        $delivery->method('forumObjId')->willReturn(self::OBJ_ID);
        $delivery->method('containerId')->willReturn(self::POSTING_ID);

        return $delivery;
    }

    /**
     * @return MockObject&AddressedAttachmentDelivery
     */
    private function deliveryMock(): MockObject
    {
        $delivery = $this->createMock(AddressedAttachmentDelivery::class);
        $delivery->method('forumObjId')->willReturn(self::OBJ_ID);
        $delivery->method('containerId')->willReturn(self::POSTING_ID);

        return $delivery;
    }
}
