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
use ILIAS\Blog\Contributor\ContributorGUI;
use ILIAS\Blog\Editing\EditingGUI;
use ILIAS\Blog\Settings\SettingsGUI;
use ilObjectListGUI;
use ilObjBlogGUI;

class NavigationGUI
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected BlogGUIContext $context
    ) {
    }

    public function setTabs(): void
    {
        $context = $this->context;
        $tabs = $this->gui->tabs();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $perm = $context->getPermission();

        if (!$context->isRepositoryNode()) {
            $ctrl->setParameterByClass(ilObjBlogGUI::class, "wsp_id", $context->getNodeId());
        }

        $this->gui->help()->setScreenIdComponent("blog");

        if ($perm->mayContribute()) {
            $ctrl->setParameterByClass(ilObjBlogGUI::class, "bmn", null);
            $tabs->addTab(
                "content",
                $lng->txt("content"),
                $ctrl->getLinkTargetByClass(EditingGUI::class, "")
            );
        }
        if ($perm->canRead()) {
            $tabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $ctrl->getLinkTargetByClass(
                    [ilObjBlogGUI::class, "ilinfoscreengui"],
                    "showSummary"
                )
            );
        }

        if ($perm->canWrite()) {
            $tabs->addTab(
                "settings",
                $lng->txt("settings"),
                $ctrl->getLinkTargetByClass(SettingsGUI::class, "")
            );

            if ($context->isRepositoryNode()) {
                $tabs->addTab(
                    "contributors",
                    $lng->txt("blog_contributors"),
                    $ctrl->getLinkTargetByClass(ContributorGUI::class, "contributors")
                );

                $blog = $context->getObject();
                if ($blog !== null) {
                    $mdgui = new \ilObjectMetaDataGUI(
                        $blog,
                        null,
                        null,
                        $context->isCallByReference()
                    );
                    $mdtab = $mdgui->getTab();
                    if ($mdtab) {
                        $tabs->addTab(
                            "meta_data",
                            $lng->txt("meta_data"),
                            $mdtab
                        );
                    }
                }
                $tabs->addTab(
                    "export",
                    $lng->txt("export"),
                    $ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }
        }

        if ($perm->mayContribute()) {
            $tabs->addNonTabbedLink(
                "preview",
                $lng->txt("blog_preview"),
                $ctrl->getLinkTargetByClass(
                    [ilObjBlogGUI::class, \ILIAS\Blog\Presentation\PresentationGUI::class],
                    "preview"
                )
            );
        }
    }

    public function setSettingsSubTabs(
        string $active
    ): void {
        $context = $this->context;
        $tabs = $this->gui->tabs();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $blog = $context->getBlog();

        $tabs->addSubTab(
            "properties",
            $lng->txt("general"),
            $ctrl->getLinkTargetByClass(ilObjBlogGUI::class, "edit")
        );
        $tabs->addSubTab(
            "side_blocks",
            $lng->txt("blog_side_blocks"),
            $ctrl->getLinkTargetByClass(
                [ilObjBlogGUI::class, \ILIAS\Blog\Settings\BlockSettingsGUI::class],
                ""
            )
        );
        $tabs->addSubTab(
            "style",
            $lng->txt("obj_sty"),
            $ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", "")
        );

        if ($context->isRepositoryNode()) {
            $tree = $this->domain->repositoryTree();
            $grp_ref_id = $tree->checkForParentType($blog->getRefId(), "grp");
            $crs_ref_id = $tree->checkForParentType($blog->getRefId(), "crs");
            if (($grp_ref_id > 0 || $crs_ref_id > 0) &&
                $context->getPermission()->getAccessHandler()->checkAccess("write", "", $blog->getRefId())) {
                $tabs->addSubTab(
                    "notifications",
                    $lng->txt("notifications"),
                    $ctrl->getLinkTargetByClass("ilobjnotificationsettingsgui", "")
                );
            }
        }

        $tabs->activateSubTab($active);
    }

    public function addLocatorItems(): void
    {
        $context = $this->context;
        $blog = $context->getObject();
        if ($blog === null) {
            return;
        }

        $this->gui->locator()->addItem(
            $blog->getTitle(),
            $this->gui->ctrl()->getLinkTargetByClass(ilObjBlogGUI::class, "preview"),
            "",
            $context->getNodeId()
        );
    }

    public function prepareHeaderAction(
        ?ilObjectListGUI $list_gui,
        bool $presentation = false
    ): ?ilObjectListGUI {
        $context = $this->context;
        if ($list_gui === null) {
            return null;
        }

        $list_gui->enableComments(false);
        $list_gui->enableNotes(false);
        if (!$presentation || $context->getObject() === null) {
            return $list_gui;
        }

        return $this->presentationHeader(
            $context->getObject(),
            $context->getPermission()
        )->get($list_gui, $context->getRequest()->getBlogPage());
    }

    protected function presentationHeader(
        \ilObjBlog $blog,
        \ILIAS\Blog\Permission\PermissionManager $permission
    ): PresentationHeaderGUI {
        return $this->gui->navigation()->presentationHeader($blog, $permission);
    }
}
