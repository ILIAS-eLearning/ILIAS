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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class StorableFlavourDecorator extends Flavour
{
    private array $streams = [];
    protected Flavour $flavour;

    public function __construct(Flavour $flavour)
    {
        $this->flavour = $flavour;
    }

    public function getFlavour(): Flavour
    {
        return $this->flavour;
    }

    public function getPersistingName(): string
    {
        return $this->flavour->getPersistingName();
    }

    public function getName(): string
    {
        return $this->flavour->getName();
    }

    public function getResourceId(): ResourceIdentification
    {
        return $this->flavour->getResourceId();
    }

    public function getDefinition(): FlavourDefinition
    {
        return $this->flavour->getDefinition();
    }

    public function getRevision(): int
    {
        return $this->flavour->getRevision();
    }

    // STREAMS

    /**
     * @description Filter Streams with a Closure which accepts a FileStream and returns bool
     */
    public function filterStreams(\Closure $filter): void
    {
        $this->streams = array_filter(
            $this->streams,
            $filter
        );
    }

    public function setStreams(array $streams): void
    {
        foreach ($streams as $index => $stream) {
            $this->addStream($index, $stream);
        }
    }


    public function addStream(int $index, FileStream $stream): self
    {
        $this->streams[$index] = $stream;

        return $this;
    }

    public function getStream(int $index = 0): ?FileStream
    {
        return $this->streams[$index] ?? null;
    }

    /**
     * @return FileStream[]
     */
    public function getStreams(): array
    {
        return $this->streams;
    }
}
