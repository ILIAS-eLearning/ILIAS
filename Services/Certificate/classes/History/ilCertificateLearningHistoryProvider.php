<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Is active?
	 *
	 * @return bool
	 */
	public function isActive()
	{

	}

	/**
	 * Get entries
	 *
	 * @param int $ts_start
	 * @param int $ts_end
	 * @return ilLearningHistoryEntry[]
	 */
	public function getEntries($ts_start, $ts_end) {

	}
}
