<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ReplaceSignal;
use ILIAS\UI\Component\Component;

/**
 * This describes a Slate
 */
interface Slate extends Component, JavaScriptBindable
{
    /**
     * Get the name of this slate
     */
    public function getName() : string;

    /**
     * Get the Symbol of the slate
     */
    public function getSymbol();

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
    public function getContents();

    /**
     * Signal to replace the contents of the slate.
     */
    public function getReplaceSignal() : ReplaceSignal;
}