<?php

namespace ILIAS\FileUpload\Collection;

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

    /**
     * @var StringMap
     */
    private $map;


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
    public function get($key)
    {
        return $this->map->get($key);
    }


    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->map->toArray();
    }


    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->map->has($key);
    }
}
