<?php

namespace SimpleBus\Message\CallableResolver;

use SimpleBus\Message\CallableResolver\Exception\UndefinedCallable;

class CallableMap
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
        $this->callablesByName = $callablesByName;
        $this->callableResolver = $callableResolver;
    }

    /**
     * @param string $name
     * @return callable
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->callablesByName)) {
            throw new UndefinedCallable(
                sprintf(
                    'Could not find a callable for name "%s"',
                    $name
                )
            );
        }

        $callable = $this->callablesByName[$name];

        return $this->callableResolver->resolve($callable);
    }
}
