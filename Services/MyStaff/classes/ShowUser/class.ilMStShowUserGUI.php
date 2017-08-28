<?php

/**
 * GUI-Class Table ilMStShowUserGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStShowUserGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStShowUserGUI:ilFormPropertyDispatchGUI
 */
class ilMStShowUserGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const CMD_INDEX = 'index';
	const CMD_SHOWUSER = 'showUser';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_APPLY_FILTER = 'applyFilter';
	/**
	 * @var int
	 */
	protected $usr_id;
	/**
	 * @var  ilTable2GUI
	 */
	protected $table;


	public function __construct() {
		$this->usr_id = $this->dic()->http()->request()->getQueryParams()['usr_id'];
		$this->ctrl()->setParameter($this, 'usr_id', $this->usr_id);

		$this->tpl()->setTitle(ilUserUtil::getNamePresentation($this->usr_id));
		$this->tpl()->setTitleIcon(ilObjUser::_getPersonalPicturePath($this->usr_id, "xxsmall"));
	}


	protected function checkAccessOrFail() {
		if (!$this->usr_id) {
			ilUtil::sendFailure($this->lng()->txt("permission_denied"), true);
			$this->ctrl()->redirectByClass('ilPersonalDesktopGUI', "");
		}

		if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff()) {
			return true;
		} else {
			ilUtil::sendFailure($this->lng()->txt("permission_denied"), true);
			$this->ctrl()->redirectByClass('ilPersonalDesktopGUI', "");
		}
	}


	public function executeCommand() {
		$this->checkAccessOrFail();

		$cmd = $this->ctrl()->getCmd();
		$next_class = $this->ctrl()->getNextClass();

		switch ($next_class) {
			case strtolower(ilFormPropertyDispatchGUI::class):
				$this->ctrl()->setReturn($this, self::CMD_INDEX);
				$table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
				$table->executeCommand();
				break;
			default:
				switch ($cmd) {
					case self::CMD_RESET_FILTER:
					case self::CMD_APPLY_FILTER:
					case self::CMD_INDEX:
						$this->addTabs('show_courses');
						$this->$cmd();
						break;
					case self::CMD_SHOWUSER:
						$this->addTabs('show_user');
						$this->$cmd();
						break;
					default:
						$this->addTabs('show_courses');
						$this->index();
						break;
				}
		}
	}


	protected function index() {
		$this->listUsers();
	}


	protected function listUsers() {
		$this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
		$this->table->setTitle(sprintf($this->lng()
		                                    ->txt('mst_courses_of'), ilObjCourse::_lookupTitle($this->usr_id)));

		/*$pub_profile = new ilPublicUserProfileGUI($this->usr_id);

		$tpl = new ilTemplate('./Services/MyStaff/templates/default/tpl.show_user_container.html', true, true);

		$tpl->setCurrentBlock('courses');
		$tpl->setVariable('COURSES', $this->table->getHTML());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('profile');
		$tpl->setVariable('PROFILE', $pub_profile->getEmbeddable());
		$tpl->parseCurrentBlock();*/

		$this->tpl()->setContent($this->table->getHTML());
	}


	protected function showUser() {
		//Redirect if Profile is not public
		$user = new ilObjUser($this->usr_id);
		if (!$user->hasPublicProfile()) {
			$this->ctrl()->redirectByClass('ilMStShowUserGUI', "index");
		}

		$pub_profile = new ilPublicUserProfileGUI($this->usr_id);
		$this->tpl()->setContent($pub_profile->getEmbeddable());
	}


	protected function applyFilter() {
		$this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_APPLY_FILTER);
		$this->table->writeFilterToSession();
		$this->table->resetOffset();
		$this->index();
	}


	protected function resetFilter() {
		$this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_RESET_FILTER);
		$this->table->resetOffset();
		$this->table->resetFilter();
		$this->index();
	}


	/**
	 * @return string
	 */
	public function getId() {
		$this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);

		return $this->table->getId();
	}


	public function cancel() {
		$this->ctrl()->redirect($this);
	}


	/**
	 * @param string $active_tab_id
	 */
	protected function addTabs($active_tab_id) {
		$lng = $this->lng();
		$tabs = $this->tabs();
		$ctrl = $this->ctrl();
		$tabs->setBackTarget($lng->txt('mst_list_users'), $ctrl->getLinkTargetByClass(array(
			ilMyStaffGUI::class,
			ilMStListUsersGUI::class,
		)));
		$tabs->addTab('show_courses', $lng->txt('mst_show_courses'), $ctrl->getLinkTargetByClass(array(
			ilMyStaffGUI::class,
			ilMStShowUserGUI::class,
		), self::CMD_INDEX));

		$user = new ilObjUser($this->usr_id);
		if ($user->hasPublicProfile()) {
			$ctrl->setParameterByClass('ilmstshowusergui', 'usr_id', $this->usr_id);
			$public_profile_url = $ctrl->getLinkTargetByClass('ilmstshowusergui', self::CMD_SHOWUSER);
			$tabs->addTab('show_user', $lng->txt('public_profile'), $public_profile_url);
		}

		if ($active_tab_id) {
			$tabs->activateTab($active_tab_id);
		}
	}
}