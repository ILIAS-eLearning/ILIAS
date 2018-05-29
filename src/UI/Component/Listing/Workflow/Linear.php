<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Workflow;

/**
 * This describes a Linear Workflow.
 */
interface Linear extends Workflow {

	const HORIZONTAL	= 'horizontal';
	const VERTICAL		= 'vertical';

	/**
	 * Linear workflow can be rendered horizontally or vertically.
	 * This gives its orientation.
	 *
	 * @return mixed
	 */
	public function getOrientation();

	/**
	 * Linear workflow can be rendered horizontally or vertically.
	 * Set the orientation to either Linear::HORIZONTAL or Linear::VERTICAL.
	 *
	 * @param mixed 	$orientation
	 * @return Linear
	 */
	public function withOrientation($orientation);

}