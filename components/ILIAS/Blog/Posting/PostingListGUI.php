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

namespace ILIAS\Blog\Posting;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ilObjBlogGUI;
use ilTemplate;
use ilBlogPosting;
use ilDatePresentation;
use ILIAS\Blog\Permission\PermissionManager;

class PostingListGUI
{
    protected string $approve_cmd_class;
    protected array $approve_cmd_path;
    protected string $admin_cmd_class;
    protected array $admin_cmd_path;
    protected \ILIAS\Blog\Keywords\KeywordManager $keyword_manager;
    protected \ILIAS\Blog\Export\ExportManager $exp_manager;
    protected int $author;
    protected string $keyword;
    protected \ILIAS\Blog\Settings\Settings $blog_settings;
    protected \ILIAS\Blog\ReadingTime\ReadingTimeManager $reading_time_manager;
    protected PostingManager $posting_manager;
    protected \ILIAS\Notes\Service $notes;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $blog_id,
        protected PermissionManager $perm,
        protected ?string $current_month = null,
        protected ?int $node_id = null,
        protected int $id_type = \ilObjBlogGUI::REPOSITORY_NODE_ID
    ) {
        global $DIC;
        $this->notes = $DIC->notes();
        $this->approve_cmd_path = [\ilObjBlogGUI::class];
        $this->approve_cmd_class = end($this->approve_cmd_path);
        $this->admin_cmd_path = [\ilObjBlogGUI::class];
        $this->admin_cmd_class = end($this->approve_cmd_path);


        $this->posting_manager = $domain->posting();
        $this->reading_time_manager = $domain->readingTime();
        $this->blog_settings =
            $domain->blogSettings()->getByObjId($this->blog_id);
        $req = $gui->standardRequest();
        $this->keyword = $req->getKeyword();
        $this->author = $req->getAuthor();
        $this->exp_manager = $domain->export()->manager();
        $this->keyword_manager = $domain->keywords();
    }

    public function render(
        array $items,
        string $a_cmd = "preview",
        string $a_link_template = "",
        bool $a_show_inactive = false,
        string $a_export_directory = ""
    ): string {
        $lng = $this->domain->lng();
        $ilCtrl = $this->gui->ctrl();
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        $wtpl = new ilTemplate("tpl.blog_list.html", true, true, "components/ILIAS/Blog");

        $is_admin = $this->perm->canManage();

        $last_month = null;
        $is_empty = true;
        foreach ($items as $item) {
            /** @var Posting $item */
            $item_id = $item->getId();
            $author = $item->getAuthor();
            $created = $item->getCreated();
            $approved = $item->isApproved();
            // only published items
            $is_active = ilBlogPosting::_lookupActive($item_id, "blp");
            if (!$is_active && !$a_show_inactive) {
                continue;
            }

            $is_empty = false;

            $month = "";
            if (!$this->keyword && !$this->author) {
                $month = substr($created->get(IL_CAL_DATE), 0, 7);
            }

            if (!$last_month || $last_month != $month) {
                if ($last_month) {
                    $wtpl->setCurrentBlock("month_bl");
                    $wtpl->parseCurrentBlock();
                }

                // title according to current "filter"/navigation
                if ($this->keyword) {
                    $title = $lng->txt("blog_keyword") . ": " . $this->keyword;
                } elseif ($this->author) {
                    $title = $lng->txt("blog_author") . ": " . \ilUserUtil::getNamePresentation($this->author);
                } else {
                    $title = $this->gui->presentation()->util()->getMonthPresentation($month);
                    $last_month = $month;
                }

                $wtpl->setVariable("TXT_CURRENT_MONTH", $title);
            }

            if (!$a_link_template) {
                $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $this->current_month);
                $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $item_id);
                $preview = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_cmd);
            } else {
                $preview = $this->exp_manager->buildExportLink(
                    $a_link_template,
                    "posting",
                    (string) $item_id,
                    $this->keyword_manager->getKeywords($this->blog_id, false)
                );
            }
            $more_link = $preview;

            // actions
            $posting_edit = $this->perm->mayEditPosting($item_id, $author);
            if (($posting_edit || $is_admin) && !$a_link_template && $a_cmd === "preview") {
                $actions = [];

                if ($is_active && $this->blog_settings->getApproval() && !$approved) {
                    if ($is_admin) {
                        $ilCtrl->setParameterByClass($this->approve_cmd_class, "apid", $item_id);
                        $actions[] = $ui_factory->link()->standard(
                            $lng->txt("blog_approve"),
                            $ilCtrl->getLinkTargetByClass($this->approve_cmd_path, "approve")
                        );
                        $ilCtrl->setParameterByClass($this->approve_cmd_class, "apid", "");
                    }

                    $wtpl->setVariable("APPROVAL", $lng->txt("blog_needs_approval"));
                }

                if ($posting_edit) {
                    $actions[] = $ui_factory->link()->standard(
                        $lng->txt("edit_content"),
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit")
                    );
                    $more_link = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit");

                    // #11858
                    if ($is_active) {
                        $actions[] = $ui_factory->link()->standard(
                            $lng->txt("blog_toggle_draft"),
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deactivatePageToList")
                        );
                    } else {
                        $actions[] = $ui_factory->link()->standard(
                            $lng->txt("blog_toggle_final"),
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "activatePageToList")
                        );
                    }

                    $actions[] = $ui_factory->link()->standard(
                        $lng->txt("rename"),
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edittitle")
                    );

                    if ($this->blog_settings->getKeywords()) { // #13616
                        $actions[] = $ui_factory->link()->standard(
                            $lng->txt("blog_edit_keywords"),
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editKeywords")
                        );
                    }

                    $actions[] = $ui_factory->link()->standard(
                        $lng->txt("blog_edit_date"),
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editdate")
                    );

                    $actions[] = $ui_factory->link()->standard(
                        $lng->txt("delete"),
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen")
                    );
                } elseif ($is_admin) {
                    // #10513
                    if ($is_active) {
                        $ilCtrl->setParameterByClass($this->admin_cmd_class, "apid", $item_id);
                        $actions[] = $ui_factory->link()->standard(
                            $lng->txt("blog_toggle_draft_admin"),
                            $ilCtrl->getLinkTargetByClass($this->admin_cmd_path, "deactivateAdmin")
                        );
                        $ilCtrl->setParameterByClass($this->admin_cmd_class, "apid", "");
                    }

                    $actions[] = $ui_factory->link()->standard(
                        $lng->txt("delete"),
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen")
                    );
                }

                $dd = $ui_factory->dropdown()->standard($actions)->withLabel($lng->txt("actions"));

                $wtpl->setCurrentBlock("actions");
                $wtpl->setVariable("ACTION_SELECTOR", $ui_renderer->render($dd));
                $wtpl->parseCurrentBlock();
            }

            // comments
            $notes_status = $this->notes->domain()->commentsActive($this->blog_id);
            if ($notes_status && !$a_link_template) {
                // count (public) notes
                $notes_context = $this->notes
                    ->data()
                    ->context(
                        $this->blog_id,
                        (int) $item_id,
                        "blp"
                    );
                $count = $this->notes
                    ->domain()
                    ->getNrOfCommentsForContext($notes_context);

                if ($a_cmd !== "preview") {
                    $wtpl->setCurrentBlock("comments");
                    $wtpl->setVariable("TEXT_COMMENTS", $lng->txt("blog_comments"));
                    $wtpl->setVariable("URL_COMMENTS", $preview);
                    $wtpl->setVariable("COUNT_COMMENTS", $count);
                    $wtpl->parseCurrentBlock();
                }
            }

            // permanent link
            if ($this->node_id !== null &&
                $a_cmd !== "preview") {
                if ($this->id_type === ilObjBlogGUI::WORKSPACE_NODE_ID) {
                    $goto = $this->gui->permanentLink(0, (int) $this->node_id)->getPermanentLink((int) $item_id);
                } else {
                    $goto = $this->gui->permanentLink((int) $this->node_id)->getPermanentLink((int) $item_id);
                }
                $wtpl->setCurrentBlock("permalink");
                $wtpl->setVariable("URL_PERMALINK", $goto);
                $wtpl->setVariable("TEXT_PERMALINK", $lng->txt("blog_link"));
                $wtpl->parseCurrentBlock();
            }

            $snippet = $this->gui->posting()->getSnippet(
                $item_id,
                $this->blog_settings->getAbstractShorten(),
                $this->blog_settings->getAbstractShortenLength(),
                "&hellip;",
                $this->blog_settings->getAbstractImage(),
                $this->blog_settings->getAbstractImageWidth(),
                $this->blog_settings->getAbstractImageHeight(),
                $a_export_directory
            );

            if ($snippet) {
                $wtpl->setCurrentBlock("more");
                $wtpl->setVariable("URL_MORE", $more_link);
                $wtpl->setVariable("TEXT_MORE", $lng->txt("blog_list_more"));
                $wtpl->parseCurrentBlock();
            }

            if (!$is_active) {
                $wtpl->setCurrentBlock("draft_text");
                $wtpl->setVariable("DRAFT_TEXT", $lng->txt("blog_draft_text"));
                $wtpl->parseCurrentBlock();
                $wtpl->setVariable("DRAFT_CLASS", " ilBlogListItemDraft");
            }

            // reading time
            $reading_time = $this->reading_time_manager->getReadingTime(
                $this->blog_id,
                $item_id
            );
            if (!is_null($reading_time)) {
                $lng->loadLanguageModule("copg");
                $wtpl->setCurrentBlock("reading_time");
                $wtpl->setVariable(
                    "READING_TIME",
                    $lng->txt("copg_est_reading_time") . ": " .
                    sprintf($lng->txt("copg_x_minutes"), $reading_time)
                );
                $wtpl->parseCurrentBlock();
            }

            $wtpl->setCurrentBlock("posting");

            $author_str = "";
            if ($this->id_type === ilObjBlogGUI::REPOSITORY_NODE_ID) {
                $authors = array();

                // primary author
                if ($author) {
                    $authors[] = \ilUserUtil::getNamePresentation($author);
                }

                // additional editors
                foreach (\ilPageObject::getPageContributors("blp", $item_id) as $editor) {
                    $editor_id = (int) $editor["user_id"];
                    if ($editor_id !== $author) {
                        $authors[] = \ilUserUtil::getNamePresentation($editor_id);
                    }
                }

                if ($authors) {
                    $author_str = implode(", ", $authors) . " - ";
                }
            }

            // title
            $wtpl->setVariable("URL_TITLE", $preview);
            $wtpl->setVariable("TITLE", $item->getTitle());

            $kw = $this->posting_manager->getKeywords($this->blog_id, $item_id);
            natcasesort($kw);
            $keywords = (count($kw) > 0)
                ? "<br>" . $lng->txt("keywords") . ": " . implode(", ", $kw)
                : "";

            $wtpl->setVariable("DATETIME", $author_str .
                ilDatePresentation::formatDate($created) . $keywords);

            // content
            $wtpl->setVariable("CONTENT", $snippet);

            $wtpl->parseCurrentBlock();
        }

        // permalink
        if ($a_cmd === "previewFullscreen") {
            $ref_id = ($this->id_type === ilObjBlogGUI::WORKSPACE_NODE_ID)
                ? 0
                : $this->node_id;
            $wsp_id = ($this->id_type === ilObjBlogGUI::WORKSPACE_NODE_ID)
                ? $this->node_id
                : 0;
            $this->gui->permanentLink($ref_id, $wsp_id)->setPermanentLink();
        }

        if (!$is_empty || $a_show_inactive) {
            return $wtpl->get();
        }
        return "";
    }
}
