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
use ilObjBlogGUI;
use ilBlogPostingGUI;
use ilToolbarGUI;
use ilTextInputGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Navigation\Link\EditingLinkBuilder;

/**
 * @ilCtrl_Calls ILIAS\Blog\Editing\EditingGUI: ilRepositorySearchGUI, ilBlogPostingGUI
 */
class EditingGUI
{
    protected int $author;
    protected string $keyword;
    protected string $month;
    protected ?\ILIAS\Blog\Settings\Settings $blog_settings;
    protected ?\ilObjBlog $blog;
    protected \ILIAS\Blog\StandardGUIRequest $blog_request;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $node_id,
        protected int $id_type,
        protected PermissionManager $perm,
        protected ?string $current_month,
        protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        protected ilObjBlogGUI $parent_gui
    ) {
        $this->blog_request = $gui->standardRequest();
        $this->blog = $parent_gui->getObject();
        $this->blog_settings = $this->domain->blogSettings()->getByObjId($this->blog->getId());
        $this->month = $this->blog_request->getMonth();
        $this->keyword = $this->blog_request->getKeyword();
        $this->author = $this->blog_request->getAuthor();
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
        $req = $this->gui->standardRequest();

        $ilCtrl->saveParameter($this, "user_page");
        $tpl->loadStandardTemplate();

        if (!$this->perm->mayContribute()) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
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

        $this->parent_gui->setContentStyleSheet();

        $ilCtrl->setParameterByClass(ilBlogPostingGUI::class, "blpg", $req->getBlogPage());
        $this->gui->tabs()->addNonTabbedLink(
            "preview",
            $lng->txt("blog_preview"),
            $ilCtrl->getLinkTargetByClass(ilBlogPostingGUI::class, "previewFullscreen")
        );
        $ilCtrl->setParameterByClass(ilBlogPostingGUI::class, "blpg", "");

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
            if ($this->blog->getNotesStatus() &&
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
            /*
            if ($cmd !== "edit") {
                $nav = $this->gui->navigation()->sideBar(
                    $this->perm,
                    $this->getLinkBuilder()
                )->render(
                    $this->parent_gui,
                    $this->parent_gui->getItems(),
                    $is_owner
                );
                $tpl->setRightContent($nav);
            } else {
                $this->gui->tabs()->setBackTarget("", "");
            }*/
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

        if (!$this->parent_gui->checkPermissionBool("read")) {
            $tpl->setOnScreenMessage('info', $lng->txt("no_permission"));
            return;
        }

        $ilTabs->activateTab("content");

        // toolbar
        if ($this->perm->mayContribute()) {
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
            $items = $this->domain->postingList($this->blog->getId())->getPostingsGroupedByMonth();
            $keys = array_keys($items);
            $first = array_shift($keys);
            if ($first != $this->month) {
                $ilToolbar->addSeparator();

                $ilCtrl->setParameter($this->parent_gui, "bmn", $first);
                $url = $ilCtrl->getLinkTarget($this->parent_gui, "");
                $ilCtrl->setParameter($this->parent_gui, "bmn", $this->month);

                $ilToolbar->addComponent(
                    $this->gui->ui()->factory()->button()->standard(
                        $lng->txt("blog_show_latest"),
                        $url
                    )
                );
            }

            // print/pdf
            $print_view = $this->gui->presentation()->getPrintView(
                $this->node_id,
                $this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID,
                $this->blog_request->getObjIds()
            );
            $modal_elements = $print_view->getModalElements(
                $ilCtrl->getLinkTarget(
                    $this->parent_gui,
                    "printViewSelection"
                )
            );
            $ilToolbar->addSeparator();
            $ilToolbar->addComponent($modal_elements->button);
            $ilToolbar->addComponent($modal_elements->modal);
        }

        $include_inactive = $this->perm->mayContribute();
        $list_items = $this->domain->postingList($this->blog->getId(), $include_inactive)
                            ->getPostingsForView(
                                $this->author ?? 0,
                                $this->keyword ?? "",
                                $this->current_month ?? ""
                            );


        $list = $nav = "";
        if ($list_items) {
            $list = $this->gui->posting()->postingList(
                $this->blog->getId(),
                $this->perm,
                $this->current_month,
                $this->node_id,
                $this->id_type
            )->render(
                $list_items,
                "preview",
                "",
                $include_inactive
            );
            $nav = $this->gui->navigation()->sideBar(
                $this->perm,
                $this->getLinkBuilder(),
                $this->blog_settings,
                $this->node_id,
                $this->id_type
            )->render(
                $this->domain->postingList($this->blog->getId())->getPostingsGroupedByMonth(),
                $include_inactive
            );
        }

        $this->parent_gui->setContentStyleSheet();

        $tpl->setContent($ilToolbar->getHTML() . $list);
        $tpl->setRightContent($nav);
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
        $title = $this->blog_request->getTitle();
        if ($title) {
            // create new posting
            $posting = new \ilBlogPosting();
            $posting->setTitle($title);
            $posting->setBlogId($this->blog->getId());
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

}
