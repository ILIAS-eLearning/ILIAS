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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use LogicException;

/**
 * Trait hasSymbolTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait hasSymbolTrait
{
    /**
     * @var \ILIAS\UI\Component\Symbol\Symbol|null
     */
    protected $symbol;

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        // bugfix mantis 25526: make aria labels mandatory
        if (($symbol instanceof Glyph && $symbol->getAriaLabel() === "") ||
            ($symbol instanceof Icon && $symbol->getLabel() === "")) {
            throw new LogicException("the symbol's aria label MUST be set to ensure accessibility");
        }

        $clone = clone $this;
        $clone->symbol = $symbol;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return $this->symbol instanceof Symbol;
    }
}
