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

namespace Portfolio\Permission;

use ILIAS\Portfolio\StandardGUIRequest;
use ILIAS\Repository\Permission\CmdEntity;
use ILIAS\Repository\Permission\CmdPermission;

class PortfolioCmdPermission extends CmdPermission
{
    protected const PORTFOLIO = "portfolio";
    protected const PORTFOLIO_PAGE = "portfolio_page";
    protected const PORTFOLIO_TEMPLATE = "portfolio_template";
    protected const PORTFOLIO_TEMPLATE_PAGE = "portfolio_template_page";

    protected ?\ilAccessHandler $repo_access;
    protected ?\ilPortfolioAccessHandler $portfolio_access;

    public function __construct(
        \ilLanguage $lng,
        ?\ilPortfolioAccessHandler $portfolio_access,
        ?\ilAccessHandler $repo_access,
        ?\ilGlobalTemplateInterface $tpl = null,
        protected ?\ilCtrlInterface $ctrl = null,
        protected ?StandardGUIRequest $request = null,
        protected int $node_id = 0
    ) {
        parent::__construct($lng, $tpl, $ctrl);
        $this->repo_access = $repo_access;
        $this->portfolio_access = $portfolio_access;
    }

    public function isCommandPermitted(
        string $cmd,
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): bool {
        if ($entity === self::PORTFOLIO) {
            return $this->hasPortfolioCmdPerm($cmd, $node_id);
        }

        if ($entity === self::PORTFOLIO_PAGE) {
            return $this->portfolioPageBelongsTo($node_id, (int) $entity_id)
                && $this->hasPortfolioPageCmdPerm($cmd, $node_id);
        }

        if ($entity === self::PORTFOLIO_TEMPLATE) {
            return $this->hasPortfolioTemplateCmdPerm($cmd, $node_id);
        }

        if ($entity === self::PORTFOLIO_TEMPLATE_PAGE) {
            return $this->portfolioTemplatePageBelongsTo($node_id, (int) $entity_id)
                && $this->hasPortfolioTemplatePageCmdPerm($cmd, $node_id);
        }

        return false;
    }

    protected function hasPortfolioCmdPerm(string $cmd, int $node_id): bool
    {
        if (in_array($cmd, [
            "create",
            "createFromTemplate",
            "save",
            "saveFromTemplate",
            "createPortfolioFromAssignment",
            "createPortfolioFromTemplate",
            "createPortfolioFromTemplateProcess",
            "createFromTemplateDirect"
        ], true)) {
            return $node_id === 0 && ($this->portfolio_access?->canCreatePortfolio() ?? false);
        }

        if ($cmd === "toRepository") {
            return true;
        }

        if (in_array($cmd, [
            "preview",
        ], true)) {
            return $this->portfolio_access?->checkAccess("read", "", $node_id) ?? false;
        }

        if (in_array($cmd, [
            "view",
            "printSelection",
            "showPrintView",
            "edit",
            "update",
            "export",
            "addPage",
            "savePage",
            "addBlog",
            "saveBlog",
            "exportWithComments",
            "setContentStyleSheet",
            "setOnlineAndShare"
        ], true)) {
            return $this->portfolio_access?->checkAccess("write", "", $node_id) ?? false;
        }

        if ($cmd === "infoScreen") {
            return $this->portfolio_access?->checkAccess("visible", "", $node_id) ?? false;
        }

        return false;
    }

