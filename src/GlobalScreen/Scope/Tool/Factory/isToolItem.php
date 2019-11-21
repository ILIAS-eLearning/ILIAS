<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Interface isToolItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isToolItem extends isItem, hasTitle, hasSymbol
{

    /**
     * @param Closure $close_callback
     *
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
