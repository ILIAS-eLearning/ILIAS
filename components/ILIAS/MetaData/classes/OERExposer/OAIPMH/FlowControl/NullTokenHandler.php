<?php

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

declare(strict_types=1);

namespace ILIAS\MetaData\OERExposer\OAIPMH\FlowControl;

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;

class NullTokenHandler implements TokenHandlerInterface
{
    public function generateToken(
        int $offset,
        ?\DateTimeImmutable $from_date,
        ?\DateTimeImmutable $until_date
    ): string {
        return '';
    }

    public function isTokenValid(string $token): bool
    {
        return false;
    }

    public function appendArgumentsFromTokenToRequest(
        RequestInterface $request,
        string $token
    ): RequestInterface {
        return new NullRequest();
    }

    public function getOffsetFromToken(string $token): int
    {
        return 0;
    }
}
