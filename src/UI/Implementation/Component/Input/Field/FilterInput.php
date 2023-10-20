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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class FilterInput extends FormInput implements \ILIAS\UI\Component\Input\Container\Filter\FilterInput
{
    use JavaScriptBindable;
    use Triggerer;

    protected bool $in_popover = false;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        protected string $label,
        protected ?string $byline = null,
    ) {
        parent::__construct($data_factory, $refinery, $this->label, $this->byline);
    }

    /**
     * Is this input a sub-component of a complex input and rendered within a Popover?
     */
    public function isInPopoverView(): bool
    {
        return $this->in_popover;
    }

    /**
     * Get an input like this, but set it to a state rendered in a Popover.
     *
     * @return static
     */
    public function withPopoverView(bool $in_popover): self
    {
        $clone = clone $this;
        $clone->in_popover = $in_popover;
        return $clone;
    }
}
