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
use ILIAS\Blog\BlogGUIContext;
use ilObjBlogGUI;
use ilBlogPostingGUI;
use ILIAS\Blog\Navigation\Link\LinkBuilder;

/**
 * @ilCtrl_Calls ILIAS\Blog\Presentation\PresentationGUI: ilBlogPostingGUI
 */
class PresentationGUI
{
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\Repository\Profile\ProfileGUI $profile_gui;
    protected ?\ILIAS\Blog\Settings\Settings $blog_settings;
    protected \ilCtrl $ctrl;
    protected \ilObjUser $user;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected BlogGUIContext $context,
        protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        protected ?\Closure $add_header_callback = null
    ) {
        global $DIC;
        $this->notes = $DIC->notes();

        $this->content_style_gui = $DIC->contentStyle()->gui();
        $this->user = $this->domain->user();
        $this->ctrl = $this->gui->ctrl();
        $this->blog_settings = $domain->blogSettings()->getByObjId($context->getBlog()->getId());
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
            $this->context->getPermission(),
            $a_items,
            $this->context->getRequest()->getBlogPage(),
            $single_posting,
            $this->context->getMonth(),
            $this->context->getRequest()->getUserPage()
        );
    }

    protected function forwardPosting(): void
    {
        $ilCtrl = $this->gui->ctrl();
        $req = $this->context->getRequest();

        $style_sheet_id = $this->content_style_domain->getEffectiveStyleId();

        $notes_status = $this->notes->domain()->commentsActive($this->context->getBlog()->getId());
        $bpost_gui = new ilBlogPostingGUI(
            $this->context->getNodeId(),
            $this->context->getPermission()->getAccessHandler(),
            $req->getBlogPage(),
            $req->getOldNr(),
            $notes_status,
            $this->context->getPermission()->mayEditPosting($req->getBlogPage()),
            $style_sheet_id
        );

        $ilCtrl->setParameter($this, "prvm", "fsc");

        $items = $this->domain->postingList($this->context->getBlog()->getId())->getPostingsGroupedByMonth();
        $this->renderToolbarNavigation($items, true);

        $ret = $ilCtrl->forwardCommand($bpost_gui);

        if ($ret != "") {
            $is_owner = $this->context->getPermission()->mayContribute();
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
                    $this->context->getPermission(),
                    $this->getLinkBuilder(),
                    $this->blog_settings,
                    $this->context->getNodeId(),
                    $this->context->getIdType()
                )->render(
                    $this->domain->postingList($this->context->getBlog()->getId(), false)->getPostingsGroupedByMonth()
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

        if (!$this->context->getPermission()->canRead()) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }
        $list_items = $this->domain->postingList($this->context->getBlog()->getId())
               ->getPostingsForView(
                   $this->context->getAuthor() ?? 0,
                   $this->keyword ?? "",
                   $this->context->getMonth()
               );


        $list = $nav = "";
        $items = $this->domain->postingList($this->context->getBlog()->getId(), false)->getPostingsGroupedByMonth();
        if ($list_items) {
            $list = $this->gui->posting()->postingList(
                $this->context->getBlog()->getId(),
                $this->context->getPermission(),
                $this->context->getMonth(),
                $this->context->getNodeId(),
                $this->context->getIdType(),
            )->render(
                $list_items,
                "previewFullscreen"
            );
            $nav = $this->gui->navigation()->sideBar(
                $this->context->getPermission(),
                $this->getLinkBuilder(),
                $this->blog_settings,
                $this->context->getNodeId(),
                $this->context->getIdType()
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
        switch ($this->context->getRequest()->getNotification()) {
            case 1:
                \ilNotification::setNotification(
                    \ilNotification::TYPE_BLOG,
                    $ilUser->getId(),
                    $this->context->getBlog()->getId(),
                    false
                );
                break;

            case 2:
                \ilNotification::setNotification(
                    \ilNotification::TYPE_BLOG,
                    $ilUser->getId(),
                    $this->context->getBlog()->getId(),
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
                $this->context->getNodeId(),
                $this->context->getBlog()->getId(),
                $ilUser->getId()
            );
        }

        // repository blogs are multi-author
        $name = "";
        if (!$this->context->isRepositoryNode()) {
            $name = \ilObjUser::_lookupName($a_user_id);
            $name = $name["lastname"] . ", " . $name["firstname"];
        }

        $ppic = "";
        if ($this->blog_settings?->getProfilePicture() && !$a_export) {
            // repository (multi-user)
            if ($this->context->isRepositoryNode()) {
                // #15030
                if ($this->context->getRequest()->getBlogPage() > 0 && !$a_export) {
                    $post = new \ilBlogPosting($this->context->getRequest()->getBlogPage());
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
        $title = \ilObject::_lookupTitle($this->context->getBlog()->getId());
        $desc = \ilObject::getLongDescriptions([$this->context->getBlog()->getId()]);
        $a_tpl->setTitle($title);
        if ($this->context->isRepositoryNode()) {
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
        $ilTabs->clearTargets();
        $tpl->setLocator();

        $this->renderFullscreenHeader($tpl, $this->context->getBlog()->getOwner());

        // #13564
        $this->ctrl->setParameter($this, "bmn", "");
        //$tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview"));
        $this->ctrl->setParameter($this, "bmn", $this->context->getMonth());

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
            $this->context->getNodeId(),
            $this->context->getBlog()->getId()
        );
    }


}
