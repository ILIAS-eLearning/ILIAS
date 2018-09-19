<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history entry collector
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryEntryCollector
{
	/**
	 * @var ilLearningHistoryService
	 */
	protected $service;

	/**
	 * Constructor
	 * @param ilLearningHistoryService $service
	 */
	public function __construct(ilLearningHistoryService $service)
	{
		$this->service = $service;
	}
	
	/**
	 * Get entries
	 *
	 * @return ilLearningHistoryEntry[]
	 */
	public function getEntries($from = null, $to = null, $user_id = null)
	{
		$entries = array();

		$to = (is_null($to))
			? time()
			: $to;
		$from = (is_null($from))
			? time() - (365 * 24 * 60 * 60)
			: $from;

		foreach ($this->service->provider()->getAllProviders(true, $user_id) as $provider)
		{
			foreach ($provider->getEntries($from, $to) as $e)
			{
				$entries[] = $e;
			}
		}
		return $entries;
	}
	

}