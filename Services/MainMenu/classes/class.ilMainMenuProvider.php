<?php

/**
 * Class ilMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMainMenuProvider extends \ILIAS\UX\Provider\AbstractProvider implements \ILIAS\UX\Provider\StaticProvider\MainMenu {

	/**
	 * @var \ILIAS\UX\MainMenu\EntryFactory
	 */
	protected $mainmenu;
	/**
	 * @var \ILIAS\UX\Identification\ProviderInterface
	 */
	protected $identification;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilPluginAdmin
	 */
	protected $plugin_admin;
	/**
	 * @var array
	 */
	private $slate_ids = [];
	/**
	 * ilMainMenuProvider constructor.
	 *
	 * @param \ILIAS\UX\Services  $ux
	 * @param \ILIAS\DI\Container $DIC
	 */
	const INTERNAL_DESKTOP = 'desktop';
	const INTERNAL_REPOSITORY = 'rep';
	const INTERNAL_ADMINISTRATION = 'adm';


	public function __construct(\ILIAS\DI\Container $DIC) {
		$this->dic = $DIC;
	}


	/**
	 * @inheritDoc
	 */
	public function inject(\ILIAS\UX\Services $services) {
		parent::inject($services);
		$this->mainmenu = $this->ux->mainmenu();
		$this->identification = $this->ux->identification()->core($this);
		$this->slate_ids = [
			self::INTERNAL_DESKTOP        => $this->identification->internal(self::INTERNAL_DESKTOP),
			self::INTERNAL_REPOSITORY     => $this->identification->internal(self::INTERNAL_REPOSITORY),
			self::INTERNAL_ADMINISTRATION => $this->identification->internal(self::INTERNAL_ADMINISTRATION),
		];
	}


	/**
	 * @inheritdoc
	 */
	public function getStaticSlates(): array {
		$slates = [];

		$lng = $this->dic->language();
		$m = $this->mainmenu;
		$dic = $this->dic;

		// Personal Desktop Slate
		$slates[] = $m->slate($this->slate_ids[self::INTERNAL_DESKTOP])->withTitle($lng->txt("personal_desktop"))->withVisibilityCallable(
			function () use ($dic) {
				return ($dic->user()->getId() != ANONYMOUS_USER_ID);
			}
		);

		// Repository
		$slates[] = $this->mainmenu->slate($this->slate_ids[self::INTERNAL_REPOSITORY])->withTitle(
			$this->dic->language()->txt("repository")
		)->withVisibilityCallable(function () use ($dic) { return ($dic->access()->checkAccess('visible', '', ROOT_FOLDER_ID)); });

		// Administration
		$slates[] = $this->mainmenu->slate($this->slate_ids[self::INTERNAL_ADMINISTRATION])->withTitle(
			$this->dic->language()->txt("administration")
		)->withAsyncContentURL("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch")->withVisibilityCallable(
			function () {

			}
		);

		return $slates;
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticEntries(): array {
		global $ilSetting;
		$lng = $this->dic->language();
		$dic = $this->dic;
		$g = $this->identification;
		$m = $this->mainmenu;
		//
		// Personal Desktop
		//

		// overview
		$entries[] = $m->link($g->internal('mm_pd_sel_items'))->withTitle($lng->txt("overview"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems"
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// my groups and courses, if both is available
		$entries[] = $m->link($g->internal('mm_pd_crs_grp'))->withTitle($lng->txt("my_courses_groups"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships"
		)->withVisibilityCallable(
			function () use ($dic) {
				$pdItemsViewSettings = new ilPDSelectedItemsBlockViewSettings($dic->user());

				return (bool)$pdItemsViewSettings->allViewsEnabled();
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// bookmarks
		$entries[] = $m->link($g->internal('mm_pd_bookm'))->withTitle($lng->txt("bookmarks"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBookmarks"
		)->withAvailableCallable(
			function () use ($ilSetting) {
				return (bool)!$ilSetting->get("disable_bookmarks");
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// private notes
		$lng->loadLanguageModule("notes");
		$t = $lng->txt("notes");
		$c = "jumpToNotes";
		if (!$ilSetting->get("disable_notes") && !$ilSetting->get("disable_comments")) {
			$t = $lng->txt("notes_and_comments");
		}
		if ($ilSetting->get("disable_notes")) {
			$t = $lng->txt("notes_comments");
			$c = "jumpToComments";
		}
		$entries[] = $m->link($g->internal('mm_pd_notes'))->withTitle($t)->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=" . $c
		)->withAvailableCallable(
			function () use ($ilSetting) {
				return (bool)(!$ilSetting->get("disable_notes") || !$ilSetting->get("disable_comments"));
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// news
		$entries[] = $m->link($g->internal('mm_pd_news'))->withTitle($lng->txt("news"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNews"
		)->withAvailableCallable(
			function () use ($ilSetting) {
				return ($ilSetting->get("block_activated_news"));
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// overview is always active
		$entries[] = $m->divider($g->internal('sep_1'));

		// MyStaff
		$entries[] = $m->link($g->internal('mm_pd_mst'))->withTitle($lng->txt("my_staff"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMyStaff"
		)->withAvailableCallable(
			function () use ($ilSetting) {
				return (bool)($ilSetting->get("enable_my_staff"));
			}
		)->withVisibilityCallable(
			function () use ($ilSetting) {
				return (bool)ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// Workspace
		$entries[] = $m->link($g->internal('mm_pd_wsp'))->withTitle($lng->txt("personal_workspace"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace"
		)->withAvailableCallable(
			function () use ($ilSetting) {
				return (bool)(!$ilSetting->get("disable_personal_workspace"));
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// portfolio
		$entries[] = $m->link($g->internal('mm_pd_port'))->withTitle($lng->txt("portfolio"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio"
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP])->withActiveCallable(
			function () use ($ilSetting) {
				return (bool)($ilSetting->get('user_portfolios'));
			}
		);

		// skills
		$entries[] = $m->link($g->internal('mm_pd_skill'))->withTitle($lng->txt("skills"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSkills"
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP])->withAvailableCallable(
			function () {
				$skmg_set = new ilSetting("skmg");

				return (bool)($skmg_set->get("enable_skmg"));
			}
		);

		// Badges
		$entries[] = $m->link($g->internal('mm_pd_contacts'))->withTitle($lng->txt("obj_bdga"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBadges"
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP])->withAvailableCallable(
			function () {
				return (bool)(ilBadgeHandler::getInstance()->isActive());
			}
		);

		// Learning Progress
		$entries[] = $m->link($g->internal('mm_pd_lp'))->withTitle($lng->txt("learning_progress"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP"
		)->withAvailableCallable(
			function () {
				return (bool)(ilObjUserTracking::_enabledLearningProgress()
					&& (ilObjUserTracking::_hasLearningProgressOtherUsers()
						|| ilObjUserTracking::_hasLearningProgressLearner()));
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// dynamic separator missing

		// calendar
		$entries[] = $m->link($g->internal('mm_pd_cal'))->withTitle($lng->txt("calendar"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToCalendar"
		)->withAvailableCallable(
			function () {
				$settings = ilCalendarSettings::_getInstance();

				return (bool)($settings->isEnabled());
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// mail
		$entries[] = $m->link($g->internal('mm_pd_mail'))->withTitle($lng->txt("mail"))->withAction(
			"ilias.php?baseClass=ilMailGUI"
		)->withVisibilityCallable(
			function () use ($dic) {
				if ($dic->user()->getId() != ANONYMOUS_USER_ID) {
					if ($dic->rbac()->system()->checkAccess(
						'internal_mail', ilMailGlobalServices::getMailObjectRefId()
					)
					) {
						return true;
					}
				}

				return false;
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		// contacts
		$entries[] = $m->link($g->internal('mm_pd_contacts'))->withTitle($lng->txt("mail_addressbook"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts"
		)->withAvailableCallable(
			function () {
				return (bool)(ilBuddySystem::getInstance()->isEnabled());
			}
		)->withParent($this->slate_ids[self::INTERNAL_DESKTOP]);

		//
		// REPOSITORY
		//
		//
		$nd = $dic->repositoryTree()->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS") {
			$title = $lng->txt("repository");
		}
		$icon = ilUtil::img(ilObject::_getIcon(ilObject::_lookupObjId(1), "tiny"));
		$title = $icon . " " . $title . " - " . $lng->txt("rep_main_page");
		$action = ilLink::_getStaticLink(1, 'root', true);
		$entries[] = $this->mainmenu->link($this->identification->internal('rep_main_page'))
			->withTitle($title)
			->withAction($action)
			->withParent($this->slate_ids[self::INTERNAL_REPOSITORY]);

		// LastVisited
		$items = $this->dic['ilNavigationHistory']->getItems();
		reset($items);
		$cnt = 0;
		$first = true;

		foreach ($items as $k => $item) {
			if ($cnt >= 10) {
				break;
			}

			if (!isset($item["ref_id"]) || !isset($_GET["ref_id"])
				|| ($item["ref_id"] != $_GET["ref_id"] || !$first)
			)            // do not list current item
			{
				if ($cnt == 0) {
					$entries[] = $this->mainmenu->divider($this->identification->internal('sep2'));
				}
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$cnt++;
				$icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
				$ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
				$entries[] = $this->mainmenu->link($this->identification->internal('rep_main_page'))
					->withTitle($icon . " " . $ititle)
					->withAction($item["link"])
					->withParent($this->slate_ids[self::INTERNAL_REPOSITORY]);
			}
			$first = false;
		}

		//
		// ADMINISTRATION
		//
		//

		return $entries;
	}
}
