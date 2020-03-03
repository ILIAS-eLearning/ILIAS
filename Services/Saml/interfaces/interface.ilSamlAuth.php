<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilSamlAuth
 */
interface ilSamlAuth
{
    /**
     * @return mixed
     */
    public function getAuthId();

    /**
     * Protect a script resource with a SAML auth.
     */
    public function protectResource();

    /**
     * @param string $key
     * @param mixed $value
     */
    public function storeParam($key, $value);

    /**
     * @return bool
     */
    public function isAuthenticated();

    /**
     * @param string $key
     * @return mixed
     */
    public function popParam($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function getParam($key);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param string $returnUrl
     */
    public function logout($returnUrl = '');

    /**
     * @return ilSamlIdpDiscovery
     */
    public function getIdpDiscovery();
}
