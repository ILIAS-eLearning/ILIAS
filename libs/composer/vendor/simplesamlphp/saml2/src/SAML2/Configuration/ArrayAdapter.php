<?php

namespace SAML2\Configuration;

/**
 * Default implementation for configuration
 */
class ArrayAdapter implements Queryable
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function get($key, $defaultValue = null)
    {
        if (!$this->has($key)) {
            return $defaultValue;
        }

        return $this->configuration[$key];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->configuration);
    }
}
