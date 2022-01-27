<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\MainControls\Slate\Prompt;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * MetaBar
 */
class MetaBar implements MainControls\MetaBar
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var SignalGeneratorInterface
     */
    private $signal_generator;

    /**
     * @var Signal
     */
    private $entry_click_signal;

    /**
     * @var Signal
     */
    private $disengage_all_signal;

    /**
     * @var array<string, Button\Bulky|Link\Bulky|Slate>
     */
    protected $entries;

    public function __construct(
        SignalGeneratorInterface $signal_generator
    ) {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function getEntries() : array
    {
        return $this->entries;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry(string $id, $entry) : MainControls\MetaBar
    {
        $classes = [Button\Bulky::class, Link\Bulky::class, Slate::class];
        $check = [$entry];
        $this->checkArgListElements("Bulky Button, Bulky Link or Slate", $check, $classes);

        $clone = clone $this;
        $clone->entries[$id] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEntryClickSignal() : Signal
    {
        return $this->entry_click_signal;
    }

    /**
     * @inheritdoc
     */
    public function getDisengageAllSignal() : Signal
    {
        return $this->disengage_all_signal;
    }

    /**
     * Set the signals for this component
     */
    protected function initSignals()
    {
        $this->entry_click_signal = $this->signal_generator->create();
        $this->disengage_all_signal = $this->signal_generator->create();
    }

    public function withClearedEntries() : MainControls\MetaBar
    {
        $clone = clone $this;
        $clone->entries = [];
        return $clone;
    }
}
