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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\ComponentDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\BaseMetaBarItemRenderer;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\MetaBarItemRenderer;
use Closure;
use ILIAS\GlobalScreen\Scope\isDecorateable;
use ILIAS\GlobalScreen\Scope\TriggererDecoratorTrait;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractBaseItem implements
    isItem,
    isDecorateable
{
    use ComponentDecoratorTrait;

    protected MetaBarItemRenderer $renderer;
    protected int $position = 0;
    protected ?Closure $available_callable = null;
    protected ?Closure $visiblility_callable = null;

    public function __construct(protected IdentificationInterface $provider_identification)
    {
        $this->renderer = new BaseMetaBarItemRenderer();
    }

    public function getRenderer(): MetaBarItemRenderer
    {
        return $this->renderer;
    }

    public function getProviderIdentification(): IdentificationInterface
    {
        return $this->provider_identification;
    }

    public function withVisibilityCallable(callable $is_visible): isItem
    {
        $clone = clone($this);
        $clone->visiblility_callable = $is_visible;

        return $clone;
    }

    public function isVisible(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        if (is_callable($this->visiblility_callable)) {
            $callable = $this->visiblility_callable;

            return $callable();
        }

        return true;
    }

    public function withAvailableCallable(callable $is_available): isItem
    {
        $clone = clone($this);
        $clone->available_callable = $is_available;

        return $clone;
    }

    public function isAvailable(): bool
    {
        if (is_callable($this->available_callable)) {
            $callable = $this->available_callable;

            return $callable();
        }

        return true;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withPosition(int $position): isItem
    {
        $clone = clone($this);
        $clone->position = $position;

        return $clone;
    }
}
