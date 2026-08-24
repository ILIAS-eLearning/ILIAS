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
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\Repository\Profile\ProfileGUI $profile_gui;
    protected int $ntf;
    protected int $user_page;
    protected int $blpg = 0;
    protected \ILIAS\Blog\Navigation\ToolbarNavigationRenderer $nav_renderer;
    protected ?\ILIAS\Blog\Settings\Settings $blog_settings;
    protected int $obj_id;
    protected \ilCtrl $ctrl;
    protected \ilObjUser $user;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected PermissionManager $perm,
        protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        protected string $current_month,
        protected int $node_id,
        protected int $id_type,
        protected int $owner_id,
        protected ?\Closure $add_header_callback = null
    ) {
        global $DIC;
        $this->notes = $DIC->notes();

        $this->content_style_gui = $DIC->contentStyle()->gui();
        $this->user = $this->domain->user();
        $this->ctrl = $this->gui->ctrl();
        if ($id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID) {
            $this->obj_id = \ilObject::_lookupObjectId($node_id);
        } else {
            $this->obj_id = $this->domain->getObjectIdForWspId($node_id);
        }
        $req = $this->gui->standardRequest();
        $this->blpg = $req->getBlogPage();
        $this->user_page = $req->getUserPage();
        $this->ntf = $req->getNotification();
        $this->blog_settings = $domain->blogSettings()->getByObjId($this->obj_id);
        $this->nav_renderer = $this->gui->navigation()->toolbarNavigationRenderer(
            $this->getLinkBuilder(),
        );
        $this->profile_gui = $gui->profile();
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

        $style_sheet_id = $this->content_style_domain->getEffectiveStyleId();

        $notes_status = $this->notes->domain()->commentsActive($this->obj_id);
        $bpost_gui = new ilBlogPostingGUI(
            $this->node_id,
            $this->perm->getAccessHandler(),
            $req->getBlogPage(),
            $req->getOldNr(),
            $notes_status,
            $this->perm->mayEditPosting($req->getBlogPage()),
            $style_sheet_id
        );

        $ilCtrl->setParameter($this, "prvm", "fsc");

        $items = $this->domain->postingList($this->obj_id)->getPostingsGroupedByMonth();
        $this->renderToolbarNavigation($items, true);

        $ret = $ilCtrl->forwardCommand($bpost_gui);

        if ($ret != "") {
            $is_owner = $this->perm->mayContribute();
            $is_active = $bpost_gui->getBlogPosting()->getActive();

            // do not show inactive postings
            $cmd = $ilCtrl->getCmd();
            if (($cmd === "previewFullscreen")
                && !$is_owner && !$is_active) {
                $ilCtrl->redirectByClass(\ilObjBlogGUI::class, "preview");
            }

            if ($cmd === "previewFullscreen") {
                if ($this->add_header_callback) {
                    ($this->add_header_callback)();
                }
                $nav = $this->gui->navigation()->sideBar(
                    $this->perm,
                    $this->getLinkBuilder(),
                    $this->blog_settings,
                    $this->node_id,
                    $this->id_type
                )->render(
                    $this->domain->postingList($this->obj_id, false)->getPostingsGroupedByMonth()
                );
                $this->renderFullScreen($ret, $nav);
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

        if (!$this->perm->canRead()) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }
        $list_items = $this->domain->postingList($this->obj_id)
               ->getPostingsForView(
                   $this->author ?? 0,
                   $this->keyword ?? "",
                   $this->current_month ?? ""
               );


        $list = $nav = "";
        $items = $this->domain->postingList($this->obj_id, false)->getPostingsGroupedByMonth();
        if ($list_items) {
            $list = $this->gui->posting()->postingList(
                $this->obj_id,
                $this->perm,
                $this->current_month,
                $this->node_id,
                $this->id_type,
            )->render(
                $list_items,
                "previewFullscreen"
            );
            $nav = $this->gui->navigation()->sideBar(
                $this->perm,
                $this->getLinkBuilder(),
                $this->blog_settings,
                $this->node_id,
                $this->id_type
            )->render(
                $items,
            );
            $this->renderToolbarNavigation($items);
        }

        $this->renderFullScreen($list, $nav);
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

    public function renderFullscreenHeader(
        \ilGlobalTemplateInterface $a_tpl,
        int $a_user_id,
        bool $a_export = false
    ): void {
        $ilUser = $this->user;

        if (!$a_export) {
            \ilChangeEvent::_recordReadEvent(
                "blog",
                $this->node_id,
                $this->obj_id,
                $ilUser->getId()
            );
        }

        // repository blogs are multi-author
        $name = "";
        if ($this->id_type !== \ilObjBlogGUI::REPOSITORY_NODE_ID) {
            $name = \ilObjUser::_lookupName($a_user_id);
            $name = $name["lastname"] . ", " . $name["firstname"];
        }

        $ppic = "";
        if ($this->blog_settings?->getProfilePicture() && !$a_export) {
            // repository (multi-user)
            if ($this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID) {
                // #15030
                if ($this->blpg > 0 && !$a_export) {
                    $post = new \ilBlogPosting($this->blpg);
                    $author_id = $post->getAuthor();
                    if ($author_id) {
                        $ppic = $this->profile_gui->getPicturePath($author_id);
                        $name = $this->profile_gui->getNamePresentation($author_id);
                        //$name = $name["lastname"] . ", " . $name["firstname"];
                    }
                }
            }
            // workspace (author == owner)
            else {
                $ppic = \ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true);
                if ($a_export) {
                    $ppic = basename($ppic);
                }
            }
        } else {
            $ppic = \ilUtil::getImagePath("standard/icon_blog.svg");
        }
        $a_tpl->resetHeaderBlock(false);
        $a_tpl->setTitleIcon($ppic);
        $title = \ilObject::_lookupTitle($this->obj_id);
        $desc = \ilObject::getLongDescriptions([$this->obj_id]);
        $a_tpl->setTitle($title);
        if ($this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID) {
            $a_tpl->setDescription(current($desc));
        } else {
            $a_tpl->setDescription($name);
        }
    }

    /**
     * Build fullscreen context
     */
    protected function renderFullScreen(
        string $a_content,
        string $a_navigation
    ): void {
        $tpl = $this->gui->ui()->mainTemplate();
        $ilTabs = $this->gui->tabs();
        $owner = $this->owner_id;

        $ilTabs->clearTargets();
        $tpl->setLocator();

        $this->renderFullscreenHeader($tpl, $owner);

        // #13564
        $this->ctrl->setParameter($this, "bmn", "");
        //$tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview"));
        $this->ctrl->setParameter($this, "bmn", $this->current_month);

        $this->setContentStyleSheet();

        // content
        $tpl->setContent($a_content);
        $tpl->setRightContent($a_navigation);
    }

    public function setContentStyleSheet(
        ?\ilGlobalTemplateInterface $a_tpl = null
    ): void {
        $tpl = $this->gui->ui()->mainTemplate();

        if ($a_tpl) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $tpl;
        }

        $this->content_style_gui->addCss(
            $ctpl,
            $this->node_id,
            $this->obj_id
        );
    }


}
