<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;
/**
 * This describes a Qucikfilter Control
 */
interface Quickfilter extends Component\Component, JavaScriptBindable, Triggerer {
	/**
	 * Get the filtering-options.
	 *
	 * @return 	array<string,string> 	value=>title
	 */
	public function getOptions();

	/**
	 * Get the url this instance should trigger.
	 *
	 * @return 	string
	 */
	public function getTargetURL();

	/**
	 * Get the label.
	 *
	 * @return 	string
	 */
	public function getLabel();

	/**
	 * Set the initial, non-functional entry
	 *
	 * @param 	string 	$label
	 *
	 * @return \Sortation
	 */
	public function withLabel($label);

	/**
	 * Get a Quckfilter with this target-url.
	 * Shy-Buttons in this control will link to this url
	 * and add $parameter_name with the selected value.
	 *
	 * @param 	string 	$url
	 * @param 	string 	$paramer_name
	 *
	 * @return \Sortation
	 */
	public function withTargetURL($url, $paramter_name);

	/**
	 * Get the identifier of this instance.
	 *
	 * @return 	string
	 */
	public function getParameterName();

	/**
	 * Get a component like this, triggering a signal of another component.
	 *
	 * @param Signal $signal A signal of another component
	 *
	 * @return $this
	 */
	public function withOnSort(Component\Signal $signal);


	/**
	 * Get the Signal for the selection of a option
	 *
	 * @return Signal
	 */
	public function getSelectSignal();

	/**
	 * Get a Quickfilter with this default value
	 * If default value will be selected
	 * the quickfilter shows initial label
	 *
	 * @param string | int 	$default_value
	 *
	 * @return $this
	 */
	public function withDefaultValue($default_value);

	/**
	 * Get the default value of this quickfilter
	 *
	 * @return string | int
	 */
	public function getDefaultValue();
}