    protected function hasPortfolioPageCmdPerm(string $cmd, int $node_id): bool
    {
        if (in_array($cmd, [
            "edit",
            "savePortfolioPagesOrdering",
            "confirmPortfolioPageDeletion",
            "deletePortfolioPages",
            "copyPageForm",
            "copyPage",
            "setOnlineAndShare"
        ], true)) {
            if (!($this->portfolio_access?->checkAccess("write", "", $node_id) ?? false)) {
                return false;
            }

            if ($cmd === "copyPage") {
                $target_id = $this->request?->getPortfolioTargetId() ?? 0;
                if ($target_id > 0 && !($this->portfolio_access?->checkAccess("write", "", $target_id) ?? false)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    protected function hasPortfolioTemplateCmdPerm(string $cmd, int $node_id): bool
    {
        if (in_array($cmd, ["create", "save"])) {
            return $this->repo_access?->checkAccess("create", "", $node_id, "prtt") ?? false;
        }
        if (in_array($cmd, [
            "preview",
            "addPage",
            "savePage",
            "createFromTemplate",
            "printSelection",
            "showPrintView"
        ], true)) {
            return $this->repo_access?->checkAccess("read", "", $node_id) ?? false;
        }

        if (in_array($cmd, ["view", "edit", "update", "addPage", "savePage", "export", "exportWithComments", "setContentStyleSheet", "copyPageForm"], true)) {
            return $this->repo_access?->checkAccess("write", "", $node_id) ?? false;
        }

        if ($cmd === "infoScreen") {
            return $this->repo_access?->checkAccess("visible", "", $node_id) ?? false;
        }

        return false;
    }

    protected function hasPortfolioTemplatePageCmdPerm(string $cmd, int $node_id): bool
    {
        if (in_array($cmd, [
            "edit",
            "savePortfolioPagesOrdering",
            "confirmPortfolioPageDeletion",
            "deletePortfolioPages",
            "copyPageForm",
            "copyPage"
        ], true)) {
            if (!($this->repo_access?->checkAccess("write", "", $node_id) ?? false)) {
                return false;
            }

            if ($cmd === "copyPage") {
                $target_id = $this->request?->getPortfolioTargetId() ?? 0;
                return $target_id > 0 && $this->hasWriteAccessForTemplate($target_id);
            }

            return true;
        }

        return in_array($cmd, ["preview", "showPage", "showPageEmbedded"], true)
            && ($this->repo_access?->checkAccess("read", "", $node_id) ?? false);
    }

    protected function hasWriteAccessForTemplate(int $template_id): bool
    {
        foreach (\ilObject::_getAllReferences($template_id) as $ref_id) {
            if ($this->repo_access?->checkAccess("write", "", $ref_id) ?? false) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultCommand(): string
    {
        if ($this->isClass(\ilObjPortfolioGUI::class)) {
            return "view";
        }

        return $this->isClass(\ilObjPortfolioTemplateGUI::class) ? "preview" : "";
    }

    public function getRequestEntity(): ?CmdEntity
    {
        if ((!$this->isClass(\ilObjPortfolioGUI::class)
                && !$this->isClass(\ilObjPortfolioTemplateGUI::class))
            || $this->request === null) {
            return null;
        }

        $cmd = $this->getRequestCommand();
        $is_template = $this->isClass(\ilObjPortfolioTemplateGUI::class);
        $page_entity = $is_template ? self::PORTFOLIO_TEMPLATE_PAGE : self::PORTFOLIO_PAGE;
        $object_entity = $is_template ? self::PORTFOLIO_TEMPLATE : self::PORTFOLIO;

        if ($cmd === "savePortfolioPagesOrdering") {
            $order = $this->request->getOrder();
            $page_ids = array_map("intval", array_keys($order));
            if ($this->portfolioPageIdsBelongToRequestObject(
                $page_ids,
                $is_template,
                $this->getRequestNodeId()
            )) {
                return $this->cmdEntity($page_entity, (string) $page_ids[0]);
            }
        }

        if (in_array($cmd, [
            "confirmPortfolioPageDeletion",
            "deletePortfolioPages",
            "copyPageForm",
            "copyPage"
        ], true)) {
            $page_ids = $this->request->getPortfolioPageIds();
            if ($this->portfolioPageIdsBelongToRequestObject(
                $page_ids,
                $is_template,
                $this->getRequestNodeId()
            )) {
                return $this->cmdEntity($page_entity, (string) $page_ids[0]);
            }
        }
        return $this->cmdEntity($object_entity);
    }

    protected function portfolioPageIdsBelongToRequestObject(
        array $page_ids,
        bool $is_template,
        int $node_id
    ): bool {
        if ($page_ids === []) {
            return false;
        }

        foreach ($page_ids as $page_id) {
            $page_id = (int) $page_id;
            $belongs_to_object = $is_template
                ? $this->portfolioTemplatePageBelongsTo($node_id, $page_id)
                : $this->portfolioPageBelongsTo($node_id, $page_id);
            if (!$belongs_to_object) {
                return false;
            }
        }

        return true;
    }

    public function getRequestNodeId(): int
    {
        $request_node_id = $this->isClass(\ilObjPortfolioTemplateGUI::class)
            ? ($this->request?->getRefId() ?? 0)
            : ($this->request?->getPortfolioId() ?? 0);
        return $request_node_id > 0 ? $request_node_id : $this->node_id;
    }

    public function isForwardPermitted(string $from_class, string $to_class): bool
    {
        if ($from_class === \ilObjPortfolioTemplateGUI::class) {
            return $this->isPortfolioTemplateForwardPermitted($to_class);
        }

        if ($from_class !== \ilObjPortfolioGUI::class) {
            return false;
        }

        $node_id = $this->getRequestNodeId();
        if ($to_class === \ilPortfolioPageGUI::class) {
            $page_id = $this->request?->getPortfolioPageId() ?? 0;
            $is_edit = $page_id > 0;
            if (!$is_edit) {
                $page_id = $this->request?->getUserPage() ?? 0;
            }
            if (!$this->portfolioPageBelongsTo($node_id, $page_id)) {
                return false;
            }
            return $this->portfolio_access?->checkAccess($is_edit ? "write" : "read", "", $node_id) ?? false;
        }

        if ($to_class === \ilCommentGUI::class) {
            $page_id = $this->request?->getUserPage() ?? 0;
            return ($page_id === 0 || $this->portfolioPageBelongsTo($node_id, $page_id))
                && ($this->portfolio_access?->checkAccess("read", "", $node_id) ?? false);
        }

        if ($to_class === \ilCommonActionDispatcherGUI::class) {
            return ($this->portfolio_access?->checkAccess("visible", "", $node_id) ?? false)
                || ($this->portfolio_access?->checkAccess("read", "", $node_id) ?? false);
        }

        if ($to_class === \ilPortfolioExerciseGUI::class) {
            return $this->portfolioAssignmentBelongsTo(
                $node_id,
                $this->request?->getExcAssId() ?? 0
            ) && ($this->portfolio_access?->checkAccess("write", "", $node_id) ?? false);
        }

        if (in_array($to_class, [
            \ilWorkspaceAccessGUI::class,
            \ilObjectContentStyleSettingsGUI::class
        ], true)) {
            return $this->portfolio_access?->checkAccess("write", "", $node_id) ?? false;
        }

        return false;
    }

    protected function portfolioPageBelongsTo(int $node_id, int $page_id): bool
    {
        return $node_id > 0
            && $page_id > 0
            && \ilPortfolioPage::findPortfolioForPage($page_id) === $node_id;
    }

    protected function portfolioAssignmentBelongsTo(int $node_id, int $assignment_id): bool
    {
        if ($node_id <= 0 || $assignment_id <= 0) {
            return false;
        }

        $owner_id = \ilObjPortfolio::_lookupOwner($node_id);
        if ($owner_id <= 0) {
            return false;
        }

        foreach ((new \ilPortfolioExercise($owner_id, $node_id))->getAssignmentsOfPortfolio() as $assignment) {
            if ((int) $assignment["ass_id"] === $assignment_id) {
                return true;
            }
        }

        return false;
    }

    protected function isPortfolioTemplateForwardPermitted(string $to_class): bool
    {
        $node_id = $this->getRequestNodeId();
        if ($to_class === \ilPortfolioTemplatePageGUI::class) {
            $page_id = $this->request?->getPortfolioPageId() ?? 0;
            $is_edit = $page_id > 0;
            if (!$is_edit) {
                $page_id = $this->request?->getUserPage() ?? 0;
            }

            return $this->portfolioTemplatePageBelongsTo($node_id, $page_id)
                && ($this->repo_access?->checkAccess($is_edit ? "write" : "read", "", $node_id) ?? false);
        }

        if ($to_class === \ilCommentGUI::class) {
            $page_id = $this->request?->getUserPage() ?? 0;
            return ($page_id === 0 || $this->portfolioTemplatePageBelongsTo($node_id, $page_id))
                && ($this->repo_access?->checkAccess("read", "", $node_id) ?? false);
        }

        if ($to_class === \ilInfoScreenGUI::class) {
            return $this->repo_access?->checkAccess("visible", "", $node_id) ?? false;
        }

        if ($to_class === \ilCommonActionDispatcherGUI::class) {
            return ($this->repo_access?->checkAccess("visible", "", $node_id) ?? false)
                || ($this->repo_access?->checkAccess("read", "", $node_id) ?? false);
        }

        if ($to_class === \ilPermissionGUI::class) {
            return $this->repo_access?->checkAccess("edit_permission", "", $node_id) ?? false;
        }

        if ($to_class === \ilObjectCopyGUI::class) {
            return $this->repo_access?->checkAccess("read", "", $node_id) ?? false;
        }

        return in_array($to_class, [
            \ilExportGUI::class,
            \ilObjectContentStyleSettingsGUI::class,
            \ilObjectMetaDataGUI::class
        ], true) && ($this->repo_access?->checkAccess("write", "", $node_id) ?? false);
    }

    protected function portfolioTemplatePageBelongsTo(int $node_id, int $page_id): bool
    {
        if ($node_id <= 0 || $page_id <= 0) {
            return false;
        }

        $template_id = \ilObject::_lookupObjId($node_id);
        return $template_id > 0
            && \ilPortfolioPage::findPortfolioForPage($page_id) === $template_id;
    }
}
