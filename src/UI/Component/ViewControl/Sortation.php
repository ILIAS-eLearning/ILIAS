<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component as C;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes a Sortation Control
 */
interface Sortation extends C\Component, JavaScriptBindable, Triggerer
{

    /**
     * Set the initial, non-functional entry
     *
     * @param 	string 	$label
     *
     * @return self
     */
    public function withLabel($label);

    /**
     * Get the label.
     *
     * @return 	string
     */
    public function getLabel();

    /**
     * Get a Sortation with this target-url.
     * Shy-Buttons in this control will link to this url
     * and add $parameter_name with the selected value.
     *
     * @param 	string 	$url
     * @param 	string 	$paramer_name
     *
     * @return self
     */
    public function withTargetURL($url, $paramter_name);

    /**
     * Get the url this instance should trigger.
     *
     * @return 	string
     */
    public function getTargetURL();

    /**
     * Get the identifier of this instance.
     *
     * @return 	string
     */
    public function getParameterName();

    /**
     * Get the sorting-options.
     *
     * @return 	array<string,string> 	value=>title
     */
    public function getOptions();

    /**
     * Get a component like this, triggering a signal of another component.
     *
     * @param C\Signal $signal A signal of another component
     *
     * @return $this
     */
    public function withOnSort(C\Signal $signal);


    /**
     * Get the Signal for the selection of a option
     *
     * @return C\Signal
     */
    public function getSelectSignal();
}
