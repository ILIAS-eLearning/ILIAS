<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAuthFrontendCredentialsSaml
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    protected ilSamlAuth $auth;
    protected string $return_to = '';
    protected array $attributes = [];

    public function __construct(ilSamlAuth $auth)
    {
        parent::__construct();

        $this->auth = $auth;

        $this->setAttributes($this->auth->getAttributes());
    }

    public function initFromRequest() : void
    {
        $this->setReturnTo((string) ($_GET['target'] ?? ''));
    }

    public function setAttributes(array $attributes) : void
    {
        $this->attributes = $attributes;
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function getReturnTo() : string
    {
        return $this->return_to;
    }

    public function setReturnTo(string $return_to) : void
    {
        $this->return_to = $return_to;
    }
}
