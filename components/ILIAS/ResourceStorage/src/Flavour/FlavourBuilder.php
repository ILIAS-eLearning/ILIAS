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

namespace ILIAS\ResourceStorage\Flavour;

use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Machine\Factory;
use ILIAS\ResourceStorage\Flavour\Machine\NullMachine;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\Repository\FlavourRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\ResourceNotFoundException;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Events\Subject;
use ILIAS\ResourceStorage\Events\Event;
use ILIAS\ResourceStorage\Events\FlavourData;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API and may be changed without notice. Do not use this class in your code.
 */
class FlavourBuilder
{
    public const VARIANT_NAME_MAX_LENGTH = 768;
    private array $current_revision_cache = [];
    private array $resources_cache = [];

    public function __construct(
        private readonly FlavourRepository $flavour_resource_repository,
        private readonly Factory $flavour_machine_factory,
        private readonly ResourceBuilder $resource_builder,
        private readonly StorageHandlerFactory $storage_handler_factory,
        private readonly StreamAccess $stream_access,
        private readonly Subject $events
    ) {
    }

    public function has(
        ResourceIdentification $identification,
        FlavourDefinition $definition
    ): bool {
        $this->checkDefinition($definition);
        return $this->flavour_resource_repository->has(
            $identification,
            $this->getResource($identification)->getCurrentRevision()->getVersionNumber(),
            $definition
        );
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function get(
        ResourceIdentification $rid,
        FlavourDefinition $definition,
        bool $force_building = false
    ): Flavour {
        $this->checkDefinition($definition);
        if (!$this->resource_builder->has($rid)) {
            throw new ResourceNotFoundException($rid->serialize());
        }
        if ($this->has($rid, $definition)) {
            return $this->read($rid, $definition, $force_building);
        }

        return $this->build($rid, $definition);
    }

    private function build(
        ResourceIdentification $rid,
        FlavourDefinition $definition
    ): Flavour {
        $flavour = $this->new($definition, $rid);
        $flavour = $this->runMachine($rid, $definition, $flavour);

        if ($definition->persist()) {
            $this->flavour_resource_repository->store($flavour);
            $flavour = $this->populateFlavourWithExistingStreams($flavour);
        }

        return $flavour;
    }

    private function read(
        ResourceIdentification $rid,
        FlavourDefinition $definition,
        bool $force_building = false
    ): Flavour {
        $current_revision = $this->getResource($rid)->getCurrentRevision();
        $flavour = $this->flavour_resource_repository->get(
            $rid,
            $current_revision->getVersionNumber(),
            $definition
        );

        if ($force_building || !$this->hasFlavourStreams($flavour)) {
            // ensure deletion of old streams
            $storage = $this->getStorageHandler($flavour);
            $storage->deleteFlavour($current_revision, $flavour);
            // run Machine
            return $this->runMachine($rid, $definition, $flavour);
        }

        return $this->populateFlavourWithExistingStreams($flavour);
    }

    private function new(FlavourDefinition $definition, ResourceIdentification $rid): Flavour
    {
        return new Flavour(
            $definition,
            $rid,
            $this->getResource($rid)->getCurrentRevision()->getVersionNumber()
        );
    }

    public function delete(
        ResourceIdentification $rid,
        FlavourDefinition $definition
    ): bool {
        $current_revision = $this->getResource($rid)->getCurrentRevision();
        $revision_number = $current_revision->getVersionNumber();

        if ($this->flavour_resource_repository->has($rid, $revision_number, $definition)) {
            $flavour = $this->flavour_resource_repository->get($rid, $revision_number, $definition);
            $this->flavour_resource_repository->delete($flavour);
            $storage = $this->getStorageHandler($flavour);
            $storage->deleteFlavour($current_revision, $flavour);

            return true;
        }

        return false;
    }

    // STREAMS

    private function hasFlavourStreams(Flavour $flavour): bool
    {
        return $this->getStorageHandler($flavour)->hasFlavour(
            $this->getCurrentRevision($flavour),
            $flavour
        );
    }

    private function storeFlavourStreams(Flavour $flavour, array $streams): void
    {
        $storable = new StorableFlavourDecorator($flavour);
        $storable->setStreams($streams);

        $this->getStorageHandler($flavour)->storeFlavour(
            $this->getCurrentRevision($flavour),
            $storable
        );
    }

    private function populateFlavourWithExistingStreams(Flavour $flavour): Flavour
    {
        $handler = $this->getStorageHandler($flavour);
        $identification = $flavour->getResourceId();
        $revision = $this->getCurrentRevision($flavour);
        foreach (
            $handler->getFlavourStreams(
                $revision,
                $flavour
            ) as $index => $file_stream
        ) {
            $flavour = $this->stream_access->populateFlavour($flavour, $file_stream, $index);
        }
        return $flavour;
    }

    // DEFINITIONS AND MACHINES
    private function checkDefinitionForMachine(FlavourDefinition $definition, FlavourMachine $machine): void
    {
        if (!$machine->canHandleDefinition($definition)) {
            throw new \InvalidArgumentException("FlavourDefinition not supported by machine");
        }
    }

    private function checkDefinition(FlavourDefinition $definition): void
    {
        if ($definition->getVariantName() === null) {
            return;
        }
        if (strlen($definition->getVariantName()) > self::VARIANT_NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException("FlavourDefinition variant name too long");
        }
    }

    public function testDefinition(
        ResourceIdentification $rid,
        FlavourDefinition $definition
    ): bool {
        try {
            $this->checkDefinition($definition);
            $machine = $this->flavour_machine_factory->get($definition);
            $this->checkDefinitionForMachine($definition, $machine);
        } catch (\Throwable) {
            return false;
        }
        if ($machine instanceof NullMachine) {
            return false;
        }
        $engine = $machine->getEngine();
        if (!$engine->isRunning()) {
            return false;
        }
        $current_revision = $this->getResource($rid)->getCurrentRevision();
        $suffix = $current_revision->getInformation()->getSuffix();
        $size = $current_revision->getInformation()->getSize();
        if ($size > $engine->getSizeLimitInBytes()) {
            return false;
        }

        return $engine->supports($suffix);
    }

    // STREAMS GENERATION
    protected function runMachine(
        ResourceIdentification $rid,
        FlavourDefinition $definition,
        Flavour $flavour
    ): Flavour {
        $revision = $this->getCurrentRevision($flavour);

        // Get Orignal Stream of Resource/Revision
        $handler = $this->getStorageHandler($flavour);
        try {
            $stream = $this->resource_builder->extractStream($revision);
            $stream->rewind();
        } catch (\Throwable $t) {
            // error while reading file stream, cannot process
            $this->events->notify(Event::FLAVOUR_BUILD_FAILED, new FlavourData($rid, $definition, $flavour, $t));
            return $flavour;
        }

        // Get Machine
        $machine = $this->flavour_machine_factory->get($definition);
        $this->checkDefinitionForMachine($definition, $machine);

        // Run Machine and get Streams
        $storable_streams = [];
        try {
            foreach (
                $machine->processStream(
                    $revision->getInformation(),
                    $stream,
                    $definition
                ) as $result
            ) {
                $generated_stream = $result->getStream();
                if ($result->isStoreable()) {
                    // Collect Streams to store persistently
                    $storable_streams[$result->getIndex()] = $generated_stream;
                }

                $cloned_stream = Streams::ofString((string) $generated_stream);

                $flavour = $this->stream_access->populateFlavour(
                    $flavour,
                    $cloned_stream,
                    $result->getIndex()
                );
            }
        } catch (\Throwable $t) {
            // error while processing stream, cannot process
            $this->events->notify(Event::FLAVOUR_BUILD_FAILED, new FlavourData($rid, $definition, $flavour, $t));
            return $flavour;
        }

        // Store Streams persistently if needed
        if ($definition->persist()) {
            $this->storeFlavourStreams($flavour, $storable_streams);
        }

        $this->events->notify(Event::FLAVOUR_BUILD_SUCCESS, new FlavourData($rid, $definition, $flavour));

        return $flavour;
    }

    // Helpers

    private function getCurrentRevision(Flavour $flavour): Revision
    {
        $rid = $flavour->getResourceId()->serialize();
        if (isset($this->current_revision_cache[$rid])) {
            // return $this->current_revision_cache[$rid]; // we are currently not able to cache this seriously,
            // since there might be situations where the revision changes in the meantime
        }
        return $this->current_revision_cache[$rid] = $this->getResourceOfFlavour($flavour)->getCurrentRevision();
    }

    private function getResource(ResourceIdentification $rid): StorableResource
    {
        $rid_string = $rid->serialize();
        return $this->resources_cache[$rid_string] ?? ($this->resources_cache[$rid_string] = $this->resource_builder->get(
            $rid
        ));
    }

    private function getResourceOfFlavour(Flavour $flavour): StorableResource
    {
        return $this->getResource($flavour->getResourceID());
    }

    private function getStorageHandler(Flavour $flavour): StorageHandler
    {
        return $this->storage_handler_factory->getHandlerForResource($this->getResourceOfFlavour($flavour));
    }
}
