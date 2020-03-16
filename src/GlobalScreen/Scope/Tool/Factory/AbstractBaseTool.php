<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;

/**
 * Class AbstractBaseTool
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTool extends AbstractParentItem implements isToolItem
{
    use SymbolDecoratorTrait;
    /**
     * @var Closure
     */
    protected $close_callback;
    /**
     * @var bool
     */
    protected $initially_hidden = false;

    /**
     * @inheritDoc
     */
    public function withInitiallyHidden(bool $initially_hidden) : isToolItem
    {
        $clone = clone($this);
        $clone->initially_hidden = $initially_hidden;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function isInitiallyHidden() : bool
    {
        return $this->initially_hidden;
    }

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
