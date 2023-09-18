<?php

namespace ILIAS\FileUpload\Collection;

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
 * Class ImmutableMapWrapper
 *
 * This class is used to wrap mutable maps to make them
 * immutable and stops the user of the api to cast the list back to a mutable one.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @Internal
 */
final class ImmutableMapWrapper implements ImmutableStringMap
{
    private \ILIAS\FileUpload\Collection\StringMap $map;


    /**
     * ImmutableMapWrapper constructor.
     *
     * @param StringMap $map The mutable map which should be wrapped.
     *
     * @since 5.3
     */
    public function __construct(StringMap $map)
    {
        $this->map = $map;
    }


    /**
     * @inheritDoc
     */
    public function get(string $key): string
    {
        return $this->map->get($key);
    }


    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->map->toArray();
    }


    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->map->has($key);
    }
}
