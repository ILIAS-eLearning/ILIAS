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

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Component\Legacy\Segment as LegacySegment;
use ILIAS\UI\Implementation\Component\Navigation\Sequence\Segment as SequenceSegment;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Button;

class Segment implements SequenceSegment, LegacySegment
{
    use ComponentHelper;

    protected ?array $actions = null;


    public function __construct(
        protected string $title,
        protected string $content
    ) {
    }

    public function getSegmentTitle(): string
    {
        return $this->title;
    }

    public function getSegmentContent(): string
    {
        return $this->content;
    }

    public function withSegmentActions(Button\Standard ...$actions): static
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    public function getSegmentActions(): array
    {
        return $this->actions ?? [];
    }

}
