<?php
include_once ("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all question plugin classes.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilQuestionsPlugin extends ilPlugin {
	/**
	 * Get Component Type
	 *
	 * @return string Component Type
	 */
	final function getComponentType() {
		return IL_COMP_MODULE;
	}
	
	/**
	 * Get Component Name.
	 *
	 * @return string Component Name
	 */
	final function getComponentName() {
		return "TestQuestionPool";
	}
	
	/**
	 * Get Slot Name.
	 *
	 * @return string Slot Name
	 */
	final function getSlot() {
		return "Questions";
	}
	
	/**
	 * Get Slot ID.
	 *
	 * @return string Slot Id
	 */
	final function getSlotId() {
		return "qst";
	}
	
	/**
	 * Object initialization done by slot.
	 */
	protected final function slotInit() {
		// nothing to do here
	}
	
	abstract function getQuestionType();
}
?>