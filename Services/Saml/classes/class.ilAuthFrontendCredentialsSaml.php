<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilAuthFrontendCredentialsSaml
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials
{
    private string $return_to = '';
    private array $attributes = [];

    public function __construct(private ilSamlAuth $auth, private ServerRequestInterface $request)
    {
        parent::__construct();

        $this->setAttributes($this->auth->getAttributes());
    }

    public function initFromRequest(): void
    {
        $this->setReturnTo((string) ($this->request->getQueryParams()['target'] ?? ''));
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getReturnTo(): string
    {
        return $this->return_to;
    }

    public function setReturnTo(string $return_to): void
    {
        $this->return_to = $return_to;
    }
}
