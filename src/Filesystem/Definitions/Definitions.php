<?php

declare(strict_types=1);

namespace ILIAS\Filesystem\Definitions;

use ILIAS\DI\Container;

class Definitions
{
    protected SuffixDefinitions $suffix_definitions;

    public function __construct(Container $c)
    {
        $this->suffix_definitions = $c['filesystem.definitions'];
    }

    public function suffix(): SuffixDefinitions
    {
        return $this->suffix_definitions;
    }
}
