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

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var
	 */

	protected $appointment;

	/**
	 * @var ilInfoScreenGUI
	 */
	protected $infoscreen;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * 
	 *
	 * @param
	 * @return
	 */
	function __construct($a_appointment, $a_info_screen, $a_toolbar)
	{
		global $DIC;
		$this->appointment = $a_appointment;
		$this->infoscreen = $a_info_screen;
		$this->toolbar = $a_toolbar;
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule("dateplaner");
		$this->tree = $DIC->repositoryTree();
		$this->ui = $DIC->ui();
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

	/**
	 * Add course/group container info
	 *
	 * @param int $a_ref_id
	 */
	function addContainerInfo($a_ref_id)
	{
		$tree = $this->tree;
		$infoscreen = $this->getInfoScreen();
		$f = $this->ui->factory();
		$r = $this->ui->renderer();

		//parent course or group title
		$cont_ref_id = $tree->checkForParentType($a_ref_id, 'grp');
		if ($cont_ref_id == 0)
		{
			$cont_ref_id = $tree->checkForParentType($a_ref_id, 'crs');
		}

		if ($cont_ref_id > 0)
		{
			$type = ilObject::_lookupType($cont_ref_id, true);
			$href = ilLink::_getStaticLink($cont_ref_id);
			$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($cont_ref_id));
			$infoscreen->addProperty($this->lng->txt("obj_" . $type), $r->render($f->button()->shy($parent_title, $href)));
		}
	}



	//TODO : SOME ELEMENTS CAN GET CUSTOM METADATA
}