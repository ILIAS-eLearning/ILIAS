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

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Regression for Mantis 0045055 / 0047976: secures the concrete behaviour of
 * every OnDuplicate mode when a file is uploaded into a collection.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class UploadStorerTest extends TestCase
{
    private const FILE_NAME = 'feedback.pdf';

    private Manager&MockObject $manage;
    private Collections&MockObject $collections;
    private ResourceCollection&MockObject $collection;
    private ResourceStakeholder&MockObject $stakeholder;
    private UploadResult $result;
    private FileStream&MockObject $stream;
    private UploadStorer $storer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manage = $this->createMock(Manager::class);
        $this->collections = $this->createMock(Collections::class);
        $this->collection = $this->createMock(ResourceCollection::class);
        $this->stakeholder = $this->createMock(ResourceStakeholder::class);
        $this->result = new UploadResult(
            self::FILE_NAME,
            123,
            'application/pdf',
            new EntryLockingStringMap(),
            new ProcessingStatus(ProcessingStatus::OK, 'ok'),
            'dummy/path'
        );
        $this->stream = $this->createMock(FileStream::class);
        $this->storer = new UploadStorer($this->manage, $this->collections);
    }

    // ALLOW: never dedupes, always stores a new, separate resource

    public function testAllowStoresNewResourceWithoutLookupEvenWhenNameExists(): void
    {
        $new_rid = new ResourceIdentification('new');

        // ALLOW must not even ask whether a same-name resource exists
        $this->collections->expects($this->never())->method('findIdentificationByNameIn');
        $this->manage->expects($this->never())->method('replaceWithUpload');
        $this->manage->expects($this->never())->method('appendNewRevision');

        $this->manage->expects($this->once())
                     ->method('upload')
                     ->with($this->result, $this->stakeholder)
                     ->willReturn($new_rid);
        $this->collection->expects($this->once())->method('add')->with($new_rid);

        $this->assertSame(
            $new_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::ALLOW, $this->result)
        );
    }

    // REJECT: on a name clash nothing is stored, the existing resource is untouched

    public function testRejectLeavesExistingResourceUntouchedAndStoresNothing(): void
    {
        $existing_rid = new ResourceIdentification('existing');
        $this->givenExistingResource($existing_rid);

        $this->manage->expects($this->never())->method('upload');
        $this->manage->expects($this->never())->method('replaceWithUpload');
        $this->manage->expects($this->never())->method('appendNewRevision');
        $this->collection->expects($this->never())->method('add');

        $this->assertNull(
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::REJECT, $this->result)
        );
    }

    public function testRejectStoresNewResourceWhenNoNameClash(): void
    {
        $new_rid = new ResourceIdentification('new');
        $this->givenNoExistingResource();

        $this->manage->expects($this->never())->method('replaceWithUpload');
        $this->manage->expects($this->never())->method('appendNewRevision');
        $this->manage->expects($this->once())->method('upload')->willReturn($new_rid);
        $this->collection->expects($this->once())->method('add')->with($new_rid);

        $this->assertSame(
            $new_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::REJECT, $this->result)
        );
    }

    // REPLACE: on a name clash overwrite the existing resource, drop the history

    public function testReplaceOverwritesExistingResource(): void
    {
        $existing_rid = new ResourceIdentification('existing');
        $this->givenExistingResource($existing_rid);

        $this->manage->expects($this->never())->method('upload');
        $this->manage->expects($this->never())->method('appendNewRevision');
        $this->collection->expects($this->never())->method('add');
        $this->manage->expects($this->once())
                     ->method('replaceWithUpload')
                     ->with($existing_rid, $this->result, $this->stakeholder)
                     ->willReturn($this->createStub(Revision::class));

        $this->assertSame(
            $existing_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::REPLACE, $this->result)
        );
    }

    public function testReplaceStoresNewResourceWhenNoNameClash(): void
    {
        $new_rid = new ResourceIdentification('new');
        $this->givenNoExistingResource();

        $this->manage->expects($this->never())->method('replaceWithUpload');
        $this->manage->expects($this->once())->method('upload')->willReturn($new_rid);
        $this->collection->expects($this->once())->method('add')->with($new_rid);

        $this->assertSame(
            $new_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::REPLACE, $this->result)
        );
    }

    // APPEND_REVISION: on a name clash append a new revision, keep the history

    public function testAppendRevisionAddsRevisionToExistingResource(): void
    {
        $existing_rid = new ResourceIdentification('existing');
        $this->givenExistingResource($existing_rid);

        $this->manage->expects($this->never())->method('upload');
        $this->manage->expects($this->never())->method('replaceWithUpload');
        $this->collection->expects($this->never())->method('add');
        $this->manage->expects($this->once())
                     ->method('appendNewRevision')
                     ->with($existing_rid, $this->result, $this->stakeholder)
                     ->willReturn($this->createStub(Revision::class));

        $this->assertSame(
            $existing_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::APPEND_REVISION, $this->result)
        );
    }

    public function testAppendRevisionStoresNewResourceWhenNoNameClash(): void
    {
        $new_rid = new ResourceIdentification('new');
        $this->givenNoExistingResource();

        $this->manage->expects($this->never())->method('appendNewRevision');
        $this->manage->expects($this->once())->method('upload')->willReturn($new_rid);
        $this->collection->expects($this->once())->method('add')->with($new_rid);

        $this->assertSame(
            $new_rid,
            $this->storer->store($this->collection, $this->stakeholder, OnDuplicate::APPEND_REVISION, $this->result)
        );
    }

    // storeStream(): the twin used for a reassembled chunked upload. It has to
    // reach the same decision as store() in every mode, only writing the resource
    // from a stream instead of from an UploadResult.

    public function testStreamAllowStoresNewResourceWithoutLookupEvenWhenNameExists(): void
    {
        $new_rid = new ResourceIdentification('new');

        $this->collections->expects($this->never())->method('findIdentificationByNameIn');
        $this->manage->expects($this->never())->method('replaceWithStream');
        $this->manage->expects($this->never())->method('appendNewRevisionFromStream');

        $this->manage->expects($this->once())
                     ->method('stream')
                     ->with($this->stream, $this->stakeholder, self::FILE_NAME)
                     ->willReturn($new_rid);
        $this->collection->expects($this->once())->method('add')->with($new_rid);

        $this->assertSame($new_rid, $this->storeStream(OnDuplicate::ALLOW));
    }

    public function testStreamRejectLeavesExistingResourceUntouchedAndStoresNothing(): void
    {
        $this->givenExistingResource(new ResourceIdentification('existing'));

        $this->manage->expects($this->never())->method('stream');
        $this->manage->expects($this->never())->method('replaceWithStream');
        $this->manage->expects($this->never())->method('appendNewRevisionFromStream');
        $this->collection->expects($this->never())->method('add');

        $this->assertNull($this->storeStream(OnDuplicate::REJECT));
    }

    public function testStreamReplaceOverwritesExistingResource(): void
    {
        $existing_rid = new ResourceIdentification('existing');
        $this->givenExistingResource($existing_rid);

        $this->manage->expects($this->never())->method('stream');
        $this->manage->expects($this->never())->method('appendNewRevisionFromStream');
        $this->collection->expects($this->never())->method('add');
        $this->manage->expects($this->once())
                     ->method('replaceWithStream')
                     ->with($existing_rid, $this->stream, $this->stakeholder, self::FILE_NAME)
                     ->willReturn($this->createStub(Revision::class));

        $this->assertSame($existing_rid, $this->storeStream(OnDuplicate::REPLACE));
    }

    public function testStreamAppendRevisionAddsRevisionToExistingResource(): void
    {
        $existing_rid = new ResourceIdentification('existing');
        $this->givenExistingResource($existing_rid);

        $this->manage->expects($this->never())->method('stream');
        $this->manage->expects($this->never())->method('replaceWithStream');
        $this->collection->expects($this->never())->method('add');
        $this->manage->expects($this->once())
                     ->method('appendNewRevisionFromStream')
                     ->with($existing_rid, $this->stream, $this->stakeholder, self::FILE_NAME)
                     ->willReturn($this->createStub(Revision::class));

        $this->assertSame($existing_rid, $this->storeStream(OnDuplicate::APPEND_REVISION));
    }

    public function testStreamStoresNewResourceWhenNoNameClash(): void
    {
        foreach ([OnDuplicate::REJECT, OnDuplicate::REPLACE, OnDuplicate::APPEND_REVISION] as $on_duplicate) {
            $this->setUp();
            $new_rid = new ResourceIdentification('new');
            $this->givenNoExistingResource();

            $this->manage->expects($this->never())->method('replaceWithStream');
            $this->manage->expects($this->never())->method('appendNewRevisionFromStream');
            $this->manage->expects($this->once())->method('stream')->willReturn($new_rid);
            $this->collection->expects($this->once())->method('add')->with($new_rid);

            $this->assertSame($new_rid, $this->storeStream($on_duplicate), $on_duplicate->name);
        }
    }

    private function storeStream(OnDuplicate $on_duplicate): ?ResourceIdentification
    {
        return $this->storer->storeStream(
            $this->collection,
            $this->stakeholder,
            $on_duplicate,
            $this->stream,
            self::FILE_NAME
        );
    }

    private function givenExistingResource(ResourceIdentification $existing_rid): void
    {
        $this->collections->method('findIdentificationByNameIn')
                          ->with($this->collection, self::FILE_NAME)
                          ->willReturn($existing_rid);
    }

    private function givenNoExistingResource(): void
    {
        $this->collections->method('findIdentificationByNameIn')
                          ->with($this->collection, self::FILE_NAME)
                          ->willReturn(null);
    }
}
