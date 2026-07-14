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

namespace ILIAS\Blog\Permission;

use ILIAS\Repository\Permission\CmdPermission;
use ILIAS\Blog\StandardGUIRequest;
use ILIAS\Repository\Permission\CmdEntity;
use ILIAS\Blog\Access\BlogAccess;
use ilRepositorySearchGUI;
use ilObjNotificationSettingsGUI;
use ILIAS\Blog\Settings\SettingsGUI;

class BlogCmdPermission extends CmdPermission
{
    protected const BLOG = "blog";
    protected const POSTING = "posting";
    protected \ilAccessHandler|\ilWorkspaceAccessHandler $access;

    public function __construct(
        protected \ilLanguage $lng,
        protected BlogAccess $perm_manager,
        ?\ilGlobalTemplateInterface $tpl = null,
        protected ?\ilCtrlInterface $ctrl = null,
        protected ?StandardGUIRequest $request = null
    ) {
        parent::__construct($lng, $tpl, $ctrl);
        $this->access = $perm_manager->getAccessHandler();
    }

    public function isCommandPermitted(
        string $cmd,                // derived or from table
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): bool {
        switch ($entity) {
            case self::BLOG:
                return $this->hasBlogCmdPerm($cmd, $node_id);
            case self::POSTING:
                return $this->hasPostingCmdPerm($cmd, $node_id, (int) $entity_id);
        }
        return false;
    }

    protected function hasBlogCmdPerm(string $cmd, int $node_id): bool
    {
        // write permission
        if (in_array($cmd, [
            "edit",
            "createExportFileWithComments",
            "createExportFile",
            "export",
            "contributors",
            "addUserFromAutoComplete",
            "addContributor",
            "confirmRemoveContributor",
            "removeContributor",
            "setContentStyleSheet",
            "exportWithComments"
        ])) {
            return $this->access->checkAccess("write", "", $node_id);
        }

        if (in_array($cmd, [
            "createPosting",
        ])) {
            return $this->perm_manager->mayContribute();
        }

        // read permission
        if (in_array($cmd, [
            "render", "preview", "addToDesk", "removeFromDesk", "renderFullscreen",
            "setNotification",
            "printViewSelection", "printPostings"
        ])) {
            return $this->access->checkAccess("read", "", $node_id);
        }

        // visible permission
        if (in_array($cmd, [
            "infoScreen"
        ])) {
            return $this->access->checkAccess("visible", "", $node_id);
        }


        return false;
    }

    protected function hasPostingCmdPerm(string $cmd, int $node_id, int $posting_id): bool
    {
        // redact or write permission
        if (in_array($cmd, [
            "approve",
            "deactivateAdmin"
        ])) {
            return $this->perm_manager->canApprove($posting_id);
        }

        return false;
    }

    public function getDefaultCommand(): string
    {
        if ($this->isClass(\ilObjBlogGUI::class)) {
            return "render";
        }
        return "";
    }

    public function getRequestEntity(): ?CmdEntity
    {
        if ($this->isClass(\ilObjBlogGUI::class)) {

            if (in_array($this->ctrl->getCmd(), ["approve", "deactivateAdmin"])) {
                $posting_id = $this->request->getApId();
                return $this->cmdEntity(
                    self::POSTING,
                    (string) $posting_id
                );
            }
            return $this->cmdEntity(
                self::BLOG
            );
        }
        return null;
    }

    public function getRequestNodeId(): int
    {
        if ($this->access instanceof \ilWorkspaceAccessHandler) {
            return $this->request->getWspId();
        }
        return $this->request->getRefId();
    }

    public function isForwardPermitted(
        string $from_class,
        string $to_class
    ): bool {
        $node_id = $this->getRequestNodeId();
        $posting_id = $this->request->getBlogPage();
        if ($from_class === \ilObjBlogGUI::class) {

            // visible or read
            if ($to_class === \ilCommonActionDispatcherGUI::class) {
                return $this->access->checkAccess("visible", "", $node_id) ||
                    $this->access->checkAccess("read", "", $node_id);
            }

            // edit permission
            if ($to_class === \ilPermissionGUI::class) {
                return $this->access->checkAccess("edit_permission", "", $node_id);
            }

            // read posting permission
            if ($to_class === \ilBlogPostingGUI::class) {
                return $this->perm_manager->canReadPosting($posting_id);
            }

            // write permission
            if (in_array($to_class, [
                \ilExportGUI::class,
                \ilRepositorySearchGUI::class,
                \ilObjectContentStyleSettingsGUI::class,
                \ilObjNotificationSettingsGUI::class,
                \ilObjectMetaDataGUI::class,
                SettingsGUI::class,
                \ILIAS\Blog\Settings\BlockSettingsGUI::class
            ])) {
                return $this->access->checkAccess("write", "", $node_id);
            }

            // read permission
            // note on ilObjectCopyGUI: This class performs the copy permission checks and
            // can act on multiple source ids
            if (in_array($to_class, [
                \ilObjectCopyGUI::class,
                \ilBlogExerciseGUI::class,
            ])) {
                return $this->access->checkAccess("read", "", $node_id);
            }

            // visible
            if ($to_class === \ilInfoScreenGUI::class) {
                return $this->access->checkAccess("visible", "", $node_id);
            }
        }
        return false;
    }

}
