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
	 * @var ilConditionObjectAdapterInterface
	 */
	protected $cond_obj_adapter;

	/**
	 * Constructor
	 */
	protected function __construct(ilConditionObjectAdapterInterface $cond_obj_adapter = null)
	{
		if (is_null($cond_obj_adapter))
		{
			$this->cond_obj_adapter = new ilConditionObjectAdapter();
		}
	}

	/**
	 * Get instance
	 *
	 * @return ilConditionService
	 */
	static public function getInstance($dic)
	{
		return new self($dic);
	}

	/**
	 * factory
	 *
	 * @return ilConditionFactory
	 */
	protected function factory()
	{
		return new ilConditionFactory($this->cond_obj_adapter);
	}

	/**
	 * query
	 *
	 * @return ilConditionQuery
	 */
	protected function query()
	{
		return new ilConditionQuery();
	}

}