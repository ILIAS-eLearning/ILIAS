<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class StakeholderRepositoryTests
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StakeholderRepositoryTests extends AbstractBaseTest
{
    protected \ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository $stakeholder_repository;
    protected \ILIAS\ResourceStorage\Identification\ResourceIdentification $identification;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stakeholder_repository = new StakeholderDBRepository($this->db_mock);
        $this->identification = new ResourceIdentification('test_identification');
    }

    public function testIdTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stakeholder ids MUST be shorter or equal to than 64 characters');
        $stakeholder = $this->getResourceStakeholder(
            str_repeat('A', 65)
        );
        $this->stakeholder_repository->register($this->identification, $stakeholder);
    }

    public function testNameTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stakeholder classnames MUST be shorter or equal to than 250 characters');
        $stakeholder = $this->getResourceStakeholder(
            str_repeat('A', 64),
            str_repeat('B', 251)
        );
        $this->stakeholder_repository->register($this->identification, $stakeholder);
    }

    protected function getResourceStakeholder(
        ?string $stakeholder_id = null,
        ?string $stakeholder_classname = null
    ): \ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder {
        return new class ($stakeholder_id, $stakeholder_classname) implements ResourceStakeholder {
            /**
             * @var string|mixed
             */
            protected string $stakeholder_id = 'the_ludicrous_long_identification_string_of_a_resource_stakeholder';
            /**
             * @var string|mixed
             */
            protected string $stakeholder_classname = 'This\Is\A\Very\Long\Class\Name\Which\Can\Not\Be\Handled\As\A\Propper\Stakeholder\In\The\ILIAS\Resource\Storage\Service';

            public function __construct(?string $stakeholder_id = null, ?string $stakeholder_classname = null)
            {
                $this->stakeholder_id = $stakeholder_id ?? $this->stakeholder_id;
                $this->stakeholder_classname = $stakeholder_classname ?? $this->stakeholder_classname;
            }

            public function getId(): string
            {
                return $this->stakeholder_id;
            }

            public function getConsumerNameForPresentation(): string
            {
                return 'VeryLong';
            }

            public function getFullyQualifiedClassName(): string
            {
                return $this->stakeholder_classname;
            }

            public function isResourceInUse(ResourceIdentification $identification): bool
            {
                return true;
            }

            public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
            {
                return false;
            }

            public function getOwnerOfResource(ResourceIdentification $identification): int
            {
                return 0;
            }

            public function getOwnerOfNewResources(): int
            {
                return 0;
            }
        };
    }
}
