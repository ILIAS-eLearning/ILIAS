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
	 * Constructor
	 * @param int $from
	 * @param int $to
	 */
	public function __construct($from = null, $to = null)
	{
		$this->to = (is_null($to))
			? time()
			: $to;
		$this->from = (is_null($from))
			? time() - (365 * 24 * 60 * 60)
			: $from;
	}
	
	/**
	 * Get entries
	 *
	 * @param
	 * @return
	 */
	protected function getEntries()
	{
		
	}
	

}