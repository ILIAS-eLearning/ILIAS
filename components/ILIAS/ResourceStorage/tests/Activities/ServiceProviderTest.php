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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\IRSSServices;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionStatus;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testAPublishedRevisionIsHandedOut(): void
    {
        $revision = $this->revision(RevisionStatus::PUBLISHED);

        $this->assertSame($revision, $this->providerWith($revision)->getRevision(new ResourceIdentification('a-rid'), 2));
    }

    /**
     * A draft is work in progress of whoever edits the resource - nobody who
     * may only read it gets it as a version.
     */
    public function testADraftIsNoRevisionToHandOut(): void
    {
        $revision = $this->revision(RevisionStatus::DRAFT);

        $this->assertNull($this->providerWith($revision)->getRevision(new ResourceIdentification('a-rid'), 3));
    }

    public function testAMissingRevisionStaysMissing(): void
    {
        $this->assertNull($this->providerWith(null)->getRevision(new ResourceIdentification('a-rid'), 4));
    }

    private function revision(RevisionStatus $status): Revision
    {
        $revision = $this->createStub(Revision::class);
        $revision->method('getStatus')->willReturn($status);

        return $revision;
    }

    private function providerWith(?Revision $revision): ServiceProvider
    {
        $resource = $this->createStub(StorableResource::class);
        $resource->method('getSpecificRevision')->willReturn($revision);

        $manager = $this->createStub(Manager::class);
        $manager->method('getResource')->willReturn($resource);

        $services = $this->createStub(IRSSServices::class);
        $services->method('manage')->willReturn($manager);

        return new ServiceProvider($services);
    }
}
