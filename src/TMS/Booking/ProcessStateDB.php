<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Stores state information about booking processes
 */
interface ProcessStateDB {
	/**
	 * @param	int		$crs_id
	 * @param	int		$usr_id
	 * @return	ProcessState|null
	 */
	public function load($crs_id, $usr_id);

	/**
	 * @param	ProcessState
	 * @return	void
	 */
	public function save(ProcessState $state);

	/**
	 * @param	ProcessState
	 * @return	void
	 */
	public function delete(ProcessState $state);
}
