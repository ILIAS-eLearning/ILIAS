<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Trigger\Triggerable;
use ILIAS\UI\Component\Trigger\TriggerAction;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * This implements commonalities between standard and primary buttons. 
 */
abstract class Button implements C\Button\Button {
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var bool
	 */
	protected $active = true;

    /**
     * @var array
     */
    protected $trigger_actions = array();


	public function __construct($label, $action) {
		$this->checkStringArg("label", $label);
		$this->checkStringArg("action", $action);
		$this->label = $label;
		$this->action = $action;
	} 

	/**
	 * @inheritdocs 
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritdocs 
	 */
	public function withLabel($label) {
		$this->checkStringArg("label", $label);
		$clone = clone $this;
		$clone->label = $label;
		return $clone;
	}

	/**
	 * @inheritdocs 
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @inheritdocs 
	 */
	public function isActive() {
		return $this->active;
	}

	/**
	 * @inheritdocs 
	 */
	public function withUnavailableAction() {
		$clone = clone $this;
		$clone->active = false;
		return $clone;
	}


    /**
     * @inheritdoc
     */
    public function triggerAction(TriggerAction $action, $event = 'click')
    {
        $this->checkArgInstanceOf('action', $action, TriggerAction::class);
        $action->setEvent($event);
        $clone = clone $this;
        $clone->trigger_actions[] = $action;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getTriggerActions()
    {
        return $this->trigger_actions;
    }

}
