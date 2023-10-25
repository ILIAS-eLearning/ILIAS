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

use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamResolver;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Flavour
{
    private array $streams = [];
    private array $stream_resolvers = [];
    private FlavourDefinition $definition;
    private ResourceIdentification $resource_id;
    private int $revision;

    public function __construct(FlavourDefinition $definition, ResourceIdentification $resource_id, int $revision)
    {
        $this->definition = $definition;
        $this->resource_id = $resource_id;
        $this->revision = $revision;
    }

    /**
     * Flavours are stored in the file system by the StroageHandler. Thereby you use this hash.
     * By crc32 these hashes have always a length of 8 characters.
     * Possible collisions are accepted, because they are very unlikely.
     */
    public function getPersistingName(): string
    {
        return hash('crc32', $this->getName());
    }

    public function getName(): string
    {
        return $this->definition->getInternalName() . $this->definition->getVariantName();
    }

    public function getResourceId(): ResourceIdentification
    {
        return $this->resource_id;
    }

    public function withStreamResolver(int $index, ?StreamResolver $stream_resolver = null): Flavour
    {
        $this->stream_resolvers[$index] = $stream_resolver;

        return $this;
    }

    public function maybeStreamResolver(int $index): ?StreamResolver
    {
        return $this->stream_resolvers[$index] ?? null;
    }

    /**
     * @return StreamResolver[]
     */
    public function getStreamResolvers(): array
    {
        return $this->stream_resolvers;
    }

    public function getDefinition(): FlavourDefinition
    {
        return $this->definition;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }
}
