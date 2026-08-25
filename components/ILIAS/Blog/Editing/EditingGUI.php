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

namespace ILIAS\Blog\Editing;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\BlogGUIContext;
use ilObjBlogGUI;
use ilBlogPostingGUI;
use ilToolbarGUI;
use ilTextInputGUI;
use ILIAS\Blog\Navigation\Link\EditingLinkBuilder;

/**
 * @ilCtrl_Calls ILIAS\Blog\Editing\EditingGUI: ilRepositorySearchGUI, ilBlogPostingGUI
 */
class EditingGUI
{
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected ?\ILIAS\Blog\Settings\Settings $blog_settings;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected BlogGUIContext $context,
        protected \ILIAS\Style\Content\Service $content_style
    ) {
        $this->blog_settings = $this->domain->blogSettings()->getByObjId($context->getBlog()->getId());
        if (!$context->isRepositoryNode()) {
            $this->content_style_domain = $content_style->domain()->styleForObjId($context->getBlog()->getId());
        } else {
            $this->content_style_domain = $content_style->domain()->styleForRefId($context->getBlog()->getRefId());
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->gui->ctrl()->getNextClass($this);
        $cmd = $this->gui->ctrl()->getCmd("render");

        switch ($next_class) {
            case strtolower(ilBlogPostingGUI::class):
                $this->forwardPosting();
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    protected function getLinkBuilder(): EditingLinkBuilder
    {
        return $this->gui->navigation()->editingLink();
    }

    protected function forwardPosting(): void
    {
        $ilCtrl = $this->gui->ctrl();
        $tpl = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $req = $this->context->getRequest();

        $ilCtrl->saveParameter($this, "user_page");
        $tpl->loadStandardTemplate();

        if (!$this->context->getPermission()->mayContribute()) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }

        $style_sheet_id = $this->content_style_domain->getEffectiveStyleId();

        $bpost_gui = new ilBlogPostingGUI(
            $this->context->getNodeId(),
            $this->context->getPermission()->getAccessHandler(),
            $req->getBlogPage(),
            $req->getOldNr(),
            $this->context->getBlog()->getNotesStatus(),
            $this->context->getPermission()->mayEditPosting($req->getBlogPage()),
            $style_sheet_id
        );

        $this->setContentStyleSheet();

        $ilCtrl->setParameterByClass(ilBlogPostingGUI::class, "blpg", $req->getBlogPage());
        $this->gui->tabs()->addNonTabbedLink(
            "preview",
            $lng->txt("blog_preview"),
            $ilCtrl->getLinkTargetByClass(ilBlogPostingGUI::class, "previewFullscreen")
        );
        $ilCtrl->setParameterByClass(ilBlogPostingGUI::class, "blpg", "");

        $ret = $ilCtrl->forwardCommand($bpost_gui);

        if ($ret != "") {
            $is_owner = $this->context->getPermission()->mayContribute();
            $is_active = $bpost_gui->getBlogPosting()->getActive();

            // do not show inactive postings
            $cmd = $ilCtrl->getCmd();
            if (($cmd === "previewFullscreen")
                && !$is_owner && !$is_active) {
                $ilCtrl->redirectByClass(ilObjBlogGUI::class, "preview");
            }

            // infos about draft status / snippet
            $info = array();
            if (!$is_active) {
                $info[] = $lng->txt("blog_draft_info_contributors");
            }
            $public_action = false;
            if ($cmd !== "history" && $cmd !== "edit" && $is_active && empty($info)) {
                $info[] = $lng->txt("blog_new_posting_info");
                $public_action = true;
            }
            if ($this->context->getBlog()->getNotesStatus() &&
                $this->blog_settings->getApproval() &&
                !$bpost_gui->getBlogPosting()->isApproved()) {
                // #9737
                $info[] = $lng->txt("blog_posting_edit_approval_info");
            }
            if ($public_action) {
                $tpl->setOnScreenMessage('success', implode("<br />", $info));
            } else {
                if (count($info) > 0) {
                    $tpl->setOnScreenMessage('info', implode("<br />", $info));
                }
            }

            // revert to edit cmd to avoid confusion
            $tpl->setContent($ret);
        }

        if (!$this->gui->tabs()->back_target) {
            $ilCtrl->setParameter($this, "bmn", "");
            $this->gui->tabs()->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "")
            );
        }
    }

    public function render(): void
    {
        $tpl = $this->gui->ui()->mainTemplate();
        $ilTabs = $this->gui->tabs();
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $ilToolbar = new ilToolbarGUI();

        if (!$this->context->getPermission()->canRead()) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }

        $ilTabs->activateTab("content");

        // toolbar
        if ($this->context->getPermission()->mayContribute()) {
            $ilToolbar->setFormAction($ilCtrl->getFormActionByClass(self::class, "createPosting"));

            $title = new ilTextInputGUI($lng->txt("title"), "title");
            $title->setSize(30);
            $ilToolbar->addStickyItem($title, true);
            $tpl->addOnLoadCode("
                document.getElementById('title').setAttribute('data-blog-input', 'posting-title');
                document.getElementById('title').setAttribute('placeholder', ' ');
            ");

            $this->gui->button(
                $lng->txt("blog_add_posting"),
                "createPosting"
            )->submit()->toToolbar(true, $ilToolbar);


            // #18763
            $items = $this->domain->postingList($this->context->getBlog()->getId())->getPostingsGroupedByMonth();
            $keys = array_keys($items);
            $first = array_shift($keys);
            if ($first != $this->context->getRequest()->getMonth()) {
                $ilToolbar->addSeparator();

                $ilCtrl->setParameterByClass(ilObjBlogGUI::class, "bmn", $first);
                $url = $ilCtrl->getLinkTargetByClass(ilObjBlogGUI::class, "");
                $ilCtrl->setParameterByClass(ilObjBlogGUI::class, "bmn", $this->context->getRequest()->getMonth());

                $ilToolbar->addComponent(
                    $this->gui->ui()->factory()->button()->standard(
                        $lng->txt("blog_show_latest"),
                        $url
                    )
                );
            }

            // print/pdf
            $print_view = $this->gui->presentation()->getPrintView(
                $this->context->getNodeId(),
                $this->context->isRepositoryNode(),
                $this->context->getRequest()->getObjIds()
            );
            $modal_elements = $print_view->getModalElements(
                $ilCtrl->getLinkTarget(
                    $this,
                    "printViewSelection"
                )
            );
            $ilToolbar->addSeparator();
            $ilToolbar->addComponent($modal_elements->button);
            $ilToolbar->addComponent($modal_elements->modal);
        }

        $include_inactive = $this->context->getPermission()->mayContribute();
        $list_items = $this->domain->postingList($this->context->getBlog()->getId(), $include_inactive)
                            ->getPostingsForView(
                                $this->context->getAuthor() ?? 0,
                                $this->context->getRequest()->getKeyword(),
                                $this->context->getMonth()
                            );


        $list = $nav = "";
        if ($list_items) {
            $list = $this->gui->posting()->postingList(
                $this->context->getBlog()->getId(),
                $this->context->getPermission(),
                $this->context->getMonth(),
                $this->context->getNodeId(),
                $this->context->getIdType()
            )->render(
                $list_items,
                "preview",
                "",
                $include_inactive
            );
            $nav = $this->gui->navigation()->sideBar(
                $this->context->getPermission(),
                $this->getLinkBuilder(),
                $this->blog_settings,
                $this->context->getNodeId(),
                $this->context->getIdType()
            )->render(
                $this->domain->postingList($this->context->getBlog()->getId())->getPostingsGroupedByMonth(),
                $include_inactive
            );
        }

        $this->setContentStyleSheet();

        $tpl->setContent($ilToolbar->getHTML() . $list);
        $tpl->setRightContent($nav);
    }

    public function printViewSelection(): void
    {
        $print_view = $this->gui->presentation()->getPrintView(
            $this->context->getNodeId(),
            $this->context->isRepositoryNode(),
            $this->context->getRequest()->getObjIds()
        );
        $print_view->sendForm();
    }

    public function printPostings(): void
    {
        $print_view = $this->gui->presentation()->getPrintView(
            $this->context->getNodeId(),
            $this->context->isRepositoryNode(),
            $this->context->getRequest()->getObjIds()
        );
        $print_view->sendPrintView();
    }

    public function approve(): void
    {
        $apid = $this->context->getRequest()->getApId();
        if ($this->context->getPermission()->canManage() && $apid > 0) {
            $post = new \ilBlogPosting($apid);
            $post->setApproved(true);
            $post->setBlogNodeId($this->context->getNodeId(), !$this->context->isRepositoryNode());
            $post->update(true, false, true, "new"); // #13434

            $this->gui->ui()->mainTemplate()->setOnScreenMessage(
                'success',
                $this->domain->lng()->txt("settings_saved"),
                true
            );
        }

        $this->gui->ctrl()->redirectByClass(
            [
                ilObjBlogGUI::class,
                self::class,
            ],
            ""
        );
    }

    public function deactivateAdmin(): void
    {
        $apid = $this->context->getRequest()->getApId();
        if ($this->context->getPermission()->canWrite() && $apid > 0) {
            // ilBlogPostingGUI::deactivatePage()
            $post = new \ilBlogPosting($apid);
            $post->setApproved(false);
            $post->setActive(false);
            $post->update(true, false, false);

            $this->gui->ui()->mainTemplate()->setOnScreenMessage(
                'success',
                $this->domain->lng()->txt("settings_saved"),
                true
            );
        }

        $this->gui->ctrl()->redirect($this, "render");
    }

    /**
     * Create new posting
     */
    public function createPosting(): void
    {
        $ctrl = $this->gui->ctrl();
        $user = $this->domain->user();
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $title = $this->context->getRequest()->getTitle();
        if ($title) {
            // create new posting
            $posting = new \ilBlogPosting();
            $posting->setTitle($title);
            $posting->setBlogId($this->context->getBlog()->getId());
            $posting->setActive(false);
            $posting->setAuthor($user->getId());
            $posting->create(false);

            // switch month list to current month (will include new posting)
            $ctrl->setParameter($this, "bmn", date("Y-m"));

            $ctrl->setParameterByClass("ilblogpostinggui", "blpg", $posting->getId());
            $ctrl->redirectByClass("ilblogpostinggui", "edit");
        } else {
            $mt->setOnScreenMessage('failure', $lng->txt("msg_no_title"), true);
            $ctrl->redirect($this, "render");
        }
    }

    public function setContentStyleSheet(): void
    {
        $this->content_style->gui()->addCss(
            $this->gui->ui()->mainTemplate(),
            $this->context->getBlog()->getRefId(),
            $this->context->getBlog()->getId()
        );
    }


}
