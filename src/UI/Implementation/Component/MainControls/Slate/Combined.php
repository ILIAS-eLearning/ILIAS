<?php

declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Button\Bulky as IBulkyButton;
use ILIAS\UI\Component\Link\Bulky as IBulkyLink;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Combined Slate
 */
class Combined extends Slate implements ISlate\Combined
{
    /**
     * @var array<Slate|BulkyButton|BulkyLink>
     */
    protected $contents = [];

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry($entry) : ISlate\Combined
    {
        $classes = [
            IBulkyButton::class,
            IBulkyLink::class,
            ISlate\Slate::class
        ];
        $check = [$entry];
        $this->checkArgListElements("Slate, Bulky -Button or -Link", $check, $classes);

        $clone = clone $this;
        $clone->contents[] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContents() : array
    {
        return $this->contents;
    }
}
