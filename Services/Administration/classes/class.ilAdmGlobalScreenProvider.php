<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilAdmGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAdmGlobalScreenProvider extends AbstractStaticMainMenuProvider {

	/**
	 * @var IdentificationInterface
	 */
	protected $top_item;


	public function __construct(\ILIAS\DI\Container $dic) {
		parent::__construct($dic);
		$this->top_item = $this->if->identifier('adm');
	}


	/**
	 * Some other components want to provide Items for the main menu which are
	 * located at the PD TopTitem by default. Therefore we have to provide our
	 * TopTitem Identification for others
	 *
	 * @return IdentificationInterface
	 */
	public function getTopItem(): IdentificationInterface {
		return $this->top_item;
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticTopItems(): array {
		$dic = $this->dic;

		return [$this->mainmenu->topParentItem($this->getTopItem())
			        ->withTitle($this->dic->language()->txt("administration"))
			        ->withPosition(3)
			        ->withVisibilityCallable(
				        function () use ($dic) { return (bool)($dic->access()->checkAccess('visible', '', SYSTEM_FOLDER_ID)); }
			        )];
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticSubItems(): array {
		$dic = $this->dic;
		$entries = [];
		$this->dic->language()->loadLanguageModule('administration');
		
		list($groups, $titems) = $this->getGroups();

		foreach ($groups as $group => $group_items) {
			if (is_array($group_items) && count($group_items) > 0) {
				// Entries
				$links = [];
				foreach ($group_items as $group_item) {
					if ($group_item == "---") {
						continue;
					}

					$path = ilObject::_getIcon("", "tiny", $titems[$group_item]["type"]);
					$icon = $this->dic->ui()->factory()->icon()->custom($path, $titems[$group_item]["type"]);

					if ($_GET["admin_mode"] == "settings" && $titems[$group_item]["ref_id"] == ROOT_FOLDER_ID) {
						$identification = $this->if->identifier('mm_adm_rep');
						$action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $titems[$group_item]["ref_id"] . "&admin_mode=repository";
					} else {
						$identification = $this->if->identifier("mm_adm_" . $titems[$group_item]["type"]);
						$action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $titems[$group_item]["ref_id"] . "&cmd=jump";
					}

					$links[] = $this->globalScreen()
						->mainBar()
						->link($identification)
						->withTitle($titems[$group_item]["title"])
						->withAction($action)
						->withIcon($icon);
				}

				// Main entry
				$entries[] = $this->globalScreen()
					->mainBar()
					->linkList($this->if->identifier('adm_content_' . $group))
					->withLinks($links)
					->withTitle($this->dic->language()->txt("adm_" . $group))
					// ->withAsyncContentURL("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch")
					->withParent($this->getTopItem())
					->withAlwaysAvailable(true)
					->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
					->withVisibilityCallable(
						function () use ($dic) {
							return (bool)($dic->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
						}
					)->withAvailableCallable(
						function () use ($dic) {
							return ($dic->user()->getId() != ANONYMOUS_USER_ID);
						}
					);
			}
		}

		return $entries;
	}


	/**
	 * @return array
	 */
	private function getGroups(): array {
		if (!$this->dic->offsetExists('tree')) { // isDependencyAvailable does not work, Fatal error: Uncaught Error: Call to undefined method ILIAS\DI\Container::tree() in /var/www/html/src/DI/Container.php on line 294
			return [[], []];
		}
		$tree = $this->dic->repositoryTree();
		$rbacsystem = $this->dic->rbac()->system();
		$lng = $this->dic->language();

		$objects = $tree->getChilds(SYSTEM_FOLDER_ID);

		foreach ($objects as $object) {
			$new_objects[$object["title"] . ":" . $object["child"]]
				= $object;
			// have to set it manually as translation type of main node cannot be "sys" as this type is a orgu itself.
			if ($object["type"] == "orgu") {
				$new_objects[$object["title"] . ":" . $object["child"]]["title"] = $lng->txt("objs_orgu");
			}
		}

		// add entry for switching to repository admin
		// note: please see showChilds methods which prevents infinite look
		$new_objects[$lng->txt("repository_admin") . ":" . ROOT_FOLDER_ID]
			= array(
			"tree"        => 1,
			"child"       => ROOT_FOLDER_ID,
			"ref_id"      => ROOT_FOLDER_ID,
			"depth"       => 3,
			"type"        => "root",
			"title"       => $lng->txt("repository_admin"),
			"description" => $lng->txt("repository_admin_desc"),
			"desc"        => $lng->txt("repository_admin_desc"),
		);

		$new_objects[$lng->txt("general_settings") . ":" . SYSTEM_FOLDER_ID]
			= array(
			"tree"   => 1,
			"child"  => SYSTEM_FOLDER_ID,
			"ref_id" => SYSTEM_FOLDER_ID,
			"depth"  => 2,
			"type"   => "adm",
			"title"  => $lng->txt("general_settings"),
		);
		ksort($new_objects);

		// determine items to show
		$items = array();
		foreach ($new_objects as $c) {
			// check visibility
			if ($tree->getParentId($c["ref_id"]) == ROOT_FOLDER_ID && $c["type"] != "adm"
				&& $_GET["admin_mode"] != "repository"
			) {
				continue;
			}
			// these objects may exist due to test cases that didnt clear
			// data properly
			if ($c["type"] == "" || $c["type"] == "objf"
				|| $c["type"] == "xxx"
			) {
				continue;
			}
			$accessible = $rbacsystem->checkAccess('visible,read', $c["ref_id"]);
			if (!$accessible) {
				continue;
			}
			if ($c["ref_id"] == ROOT_FOLDER_ID
				&& !$rbacsystem->checkAccess('write', $c["ref_id"])
			) {
				continue;
			}
			if ($c["type"] == "rolf" && $c["ref_id"] != ROLE_FOLDER_ID) {
				continue;
			}
			$items[] = $c;
		}

		$titems = array();
		foreach ($items as $i) {
			$titems[$i["type"]] = $i;
		}

		// admin menu layout
		$layout = array(

			"basic"               =>
				array("adm", "mme", "stys", "adve", "lngf", "hlps", "accs", "cmps", "extt", "wfe"),
			"user_administration" =>
				array("usrf", 'tos', "rolf", "orgu", "auth", "ps"),
			"learning_outcomes"   =>
				array("skmg", "bdga", "cert", "trac"),
			"user_services"       =>
				array("pdts", "prfa", "nwss", "awra", "cadm", "cals", "mail"),
			"content_services"    =>
				array("seas", "mds", "tags", "taxs", 'ecss', "ltis", "otpl", "pdfg"),
			"maintenance"         =>
				array('logs', 'sysc', "recf", "root"),
			"container"           =>
				array("reps", "crss", "grps", "prgs"),
			"content_objects"     =>
				array("bibs", "blga", "chta", "excs", "facs", "frma",
				      "lrss", "mcts", "mobs", "svyf", "assf", "wbrs", "wiks"),
		);
		$groups = [];
		// now get all items and groups that are accessible
		foreach ($layout as $group => $entries) {
			$groups[$group] = array();
			$entries_since_last_sep = false;
			foreach ($entries as $e) {
				if ($e == "---" || $titems[$e]["type"] != "") {
					if ($e == "---" && $entries_since_last_sep) {
						$groups[$group][] = $e;
						$entries_since_last_sep = false;
					} else {
						if ($e != "---") {
							$groups[$group][] = $e;
							$entries_since_last_sep = true;
						}
					}
				}
			}
		}

		return [$groups, $titems];
	}
}
