<?php

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/TMS/Table/TMSTableParentGUI.php");

/**
 * This is our base implementation for the ilias table gui.
 * It is minimized only to show data.
 * Configuration always happens in parent gui
 *
 * @author Stefan Hecken	<stefan.hecken@concepts-and-training.de>
 */
final class ilTMSTableGUI extends ilTable2GUI
{
	/**
	 * @var \Closure
	 */
	protected $fill_row;

	/**
	 * @var string
	 */
	protected $table_id;

	public function __construct(TMSTableParentGUI $parent_gui, $parent_cmd, \Closure $fill_row, $table_id)
	{
		$this->fill_row = $fill_row;
		$this->setId($table_id);
		parent::__construct($parent_gui, $parent_cmd);
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setShowRowsSelector(false);
		$this->setDefaultOrderDirection("asc");
	}

	public function fillRow($a_set)
	{
		$fnc = $this->fill_row;
		$fnc($this, $a_set);
	}

	/**
	 * Get the property tpl
	 *
	 * @return \ilTemplate
	 */
	public function getTemplate() {
		return $this->tpl;
	}
}
