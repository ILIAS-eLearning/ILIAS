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

namespace ILIAS\Blog\Navigation;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Access\BlogAccess;

class ToolbarNavigationRenderer
{
    protected array $items;
    protected InternalGUIService $gui;
    protected int $portfolio_page;
    protected bool $prtf_embed;
    protected int $blog_page;
    protected \ILIAS\Blog\Presentation\Util $util;
    protected $current_month;
    protected \ilCtrl $ctrl;
    protected BlogAccess $blog_access;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
        $this->util = $gui->presentation()->util();
    }

    public function renderToolbarNavigation(
        BlogAccess $blog_acces,
        array $a_items,
        int $blog_page,
        bool $single_posting,
        bool $prtf_embed,
        $month,
        int $portfolio_page
    ): void {

        $this->blog_access = $blog_acces;
        $toolbar = $this->gui->toolbar();
        $lng = $this->domain->lng();
        $this->ctrl = $ctrl = $this->gui->ctrl();
        $f = $this->gui->ui()->factory();
        $this->items = $a_items;
        $this->current_month = $month;
        $this->blog_page = $blog_page;
        $this->prtf_embed = $prtf_embed;
        $this->portfolio_page = $portfolio_page;

        $cmd = ($prtf_embed)
            ? "previewEmbedded"
            : "previewFullscreen";

        if ($single_posting) {	// single posting view
            $next_posting = $this->getNextPosting($blog_page);
            if ($next_posting > 0) {
                $this->renderPreviousButton($this->getPostingTarget($next_posting, $cmd));
            } else {
                $this->renderPreviousButton("");
            }

            $this->renderPostingDropdown($cmd);

            $prev_posting = $this->getPreviousPosting($blog_page);
            if ($prev_posting > 0) {
                $this->renderNextButton($this->getPostingTarget($prev_posting, $cmd));
            } else {
                $this->renderNextButton("");
            }


            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $blog_page);

            $this->renderActionDropdown(true);

        } else {		// month view
            $next_month = $this->getNextMonth($month);
            if ($next_month !== "") {
                $this->renderPreviousButton($this->getMonthTarget($next_month));
            } else {
                $this->renderPreviousButton("");
            }

            $this->renderMonthDropdown();

            $prev_month = $this->getPreviousMonth($month);
            if ($prev_month !== "") {
                $this->renderNextButton($this->getMonthTarget($prev_month));
            } else {
                $this->renderNextButton("");
            }

            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);

            $this->renderActionDropdown(false);
        }
    }

    protected function renderActionDropdown(bool $single_posting): void
    {
        $lng = $this->domain->lng();
        $toolbar = $this->gui->toolbar();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->ctrl;
        $actions = [];
        if ($this->blog_access->mayContribute()) {
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "prvm", "");

            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", "");
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", "");
            $link = $ctrl->getLinkTargetByClass(\ilObjBlogGUI::class, "");
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $this->blog_page);
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $this->current_month);
            $actions[] = $f->button()->shy(
                $lng->txt("blog_edit"),
                $link
            );
        }

        if ($single_posting && $this->blog_access->mayContribute() && $this->blog_access->mayEditPosting($this->blog_page)) {
            $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $this->blog_page);
            if ($this->prtf_embed) {
                $ctrl->setParameterByClass(\ilObjPortfolioGUI::class, "ppage", $this->portfolio_page);
            }
            $link = $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, "edit");
            $actions[] = $f->button()->shy(
                $lng->txt("blog_edit_posting"),
                $link
            );
        }
        if (count($actions) > 0) {
            $toolbar->addStickyItem($f->dropdown()->standard($actions));
        }
    }

    protected function getLatestPosting(): int
    {
        reset($this->items);
        $month = current($this->items);
        if (is_array($month)) {
            return (int) current($month)["id"];
        }
        return 0;
    }

    public function getNextPosting(
        int $blog_page
    ): int {
        reset($this->items);
        $found = "";
        $next_blpg = 0;
        foreach ($this->items as $month => $items) {
            foreach ($items as $item) {
                if ($item["id"] == $blog_page) {
                    $found = true;
                }
                if (!$found) {
                    $next_blpg = (int) $item["id"];
                }
            }
        }
        return $next_blpg;
    }

    protected function getPreviousPosting(
        int $blog_page
    ): int {
        reset($this->items);
        $found = "";
        $prev_blpg = 0;
        foreach ($this->items as $month => $items) {
            foreach ($items as $item) {
                if ($found && $prev_blpg === 0) {
                    $prev_blpg = (int) $item["id"];
                }
                if ((int) $item["id"] === $blog_page) {
                    $found = true;
                }
            }
        }
        return $prev_blpg;
    }

    protected function getPostingTarget(int $posting, string $cmd): string
    {
        $this->ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", (string) $posting);
        return $this->ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, $cmd);
    }

    protected function getMonthTarget(string $month): string
    {
        $this->ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);
        return $this->ctrl->getLinkTargetByClass(\ilObjBlogGUI::class, "preview");
    }

    protected function renderMonthDropdown(): void
    {
        $toolbar = $this->gui->toolbar();
        $f = $this->gui->ui()->factory();
        $m = [];
        foreach ($this->items as $month => $items) {
            $label = $this->util->getMonthPresentation($month);
            if ($month === $this->current_month) {
                $label = "» " . $label;
            }
            $m[] = $f->link()->standard(
                $label,
                $this->getMonthTarget($month)
            );
        }
        if (count($m) > 0) {
            $toolbar->addStickyItem($f->dropdown()->standard($m)->withLabel(
                $this->getDropdownLabel($this->util->getMonthPresentation($this->current_month))
            ));
        }
    }

    protected function getNextMonth(
        $current_month
    ): string {
        reset($this->items);
        $found = "";
        foreach ($this->items as $month => $items) {
            if ($month > $current_month) {
                $found = $month;
            }
        }
        return $found;
    }

    protected function getPreviousMonth(
        $current_month
    ): string {
        reset($this->items);
        $found = "";
        foreach ($this->items as $month => $items) {
            if ($month < $current_month && $found === "") {
                $found = $month;
            }
        }
        return $found;
    }

    /**
     * @param array $a_items item array
     */
    protected function getLatestMonth(): string
    {
        reset($this->items);
        return key($this->items);
    }

    protected function renderNextButton(string $href = ""): void
    {
        $this->renderNavButton("right", $href);
    }

    protected function renderPreviousButton(string $href = ""): void
    {
        $this->renderNavButton("left", $href);
    }

    protected function renderNavButton(string $dir, string $href = ""): void
    {
        $toolbar = $this->gui->toolbar();
        $b = $this->gui->ui()->factory()->button()->standard(
            "<span class=\"glyphicon glyphicon-chevron-" . $dir . " \" aria-hidden=\"true\"></span>",
            $href
        );
        if ($href === "") {
            $b = $b->withUnavailableAction();
        }
        $toolbar->addStickyItem($b);
    }

    protected function renderPostingDropdown(string $cmd): void
    {
        $toolbar = $this->gui->toolbar();
        $f = $this->gui->ui()->factory();
        $m = [];
        $dd_title = "";
        foreach ($this->items as $month => $items) {
            $label = $this->util->getMonthPresentation($month);
            $m[] = $f->button()->shy(
                $label,
                $this->getMonthTarget($month)
            )->withUnavailableAction();
            foreach ($items as $item) {
                $label = $item["title"];
                if ((int) $item["id"] === $this->blog_page) {
                    $label = "» " . $label;
                    $dd_title = $item["title"];
                }
                $label = str_pad("", 12, "&nbsp;") . $label;
                $m[] = $f->link()->standard(
                    $label,
                    $this->getPostingTarget((int) $item["id"], $cmd)
                );
            }
        }
        if (count($m) > 0) {
            $toolbar->addStickyItem($f->dropdown()->standard($m)->withLabel(
                $this->getDropdownLabel($dd_title)
            ));
        }
    }

    protected function getDropdownLabel(string $label): string
    {
        return "<span style='vertical-align: bottom; max-width:60px; display: inline-block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>" . $label . "</span>";
    }

}
