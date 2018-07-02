<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface TimezoneDB {
	/**
	 * Read the dates for given year
	 *
	 * @param string 	$year
	 *
	 * @return \DateTime[]
	 */
	public function readFor($year);
}