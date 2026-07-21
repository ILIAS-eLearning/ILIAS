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
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Posting\Posting;
use ILIAS\Blog\Navigation\Link\LinkBuilder;
use ILIAS\Blog\Editing\EditingGUI;

class ToolbarNavigationRenderer
{
    protected Link\EditingLinkBuilder $edit_link_builder;
    protected array $items;
    protected InternalGUIService $gui;
    protected int $portfolio_page;
    protected int $blog_page;
    protected \ILIAS\Blog\Presentation\Util $util;
    protected string $current_month;
    protected \ilCtrl $ctrl;
    protected PermissionManager $blog_access;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        protected LinkBuilder $pres_link_builder
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
        $this->util = $gui->presentation()->util();
        $this->edit_link_builder = $gui->navigation()->editingLink();
    }

    public function renderToolbarNavigation(
        PermissionManager $blog_acces,
        array $a_items,
        int $blog_page,
        bool $single_posting,
        string $month,
        int $portfolio_page
    ): void {

        $this->blog_access = $blog_acces;
        $this->ctrl = $ctrl = $this->gui->ctrl();
        $this->items = $a_items;
        $this->current_month = $month;
        $this->blog_page = $blog_page;
        $this->portfolio_page = $portfolio_page;

        if ($single_posting) {	// single posting view
            $next_posting = $this->getNextPosting($blog_page);
            if ($next_posting > 0) {
                $this->renderPreviousButton($this->getPostingTarget($next_posting));
            } else {
                $this->renderPreviousButton("");
            }

            $this->renderPostingDropdown();

            $prev_posting = $this->getPreviousPosting($blog_page);
            if ($prev_posting > 0) {
                $this->renderNextButton($this->getPostingTarget($prev_posting));
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
            $link = $this->edit_link_builder->forMainList();
            $actions[] = $f->button()->shy(
                $lng->txt("blog_edit"),
                $link
            );
        }

        if ($single_posting && $this->blog_access->mayContribute() && $this->blog_access->mayEditPosting($this->blog_page)) {
            $link = $this->edit_link_builder->forPosting($this->blog_page);
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
            $first = current($month);
            if ($first instanceof \ILIAS\Blog\Posting\Posting) {
                return (int) $first->getId();
            }
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
                /** @var \ILIAS\Blog\Posting\Posting $item */
                $item_id = (int) $item->getId();
                if (!$this->blog_access->isActive($item_id)) {
                    continue;
                }
                if ($item_id == $blog_page) {
                    $found = true;
                }
                if (!$found) {
                    $next_blpg = $item_id;
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
                /** @var \ILIAS\Blog\Posting\Posting $item */
                $item_id = (int) $item->getId();
                if (!$this->blog_access->isActive($item_id)) {
                    continue;
                }
                if ($found && $prev_blpg === 0) {
                    $prev_blpg = $item_id;
                }
                if ($item_id === $blog_page) {
                    $found = true;
                }
            }
        }
        return $prev_blpg;
    }

    protected function getPostingTarget(int $posting): string
    {
        return $this->pres_link_builder->forPosting($posting);
    }

    protected function getMonthTarget(string $month): string
    {
        return $this->pres_link_builder->forMonth($month);
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
        string $current_month
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
        string $current_month
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

    protected function renderPostingDropdown(): void
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
            /** @var Posting $item */
            foreach ($items as $item) {
                if (!$this->blog_access->isActive((int) $item->getId())) {
                    continue;
                }
                $label = $item->getTitle();
                if ((int) $item->getId() === $this->blog_page) {
                    $label = "» " . $label;
                    $dd_title = $item->getTitle();
                }
                $label = str_pad("", 12, "&nbsp;") . $label;
                $m[] = $f->link()->standard(
                    $label,
                    $this->getPostingTarget((int) $item->getId())
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
