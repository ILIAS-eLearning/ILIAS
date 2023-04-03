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

namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use LogicException;
use ILIAS\GlobalScreen\ScreenContext\ContextRepository;

/**
 * Class CalledContexts
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class CalledContexts extends ContextCollection
{
    /**
     * @var mixed[]
     */
    private $call_locations = [];

    public function current() : ScreenContext
    {
        return $this->getLast();
    }

    public function push(ScreenContext $context) : void
    {
        $this->claim(
            $context,
            $context->getUniqueContextIdentifier() === 'external'
        ); // external can be claimed multiple times
    }

    public function external() : ContextCollection
    {
        $this->claim($this->repo->external(), true);

        return $this;
    }

    public function clear() : void
    {
        $this->call_locations = [];
        $this->stack = [];
    }

    protected function claim(ScreenContext $context, bool $silent = false) : void
    {
        $this->checkCallLocation($context, $silent);

        if (!$silent && in_array($context, $this->stack)) {
            throw new LogicException("A context can only be claimed once");
        }
        if (end($this->stack) instanceof ScreenContext) {
            $context = $context->withAdditionalData($this->getLast()->getAdditionalData());
        }

        parent::push($context);
    }

    private function checkCallLocation(ScreenContext $context, bool $silent = false) : void
    {
        $called_classes = array_filter(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            function ($item) : bool {
                if (!isset($item['class'])) {
                    return false;
                }

                return (!in_array($item['class'], [CalledContexts::class, ContextCollection::class]));
            }
        );
        array_walk(
            $called_classes,
            function (&$item) : void {
                $item = ($item['class'] ?? '') . ":" . ($item['line'] ?? '');
            }
        );

        $call_location = reset($called_classes);

        if (!$silent && isset($this->call_locations[$context->getUniqueContextIdentifier()])) {
            $first_location = $this->call_locations[$context->getUniqueContextIdentifier()];
            throw new LogicException("context '{$context->getUniqueContextIdentifier()}' already claimed in $first_location, second try in $call_location");
        }
        $this->call_locations[$context->getUniqueContextIdentifier()] = $call_location;
    }
}
