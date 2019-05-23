<?php

namespace SimpleBus\Message\CallableResolver;

use SimpleBus\Message\CallableResolver\Exception\CouldNotResolveCallable;

class ServiceLocatorAwareCallableResolver implements CallableResolver
{
    /**
     * @var callable
     */
    private $serviceLocator;

    public function __construct(callable $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param $maybeCallable
     * @return callable
     */
    public function resolve($maybeCallable)
    {
        if (is_callable($maybeCallable)) {
            return $maybeCallable;
        }

        if (is_string($maybeCallable)) {
            // a string can be converted to an object, which may then be a callable
            return $this->resolve($this->loadService($maybeCallable));
        }

        // to make the upgrade process easier: auto-select the "handle" method
        if (is_object($maybeCallable) && method_exists($maybeCallable, 'handle')) {
            return [$maybeCallable, 'handle'];
        }

        // to make the upgrade process easier: auto-select the "notify" method
        if (is_object($maybeCallable) && method_exists($maybeCallable, 'notify')) {
            return [$maybeCallable, 'notify'];
        }

        if (is_array($maybeCallable) && count($maybeCallable) === 2) {
            // Symfony 3.3 supports services by classname. This interferes with `is_callable` above
            // so the SymfonyBridge will now use an array with `serviceId`, `method` keys.
            if (array_key_exists('serviceId', $maybeCallable)) {
                $serviceId = $maybeCallable['serviceId'];
                $method = $maybeCallable['method'];
            } else {
                list($serviceId, $method) = $maybeCallable;
            }

            if (is_string($serviceId)) {
                return $this->resolve([$this->loadService($serviceId), $method]);
            }
        }

        throw CouldNotResolveCallable::createFor($maybeCallable);
    }

    private function loadService($serviceId)
    {
        return call_user_func($this->serviceLocator, $serviceId);
    }
}
