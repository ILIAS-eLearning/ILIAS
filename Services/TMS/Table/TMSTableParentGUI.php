<?php

/**
 * This is an abstract class for parent gui of tms tables
 * Parent guis has to iplement this to work fine
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
abstract class TMSTableParentGUI {
	/**
	 * Get the closure table should be filled with
	 *
	 * @return \Closure
	 */
	abstract protected function fillRow();

	/**
	 * Get the basic command table has to use
	 *
	 * @return string
	 */
	abstract protected function tableCommand();

	/**
	 * Get the id of table
	 *
	 * @return string
	 */
	abstract protected function tableId();

	/**
	 * Get an instance of the table gui
	 *
	 * @return ilTMSTableGUI
	 */
	protected function getTMSTableGUI() {
		require_once("Services/TMS/Table/ilTMSTableGUI.php");
		return new ilTMSTableGUI($this, $this->tableCommand(), $this->fillRow(), $this->tableId());
	}
}