<?php

/**
 * cat-tms-patch start
 */

class Helper {
	use \ILIAS\TMS\MyUsersHelper;

	const F_TITLE = "f_title";
	const F_TYPE = "f_type";
	const F_TOPIC = "f_topic";
	const F_TARGET_GROUP = "f_target";
	const F_DURATION = "f_duration";
	const F_SORT_VALUE = "f_sort_value";

	const S_TITLE_ASC = "s_title_asc";
	const S_PERIOD_ASC = "s_period_asc";
	const S_CITY_ASC = "s_city_asc";

	const S_TITLE_DESC = "s_title_desc";
	const S_PERIOD_DESC = "s_period_desc";
	const S_CITY_DESC = "s_city_desc";

	const S_USER = "s_user";

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	public function __construct() {
		global $DIC;
		$this->g_user = $DIC->user();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_lng = $DIC->language();
		$this->g_factory = $DIC->ui()->factory();
		$this->g_renderer = $DIC->ui()->renderer();
	}

	/**
	 * Get needed values from bkm. Just best for user
	 *
	 * @param ilObjBookingModalities[] 	$bms
	 *
	 * @return array<integer, int | string>
	 */
	public function getBestBkmValues(array $bkms) {
		require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilBookingModalitiesPlugin.php");
		return ilBookingModalitiesPlugin::bestValuesForUser($bkms);
	}

	/**
	 * Get information about selected venue
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getVenueInfos($crs_id) {
		$plugin = ilPluginAdmin::getPluginObjectById('venues');
		if(!$plugin) {
			return array(-1,"", "");
		}

		return $plugin->getVenueInfos($crs_id);
	}

	/**
	 * Get information about selected provider
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getProviderInfos($crs_id) {
		$plugin = ilPluginAdmin::getPluginObjectById('trainingprovider');
		if(!$plugin) {
			return array(-1);
		}

		return $plugin->getProviderInfos($crs_id);
	}

	/**
	 * Get information from course classification object
	 *
	 * @param ilObjCourseClassification 	$ccl
	 *
	 * @return array<integer, string | int[] | string[] | null> 
	 */
	public function getCourseClassificationValues($ccl) {
		if($ccl === null) {
			return array(null,
				"",
				array(),
				array(),
				"",
				array(),
				array(),
			);
		}

		return $ccl->getCourseClassificationValues();
	}

