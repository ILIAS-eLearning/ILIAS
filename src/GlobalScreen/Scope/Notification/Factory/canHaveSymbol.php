<?php

declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Interface canHaveSymbol
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface canHaveSymbol
{
    /**
     * @param Symbol $symbol
     * @return canHaveSymbol
     */
    public function withSymbol(Symbol $symbol): self;

    /**
     * @return bool
     */
    public function hasSymbol(): bool;

    /**
     * @return Symbol
     */
    public function getSymbol(): Symbol;
}
