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
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\SymbolDecoratorTrait;

/**
 * Class AbstractBaseTool
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTool extends AbstractParentItem implements isToolItem
{
    use SymbolDecoratorTrait;

    /**
     * @var \Closure|null
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
