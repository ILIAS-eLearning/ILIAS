<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Calendar/interfaces/interface.ilDatePeriod.php');

/**
* Booking period
* Used for calculation of recurring events 
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilBookingPeriod implements ilDatePeriod
{
	private $start = null;
	private $end = null;

	/**
	 * Constructor
	 */
	public function __construct(ilDateTime $start,ilDateTime $end)
	{
		$this->start = $start;
		$this->end = $end;
	}
	/**
	 * @see ilDatePeriod::getEnd()
	 */
	public function getEnd()
	{
		return $this->end;
	}
	/**
	 * @see ilDatePeriod::getStart()
	 */
	public function getStart()
	{
		return $this->start;
	}
	/**
	 * @see ilDatePeriod::isFullday()
	 */
	public function isFullday()
	{
		return false;
	}
}
?>