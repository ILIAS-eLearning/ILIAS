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
use ilTemplate;
use ilRSSButtonGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Navigation\Link\LinkBuilder;
use ILIAS\Blog\Navigation\Link\EditingLinkBuilder;
use ILIAS\Blog\Navigation\Link\ExportLinkBuilder;
use ILIAS\Blog\Navigation\Link\PresentationLinkBuilder;
use ILIAS\Blog\Settings\Settings;

class SideBarGUI
{
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        protected ?PermissionManager $perm,
        protected LinkBuilder $link_builder,
        protected Settings $settings,
        protected ?int $node_id = null,
        protected int $id_type = \ilObjBlogGUI::REPOSITORY_NODE_ID
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
    }

    protected function mayEditPosting(int $blpg): bool
    {
        if ($this->link_builder instanceof EditingLinkBuilder &&
            !is_null($this->perm) &&
            $this->perm->mayEditPosting($blpg)) {
            return true;
        }
        return false;
    }

    protected function isExport(): bool
    {
        return $this->link_builder instanceof ExportLinkBuilder;
    }

    protected function isPresentation(): bool
    {
        return $this->link_builder instanceof PresentationLinkBuilder;
    }

    /**
     * Build navigation blocks
     */
    public function render(
        array $items,
        bool $show_inactive = false,
        int $blpg = 0
    ): string {
        $lng = $this->domain->lng();
        $ui = $this->gui->ui();
        if ($this->settings->getOrder()) {
            $order = array_flip($this->settings->getOrder());
        } else {
            $order = array(
                "navigation" => 0,
                "keywords" => 2,
                "authors" => 1
            );
        }

        $wtpl = new ilTemplate("tpl.blog_list_navigation.html", true, true, "components/ILIAS/Blog");

        $blocks = array();

        // by date
        if (count($items)) {
            $blocks[$order["navigation"] ?? 0] = array(
                $lng->txt("blog_navigation"),
                $this->gui->navigation()->monthBlock(
                    $this->link_builder,
                    $this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID
                )->render(
                    $items,
                    $show_inactive,
                    $blpg
                )
            );
        }

        if ($this->settings->getKeywords()) {
            // keywords
            $may_edit_keywords = ($blpg > 0 &&
                $this->mayEditPosting($blpg));

            $keywords = $this->gui->navigation()->keywordBlock(
                $this->link_builder,
                $this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID
            )->render(
                $show_inactive,
                $blpg
            );
            if ($keywords || $may_edit_keywords) {
                if (!$keywords) {
                    $keywords = $lng->txt("blog_no_keywords");
                }
                $cmd = null;
                $blocks[$order["keywords"] ?? 2] = array(
                    $lng->txt("blog_keywords"),
                    $keywords,
                    $cmd
                        ? array($cmd, $lng->txt("blog_edit_keywords"))
                        : null
                );
            }
        }

        // is not part of (html) export
        if (!$this->isExport()) {
            // authors
            if ($this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID &&
                $this->settings->getAuthors()) {
                $authors = $this->gui->navigation()->authorBlock($this->link_builder)->render(
                    $show_inactive
                );
                if ($authors) {
                    $blocks[$order["authors"] ?? 1] = array($lng->txt("blog_authors"), $authors);
                }
            }

            // rss
            if ($this->settings->getRSS() &&
                $this->domain->settings()->get('enable_global_profiles') &&
                $this->isPresentation()) {
                // #10827
                $blog_id = (string) $this->node_id;
                if ($this->id_type !== \ilObjBlogGUI::WORKSPACE_NODE_ID) {
                    $blog_id .= "_cll";
                }
                $url = ILIAS_HTTP_PATH . "/feed.php?blog_id=" . $blog_id .
                    "&client_id=" . rawurlencode(CLIENT_ID);

                $wtpl->setVariable("RSS_BUTTON", ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS, $url));
            }
        }

        if (count($blocks)) {
            $ui_factory = $ui->factory();
            $ui_renderer = $ui->renderer();

            ksort($blocks);
            foreach ($blocks as $block) {
                $title = $block[0];
                $content = $block[1];

                $secondary_panel = $ui_factory->panel()->secondary()->legacy($title, $ui_factory->legacy()->content($content));

                if (isset($block[2]) && is_array($block[2])) {
                    $link = $ui_factory->button()->shy($block[2][1], $block[2][0]);
                    $secondary_panel = $secondary_panel->withFooter($link);
                }

                $wtpl->setCurrentBlock("block_bl");
                $wtpl->setVariable("BLOCK", $ui_renderer->render($secondary_panel));
                $wtpl->parseCurrentBlock();
            }
        }

        return $wtpl->get();
    }
}
