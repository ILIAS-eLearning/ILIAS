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

namespace ILIAS\File\Activities;

use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\ResourceStorage\Activities\DeliverResource;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeliverFileObjectTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const string SOME_RID = 'a-resource';

    private RepositoryProvider&MockObject $repository;
    private DeliverResource&MockObject $deliver_resource;
    private DeliverFileObject $activity;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->deliver_resource = $this->createMock(DeliverResource::class);

        $this->activity = new DeliverFileObject(
            $this->repository,
            $this->deliver_resource,
            new FilePayloadFactory(),
        );
    }

    /**
     * The point of this activity: it checks the permission on the object and the
     * delivery of the resource then happens regardless of who owns it.
     */
    public function testTheReadPermissionOnTheObjectDecides(): void
    {
        $payload = new FilePayload('notes.txt', 'text/plain', 12, base64_encode('some content'));

        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayRead')
            ->with(self::SOME_USER, self::SOME_FILE, 'file')
            ->willReturn(true);
        $this->repository->method('lookupResourceId')->willReturn(self::SOME_RID);

        $this->deliver_resource->expects($this->once())->method('perform')
            ->with(['rid' => self::SOME_RID])
            ->willReturn($payload);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isOK());
        $this->assertSame($payload, $result->value());
    }

    public function testWithoutTheReadPermissionNothingIsDelivered(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayRead')->willReturn(false);
        $this->deliver_resource->expects($this->never())->method('perform');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testSomethingThatIsNoFileObjectIsRejected(): void
    {
        $this->repository->expects($this->once())->method('lookupType')->willReturn('cat');
        $this->deliver_resource->expects($this->never())->method('perform');

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE])->isError()
        );
    }

    public function testAnonymousRequestsGetNothing(): void
    {
        $this->repository->expects($this->never())->method('mayRead');
        $this->deliver_resource->expects($this->never())->method('perform');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, ['ref_id' => self::SOME_FILE])->isError());
    }

    public function testAFileObjectWithoutContentIsReportedAsSuch(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayRead')->willReturn(true);
        $this->repository->expects($this->once())->method('lookupResourceId')->willReturn('');
        $this->deliver_resource->expects($this->never())->method('perform');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('no content', (string) $result->error());
    }

    public function testAFileObjectIsRequired(): void
    {
        $this->repository->expects($this->never())->method('mayRead');
        $this->deliver_resource->expects($this->never())->method('perform');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [])->isError());
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
