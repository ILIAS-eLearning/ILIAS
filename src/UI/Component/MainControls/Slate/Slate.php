<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes a Slate
 */
interface Slate extends Component, JavaScriptBindable, Triggerer
{
    /**
     * Get the name of this slate
     */
    public function getName() : string;

    /**
     * Get the Symbol of the slate
     */
    public function getSymbol() : Symbol;

    /**
     * Signal that toggles the slate when triggered.
     */
    public function getToggleSignal() : Signal;

    /**
     * Signal that  engages the slate when triggered.
     */
    public function getEngageSignal() : Signal;

    /**
     * Configures the slate to be rendered as engaged (or not).
     */
    public function withEngaged(bool $state) : Slate;

    /**
     * Should the slate be rendered as engaged?
     */
    public function getEngaged() : bool;

    /**
     * @return Component[]
     */
    public function getContents() : array;

    /**
     * Signal to replace the contents of the slate.
     */
    public function getReplaceSignal() : ?Signal;

    /**
     * A Signal that is triggered when the slate "comes into view", i.e. is being engaged.
     */
    public function appendOnInView(Signal $signal) : Slate;

    /**
     * Slates in the main bar need to be addressable via JS, a.o. for storing
     * current activation states or triggering them from the outside.
     */
    public function withMainBarTreePosition(string $tree_pos) : Slate;

    public function getMainBarTreePosition() : ?string;
}
