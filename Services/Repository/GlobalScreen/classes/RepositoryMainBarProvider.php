<?php namespace ILIAS\Repository\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
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
        $access_helper = BasicAccessCheckClosures::getInstance();

        $title = $this->getHomeItem()->getTitle();
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::ROOT, $title)->withIsOutlined(true);

        // Home
        $entries[] = $this->getHomeItem()
            ->withVisibilityCallable($access_helper->isRepositoryReadable())
            ->withParent($top)
            ->withSymbol($icon)
            ->withPosition(10);

        // Tree-View
        $mode = ($_SESSION["il_rep_mode"] == "flat")
            ? "tree"
            : "flat";
        $link = "ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&set_mode=" . $mode . "&ref_id=" . $_GET["ref_id"];
        $title = ($mode == "flat")
            ? $this->dic->language()->txt("mm_repo_tree_view_act")
            : $this->dic->language()->txt("mm_repo_tree_view_deact");

//        $title = $this->dic->language()->txt("mm_rep_tree_view");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_reptr.svg"), $title);

        /*
        if ($_GET["baseClass"] == "ilRepositoryGUI") {
            $entries[] = $this->mainmenu->link($this->if->identifier('tree_view'))
                ->withAction($link)
                ->withParent($top)
                ->withPosition(30)
                ->withSymbol($icon)
                ->withTitle($title);
        }*/

        $entries[]
            = $this->mainmenu->complex($this->if->identifier('rep_tree_view'))
            ->withVisibilityCallable($access_helper->isRepositoryReadable())
            ->withContentWrapper(function () {
                return $this->dic->ui()->factory()->legacy($this->renderRepoTree());
            })
            ->withSupportsAsynchronousLoading(false)
            ->withTitle($title)
            ->withSymbol($icon)
            ->withParent($top)
            ->withPosition(20);

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_lstv.svg"), $title);

        $p = $this;
        $entries[] = $this->mainmenu
            ->complex($this->if->identifier('last_visited'))
            ->withTitle($this->dic->language()->txt('last_visited'))
            ->withSupportsAsynchronousLoading(true)
            ->withVisibilityCallable($access_helper->isUserLoggedIn($access_helper->isRepositoryReadable()))
            ->withPosition(30)
            ->withSymbol($icon)
            ->withParent($top)
            ->withContentWrapper(function () use ($p) {
                return $this->dic->ui()->factory()->legacy($p->renderLastVisited());
            });


        return $entries;
    }


    private function getHomeItem() : Link
    {
        $dic = $this->dic;

        $title = function () use ($dic) : string {
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



    /**
     * Render last visited
     *
     * @return string
     */
    protected function renderLastVisited()
    {
        $nav_items = [];
        if (isset($this->dic['ilNavigationHistory'])) {
            $nav_items = $this->dic['ilNavigationHistory']->getItems();
        }
        reset($nav_items);
        $cnt = 0;
        $first = true;
        $item_groups = [];

        $f = $this->dic->ui()->factory();
        foreach ($nav_items as $k => $nav_item) {
            if ($cnt >= 10) {
                break;
            }

            if (!isset($nav_item["ref_id"]) || !isset($_GET["ref_id"])
                || ($nav_item["ref_id"] != $_GET["ref_id"] || !$first)
            ) {            // do not list current item
                $ititle = ilUtil::shortenText(strip_tags($nav_item["title"]), 50, true); // #11023
                $obj_id = ilObject::_lookupObjectId($nav_item["ref_id"]);
                $items[] = $f->item()->standard(
                    $f->button()->shy($ititle, $nav_item["link"])
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($obj_id), $ititle));
            }
            $first = false;
        }

        $item_groups[] = $f->item()->group("", $items);
        $panel = $f->panel()->secondary()->listing("", $item_groups);

        return $this->dic->ui()->renderer()->render([$panel]);
    }



    /**
     * Render repository tree
     *
     * @return string
     */
    protected function renderRepoTree()
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ref_id = (int) $_GET["ref_id"];
        if ($_GET["baseClass"] == "ilAdministrationGUI" || $ref_id <= 0 || !$tree->isInTree($ref_id)) {
            $ref_id = $tree->readRootId();
        }

        $DIC->ctrl()->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        $exp = new \ilRepositoryExplorerGUI("ilrepositorygui", "showRepTree");
        $exp->setSkipRootNode(true);

        return $exp->getHTML();
    }
}
