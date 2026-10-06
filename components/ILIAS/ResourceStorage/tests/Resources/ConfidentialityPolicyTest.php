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

namespace ILIAS\components\ResourceStorage\Resources\UI;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\Revision;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ConfidentialityPolicyTest extends TestCase
{
    private const OWNER_ID = 42;
    private const OTHER_USER_ID = 6;

    public function testNonConfidentialResourceIsNeverRedacted(): void
    {
        $policy = new ConfidentialityPolicy(self::OTHER_USER_ID);

        $this->assertFalse($policy->isRedacted($this->getResource(false)));
    }

    public function testConfidentialResourceIsRedactedForOtherUsers(): void
    {
        $policy = new ConfidentialityPolicy(self::OTHER_USER_ID);

        $this->assertTrue($policy->isRedacted($this->getResource(true)));
    }

    public function testConfidentialResourceIsNotRedactedForOwnerOfCurrentRevision(): void
    {
        $policy = new ConfidentialityPolicy(self::OWNER_ID);

        $this->assertFalse($policy->isRedacted($this->getResource(true)));
    }

    private function getResource(bool $confidential): StorableResource
    {
        $revision = $this->createStub(Revision::class);
        $revision->method('getOwnerId')->willReturn(self::OWNER_ID);

        $resource = $this->createStub(StorableResource::class);
        $resource->method('isConfidential')->willReturn($confidential);
        $resource->method('getCurrentRevisionIncludingDraft')->willReturn($revision);

        return $resource;
    }
}
