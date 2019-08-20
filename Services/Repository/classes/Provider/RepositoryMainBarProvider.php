<?php namespace ILIAS\Repository\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilLink;
use ilObject;
use ilUtil;
use InvalidArgumentException;

/**
 * Class RepositoryMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RepositoryMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $top = StandardTopItemsProvider::getInstance()->getRepositoryIdentification();

        // Home
        $entries[] = $this->getHomeItem()
            ->withParent($top)
	        ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("root", "")->withIsOutlined(true))
	        ->withPosition(20);

        // Tree-View
        $entries[] = $this->mainmenu->link($this->if->identifier('tree_view'))
            ->withAction("#")
            ->withParent($top)
            ->withPosition(30)
	        ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("root", "")->withIsOutlined(true))
            ->withTitle($this->dic->language()->txt("mm_repo_tree_view"));

        // LastVisited
        $entries[] = $this->getLastVisitedItem()
            ->withPosition(40)
            ->withParent($top);

        return $entries;
    }


    private function getHomeItem() : Link
    {
        $dic = $this->dic;

        $title = function () use ($dic): string {
            try {
                $nd = $dic['tree']->getNodeData(ROOT_FOLDER_ID);
                $title = ($nd["title"] === "ILIAS" ? $dic->language()->txt("repository") : $nd["title"]);
                $icon = ilUtil::img(ilObject::_getIcon(ilObject::_lookupObjId(1), "tiny"));
            } catch (InvalidArgumentException $e) {
                return "";
            }

            return $title . " - " . $dic->language()->txt("rep_main_page");
        };

        $action = function () : string {
            try {
                $static_link = ilLink::_getStaticLink(1, 'root', true);
            } catch (InvalidArgumentException $e) {
                return "";
            }

            return $static_link;
        };

        return $this->mainmenu->link($this->if->identifier('rep_main_page'))
            ->withTitle($title())
            ->withAction($action());
    }


    private function getLastVisitedItem() : LinkList
    {
        $dic = $this->dic;
        // LastVisited
        $links = function () : array {
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
                    $ititle = ilUtil::shortenText(strip_tags($item["title"]), 50, true); // #11023
                    $links[] = $this->mainmenu->link($this->if->identifier('last_visited_' . $item["ref_id"]))
                        ->withTitle($ititle)
                        ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard($item['type'], $item['type']))
                        ->withAction($item["link"]);
                }
                $first = false;
            }

            return $links;
        };

        return $this->mainmenu->linkList($this->if->identifier('last_visited'))
            ->withLinks($links)
            ->withTitle($this->dic->language()->txt('last_visited'))
            ->withVisibilityCallable(
                function () use ($dic) {
                    return ($dic->user()->getId() != ANONYMOUS_USER_ID);
                }
            );
    }
}
