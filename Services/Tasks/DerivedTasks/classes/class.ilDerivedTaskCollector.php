<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task collector
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
class ilDerivedTaskCollector
{
	/**
	 * @var ilTaskService
	 */
	protected $service;

	/**
	 * Constructor
	 * @param ilTaskService $service
	 */
	public function __construct(ilTaskService $service)
	{
		$this->service = $service;
	}

	/**
	 * Get entries
	 *
	 * @param int $user_id user id
	 * @return ilDerivedTask[]
	 */
	public function getEntries(int $user_id)
	{
		$sort_array = [];
		/** @var ilDerivedTaskProvider $provider */
		foreach ($this->service->derived()->factory()->getAllProviders(true, $user_id) as $provider)
		{
			foreach ($provider->getTasks($user_id) as $t)
			{
				$sort_array[] = array("entry" => $t,"ts" => $t->getDeadline());
			}
		}

		$sort_array = ilUtil::sortArray($sort_array, "ts", "desc");

		// add today entry
		$entries = [];

		foreach ($sort_array as $s)
		{
			$entries[] = $s["entry"];
		}

		return $entries;
	}


}