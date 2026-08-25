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

use ILIAS\Blog\BlogGUIContext;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Navigation\Link\PresentationLinkBuilder;
use ILIAS\Blog\Navigation\Link\EditingLinkBuilder;
use ILIAS\Blog\Navigation\Link\ExportLinkBuilder;
use ILIAS\Blog\Navigation\Link\LinkBuilder;
use ILIAS\Blog\Settings\Settings;

class GUIService
{
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
    }

    public function toolbarNavigationRenderer(
        LinkBuilder $link_builder
    ): ToolbarNavigationRenderer {
        return new ToolbarNavigationRenderer(
            $this->domain,
            $this->gui,
            $link_builder
        );
    }

    public function monthBlock(
        LinkBuilder $link_builder,
        bool $is_repository
    ): MonthBlockGUI {
        return new MonthBlockGUI(
            $this->domain,
            $this->gui,
            $link_builder,
            $is_repository
        );
    }

    public function authorBlock(
        LinkBuilder $link_builder
    ): AuthorBlockGUI {
        return new AuthorBlockGUI(
            $this->domain,
            $this->gui,
            $link_builder
        );
    }

    public function keywordBlock(
        LinkBuilder $link_builder,
        bool $is_repository
    ): KeywordBlockGUI {
        return new KeywordBlockGUI(
            $this->domain,
            $this->gui,
            $link_builder,
            $is_repository
        );
    }

    public function sideBar(
        ?PermissionManager $perm,
        LinkBuilder $link_builder,
        Settings $settings,
        ?int $node_id = null,
        int $id_type = \ilObjBlogGUI::REPOSITORY_NODE_ID
    ): SideBarGUI {
        return new SideBarGUI(
            $this->domain,
            $this->gui,
            $perm,
            $link_builder,
            $settings,
            $node_id,
            $id_type
        );
    }

    public function presentationHeader(
        \ilObjBlog $blog,
        PermissionManager $perm,
    ): PresentationHeaderGUI {
        return new PresentationHeaderGUI(
            $this->domain,
            $this->gui,
            $blog,
            $perm
        );
    }

    public function navigationGUI(BlogGUIContext $context): NavigationGUI
    {
        return new NavigationGUI(
            $this->domain,
            $this->gui,
            $context
        );
    }

    public function presentationLink(): PresentationLinkBuilder
    {
        return new PresentationLinkBuilder(
            $this->gui->ctrl(),
        );
    }

    public function editingLink(): EditingLinkBuilder
    {
        return new EditingLinkBuilder(
            $this->gui->ctrl(),
        );
    }

    public function exportLink(
        string $link_template = "",
        array $keyword_map = []
    ): ExportLinkBuilder {
        return new ExportLinkBuilder(
            $link_template,
            $keyword_map
        );
    }

}
