<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface TimezoneChecker {
	/**
	 * Checks the given date is in summer timezone
	 *
	 * @param DateTime 	$date
	 *
	 * @return bool
	 */
	public function isSummerTime(\DateTime $date);
}