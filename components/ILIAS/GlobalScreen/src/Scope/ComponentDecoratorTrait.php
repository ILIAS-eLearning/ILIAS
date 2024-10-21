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

namespace ILIAS\GlobalScreen\Scope;

use Closure;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\isGlobalScreenItem;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Help\Topic;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ComponentDecoratorTrait
{
    use CheckClosureTrait;

    private ?Closure $component_decorator = null;
    private ?Closure $triggerer_decorator = null;
    private ?Closure $symbol_decorator = null;

    private array $topics = [];

    public function withTopics(Topic ...$topics): self
    {
        $this->topics = $topics;

        return $this;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function addComponentDecorator(Closure $component_decorator): self
    {
        $this->checkClosureForSignature($component_decorator, Component::class);

        if ($this->component_decorator instanceof Closure) {
            $existing = $this->component_decorator;
            $this->component_decorator = static function (Component $c) use (
                $component_decorator,
                $existing
            ): Component {
                $component = $existing($c);

                return $component_decorator($component);
            };
        } else {
            $this->component_decorator = $component_decorator;
        }

        return $this;
    }

    public function getComponentDecorator(): ?Closure
    {
        return $this->component_decorator;
    }

    public function addSymbolDecorator(Closure $symbol_decorator): isDecorateable
    {
        $this->checkClosureForSignature($symbol_decorator, Symbol::class);

        if ($this->symbol_decorator instanceof Closure) {
            $existing = $this->symbol_decorator;
            $this->symbol_decorator = static function (Symbol $c) use ($symbol_decorator, $existing): Symbol {
                $component = $existing($c);

                return $symbol_decorator($component);
            };
        } else {
            $this->symbol_decorator = $symbol_decorator;
        }

        return $this;
    }

    public function getSymbolDecorator(): ?Closure
    {
        return $this->symbol_decorator;
    }
}
