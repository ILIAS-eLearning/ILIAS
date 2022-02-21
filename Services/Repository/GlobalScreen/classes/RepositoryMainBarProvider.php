<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Repository\Provider;

use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilLink;
use ilObject;
use ilUtil;
use InvalidArgumentException;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\DI\Container;
use ILIAS\Repository\StandardGUIRequest;
use ilStr;

/**
 * Repository related main menu items
 * - Repository Home
 * - Repository Tree
 * - Last Visited
 *
 * Note: The Favourites menut item is currently part of the Dashboard PDMainBarProvider
 * and should be moved here, since the Favourites services is implemented as a sub-service
 * of the repository service.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Alexander Killing <killing@leifos.de>
 */
class RepositoryMainBarProvider extends AbstractStaticMainMenuProvider
{
    protected StandardGUIRequest $request;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->request = $dic->repository()->internal()->gui()->standardRequest();
    }

    public function getStaticTopItems() : array
    {
        return [];
    }

    public function getStaticSubItems() : array
    {
        $top = StandardTopItemsProvider::getInstance()->getRepositoryIdentification();
        $access_helper = BasicAccessCheckClosures::getInstance();

        $title = $this->getHomeItem()->getTitle();
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::ROOT, $title)->withIsOutlined(true);

        // Home
        $entries[] = $this->getHomeItem()
            ->withVisibilityCallable($access_helper->isRepositoryVisible())
            ->withParent($top)
            ->withSymbol($icon)
            ->withPosition(10);

        // Tree-View
        $title = $this->dic->language()->txt("mm_rep_tree_view");

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_reptr.svg"), $title);

        \ilRepositoryExplorerGUI::init();
        $entries[]
            = $this->mainmenu->complex($this->if->identifier('rep_tree_view'))
            ->withVisibilityCallable($access_helper->isRepositoryVisible())
            ->withContentWrapper(function () {
                return $this->dic->ui()->factory()->legacy($this->renderRepoTree());
            })
            ->withSupportsAsynchronousLoading(true)
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

    protected function renderLastVisited() : string
    {
        $nav_items = [];
        if (isset($this->dic['ilNavigationHistory'])) {
            $nav_items = $this->dic['ilNavigationHistory']->getItems();
        }
        reset($nav_items);
        $cnt = 0;
        $first = true;
        $item_groups = [];
        $items = [];

        $f = $this->dic->ui()->factory();
        foreach ($nav_items as $k => $nav_item) {
            if ($cnt++ >= 10) {
                break;
            }

            if (!isset($nav_item["ref_id"]) || $this->request->getRefId() == 0
                || ($nav_item["ref_id"] != $this->request->getRefId() || !$first)
            ) {            // do not list current item
                $ititle = ilStr::shortenTextExtended(strip_tags($nav_item["title"]), 50, true); // #11023
                $obj_id = ilObject::_lookupObjectId($nav_item["ref_id"]);
                $items[] = $f->item()->standard(
                    $f->link()->standard($ititle, $nav_item["link"])
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($obj_id), $ititle));
            }
            $first = false;
        }

        if (count($items) > 0) {
            $item_groups[] = $f->item()->group("", $items);
            $panel = $f->panel()->secondary()->listing("", $item_groups);
            return $this->dic->ui()->renderer()->render([$panel]);
        }

        return $this->dic->ui()->renderer()->render($this->getNoLastVisitedMessage());
    }

    // No favourites message box
    public function getNoLastVisitedMessage() : MessageBox
    {
        global $DIC;

        $lng = $DIC->language();
        $ui = $DIC->ui();
        $lng->loadLanguageModule("rep");
        $txt = $lng->txt("rep_no_last_visited_mess");
        $mbox = $ui->factory()->messageBox()->info($txt);

        return $mbox;
    }

    protected function renderRepoTree() : string
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $ref_id = $this->request->getRefId();
        if ($this->request->getBaseClass() == "ilAdministrationGUI" || $ref_id <= 0 || !$tree->isInTree($ref_id)) {
            $ref_id = $tree->readRootId();
        }

        $DIC->ctrl()->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        $exp = new \ilRepositoryExplorerGUI("ilrepositorygui", "showRepTree");
        $exp->setSkipRootNode(true);
        return $exp->getHTML() . "<script>" . $exp->getOnLoadCode() . "</script>";
    }
}
