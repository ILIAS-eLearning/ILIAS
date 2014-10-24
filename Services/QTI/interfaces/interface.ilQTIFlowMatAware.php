<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilQTIMaterialAware
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilQTIFlowMatAware
{
	/**
	 * @param ilQTIFlowMat $flow_mat
	 */
	public function addFlowMat(ilQTIFlowMat $flow_mat);

	/**
	 * @param $index int
	 * @return ilQTIFlowMat|null
	 */
	public function getFlowMat($index);
}