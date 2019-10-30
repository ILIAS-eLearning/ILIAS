<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\TriggeredSignal;
use ILIAS\UI\Component\Signal;

class Toggle extends Button implements C\Button\Toggle
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var bool
     */
    protected $is_on;

    /**
     * @var string|null
     */
    protected $action_off = null;

    /**
     * @var string|null
     */
    protected $action_on = null;

    /**
     * @inheritdoc
     */
    public function __construct($label, $action_on, $action_off, $is_on, Signal $click = null)
    {
        $this->checkStringOrSignalArg("action", $action_on);
        $this->checkStringOrSignalArg("action_off", $action_off);
        $this->checkBoolArg("is_on", $is_on);

        // no way to resolve conflicting string actions
        $button_action = (is_null($click)) ? "" : $click;

        parent::__construct($label, $button_action);

        if (is_string($action_on)) {
            $this->action_on = $action_on;
        } else {
            $this->setTriggeredSignal($action_on, "toggle_on");
        }

        if (is_string($action_off)) {
            $this->action_off = $action_off;
        } else {
            $this->setTriggeredSignal($action_off, "toggle_off");
        }

        $this->is_on = $is_on;
    }

    /**
     * @inheritdoc
     */
    public function isOn() : bool
    {
        return $this->is_on;
    }

    /**
     * @inheritdoc
     */
    public function getActionOff()
    {
        if ($this->action_off !== null) {
            return $this->action_off;
        }

        return $this->getTriggeredSignalsFor("toggle_off");
    }

    /**
     * @inheritdoc
     */
    public function getActionOn()
    {
        if ($this->action_on !== null) {
            return $this->action_on;
        }

        return $this->getTriggeredSignalsFor("toggle_on");
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalToggleOnSignal(Signal $signal) : \ILIAS\UI\Component\Button\Toggle
    {
        return $this->appendTriggeredSignal($signal, "toggle_on");
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalToggleOffSignal(Signal $signal) : \ILIAS\UI\Component\Button\Toggle
    {
        return $this->appendTriggeredSignal($signal, "toggle_off");
    }
}
