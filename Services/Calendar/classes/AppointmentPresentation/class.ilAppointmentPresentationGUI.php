<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	protected static $instance; // [ilCalendarAppointmentPresentationFactory]
	protected $toolbar;
	protected $appointment;
	protected $infoscreen;

	/**
	 * 
	 *
	 * @param
	 * @return
	 */
	function __construct($a_appointment, $a_info_screen, $a_toolbar)
	{
		$this->appointment = $a_appointment;
		$this->infoscreen = $a_info_screen;
		$this->toolbar = $a_toolbar;
	}
	
	
	/**
	 *
	 * @return self
	 */
	public static function getInstance($a_appointment, $a_info_screen, $a_toolbar)
	{
		return new static($a_appointment, $a_info_screen, $a_toolbar);
	}

	/**
	 * @return ilToolbarGUI
	 */
	public function getToolbar()
	{
		return $this->toolbar;
	}

	/**
	 * @return ilInfoScreenGUI
	 */
	public function getInfoScreen()
	{
		return $this->infoscreen;
	}

	public function getCatId($a_entry_id)
	{
		return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
	}

	public function getCatInfo($a_cat_id)
	{
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($a_cat_id);
		$entry_obj_id = isset($cat_info['subitem_obj_ids'][$cat_id]) ?
			$cat_info['subitem_obj_ids'][$cat_id] :
			$cat_info['obj_id'];
		return $cat_info;
	}

	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{

	}


	//TODO : SOME ELEMENTS CAN GET CUSTOM METADATA
}