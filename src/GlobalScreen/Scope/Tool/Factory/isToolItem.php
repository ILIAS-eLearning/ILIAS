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
namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Interface isToolItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isToolItem extends isItem, hasTitle, hasSymbol
{
    /**
     * @param bool $initially_hidden
     * @return isToolItem
     */
    public function withInitiallyHidden(bool $initially_hidden) : isToolItem;

    /**
     * @return bool
     */
    public function isInitiallyHidden() : bool;

    /**
     * @param Closure $close_callback
     * @return isToolItem
     */
    public function withCloseCallback(Closure $close_callback) : isToolItem;

    /**
     * @return Closure
     */
    public function getCloseCallback() : Closure;

    /**
     * @return bool
     */
    public function hasCloseCallback() : bool;
}
