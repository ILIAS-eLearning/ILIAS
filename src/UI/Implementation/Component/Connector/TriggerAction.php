<?php

namespace ILIAS\UI\Implementation\Component\Connector;

/**
 * Class TriggerAction
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class TriggerAction implements \ILIAS\UI\Component\Connector\TriggerAction {

	// Note: As for now, use the same events as available in JQuery
	const EVENT_CLICK = 'click';
	const EVENT_HOVER = 'hover';
	const EVENT_CHANGE = 'change';

	/**
	 * @var array
	 */
	protected static $events = array(
		self::EVENT_CLICK,
		self::EVENT_HOVER,
		self::EVENT_CHANGE,
	);

	/**
	 * The component which is executing this action
	 *
	 * @var \ILIAS\UI\Component\Component
	 */
	protected $component;

//	/**
//	 * The event triggering this action
//	 *
//	 * @var string
//	 */
//	protected $event = self::EVENT_CLICK;


	/**
	 * @param \ILIAS\UI\Component\Component $component
	 */
	public function __construct(\ILIAS\UI\Component\Component $component) {
		$this->component = $component;
//		$this->setEvent($event);
	}


	/**
	 * @inheritdoc
	 */
	public function getComponent() {
		return $this->component;
	}


//	/**
//	 * @inheritdoc
//	 */
//	public function getEvent() {
//		return $this->event;
//	}


//	/**
//	 * @inheritdoc
//	 */
//	abstract public function getSupportedEvents();


	/**
	 * @inheritdoc
	 */
	abstract public function renderJavascript($id);


//	/**
//	 * @inheritdoc
//	 */
//	public function setEvent($event) {
//		if (!in_array($event, $this->getSupportedEvents())) {
//			throw new \InvalidArgumentException("$event is not supported for action " . get_class() . ", use one of "
//				. implode(', ', $this->getSupportedEvents()));
//		}
//		$this->event = $event;
//	}
}