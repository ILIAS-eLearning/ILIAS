<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;

/**
 * Class AbstractBaseTool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTool extends AbstractParentItem implements isToolItem
{

    /**
     * @var Closure
     */
    protected $close_callback;


    /**
     * @inheritDoc
     */
    public function withCloseCallback(Closure $close_callback) : isToolItem
    {
        $clone = clone($this);
        $clone->close_callback = $close_callback;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getCloseCallback() : Closure
    {
        return $this->close_callback;
    }


    /**
     * @inheritDoc
     */
    public function hasCloseCallback() : bool
    {
        return $this->close_callback instanceof Closure;
    }
}
