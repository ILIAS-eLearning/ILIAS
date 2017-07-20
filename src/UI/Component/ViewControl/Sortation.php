<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component\Component;
/**
 * This describes a Sortation Control
 */
interface Sortation extends Component {

	/**
	 * Set the initial, non-functional entry
	 *
	 * @param 	string 	$label
	 *
	 * @return \Sortation
	 */
	public function withLabel($label);

	/**
	 * Get the label.
	 *
	 * @return 	string
	 */
	public function getLabel();

	/**
	 * Set the parameter name to something other than "sortation".
	 *
	 * @param 	string 	$param
	 *
	 * @return \Sortation
	 */
	public function withParameterName($param);

	/**
	 * Get the parameter name.
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

}
