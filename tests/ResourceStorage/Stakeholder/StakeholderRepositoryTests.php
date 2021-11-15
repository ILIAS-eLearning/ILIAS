<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class StakeholderRepositoryTests
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StakeholderRepositoryTests extends AbstractBaseTest
{

    /**
     * @var StakeholderDBRepository
     */
    protected $stakeholder_repository;
    /**
     * @var ResourceIdentification
     */
    protected $identification;

    protected function setUp() : void
    {
        parent::setUp();
        $this->stakeholder_repository = new StakeholderDBRepository($this->db_mock);
        $this->identification = new ResourceIdentification('test_identification');
    }

    public function testIdTooLong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stakeholder ids MUST be shorter or equal to than 64 characters');
        $stakeholder = $this->getResourceStakeholder(
            str_repeat('A', 65)
        );
        $this->stakeholder_repository->register($this->identification, $stakeholder);
    }

    public function testNameTooLong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stakeholder classnames MUST be shorter or equal to than 250 characters');
        $stakeholder = $this->getResourceStakeholder(
            str_repeat('A', 64),
            str_repeat('B', 251)
        );
        $this->stakeholder_repository->register($this->identification, $stakeholder);
    }

    /**
     * @return ResourceStakeholder
     */
    protected function getResourceStakeholder(?string $stakeholder_id = null, ?string $stakeholder_classname = null)
    {
        return new class($stakeholder_id, $stakeholder_classname) implements ResourceStakeholder {

            protected $stakeholder_id = 'the_ludicrous_long_identification_string_of_a_resource_stakeholder';
            protected $stakeholder_classname = 'This\Is\A\Very\Long\Class\Name\Which\Can\Not\Be\Handled\As\A\Propper\Stakeholder\In\The\ILIAS\Resource\Storage\Service';

            public function __construct(?string $stakeholder_id = null, ?string $stakeholder_classname = null)
            {
                $this->stakeholder_id = $stakeholder_id ?? $this->stakeholder_id;
                $this->stakeholder_classname = $stakeholder_classname ?? $this->stakeholder_classname;
            }

            public function getId() : string
            {
                return $this->stakeholder_id;
            }

            public function getConsumerNameForPresentation() : string
            {
                return 'VeryLong';
            }

            public function getFullyQualifiedClassName() : string
            {
                return $this->stakeholder_classname;
            }

            public function isResourceInUse(ResourceIdentification $identification) : bool
            {
                return true;
            }

            public function resourceHasBeenDeleted(ResourceIdentification $identification) : bool
            {
                return false;
            }

            public function getOwnerOfResource(ResourceIdentification $identification) : int
            {
                return 0;
            }

            public function getOwnerOfNewResources() : int
            {
                return 0;
            }

        };
    }
}

