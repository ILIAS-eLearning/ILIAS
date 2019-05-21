<?php

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface ServiceProviderAware
{
    /**
     * @param ServiceProvider $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProvider $serviceProvider);
}
