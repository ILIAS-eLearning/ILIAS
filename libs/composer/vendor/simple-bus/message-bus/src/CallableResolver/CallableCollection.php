<?php

namespace SimpleBus\Message\CallableResolver;

use Assert\Assertion;

class CallableCollection
{
    /**
     * @var array
     */
    private $callablesByName;

    /**
     * @var CallableResolver
     */
    private $callableResolver;

    public function __construct(
        array $callablesByName,
        CallableResolver $callableResolver
    ) {
        Assertion::allIsArray($callablesByName, 'You need to provide arrays of callables, indexed by name');

        $this->callablesByName = $callablesByName;
        $this->callableResolver = $callableResolver;
    }

    /**
     * @param string $name
     * @return callable[]
     */
    public function filter($name)
    {
        if (!array_key_exists($name, $this->callablesByName)) {
            return [];
        }

        $callables = $this->callablesByName[$name];

        return array_map([$this->callableResolver, 'resolve'], $callables);
    }
}
