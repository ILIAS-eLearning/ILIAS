<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ReplaceSignal;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ReplaceSignal as ReplaceSignalImplementation;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Slate
 */
abstract class Slate implements ISlate\Slate
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    // allowed ARIA roles
    const MENU = 'menu';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Symbol
     */
    protected $symbol;

    /**
     * @var Signal
     */
    protected $toggle_signal;

    /**
     * @var Signal
     */
    protected $engage_signal;

    /**
     * @var ReplaceSignal
     */
    protected $replace_signal;

    /**
     * @var bool
     */
    protected $engaged = false;

    /**
     * @var string
     */
    protected $mainbar_tree_position;

    /**
     * @var string
     */
    protected $aria_role;

    /**
     * @var string[]
     */
    protected static $allowed_aria_roles = array(
        self::MENU
    );

    /**
     * @param string 	$name 	name of the slate, also used as label
     * @param Symbol	$symbol
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        Symbol $symbol
    ) {
        $this->signal_generator = $signal_generator;
        $this->name = $name;
        $this->symbol = $symbol;

        $this->initSignals();
    }

    /**
     * Set the signals for this component.
     */
    protected function initSignals()
    {
        $this->toggle_signal = $this->signal_generator->create();
        $this->engage_signal = $this->signal_generator->create();
        $this->replace_signal = $this->signal_generator->create(ReplaceSignalImplementation::class);
    }

    /**
     * @inheritdoc
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritdoc
     */
    public function getToggleSignal() : Signal
    {
        return $this->toggle_signal;
    }

    /**
     * @inheritdoc
     */
    public function getEngageSignal() : Signal
    {
        return $this->engage_signal;
    }

    /**
     * @inheritdoc
     */
    public function withEngaged(bool $state) : ISlate\Slate
    {
        $clone = clone $this;
        $clone->engaged = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEngaged() : bool
    {
        return $this->engaged;
    }

    /**
     * @inheritdoc
     */
    abstract public function getContents() : array;

    /**
     * @inheritdoc
     */
    public function getReplaceSignal() : ReplaceSignal
    {
        return $this->replace_signal;
    }

    /**
     * @inheritdoc
     */
    public function appendOnInView(Signal $signal) : \ILIAS\UI\Component\MainControls\Slate\Slate
    {
        return $this->appendTriggeredSignal($signal, 'in_view');
    }


    abstract public function withMappedSubNodes(callable $f);

    /**
     * @inheritdoc
     */
    public function withMainBarTreePosition(string $tree_pos)
    {
        $clone = clone $this;
        $clone->mainbar_tree_position = $tree_pos;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMainBarTreePosition()
    {
        return $this->mainbar_tree_position;
    }

    public function getMainBarTreeDepth()
    {
        $pos = explode(':', $this->mainbar_tree_position);
        return count($pos) - 1;
    }

    /**
     * Get a slate like this, but with an additional ARIA role.
     *
     * @param string $aria_role
     * @return Slate
     */
    public function withAriaRole(string $aria_role) : Slate
    {
        $this->checkArgIsElement(
            "role",
            $aria_role,
            self::$allowed_aria_roles,
            implode('/', self::$allowed_aria_roles)
        );
        $clone = clone $this;
        $clone->aria_role = $aria_role;
        return $clone;
    }

    /**
     * Get the ARIA role on the slate.
     *
     * @return string|null
     */
    public function getAriaRole() : ?string
    {
        return $this->aria_role;
    }
}
