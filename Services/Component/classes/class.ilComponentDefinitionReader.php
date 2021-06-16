<?php

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 */
class ilComponentDefinitionReader
{
    /**
     * @var ilComponentDefinitionProcessor[]
     */
    protected array $processors;

    public function __construct(
        ilComponentDefinitionProcessor ...$processor
    ) {
        $this->processor = $processor;
    }

    /**
     * This methods is supposed to purge existing data in the registered
     * processor.
     */
    public function purge() : void
    {
        foreach ($this->processor as $p) {
            $p->purge();
        }
    }
}
