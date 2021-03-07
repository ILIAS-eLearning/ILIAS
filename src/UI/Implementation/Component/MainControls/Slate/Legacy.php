<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Legacy\Legacy as ILegacy;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Legacy Slate
 */
class Legacy extends Slate implements ISlate\Legacy
{
    /**
     * @var \ILIAS\UI\Component\Component []
     */
    protected $contents = [];

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        Symbol $symbol,
        ILegacy $content
    ) {
        parent::__construct($signal_generator, $name, $symbol);
        $this->contents = [$content];
    }

    public function getContents() : array
    {
        return $this->contents;
    }

    public function withMappedSubNodes(callable $f)
    {
        return $this;
    }
}
