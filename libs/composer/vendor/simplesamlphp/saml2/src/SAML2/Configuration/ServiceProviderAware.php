<?php

namespace SAML2\Configuration;

/**
 * Interface for triggering setter injection
 */
interface ServiceProviderAware
{
    public function setServiceProvider(ServiceProvider $serviceProvider);
}
