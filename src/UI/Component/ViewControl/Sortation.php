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
	 * Get a Sortation with a specific identifier; this is necessary if
	 * there is more than one instance on the page. Defaults to "sortation".
	 * The identifier equals the request parameter that indicates the sorting order.
	 *
	 * @param 	string 	$identifier
	 *
	 * @return \Sortation
	 */
	public function withIdentifier($identifier);

	/**
	 * Get the identifier of this instance.
	 *
	 * @return 	string
	 */
	public function getIdentifier();

	/**
	 * Get the sorting-options.
	 *
	 * @return 	array<string,string> 	value=>title
	 */
	public function getOptions();

}
