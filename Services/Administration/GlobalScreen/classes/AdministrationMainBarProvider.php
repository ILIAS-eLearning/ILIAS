<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Administration;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Class AdministrationMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class AdministrationMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems() : array
    {
        return [];
    }


    public function getStaticSubItems() : array
    {
        $access_helper = BasicAccessCheckClosuresSingleton::getInstance();
        $top = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();

        if (!$access_helper->isUserLoggedIn()() || !$access_helper->hasAdministrationAccess()()) {
            return [];
        }

        $entries = [];
        $this->dic->language()->loadLanguageModule('administration');

        $admin_request = new AdminGUIRequest(
            $this->dic->http(),
            $this->dic->refinery()
        );

        [$groups, $titems] = $this->getGroups();
        $position = 1;
        foreach ($groups as $group => $group_items) {
            // Is Group
            if (is_array($group_items) && count($group_items) > 0) {
                // Entries
                $links = [];
                foreach ($group_items as $group_item) {
                    if ($group_item === "---") {
                        continue;
                    }

                    $icon = $this->dic->ui()->factory()->symbol()->icon()->standard($titems[$group_item]["type"], $titems[$group_item]["title"]);
                    
                    $ref_id = $titems[$group_item]["ref_id"];
                    if ($admin_request->getAdminMode() !== 'repository' && $ref_id == ROOT_FOLDER_ID) {
                        $identification = $this->if->identifier('mm_adm_rep');
                        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $ref_id . "&admin_mode=repository";
                    } else {
                        $identification = $this->if->identifier("mm_adm_" . $titems[$group_item]["type"]);
                        $action = "ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $ref_id . "&cmd=jump";
                    }

                    $links[] = $this->globalScreen()
                        ->mainBar()
                        ->link($identification)
                        ->withTitle($titems[$group_item]["title"])
                        ->withAction($action)
                        ->withSymbol($icon)
                        ->withVisibilityCallable(function () use ($ref_id) {
                            return $this->dic->rbac()->system()->checkAccess('visible,read', (int) $ref_id);
                        });
                }

                // Main entry
                $title = $this->dic->language()->txt("adm_" . $group);
                $entries[] = $this->globalScreen()
                    ->mainBar()
                    ->linkList($this->if->identifier('adm_content_' . $group))
                    ->withSupportsAsynchronousLoading(true)
                    ->withLinks($links)
                    ->withTitle($title)
                    ->withSymbol($this->getIconForGroup($group, $title))
                    ->withParent($top)
                    ->withPosition($position * 10)
                    ->withAlwaysAvailable(true)
                    ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
                    ->withVisibilityCallable(
                        $access_helper->hasAdministrationAccess()
                    )->withAvailableCallable(
                        $access_helper->isUserLoggedIn()
                    );
                $position++;
            }
        }

        return $entries;
    }

    protected function getIconForGroup(string $group, string $title) : Icon
    {
        $icon_map = array(
            "maintenance" => "icon_sysa",
            "layout_and_navigation" => "icon_laya",
            "repository_and_objects" => "icon_repa",
            "personal_workspace" => "icon_pwsa",
            "achievements" => "icon_achva",
            "communication" => "icon_coma",
            "user_administration" => "icon_usra",
            "search_and_find" => "icon_safa",
            "extending_ilias" => "icon_exta"
        );
        $icon_path = \ilUtil::getImagePath( $icon_map[$group] . ".svg");
        return $this->dic->ui()->factory()->symbol()->icon()->custom($icon_path, $title);
    }

    private function getGroups() : array
    {
        if (!$this->dic->offsetExists('tree')) { // isDependencyAvailable does not work, Fatal error: Uncaught Error: Call to undefined method ILIAS\DI\Container::tree() in /var/www/html/src/DI/Container.php on line 294
            return [[], []];
        }
        $tree = $this->dic->repositoryTree();
        $lng = $this->dic->language();

        $admin_request = new AdminGUIRequest(
            $this->dic->http(),
            $this->dic->refinery()
        );


        $objects = $tree->getChilds(SYSTEM_FOLDER_ID);

        foreach ($objects as $object) {
            $new_objects[$object["title"] . ":" . $object["child"]]
                = $object;
            // have to set it manually as translation type of main node cannot be "sys" as this type is a orgu itself.
            if ($object["type"] === "orgu") {
                $new_objects[$object["title"] . ":" . $object["child"]]["title"] = $lng->txt("objs_orgu");
            }
        }

        // add entry for switching to repository admin
        // note: please see showChilds methods which prevents infinite look
        $new_objects[$lng->txt("repository_admin") . ":" . ROOT_FOLDER_ID]
            = array(
            "tree" => 1,
            "child" => ROOT_FOLDER_ID,
            "ref_id" => ROOT_FOLDER_ID,
            "depth" => 3,
            "type" => "root",
            "title" => $lng->txt("repository_admin"),
            "description" => $lng->txt("repository_admin_desc"),
            "desc" => $lng->txt("repository_admin_desc"),
        );

        $new_objects[$lng->txt("general_settings") . ":" . SYSTEM_FOLDER_ID]
            = array(
            "tree" => 1,
            "child" => SYSTEM_FOLDER_ID,
            "ref_id" => SYSTEM_FOLDER_ID,
            "depth" => 2,
            "type" => "adm",
            "title" => $lng->txt("general_settings"),
        );
        ksort($new_objects);

        // determine items to show
        $items = array();
        foreach ($new_objects as $c) {
            // check visibility
            if ($c["type"] !== "adm" && $tree->getParentId((int) $c["ref_id"]) === ROOT_FOLDER_ID
                && $admin_request->getAdminMode() !== "repository"
            ) {
                continue;
            }
            // these objects may exist due to test cases that didnt clear
            // data properly
            if ($c["type"] == "" || $c["type"] === "objf"
                || $c["type"] === "xxx"
            ) {
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
            "maintenance" =>
                array("adm", "lngf", "hlps", "wfe", "pdfg", 'fils', 'logs', 'sysc', "recf", "root"),
            "layout_and_navigation" =>
                array("mme", "stys", "adve", "accs"),
            "repository_and_objects" =>
                array("reps", "crss", "grps", "prgs", "bibs", "blga", "cpad", "chta", "facs", "frma", "lrss",
                      "mcts", "mobs", "svyf", "assf", "wbrs", 'lsos'),
            "personal_workspace" =>
                array("tags", "cals", "prfa", "prss", "nots"),
            "achievements" =>
                array("lhts", "skmg", "trac", "bdga", "cert"),
            "communication" =>
                array("mail", "cadm", "nwss", "coms", "adn", "awra", "nota"),
            "user_administration" =>
                array("usrf", 'tos', "rolf", "otpl", "auth", "ps"),
            "search_and_find" =>
                array("seas", "mds", "taxs"),
            "extending_ilias" =>
                array('ecss', "ltis", "wbdv", "cmis", "cmps", "extt")
        );
        $groups = [];
        // now get all items and groups that are accessible
        foreach ($layout as $group => $entries) {
            $groups[$group] = array();
            $entries_since_last_sep = false;
            foreach ($entries as $e) {
                if ($e === "---" || (isset($titems[$e]["type"]) && $titems[$e]["type"] != "")) {
                    if ($e === "---" && $entries_since_last_sep) {
                        $groups[$group][] = $e;
                        $entries_since_last_sep = false;
                    } else {
                        if ($e !== "---") {
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
