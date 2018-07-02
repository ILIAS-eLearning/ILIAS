<?php
namespace ILIAS\TMS\Mailing;

/**
 * Interface ICalBuilder
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
interface ICalBuilder
{
	/**
	 * Get an iCal string for crs_id.
	 *
	 * @param 	array 	$info
	 * @return 	string
	 */
	public function getIcalString(array $info);
}