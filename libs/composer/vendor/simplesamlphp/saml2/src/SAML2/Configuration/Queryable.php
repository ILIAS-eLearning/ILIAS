<?php

namespace SAML2\Configuration;

/**
 * Interface \SAML2\Configuration\Queryable
 */
interface Queryable
{
    /**
     * Query for whether or not the configuration has a value for the key
     *
     * @param string $key
     * @return bool
     */
    public function has($key);


    /**
     * Query to get the value in the configuration for the given key. If no value is present the default value is
     * returned
     *
     * @param string     $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null);
}
