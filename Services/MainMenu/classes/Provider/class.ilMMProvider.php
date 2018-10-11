<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

/**
 * Class ilMMProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider {

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var array
	 */
	private $slate_ids = [];
	/**
	 * ilMMProvider constructor.
	 *
	 * @param \ILIAS\GlobalScreen\Services $ux
	 * @param \ILIAS\DI\Container          $DIC
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
	public function inject(\ILIAS\GlobalScreen\Services $services) {
		parent::inject($services);
		$this->slate_ids = [
			self::INTERNAL_DESKTOP        => $this->if->identifier(self::INTERNAL_DESKTOP),
			self::INTERNAL_REPOSITORY     => $this->if->identifier(self::INTERNAL_REPOSITORY),
			self::INTERNAL_ADMINISTRATION => $this->if->identifier(self::INTERNAL_ADMINISTRATION),
		];
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem[]
	 */
	public function getStaticSlates(): array {
		$slates = [];

		$lng = $this->dic->language();
		$m = $this->mainmenu;
		$dic = $this->dic;

		// Personal Desktop TopParentItem
		$slates[] = $m->topParentItem($this->getDesktop())->withTitle($lng->txt("personal_desktop"))->withVisibilityCallable(
			function () use ($dic) {
				return (bool)($dic->user()->getId() != ANONYMOUS_USER_ID);
			}
		);

		// Repository
		$slates[] = $this->mainmenu->topParentItem($this->getRepository())->withTitle(
			$this->dic->language()->txt("repository")
		)->withVisibilityCallable(function () use ($dic) { return (bool)($dic->access()->checkAccess('visible', '', ROOT_FOLDER_ID)); });

		// Administration
		$slates[] = $this->mainmenu->topParentItem($this->getAdministration())->withTitle(
			$this->dic->language()->txt("administration")
		)->withVisibilityCallable(function () use ($dic) { return (bool)($dic->access()->checkAccess('visible', '', SYSTEM_FOLDER_ID)); });

		return $slates;
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem[]
	 */
	public function getStaticEntries(): array {
		$lng = $this->dic->language();
		$dic = $this->dic;
		$g = $this->if;
		$m = $this->mainmenu;
		//
		// Personal Desktop
		//

		// overview
		$entries[] = $m->link($g->identifier('mm_pd_sel_items'))->withTitle($lng->txt("overview"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems"
		)->withParent($this->getDesktop());

		// my groups and courses, if both is available
		$entries[] = $m->link($g->identifier('mm_pd_crs_grp'))->withTitle($lng->txt("my_courses_groups"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships"
		)->withVisibilityCallable(
			function () use ($dic) {
				$pdItemsViewSettings = new ilPDSelectedItemsBlockViewSettings($dic->user());

				return (bool)$pdItemsViewSettings->allViewsEnabled();
			}
		)->withParent($this->getDesktop());

		// bookmarks
		$entries[] = $m->link($g->identifier('mm_pd_bookm'))->withTitle($lng->txt("bookmarks"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBookmarks"
		)->withAvailableCallable(
			function () use ($dic) {
				return (bool)!$dic->settings()->get("disable_bookmarks");
			}
		)->withParent($this->getDesktop());

		// private notes
		$action = function () use ($dic): string {
			$c = "jumpToNotes";
			if ($dic->settings()->get("disable_notes")) {
				$c = "jumpToComments";
			}

			return "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=" . $c;
		};
		$action = $action();
		$title = function () use ($dic): string {
			$dic->language()->loadLanguageModule("notes");
			$t = $dic->language()->txt("notes");
			if (!$dic->settings()->get("disable_notes") && !$dic->settings()->get("disable_comments")) {
				$t = $dic->language()->txt("notes_and_comments");
			}
			if ($dic->settings()->get("disable_notes")) {
				$t = $dic->language()->txt("notes_comments");
			}

			return $t;
		};
		$title = $title();
		$entries[] = $m->link($g->identifier('mm_pd_notes'))->withTitle(
			$title
		)->withAction(
			$action
		)->withAvailableCallable(
			function () use ($dic) {
				return (bool)(!$dic->settings()->get("disable_notes") || !$dic->settings()->get("disable_comments"));
			}
		)->withParent($this->getDesktop());

		// news
		$entries[] = $m->link($g->identifier('mm_pd_news'))->withTitle($lng->txt("news"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNews"
		)->withAvailableCallable(
			function () use ($dic) {
				return ($dic->settings()->get("block_activated_news"));
			}
		)->withParent($this->getDesktop());

		// overview is always active
		$entries[] = $m->separator($g->identifier('sep_1'));

		// MyStaff
		$entries[] = $m->link($g->identifier('mm_pd_mst'))->withTitle($lng->txt("my_staff"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMyStaff"
		)->withAvailableCallable(
			function () use ($dic) {
				return (bool)($dic->settings()->get("enable_my_staff"));
			}
		)->withVisibilityCallable(
			function () {
				return (bool)ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
			}
		)->withParent($this->getDesktop());

		// Workspace
		$entries[] = $m->link($g->identifier('mm_pd_wsp'))->withTitle($lng->txt("personal_workspace"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace"
		)->withAvailableCallable(
			function () use ($dic) {
				return (bool)(!$dic->settings()->get("disable_personal_workspace"));
			}
		)->withParent($this->getDesktop());

		// portfolio
		$entries[] = $m->link($g->identifier('mm_pd_port'))->withTitle($lng->txt("portfolio"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio"
		)->withParent($this->getDesktop())->withActiveCallable(
			function () use ($dic) {
				return (bool)($dic->settings()->get('user_portfolios'));
			}
		);

		// skills
		$entries[] = $m->link($g->identifier('mm_pd_skill'))->withTitle($lng->txt("skills"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSkills"
		)->withParent($this->getDesktop())->withAvailableCallable(
			function () {
				$skmg_set = new ilSetting("skmg");

				return (bool)($skmg_set->get("enable_skmg"));
			}
		);

		// Badges
		$entries[] = $m->link($g->identifier('mm_pd_contacts'))->withTitle($lng->txt("obj_bdga"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBadges"
		)->withParent($this->getDesktop())->withAvailableCallable(
			function () {
				return (bool)(ilBadgeHandler::getInstance()->isActive());
			}
		);

		// Learning Progress
		$entries[] = $m->link($g->identifier('mm_pd_lp'))->withTitle($lng->txt("learning_progress"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP"
		)->withAvailableCallable(
			function () {
				return (bool)(ilObjUserTracking::_enabledLearningProgress()
					&& (ilObjUserTracking::_hasLearningProgressOtherUsers()
						|| ilObjUserTracking::_hasLearningProgressLearner()));
			}
		)->withParent($this->getDesktop());

		// dynamic separator missing

		// calendar
		$entries[] = $m->link($g->identifier('mm_pd_cal'))->withTitle($lng->txt("calendar"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToCalendar"
		)->withAvailableCallable(
			function () {
				$settings = ilCalendarSettings::_getInstance();

				return (bool)($settings->isEnabled());
			}
		)->withParent($this->getDesktop());

		// mail
		$entries[] = $m->link($g->identifier('mm_pd_mail'))->withTitle($lng->txt("mail"))->withAction(
			"ilias.php?baseClass=ilMailGUI"
		)->withAvailableCallable(
			function () use ($dic) {
				return ($dic->user()->getId() != ANONYMOUS_USER_ID);
			}
		)->withVisibilityCallable(
			function () use ($dic) {
				return $dic->rbac()->system()->checkAccess(
					'internal_mail', ilMailGlobalServices::getMailObjectRefId()
				);
			}
		)->withParent($this->getDesktop());

		// contacts
		$entries[] = $m->link($g->identifier('mm_pd_contacts'))->withTitle($lng->txt("mail_addressbook"))->withAction(
			"ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts"
		)->withAvailableCallable(
			function () {
				return (bool)(ilBuddySystem::getInstance()->isEnabled());
			}
		)->withParent($this->getDesktop());

		//
		// REPOSITORY
		//
		$title = function () use ($dic): string {
			try {
				$nd = $dic['tree']->getNodeData(ROOT_FOLDER_ID);
				$title = ($nd["title"] === "ILIAS" ? $dic->language()->txt("repository") : $nd["title"]);
				$icon = ilUtil::img(ilObject::_getIcon(ilObject::_lookupObjId(1), "tiny"));
			} catch (InvalidArgumentException $e) {
				return "";
			}

			return $icon . " " . $title . " - " . $dic->language()->txt("rep_main_page");
		};

		$action = function (): string {
			try {
				$static_link = ilLink::_getStaticLink(1, 'root', true);
			} catch (InvalidArgumentException $e) {
				return "";
			}

			return $static_link;
		};

		$entries[] = $this->mainmenu->link($this->if->identifier('rep_main_page'))
			->withTitle($title())
			->withAction($action())
			->withParent($this->getRepository());

		// LastVisited
		$links = function (): array {
			$items = [];
			if (isset($this->dic['ilNavigationHistory'])) {
				$items = $this->dic['ilNavigationHistory']->getItems();
			}
			$links = [];
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
					$obj_id = ilObject::_lookupObjId($item["ref_id"]);
					$icon = ilUtil::img(ilObject::_getIcon($obj_id, "tiny"));
					$ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
					$links[] = $this->mainmenu->link($this->if->identifier('last_visited_' . $obj_id))
						->withTitle($icon . " " . $ititle)
						->withAction($item["link"]);
				}
				$first = false;
			}

			return $links;
		};
		$entries[] = $this->mainmenu->linkList($this->if->identifier('last_visited'))
			->withLinks($links)
			->withTitle($this->dic->language()->txt('last_visited'))
			->withParent($this->getRepository())->withVisibilityCallable(
				function () use ($dic) {
					return ($dic->user()->getId() != ANONYMOUS_USER_ID);
				}
			);

		//
		// ADMINISTRATION
		//
		$entries[] = $this->globalScreen()
			->mainmenu()
			->complex($this->if->identifier('adm_content'))
			->withAsyncContentURL("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch")
			->withVisibilityCallable(
				function () use ($dic) {
					return (bool)($dic->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
				}
			)->withAvailableCallable(
				function () use ($dic) {
					return ($dic->user()->getId() == ANONYMOUS_USER_ID);
				}
			);

		return $entries;
	}


	/**
	 * @return IdentificationInterface
	 */
	public function getDesktop(): IdentificationInterface {
		return $this->slate_ids[self::INTERNAL_DESKTOP];
	}


	/**
	 * @return IdentificationInterface
	 */
	public function getRepository(): IdentificationInterface {
		return $this->slate_ids[self::INTERNAL_REPOSITORY];
	}


	/**
	 * @return IdentificationInterface
	 */
	public function getAdministration(): IdentificationInterface {
		return $this->slate_ids[self::INTERNAL_ADMINISTRATION];
	}
}