	/**
	 * Prepare the filter modal
	 *
	 * @param ilPropertyFormGUI $form
	 *
	 * @return string
	 */
	public function prepareModal(ilPropertyFormGUI $form)
	{
		require_once('./Services/Form/classes/class.ilTextInputGUI.php');
		require_once('./Services/Form/classes/class.ilDateDurationInputGUI.php');
		require_once("Services/Component/classes/class.ilPluginAdmin.php");

		$item = new ilTextInputGUI($this->g_lng->txt('title'), self::F_TITLE);
		$form->addItem($item);

		if(ilPluginAdmin::isPluginActive('xccl')) {
			$plugin = ilPluginAdmin::getPluginObjectById('xccl');
			$actions = $plugin->getActions();

			$item = new ilSelectInputGUI($this->g_lng->txt('type'), self::F_TYPE);
			$options = array(-1 => "Alle") + $actions->getTypeOptions();
			$item->setOptions($options);
			$form->addItem($item);

			$item = new ilSelectInputGUI($this->g_lng->txt('topic'), self::F_TOPIC);
			$options = array(-1 => "Alle") + $actions->getTopicOptions();
			$item->setOptions($options);
			$form->addItem($item);

			$item = new ilSelectInputGUI($this->g_lng->txt('target_group'), self::F_TARGET_GROUP);
			$options = array(-1 => "Alle") + $actions->getTargetGroupOptions();
			$item->setOptions($options);
			$form->addItem($item);
		}

		$item = new ilDateDurationInputGUI($this->g_lng->txt('duration'), self::F_DURATION);
		$item->setStart(new ilDateTime(date("Y-01-01 00:00:00"), IL_CAL_DATETIME));
		$item->setEnd(new ilDateTime(date("Y-12-31 23:59:59"), IL_CAL_DATETIME));
		$form->addItem($item);

		$item = new ilHiddenInputGUI('cmd');
		$item->setValue('submit');
		$form->addItem($item);


		if (isset($_POST['cmd']) && $_POST['cmd'] == 'submit') {
			$form->setValuesByPost();
		}

		// Build a submit button (action button) for the modal footer
		$form_id = 'form_' . $form->getId();
		$submit = $this->g_factory->button()->primary($this->g_lng->txt('search'), "#")->withOnLoadCode(function($id) use ($form_id) {
			return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
		});

		$reset = $this->g_factory->button()->standard($this->g_lng->txt('reset'), "#")->withOnLoadCode(function($id) use ($form_id) {
			$dur1 = '$("input[name=\'f_duration[start]\']").val("'.date("01.01.Y").'");';
			$dur2 = '$("input[name=\'f_duration[end]\']").val("'.date("31.12.Y").'");';
			return "$('#{$id}').click(function() { 
				$('#f_title').val('');
				$('#f_type option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_topic option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_target option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_not_min_member').prop('checked', false );
				".$dur1."
				".$dur2."
				return false; 
			});";
		});

		$modal = $this->g_factory->modal()->roundtrip($this->g_lng->txt('filter'), $this->g_factory->legacy($form->getHTML()))
			->withActionButtons([$reset, $submit]);

		return $modal;
	}

	/**
	 * Parse port array for filter values
	 *
	 * @return string[]
	 */
	public function getFilterValuesFrom(array $values) {
		$filter = array();

		if(array_key_exists(self::F_TITLE, $values)) {
			$title = trim($values[self::F_TITLE]);
			if($title != "") {
				$filter[self::F_TITLE] = $title;
			}
		}

		if(array_key_exists(self::F_TYPE, $values)) {
			$type = $values[self::F_TYPE];
			if($type != -1) {
				$filter[self::F_TYPE] = $type;
			}
		}

		if(array_key_exists(self::F_TOPIC, $values)) {
			$topic = $values[self::F_TOPIC];
			if($topic != -1) {
				$filter[self::F_TOPIC] = $topic;
			}
		}

		if(array_key_exists(self::F_TARGET_GROUP, $values)) {
			$target_group = $values[self::F_TARGET_GROUP];
			if($target_group != -1) {
				$filter[self::F_TARGET_GROUP] = $target_group;
			}
		}

		if(array_key_exists(self::F_DURATION, $values)) {
			$filter[self::F_DURATION] = $values[self::F_DURATION];
		}

		return $filter;
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	public function formatDate($dat, $use_time = false) {
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $this->g_user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}

	/**
	 * Sorts filtered bookable training according to user input
	 *
	 * @param string[] 	$values
	 * @param BookableCourse[]
	 *
	 * @return BookableCourse[]
	 */
	public function sortBookableTrainings(array $values, $bookable_trainings) {
		if(array_key_exists(self::F_SORT_VALUE, $values)
			&& $values[self::F_SORT_VALUE] != ""
		) {
			$function = null;
			switch($values[self::F_SORT_VALUE]) {
				case self::S_TITLE_ASC:
					uasort($bookable_trainings, $this->getTitleSortingClosure("asc"));
					break;
				case self::S_TITLE_DESC:
					uasort($bookable_trainings, $this->getTitleSortingClosure("desc"));
					break;
				case self::S_PERIOD_ASC:
					uasort($bookable_trainings, $this->getPeriodSortingClosure("asc"));
					break;
				case self::S_PERIOD_DESC:
					uasort($bookable_trainings, $this->getPeriodSortingClosure("desc"));
					break;
				case self::S_CITY_DESC:
					uasort($bookable_trainings, $this->getCitySortingClosure("asc"));
					break;
				case self::S_CITY_DESC:
					uasort($bookable_trainings, $this->getCitySortingClosure("desc"));
					break;
			}
		}

		return $bookable_trainings;
	}

	/**
	 * Get sorting closure for title
	 *
	 * @param string 	$direction
	 *
	 * @return Closure
	 */
	protected function getTitleSortingClosure($direction) {
		if($direction == "asc") {
			return function($a, $b) {
					return strcasecmp($a->getTitle(), $b->getTitle());
				};
		}

		if($direction == "desc") {
			return function($a, $b) {
					return strcasecmp($b->getTitle(), $a->getTitle());
				};
		}
	}

	/**
	 * Get sorting closure for period
	 *
	 * @param string 	$direction
	 *
	 * @return Closure
	 */
	protected function getPeriodSortingClosure($direction) {
		if($direction == "asc") {
			return function($a, $b) {
					if(is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
						return 0;
					} else if(is_null($a->getBeginDate()) && !is_null($b->getBeginDate())) {
						return 1;
					} else if(!is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
						return -1;
					}

					$start_date_a = $a->getBeginDate()->get(IL_CAL_DATE);
					$start_date_b = $b->getBeginDate()->get(IL_CAL_DATE);
					return strcmp($start_date_a, $start_date_b);
				};
		}

		if($direction == "desc") {
			return function($a, $b) {
					if(is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
						return 0;
					} else if(is_null($a->getBeginDate()) && !is_null($b->getBeginDate())) {
						return 1;
					} else if(!is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
						return -1;
					}

					$start_date_a = $a->getBeginDate()->get(IL_CAL_DATE);
					$start_date_b = $b->getBeginDate()->get(IL_CAL_DATE);
					return strcmp($start_date_b, $start_date_a);
				};
		}
	}

	/**
	 * Get sorting closure for city
	 *
	 * @param string 	$direction
	 *
	 * @return Closure
	 */
	protected function getCitySortingClosure($direction) {
		if($direction == "asc") {
			return function($a, $b) {
					return strcmp($a->getLocation(), $b->getLocation());
				};
		}

		if($direction == "desc") {
			return function($a, $b) {
					return strcmp($b->getLocation(), $a->getLocation());
				};
		}
	}
}

/**
 * cat-tms-patch end
 */
