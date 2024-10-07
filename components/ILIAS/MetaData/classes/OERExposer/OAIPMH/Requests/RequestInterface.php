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

interface RequestInterface
{
    public function baseURL(): URI;

    public function verb(): Verb;

    public function withArgument(Argument $key, string $value): RequestInterface;

    public function argumentValue(Argument $argument): string;

    public function hasArgument(Argument $argument): bool;

    /**
     * Returns true if this either has all required arguments,
     * any subset of the optional arguments, and no others,
     * or if this has only one of the exclusive arguments.
     * @param Argument[] $required
     * @param Argument[] $optional
     * @param Argument[] $exclusive
     */
    public function hasCorrectArguments(
        array $required,
        array $optional,
        array $exclusive
    ): bool;

    /**
     * @return Argument[]
     */
    public function argumentKeys(): \Generator;
}
