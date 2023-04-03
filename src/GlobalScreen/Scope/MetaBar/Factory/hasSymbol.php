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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\GlobalScreen\isGlobalScreenItem;
use Closure;

/**
 * Interface hasSymbol
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasSymbol extends isItem
{
    /**
     * @param Symbol $symbol
     * @return hasSymbol
     */
    public function withSymbol(Symbol $symbol) : hasSymbol;

    /**
     * @return Symbol
     */
    public function getSymbol() : Symbol;

    /**
     * @return bool
     */
    public function hasSymbol() : bool;

    /**
     * @param Closure $symbol_decorator
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol
     */
    public function addSymbolDecorator(Closure $symbol_decorator) : isGlobalScreenItem;

    /**
     * @return Closure|null
     */
    public function getSymbolDecorator() : ?Closure;
}
