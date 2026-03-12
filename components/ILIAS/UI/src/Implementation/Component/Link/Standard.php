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

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Symbol\Symbol;

class Standard extends Link implements C\Link\Standard
{
    protected string $label;
    protected ?Symbol $symbol = null;

    public function __construct(string $label, string $action)
    {
        parent::__construct($action);
        $this->label = $label;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getSymbol(): ?Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritdoc
     */
    public function withSymbol(?Symbol $symbol): C\Link\Standard
    {
        $clone = clone $this;
        $clone->symbol = $symbol;
        return $clone;
    }
}
