<?php

namespace ILIAS\UI\Implementation\Component\Connector;

use ILIAS\UI\Component\Connector as Connector;

/**
 * Class ComponentConnection
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ComponentConnection implements Connector\ComponentConnection {

	/**
	 * @var Connector\Triggerer
	 */
	protected $triggerer;
	/**
	 * @var Connector\Triggerable
	 */
	protected $triggered;
	/**
	 * @var Connector\TriggerAction
	 */
	protected $action;
	/**
	 * @var string
	 */
	protected $event;


	/**
	 * @param Connector\Triggerer     $triggerer
	 * @param Connector\TriggerAction $action
	 * @param                         $event
	 */
	public function __construct(Connector\Triggerer $triggerer, Connector\TriggerAction $action, $event) {
		$this->triggerer = $triggerer;
		$this->action = $action;
		$this->triggered = $action->getComponent();
		$this->event = $event;
	}


	/**
	 * @inheritdoc
	 */
	public function getTriggererComponent() {
		return $this->triggerer;
	}


	/**
	 * @inheritdoc
	 */
	public function getTriggeredComponent() {
		return $this->triggered;
	}


	/**
	 * @inheritdoc
	 */
	public function getTriggerAction() {
		return $this->action;
	}


	/**
	 * @inheritdoc
	 */
	public function getEvent() {
		return $this->event;
	}
}