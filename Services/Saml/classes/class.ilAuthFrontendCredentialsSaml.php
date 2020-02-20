<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAuthFrontendCredentialsSaml
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    /** @var array */
    protected $attributes = [];
    /** @var string */
    protected $return_to = '';
    /** @var ilSamlAuth */
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
    public function initFromRequest() : void
    {
        $this->setReturnTo(isset($_GET['target']) ? $_GET['target'] : '');
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes) : void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getReturnTo() : string
    {
        return $this->return_to;
    }

    /**
     * @param string $return_to
     */
    public function setReturnTo(string $return_to) : void
    {
        $this->return_to = $return_to;
    }
}
