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
use ILIAS\ResourceStorage\Activities\ResourceVersion;
use ILIAS\ResourceStorage\Activities\ServiceProvider;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListFileVersionsTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const string SOME_RID = 'a-resource';

    private RepositoryProvider&MockObject $repository;
    private ServiceProvider&MockObject $storage;
    private ListFileVersions $activity;

    protected function setUp(): void
    {
        // the activities refuse every user as long as they cannot tell the
        // anonymous one, see AccessCheck
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->storage = $this->createMock(ServiceProvider::class);
        $this->activity = new ListFileVersions($this->repository, $this->storage, new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    public function testTheVersionsOfAWritableFileAreListed(): void
    {
        $rid = new ResourceIdentification(self::SOME_RID);

        $this->givenAWritableFile();
        $this->storage->expects($this->once())->method('find')->with(self::SOME_RID)->willReturn($rid);
        $this->storage->expects($this->once())->method('getRevisions')->with($rid)->willReturn([
            $this->revision(1, 'first.txt', 6),
            $this->revision(2, 'second.txt', 7),
        ]);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isOK());
        $versions = $result->value();
        $this->assertContainsOnlyInstancesOf(ResourceVersion::class, $versions);
        $this->assertSame([1, 2], array_map(static fn(ResourceVersion $v): int => $v->version, $versions));
        $this->assertSame('second.txt', $versions[1]->title);
        $this->assertSame(7, $versions[1]->owner_id);
        $this->assertSame('2026-01-02T03:04:05+00:00', $versions[0]->created);
    }

    /**
     * The list names who uploaded each version - reading the file is not
     * enough for that, as in the versions tab of the GUI.
     */
    public function testReadingIsNotEnoughToSeeTheVersions(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayRead')->willReturn(true);
        $this->repository->expects($this->once())->method('mayWrite')->willReturn(false);
        $this->storage->expects($this->never())->method('getRevisions');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
    }

    public function testOnlyFilesHaveVersions(): void
    {
        $this->repository->method('lookupType')->willReturn('crs');
        $this->repository->expects($this->never())->method('mayWrite');
        $this->storage->expects($this->never())->method('getRevisions');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
    }

    public function testAFileWithoutContentHasNoVersions(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayWrite')->willReturn(true);
        $this->repository->expects($this->once())->method('lookupResourceId')->willReturn('');
        $this->storage->expects($this->never())->method('getRevisions');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertSame(404, $result->error()->getCode());
    }

    public function testAnonymousRequestsListNothing(): void
    {
        $this->repository->expects($this->never())->method('mayWrite');
        $this->storage->expects($this->never())->method('getRevisions');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, ['ref_id' => self::SOME_FILE])->isError());
    }

    private function givenAWritableFile(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayWrite')
            ->with(self::SOME_USER, self::SOME_FILE, 'file')
            ->willReturn(true);
        $this->repository->method('lookupResourceId')->willReturn(self::SOME_RID);
    }

    private function revision(int $version, string $title, int $owner_id): Revision
    {
        $information = $this->createStub(Information::class);
        $information->method('getTitle')->willReturn($title);
        $information->method('getMimeType')->willReturn('text/plain');
        $information->method('getSize')->willReturn(12);
        $information->method('getCreationDate')->willReturn(new \DateTimeImmutable('2026-01-02T03:04:05+00:00'));

        $revision = $this->createStub(Revision::class);
        $revision->method('getVersionNumber')->willReturn($version);
        $revision->method('getOwnerId')->willReturn($owner_id);
        $revision->method('getInformation')->willReturn($information);

        return $revision;
    }

    private function inputFactory(): InputFactory
    {
        return $this->createStub(InputFactory::class);
    }
}
