<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
require_once 'Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Class ilAuthFrontendCredentialsSaml
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var string
     */
    protected $return_to = '';

    /**
     * @var ilSamlAuth
     */
    protected $auth;

    /**
     * ilAuthFrontendCredentialsSaml constructor.
     * @param ilSamlAuth $auth
     */
    public function __construct(ilSamlAuth $auth)
    {
        parent::__construct();

        $this->auth = $auth;

        $this->setAttributes($this->auth->getAttributes());
    }

    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->setReturnTo(isset($_GET['target']) ? $_GET['target'] : '');
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getReturnTo()
    {
        return $this->return_to;
    }

    /**
     * @param string $return_to
     */
    public function setReturnTo($return_to)
    {
        $this->return_to = $return_to;
    }
}
