<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Cancel/classes/ilTMSCancelGUI.php");

/**
 * Displays the TMS cancel
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSelfCancelGUI extends \ilTMSCancelGUI {
	/**
	 * @inheritdocs
	 */
	protected function getComponentClass() {
		return Booking\SelfBookingStep::class;
	}

	/**
	 * @inheritdocs
	 */
	protected function setParameter($crs_ref_id, $usr_id) {
		$this->g_ctrl->setParameterByClass("ilTMSSelfCancelGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSSelfCancelGUI", "usr_id", $usr_id);
	}

	/**
	 * @inheritdoc
	 */
	protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id){
		$event = Booking\Actions::EVENT_USER_CANCELED_COURSE;

		require_once("Services/Membership/classes/class.ilWaitingList.php");
		$crs_id = \ilObject::_lookupObjId($crs_ref_id);
		if(\ilWaitingList::_isOnList($target_usr_id, $crs_id))  {
			$event = Booking\Actions::EVENT_USER_CANCELED_WAITING;
		}
		$this->fireBookingEvent($event, $target_usr_id, $crs_ref_id);
	}
}

/**
 * cat-tms-patch end
 */
