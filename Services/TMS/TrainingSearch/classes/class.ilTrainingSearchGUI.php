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
	const CMD_CHANGE_USER = "changeUser";
	const CMD_QUICKFILTER = "quickFilter";
	const CMD_SORT = "sort";

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * UserId of the user that is going to be booked. Initially set to current ilUser.
	 * Initial the current ilUser.
	 * This might be changed, if the current user is allowed to book for others.
	 *
	 * @var int
	 */
	protected $search_user_id;

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
		$this->search_user_id = $DIC->user()->getId();
		$this->g_lng = $DIC->language();
		$this->g_toolbar = $DIC->toolbar();
		$this->g_f = $DIC->ui()->factory();
		$this->g_renderer = $DIC->ui()->renderer();
		$this->g_user = $DIC->user();

		$this->parent = $parent;
		$this->db = $db;
		$this->helper = $helper;

		$this->g_lng->loadLanguageModule('tms');
	}

	public function executeCommand() {
		$next_class = $this->g_ctrl->getNextClass();
		$this->changeUser();

		switch ($next_class) {
			case "iltmsbookinggui":
				require_once("Services/TMS/Booking/classes/class.ilTMSBookingGUI.php");
				$gui = new ilTMSBookingGUI($this, self::CMD_SHOW);
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				$cmd = $this->g_ctrl->getCmd(self::CMD_SHOW);
				switch($cmd) {
					case self::CMD_SHOW:
					case self::CMD_CHANGE_USER:
						$this->show();
						break;
					case self::CMD_FILTER:
						$this->filter();
						break;
					case self::CMD_QUICKFILTER:
						$this->quickFilter();
						break;
					case self::CMD_SORT:
						$this->sort();
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
		$get = $_GET;
		$filter = $this->helper->getFilterValuesFrom($get);
		$bookable_trainings = $this->getBookableTrainings($filter);
		$bookable_trainings = $this->helper->sortBookableTrainings(array(Helper::F_SORT_VALUE => Helper::S_TITLE_ASC), $bookable_trainings);
		$this->showTrainings($bookable_trainings);
	}

	/**
	 * Post processing for filter values
	 *
	 * @return void
	 */
	public function filter() {
		$post = $_POST;
		$filter = $this->helper->getFilterValuesFrom($post);
		$bookable_trainings = $this->getBookableTrainings($filter);
		$this->showTrainings($bookable_trainings);
	}

	/**
	 * Sorts all table entries according to selection
	 *
	 * @return void
	 */
	protected function sort() {
		$get = $_GET;
		$filter = $this->helper->getFilterValuesFrom($get);
		$bookable_trainings = $this->getBookableTrainings($filter);
		$bookable_trainings = $this->helper->sortBookableTrainings($get, $bookable_trainings);
		$this->showTrainings($bookable_trainings);
	}

	/**
	 * Post processing for quick filter values
	 *
	 * @return void
	 */
	public function quickFilter() {
		$get = $_GET;
		$filter = $this->helper->getFilterValuesFrom($get);
		$bookable_trainings = $this->getBookableTrainings($filter);
		$this->showTrainings($bookable_trainings);
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
		$table = new ilTrainingSearchTableGUI($this, $this->helper, $this->search_user_id);
		$table->setData($bookable_trainings);

		$modal = $this->prepareModal($button1);
		$button1 = $this->g_f->button()->standard($this->g_lng->txt('search'), '#')
			->withOnClick($modal->getShowSignal());

		$view_control = array($button1);
		$view_control = $this->addSortationObjects($view_control);
		$content = $this->g_renderer->render($modal).$table->render($view_control);

		if(count($bookable_trainings) == 0) {
			$content .= $this->getNoAvailableTrainings();
		}

		$this->g_tpl->setContent($content);
		$this->g_tpl->show();
	}

	/**
	 * Add all sorting and filter items for the table
	 *
	 * @return Sortation[]
	 */
	protected function addSortationObjects($view_control) {
		$employees = $this->helper->getUserWhereCurrentCanBookFor((int)$this->g_user->getId());
		if(count($employees) > 1) {
			$link = $this->g_ctrl->getLinkTarget($this, ilTrainingSearchGUI::CMD_CHANGE_USER);
			$view_control[] = $this->g_f->viewControl()->quickfilter($employees)
				->withTargetURL($link, Helper::S_USER)
				->withDefaultValue($this->g_user->getId())
				->withLabel($this->g_lng->txt("employees"));
		}

		require_once("Services/Component/classes/class.ilPluginAdmin.php");
		if(ilPluginAdmin::isPluginActive('xccl')) {
			$plugin = ilPluginAdmin::getPluginObjectById('xccl');
			$actions = $plugin->getActions();
			$link = $this->g_ctrl->getLinkTarget($this, ilTrainingSearchGUI::CMD_QUICKFILTER);

			$options = array("" => $this->g_lng->txt("show_all"));
			$type_options = $actions->getTypeOptions();
			uasort($type_options, function($a, $b) { return strcmp($a, $b);});
			$view_control[] = $this->g_f->viewControl()->quickfilter($options + $type_options)
						->withTargetURL($link, Helper::F_TYPE)
						->withDefaultValue("")
						->withLabel($plugin->txt("conf_options_type"));

			$topic_options = $actions->getTopicOptions();
			uasort($topic_options, function($a, $b) { return strcmp($a, $b);});
			$view_control[] = $this->g_f->viewControl()->quickfilter($options + $topic_options)
						->withTargetURL($link, Helper::F_TOPIC)
						->withDefaultValue("")
						->withLabel($plugin->txt("conf_options_topic"));
		}

		$link = $this->g_ctrl->getLinkTarget($this, ilTrainingSearchGUI::CMD_SORT);
		$view_control[] = $this->g_f->viewControl()->sortation($this->getSortOptions())
						->withTargetURL($link, Helper::F_SORT_VALUE)
						->withLabel($this->g_lng->txt(Helper::S_TITLE_ASC));

		return $view_control;
	}

	/**
	 * Get empty search-results message
	 *
	 * @return void
	 */
	protected function getNoAvailableTrainings() {
		return $this->g_lng->txt('no_trainings_available');
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
		return $this->db->getBookableTrainingsFor($this->search_user_id, $filter);
	}

	/**
	 * Get a link to book the given training.
	 *
	 * @param	BookableCourse	$course
	 * @return	string
	 */
	public function getBookingLink(BookableCourse $course) {
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", $course->getRefId());
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", $this->search_user_id);
		$link = $this->g_ctrl->getLinkTargetByClass("ilTMSBookingGUI", "start");
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
		return $link;
	}

	/**
	 * Change user courses are searched for to selected user
	 *
	 * @return void
	 */
	protected function changeUser() {
		$get = $_GET;
		if(isset($get[Helper::S_USER]) && $get[Helper::S_USER] !== "") {
			$this->search_user_id = (int)$get[Helper::S_USER];
		}
	}

	/**
	 * Get the option for sorting of table
	 *
	 * @return string[]
	 */
	public function getSortOptions() {
		return array(
			Helper::S_TITLE_ASC => $this->g_lng->txt(Helper::S_TITLE_ASC),
			Helper::S_TITLE_DESC => $this->g_lng->txt(Helper::S_TITLE_DESC),
			Helper::S_PERIOD_ASC => $this->g_lng->txt(Helper::S_PERIOD_ASC),
			Helper::S_PERIOD_DESC => $this->g_lng->txt(Helper::S_PERIOD_DESC),
			Helper::S_CITY_ASC => $this->g_lng->txt(Helper::S_CITY_ASC),
			Helper::S_CITY_DESC => $this->g_lng->txt(Helper::S_CITY_DESC)
		);
	}
}

/**
 * cat-tms-patch end
 */
