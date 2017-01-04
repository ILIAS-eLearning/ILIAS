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
	 * The component which is executing this action
	 *
	 * @var \ILIAS\UI\Component\Component
	 */
	protected $component;


	/**
	 * @param \ILIAS\UI\Component\Component $component
	 */
	public function __construct(\ILIAS\UI\Component\Component $component) {
		$this->component = $component;
	}


	/**
	 * @inheritdoc
	 */
	public function getComponent() {
		return $this->component;
	}

	/**
	 * @inheritdoc
	 */
	abstract public function renderJavascript($id);

}