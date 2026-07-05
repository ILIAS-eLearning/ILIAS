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
use ilObjectListGUI;
use ILIAS\Blog\Presentation\PresentationGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Editing\EditingGUI;

/**
 * alles nur in presentation aufrufen! (rest macht nur )
 * ilObjBlogGUI->addHeaderActionForCommand (called in editing, presentation and bloggui)
 * -> addHeaderActionForCommandInternal
 *   -> initHeaderAction
 *      -> $this->initHeaderAction (ilObjectGUI)
 *   -> insertHeaderAction
 */
class PresentationHeaderGUI
{
    protected \ilObjUser $user;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjBlog $blog,
        protected PermissionManager $perm,
    ) {
        $this->user = $this->domain->user();
    }

    public function addHeaderAction(
        ?\ilObjectListGUI $list_gui
    ): void {
        $user = $this->domain->user();

        // notification
        if ($user->getId() !== ANONYMOUS_USER_ID) {
            $this->insertHeaderAction($list_gui);
        }
    }

    public function get(
        ?\ilObjectListGUI $lg,
        int $posting_id = 0
    ): ?ilObjectListGUI {
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        if ($posting_id > 0) {
            if ($this->blog->getNotesStatus()) {
                $lg->enableComments(true);
            }
            $lg->enableNotes(true);
        }
        $lg->enableTags(false);

        if (\ilNotification::hasNotification(
            \ilNotification::TYPE_BLOG,
            $this->user->getId(),
            $this->blog->getId()
        )
        ) {
            $ctrl->setParameterByClass(
                PresentationGUI::class,
                "ntf",
                "1"
            );
            $link = $ctrl->getLinkTargetByClass(
                PresentationGUI::class,
                "setNotification"
            );
            $ctrl->setParameter($this, "ntf", "");
            if (\ilNotification::hasOptOut($this->blog->getId())) {
                $lg->addCustomCommand($link, "blog_notification_toggle_off");
            }

            $lg->addHeaderIcon(
                "not_icon",
                \ilUtil::getImagePath("object/notification_on.svg"),
                $lng->txt("blog_notification_activated")
            );
        } else {
            $ctrl->setParameterByClass(PresentationGUI::class, "ntf", 2);
            $link = $ctrl->getLinkTargetByClass(PresentationGUI::class, "setNotification");
            $ctrl->setParameterByClass(PresentationGUI::class, "ntf", "");
            $lg->addCustomCommand($link, "blog_notification_toggle_on");

            $lg->addHeaderIcon(
                "not_icon",
                \ilUtil::getImagePath("object/notification_off.svg"),
                $lng->txt("blog_notification_deactivated")
            );
        }

        // #11758
        if ($this->perm->mayContribute()) {
            $edit_path = [
                \ilObjBlogGUI::class,
                EditingGUI::class
            ];
            $ctrl->setParameterByClass(EditingGUI::class, "bmn", "");
            $ctrl->setParameterByClass(EditingGUI::class, "blpg", "");
            $link = $ctrl->getLinkTargetByClass($edit_path, "");
            $lg->addCustomCommand($link, "blog_edit"); // #11868

            $posting_path = [
                \ilObjBlogGUI::class,
                EditingGUI::class,
                \ilBlogPostingGUI::class,
            ];
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $posting_id);

            if ($posting_id && $this->perm->mayEditPosting($posting_id)) {
                $link = $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, "edit");
                $lg->addCustomCommand($link, "blog_edit_posting");
            }
        }

        $ctrl->setParameterByClass(PresentationGUI::class, "ntf", "");

        return $lg;
    }
}
