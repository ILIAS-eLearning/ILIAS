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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\Data\URI;

class NullRequest implements RequestInterface
{
    public function baseURL(): URI
    {
        return new URI('http://0');
    }

    public function verb(): Verb
    {
        return Verb::NULL;
    }

    public function withArgument(Argument $key, string $value): RequestInterface
    {
        return $this;
    }

    public function argumentValue(Argument $argument): string
    {
        return '';
    }

    public function hasArgument(Argument $argument): bool
    {
        return false;
    }

    public function hasCorrectArguments(array $required, array $optional, array $exclusive): bool
    {
        return false;
    }

    /**
     * @return Argument[]
     */
    public function argumentKeys(): \Generator
    {
        yield from [];
    }
}
