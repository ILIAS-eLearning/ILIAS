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

namespace ILIAS\Export\ImportHandler\File\Namespace;

use ILIAS\Export\ImportHandler\I\File\Namespace\ilHandlerInterface as ilParserNamespaceHandlerInterface;

class ilHandler implements ilParserNamespaceHandlerInterface
{
    protected string $prefix;
    protected string $namespace;

    public function __construct()
    {
        $this->prefix = '';
        $this->namespace = '';
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function withPrefix(string $prefix): ilParserNamespaceHandlerInterface
    {
        $clone = clone $this;
        $clone->prefix = $prefix;
        return $clone;
    }

    public function withNamespace(string $namespace): ilParserNamespaceHandlerInterface
    {
        $clone = clone $this;
        $clone->namespace = $namespace;
        return $clone;
    }
}
