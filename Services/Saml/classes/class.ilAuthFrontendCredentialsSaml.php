<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilAuthFrontendCredentialsSaml
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials
{
    private ilSamlAuth $auth;
    private ServerRequestInterface $request;
    private string $return_to = '';
    private array $attributes = [];

    public function __construct(ilSamlAuth $auth, ServerRequestInterface $request)
    {
        parent::__construct();

        $this->auth = $auth;
        $this->request = $request;

        $this->setAttributes($this->auth->getAttributes());
    }

    public function initFromRequest() : void
    {
        $this->setReturnTo((string) ($this->request->getQueryParams()['target'] ?? ''));
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
