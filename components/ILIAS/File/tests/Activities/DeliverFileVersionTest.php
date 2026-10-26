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

use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Activities\ServiceProvider;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeliverFileVersionTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const string SOME_RID = 'a-resource';

    private RepositoryProvider&MockObject $repository;
    private ServiceProvider&MockObject $storage;
    private DeliverFileVersion $activity;

    protected function setUp(): void
    {
        // the activities refuse every user as long as they cannot tell the
        // anonymous one, see AccessCheck
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->storage = $this->createMock(ServiceProvider::class);
        $this->activity = new DeliverFileVersion($this->repository, $this->storage, new FilePayloadFactory(), new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    /**
     * The content and the name are those of the requested version, not of the
     * current one.
     */
    public function testTheRequestedVersionIsDelivered(): void
    {
        $rid = new ResourceIdentification(self::SOME_RID);

        $this->givenAWritableFile();
        $this->storage->method('find')->willReturn($rid);
        $this->storage->expects($this->once())->method('getRevision')
            ->with($rid, 1)
            ->willReturn($this->revision('first.txt'));
        $this->storage->expects($this->once())->method('readStream')
            ->with($rid, 1)
            ->willReturn(Streams::ofString('old content'));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'version' => 1,
        ]);

        $this->assertTrue($result->isOK());
        $this->assertSame('first.txt', $result->value()->filename);
        $this->assertSame('old content', base64_decode($result->value()->content, true));
        $this->assertSame(11, $result->value()->size);
    }

    public function testAVersionThatDoesNotExistIsNotFound(): void
    {
        $this->givenAWritableFile();
        $this->storage->method('find')->willReturn(new ResourceIdentification(self::SOME_RID));
        $this->storage->method('getRevision')->willReturn(null);
        $this->storage->expects($this->never())->method('readStream');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'version' => 9,
        ]);

        $this->assertTrue($result->isError());
        $this->assertSame(404, $result->error()->getCode());
    }

    public function testWithoutWriteNothingIsDelivered(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayWrite')->willReturn(false);
        $this->storage->expects($this->never())->method('readStream');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'version' => 1,
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAVersionIsRequired(): void
    {
        $this->repository->expects($this->never())->method('mayWrite');
        $this->storage->expects($this->never())->method('readStream');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
        ]);

        $this->assertTrue($result->isError());
        $this->assertSame(400, $result->error()->getCode());
    }

    public function testAnonymousRequestsGetNothing(): void
    {
        $this->repository->expects($this->never())->method('mayWrite');
        $this->storage->expects($this->never())->method('readStream');

        $result = $this->activity->maybePerformAs($this->inputFactory(), 0, [
            'ref_id' => self::SOME_FILE,
            'version' => 1,
        ]);

        $this->assertTrue($result->isError());
    }

    private function givenAWritableFile(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayWrite')
            ->with(self::SOME_USER, self::SOME_FILE, 'file')
            ->willReturn(true);
        $this->repository->method('lookupResourceId')->willReturn(self::SOME_RID);
    }

    private function revision(string $title): Revision
    {
        $information = $this->createStub(Information::class);
        $information->method('getTitle')->willReturn($title);
        $information->method('getMimeType')->willReturn('text/plain');

        $revision = $this->createStub(Revision::class);
        $revision->method('getInformation')->willReturn($information);

        return $revision;
    }

    private function inputFactory(): InputFactory
    {
        return $this->createStub(InputFactory::class);
    }
}
