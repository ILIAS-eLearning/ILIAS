<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component;

/**
 * Trait for components implementing JavaScriptBindable providing standard
 * implementation.
 */
trait JavaScriptBindable
{
    /**
     * @var		\Closure|null
     */
    private $on_load_code_binder = null;

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::withOnLoadCode
     */
    public function withOnLoadCode(\Closure $binder)
    {
        $this->checkBinder($binder);
        $clone = clone $this;
        $clone->on_load_code_binder = $binder;
        return $clone;
    }

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::withAdditionalOnLoadCode
     */
    public function withAdditionalOnLoadCode(\Closure $binder)
    {
        $current_binder = $this->getOnLoadCode();
        if ($current_binder === null) {
            return $this->withOnLoadCode($binder);
        }

        $this->checkBinder($binder);
        return $this->withOnLoadCode(function ($id) use ($current_binder, $binder) {
            return $current_binder($id) . "\n" . $binder($id);
        });
    }

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::getOnLoadCode
     */
    public function getOnLoadCode()
    {
        return $this->on_load_code_binder;
    }

    /**
     * @param	\Closure	$binder
     * @throw	\InvalidArgumentException	if closure does not take one argument
     * @return 	null
     */
    private function checkBinder(\Closure $binder)
    {
        $refl = new \ReflectionFunction($binder);
        $args = array_map(function ($arg) {
            return $arg->name;
        }, $refl->getParameters());
        if (array("id") !== $args) {
            throw new \InvalidArgumentException('Expected closure "$binder" to have exactly one argument "$id".');
        }
    }
}
