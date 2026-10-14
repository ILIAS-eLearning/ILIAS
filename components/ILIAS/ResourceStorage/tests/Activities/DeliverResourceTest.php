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

namespace ILIAS\ResourceStorage\Activities;

use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeliverResourceTest extends TestCase
{
    private const int OWNER = 42;
    private const int SOMEBODY_ELSE = 43;
    private const string SOME_RID = 'a-resource';

    private ServiceProvider&MockObject $storage;
    private DeliverResource $activity;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(ServiceProvider::class);

        $this->activity = new DeliverResource(
            $this->storage,
            new FilePayloadFactory(),
        );
    }

    public function testTheOwnerGetsTheResource(): void
    {
        $this->givenAResourceOwnedBy(self::OWNER);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::OWNER, ['rid' => self::SOME_RID]);

        $this->assertTrue($result->isOK());

        $payload = $result->value();

        $this->assertInstanceOf(FilePayload::class, $payload);
        $this->assertSame('notes.txt', $payload->filename);
        $this->assertSame(base64_encode('some content'), $payload->content);
    }

    public function testNobodyElseGetsItThroughTheApi(): void
    {
        $this->givenAResourceOwnedBy(self::OWNER);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOMEBODY_ELSE, ['rid' => self::SOME_RID]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testAnonymousRequestsGetNothing(): void
    {
        // a request without a user is turned down before the storage is asked
        $this->storage->expects($this->never())->method('find');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, ['rid' => self::SOME_RID])->isError());
    }

    /**
     * The internal way in: a calling activity has checked the access to whatever
     * the resource belongs to, so ownership is none of this activity's business.
     */
    public function testAnActivityMayPerformItForSomebodyElsesResource(): void
    {
        $this->givenAResourceOwnedBy(self::OWNER);

        $payload = $this->activity->perform(['rid' => self::SOME_RID]);

        $this->assertSame(base64_encode('some content'), $payload->content);
    }

    public function testAnUnknownResourceIsReportedAsSuch(): void
    {
        $this->storage->expects($this->once())->method('find')->willReturn(null);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::OWNER, ['rid' => 'nope']);

        $this->assertTrue($result->isError());
    }

    public function testAResourceIdentificationIsRequired(): void
    {
        $this->storage->expects($this->never())->method('find');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::OWNER, [])->isError());
    }

    private function givenAResourceOwnedBy(int $usr_id): void
    {
        $rid = new ResourceIdentification(self::SOME_RID);

        $information = $this->createStub(Information::class);
        $information->method('getTitle')->willReturn('notes.txt');
        $information->method('getMimeType')->willReturn('text/plain');

        $this->storage->expects($this->atLeastOnce())->method('find')->willReturn($rid);
        $this->storage->method('getOwnerId')->willReturn($usr_id);
        $this->storage->method('getInformation')->willReturn($information);
        $this->storage->method('readStream')->willReturn(Streams::ofString('some content'));
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
