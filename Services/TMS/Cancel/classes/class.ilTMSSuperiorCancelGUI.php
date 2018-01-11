<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Cancel/classes/class.ilTMSCancelPlayerStateDB.php");
require_once("Services/TMS/Cancel/classes/ilTMSCancelGUI.php");

/**
 * Displays the TMS cancel for superiors 
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSuperiorCancelGUI extends \ilTMSCancelGUI {
	/**
	 * @inheritdocs
	 */
	protected function getComponentClass() {
		return Booking\Step::class;
	}

	/**
	 * @inheritdocs
	 */
	protected function setParameter($crs_ref_id, $usr_id) {
		$this->g_ctrl->setParameterByClass("ilTMSSuperiorCancelGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSSuperiorCancelGUI", "usr_id", $usr_id);
	}
}

/**
 * cat-tms-patch end
 */
