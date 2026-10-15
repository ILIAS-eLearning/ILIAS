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

use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Stakeholder\ConfidentialStakeholder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ConfidentialResourceTest extends TestCase
{
    public function testResourcesAreNotConfidentialByDefault(): void
    {
        $resource = new StorableFileResource(new ResourceIdentification('rid'));

        $this->assertFalse($resource->isConfidential());
    }

    #[DataProvider('confidentialProvider')]
    public function testStakeholderDefaultIsApplied(bool $stakeholder_default): void
    {
        $resource = new StorableFileResource(new ResourceIdentification('rid'));
        $stakeholder = $this->createStub(ConfidentialStakeholder::class);
        $stakeholder->method('areNewResourcesConfidential')->willReturn($stakeholder_default);

        $manager = (new \ReflectionClass(Manager::class))->newInstanceWithoutConstructor();
        (new \ReflectionMethod(BaseManager::class, 'applyConfidentiality'))->invoke($manager, $resource, $stakeholder);

        $this->assertSame($stakeholder_default, $resource->isConfidential());
    }

    public function testRegularStakeholderDoesNotMarkResourcesAsConfidential(): void
    {
        $resource = new StorableFileResource(new ResourceIdentification('rid'));

        $manager = (new \ReflectionClass(Manager::class))->newInstanceWithoutConstructor();
        (new \ReflectionMethod(BaseManager::class, 'applyConfidentiality'))->invoke(
            $manager,
            $resource,
            $this->createStub(ResourceStakeholder::class)
        );

        $this->assertFalse($resource->isConfidential());
    }

    #[DataProvider('confidentialProvider')]
    public function testSetConfidentialStoresResource(bool $confidential): void
    {
        $rid = new ResourceIdentification('rid');
        $resource = new StorableFileResource($rid);
        $resource->setConfidential(!$confidential);

        $builder = $this->createMock(ResourceBuilder::class);
        $builder->expects($this->exactly(2))->method('get')->with($rid)->willReturn($resource);
        $builder->expects($this->once())
                ->method('store')
                ->with($this->callback(
                    static fn(StorableFileResource $r): bool => $r->isConfidential() === $confidential
                ));

        $manager = new Manager(
            $builder,
            $this->createStub(CollectionBuilder::class),
            $this->createStub(RepositoryPreloader::class)
        );
        $manager->setConfidential($rid, $confidential);

        $this->assertSame($confidential, $manager->isConfidential($rid));
    }

    public static function confidentialProvider(): \Iterator
    {
        yield 'confidential' => [true];
        yield 'not confidential' => [false];
    }
}
