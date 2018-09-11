<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition service
 *
 * @author @leifos.de
 * @ingroup
 */
class ilConditionService
{
	/**
	 * Constructor
	 */
	protected function __construct($dic)
	{

	}

	/**
	 * Get instance
	 *
	 * @return ilConditionService
	 */
	protected function getInstance($dic)
	{
		return new self($dic);
	}


}