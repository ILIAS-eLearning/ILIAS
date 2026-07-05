<?php

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

declare(strict_types=1);

namespace ILIAS\Blog\Presentation;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ilObjBlogGUI;
use ilBlogPostingGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Navigation\Link\LinkBuilder;

/**
 * @ilCtrl_Calls ILIAS\Blog\Presentation\PresentationGUI: ilBlogPostingGUI
 */
class PresentationGUI
{
    protected int $ntf;
    protected int $user_page;
    protected int $blpg = 0;
    protected \ILIAS\Blog\Navigation\ToolbarNavigationRenderer $nav_renderer;
    protected ?\ilObjBlog $blog;
    protected ?\ILIAS\Blog\Settings\Settings $blog_settings;
    protected int $obj_id;
    protected \ilCtrl $ctrl;
    protected \ilObjUser $user;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected ilObjBlogGUI $parent_gui,
        protected PermissionManager $perm,
        protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        protected string $current_month,
        protected ?int $node_id,
        protected int $id_type,
    ) {
        $this->user = $this->domain->user();
        $this->ctrl = $this->gui->ctrl();
        $this->blog = $this->parent_gui->getObject();
        $this->obj_id = $this->blog->getId();
        $req = $this->gui->standardRequest();
        $this->blpg = $req->getBlogPage();
        $this->user_page = $req->getUserPage();
        $this->ntf = $req->getNotification();
        $this->blog_settings = $domain->blogSettings()->getByObjId($this->obj_id);
        $this->nav_renderer = $this->gui->navigation()->toolbarNavigationRenderer(
            $this->getLinkBuilder(),
        );
    }

    protected function getLinkBuilder(): LinkBuilder
    {
        return $this->gui->navigation()->presentationLink();
    }

    public function executeCommand(): void
    {
        $next_class = $this->gui->ctrl()->getNextClass($this);
        $cmd = $this->gui->ctrl()->getCmd("preview");

        switch ($next_class) {
            case strtolower(ilBlogPostingGUI::class):
                $this->forwardPosting();
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Toolbar navigation
     */
    public function renderToolbarNavigation(
        array $a_items,
        bool $single_posting = false
    ): void {
        $nav_renderer = $this->gui->navigation()->toolbarNavigationRenderer(
            $this->getLinkBuilder(),
        );
        $nav_renderer->renderToolbarNavigation(
            $this->perm,
            $a_items,
            $this->blpg,
            $single_posting,
            $this->current_month,
            $this->user_page
        );
    }

    protected function forwardPosting(): void
    {
        $ilCtrl = $this->gui->ctrl();
        $req = $this->gui->standardRequest();

        if ($this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID) {
            //$this->parent_gui->setLocator();
        }

        $style_sheet_id = $this->content_style_domain->getEffectiveStyleId();

        $bpost_gui = new ilBlogPostingGUI(
            $this->node_id,
            $this->perm->getAccessHandler(),
            $req->getBlogPage(),
            $req->getOldNr(),
            $this->blog->getNotesStatus(),
            $this->perm->mayEditPosting($req->getBlogPage()),
            $style_sheet_id
        );

        $ilCtrl->setParameter($this, "prvm", "fsc");

        $this->renderToolbarNavigation($this->parent_gui->getItems(), true);

        $ret = $ilCtrl->forwardCommand($bpost_gui);

        if ($ret != "") {
            $is_owner = $this->perm->mayContribute();
            $is_active = $bpost_gui->getBlogPosting()->getActive();

            // do not show inactive postings
            $cmd = $ilCtrl->getCmd();
            if (($cmd === "previewFullscreen")
                && !$is_owner && !$is_active) {
                $ilCtrl->redirect($this->parent_gui, "preview");
            }

            if ($cmd === "previewFullscreen") {
                $this->parent_gui->addPresentationHeaderAction();
                $this->parent_gui->filterInactivePostings();
                $nav = $this->gui->navigation()->sideBar(
                    $this->perm,
                    $this->getLinkBuilder(),
                    $this->blog_settings,
                    $this->node_id,
                    $this->id_type
                )->render(
                    $this->parent_gui,
                    $this->parent_gui->getItems(),
                );
                $this->parent_gui->renderFullScreen($ret, $nav);
            }
        }
    }

    /**
     * Render fullscreen presentation
     */
    public function preview(): void
    {
        $lng = $this->domain->lng();
        $tpl = $this->gui->ui()->mainTemplate();

        if (!$this->parent_gui->checkPermissionBool("read")) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }

        $this->parent_gui->filterInactivePostings();

        $list_items = $this->parent_gui->getListItems();

        $list = $nav = "";
        if ($list_items) {
            $list = $this->parent_gui->renderList($list_items, "previewFullscreen");
            $nav = $this->gui->navigation()->sideBar(
                $this->perm,
                $this->getLinkBuilder(),
                $this->blog_settings,
                $this->node_id,
                $this->id_type
            )->render(
                $this->parent_gui,
                $this->parent_gui->getItems(),
            );
            $this->renderToolbarNavigation($this->parent_gui->getItems());
        }

        $this->parent_gui->renderFullScreen($list, $nav);
    }

    protected function setNotification(): void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        switch ($this->ntf) {
            case 1:
                \ilNotification::setNotification(
                    \ilNotification::TYPE_BLOG,
                    $ilUser->getId(),
                    $this->obj_id,
                    false
                );
                break;

            case 2:
                \ilNotification::setNotification(
                    \ilNotification::TYPE_BLOG,
                    $ilUser->getId(),
                    $this->obj_id,
                    true
                );
                break;
        }

        $ilCtrl->redirect($this, "");
    }

}
