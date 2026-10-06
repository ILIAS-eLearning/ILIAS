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

namespace ILIAS\ResourceStorage\Manager;

use ILIAS\ResourceStorage\AbstractBaseResourceBuilderTestCase;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Lock\LockHandlerResult;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Stakeholder\ConfidentialStakeholder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
#[AllowMockObjectsWithoutExpectations]
final class ConfidentialResourceCreationTest extends AbstractBaseResourceBuilderTestCase
{
    #[DataProvider('stakeholderProvider')]
    public function testUploadAppliesStakeholderDefault(string $stakeholder_type, bool $expected): void
    {
        $builder = $this->getResourceBuilderExpectingStore($expected);
        $builder->expects($this->once())
                ->method('new')
                ->willReturn(new StorableFileResource(new ResourceIdentification('rid')));

        $this->getManager($builder)->upload(
            $this->getDummyUploadResult('info.xml', 'text/xml', 128),
            $this->getStakeholder($stakeholder_type)
        );
    }

    #[DataProvider('stakeholderProvider')]
    public function testStreamAppliesStakeholderDefault(string $stakeholder_type, bool $expected): void
    {
        $builder = $this->getResourceBuilderExpectingStore($expected);
        $builder->expects($this->once())
                ->method('newFromStream')
                ->willReturn(new StorableFileResource(new ResourceIdentification('rid')));

        $this->getManager($builder)->stream(
            $this->getDummyStream(),
            $this->getStakeholder($stakeholder_type),
            'info.txt'
        );
    }

    #[DataProvider('cloneProvider')]
    public function testCloneKeepsConfidentialFlag(bool $confidential): void
    {
        $this->storage_handler->method('getIdentificationGenerator')->willReturn($this->id_generator);
        $this->storage_handler->method('getID')->willReturn('fsv2');
        $this->resource_repository->method('blank')->willReturnCallback(
            static fn(ResourceIdentification $rid): StorableResource => new StorableFileResource($rid)
        );
        $this->locking->method('lockTables')->willReturn($this->createStub(LockHandlerResult::class));

        $original = new StorableFileResource(new ResourceIdentification('original'));
        $original->setConfidential($confidential);

        $clone = (new ResourceBuilder(
            $this->storage_handler_factory,
            $this->repositories,
            $this->locking,
            $this->stream_access
        ))->clone($original);

        $this->assertNotSame($original->getIdentification()->serialize(), $clone->getIdentification()->serialize());
        $this->assertSame($confidential, $clone->isConfidential());
    }

    public static function stakeholderProvider(): \Iterator
    {
        yield 'confidential stakeholder' => ['confidential', true];
        yield 'confidential stakeholder, disabled' => ['confidential_disabled', false];
        yield 'regular stakeholder' => ['regular', false];
    }

    public static function cloneProvider(): \Iterator
    {
        yield 'confidential' => [true];
        yield 'not confidential' => [false];
    }

    private function getStakeholder(string $type): ResourceStakeholder
    {
        if ($type === 'regular') {
            return $this->createStub(ResourceStakeholder::class);
        }
        $stakeholder = $this->createStub(ConfidentialStakeholder::class);
        $stakeholder->method('areNewResourcesConfidential')->willReturn($type === 'confidential');

        return $stakeholder;
    }

    private function getResourceBuilderExpectingStore(bool $expected): ResourceBuilder
    {
        $builder = $this->createMock(ResourceBuilder::class);
        $builder->expects($this->once())
                ->method('store')
                ->with($this->callback(
                    static fn(StorableResource $resource): bool => $resource->isConfidential() === $expected
                ));

        return $builder;
    }

    private function getManager(ResourceBuilder $builder): Manager
    {
        return new Manager(
            $builder,
            $this->createStub(CollectionBuilder::class),
            $this->createStub(RepositoryPreloader::class)
        );
    }
}
