<?php
/**
 * cat-tms-patch start
 */

require_once("Services/TMS/TrainingSearch/classes/Helper.php");

/**
 * Displays the TMS training search
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 * @ilCtrl_Calls	ilTrainingSearchGUI: ilTMSBookingGUI
 */
class ilTrainingSearchGUI {
	const CMD_SHOW = "show";
	const CMD_SHOW_MODAL = "showModal";
	const CMD_FILTER = "filter";

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var ilPersonalDesktopGUI
	 */
	protected $parent;

	/**
	 * @var TrainingSearchDB
	 */
	protected $db;

	public function __construct(ilPersonalDesktopGUI $parent, TrainingSearchDB $db, Helper $helper) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->g_toolbar = $DIC->toolbar();

		$this->parent = $parent;
		$this->db = $db;
		$this->helper = $helper;

		$this->g_lng->loadLanguageModule('tms');
	}

	public function executeCommand() {
		$next_class = $this->g_ctrl->getNextClass();

		switch ($next_class) {
			case "iltmsbookinggui":
				require_once("Services/TMS/Booking/classes/class.ilTMSBookingGUI.php");
				$gui = new ilTMSBookingGUI($this, self::CMD_SHOW);
				$gui->redirectOnParallelCourses();
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				$cmd = $this->g_ctrl->getCmd(self::CMD_SHOW);
				switch($cmd) {
					case self::CMD_SHOW:
						$this->show();
						break;
					case self::CMD_FILTER:
						$this->filter();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
				}
		}
	}

	/**
	 * Shows all bookable trainings
	 *
	 * @param string[] 	$filter
	 *
	 * @return void
	 */
	protected function show() {
		$bookable_trainings = $this->getBookableTrainings(array());
		if(count($bookable_trainings) > 0) {
			$this->showTrainings($bookable_trainings);
		} else {
			$this->showNoAvailableTrainings();
		}
	}

	/**
	 * Post processing for filter values
	 *
	 * @return void
	 */
	protected function filter() {
		$post = $_POST;
		$filter = $this->helper->getFilterValuesFrom($post);
		$bookable_trainings = $this->getBookableTrainings($filter);
		if(count($bookable_trainings) > 0) {
			$this->showTrainings($bookable_trainings);
		} else {
			$this->showNoAvailableTrainings();
		}

	}

	/**
	 * Show bookable trainings
	 *
	 * @param BookableCourse[] 	$bookable_trainings
	 *
	 * @return void
	 */
	protected function showTrainings(array $bookable_trainings) {
		require_once("Services/TMS/TrainingSearch/classes/class.ilTrainingSearchTableGUI.php");
		$table = new ilTrainingSearchTableGUI($this, $this->helper);
		$table->setData($bookable_trainings);

		$modal = $this->prepareModal();
		$this->g_tpl->setContent($modal."<br \><br \><br \>".$table->render());
		$this->g_tpl->show();
	}

	/**
	 * Show empty search-results message
	 *
	 * @return void
	 */
	protected function showNoAvailableTrainings() {
		$modal = $this->prepareModal();
		$msg = $this->g_lng->txt('no_trainings_available');
		$this->g_tpl->setContent($modal."<br \><br \><br \>".$msg);
		$this->g_tpl->show();
	}

	/**
	 * Prepare the filter modal
	 *
	 * @param ilPropertyFormGUI $form
	 *
	 * @return string
	 */
	public function prepareModal() {
		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setId(uniqid('form'));
		$form->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_FILTER));

		return $this->helper->prepareModal($form);
	}

	/**
	 * Get Bookable trainings
	 *
	 * @param array<int, string | int | string[]> 	$filter
	 *
	 * @return BookableCourse[]
	 */
	protected function getBookableTrainings(array $filter) {
		return $this->db->getBookableTrainingsFor($this->g_user->getId(), $filter);
	}

	/**
	 * Get a link to book the given training.
	 *
	 * @param	BookableCourse	$course
	 * @return	string
	 */
	public function getBookingLink(BookableCourse $course) {
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", $course->getRefId());
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", $this->g_user->getId());
		$link = $this->g_ctrl->getLinkTargetByClass("ilTMSBookingGUI", "start");
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
		return $link;
	}
}

/**
 * cat-tms-patch end
 */
