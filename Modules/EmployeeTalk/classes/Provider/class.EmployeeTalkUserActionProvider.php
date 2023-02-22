<?php

declare(strict_types=1);

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

use ILIAS\DI\Container;
use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\MyStaff\ilMyStaffAccess;

final class EmployeeTalkUserActionProvider extends ilUserActionProvider
{
    public const JUMP_TO_USER_TALK_LIST = 'etal_jump_to_user_talks';

    private ilLanguage $language;
    private ilCtrl $controlFlow;
    private ilMyStaffAccess $access;

    public function __construct()
    {
        parent::__construct();

        /**
         * @var Container $container
         */
        $container = $GLOBALS['DIC'];
        $this->language = $container->language();
        $this->controlFlow = $container->ctrl();
        $this->access = ilMyStaffAccess::getInstance();

        $this->language->loadLanguageModule('etal');
    }

    public function collectActionsForTargetUser(int $a_target_user): ilUserActionCollection
    {
        $actions = ilUserActionCollection::getInstance();

        if ($this->hasAccess($a_target_user)) {
            $jumpToUserTalkList = new ilUserAction();
            $jumpToUserTalkList->setType(self::JUMP_TO_USER_TALK_LIST);
            $jumpToUserTalkList->setText($this->language->txt('mm_org_etal'));
            $jumpToUserTalkList->setHref($this->controlFlow->getLinkTargetByClass([
                    strtolower(ilDashboardGUI::class),
                    strtolower(ilMyStaffGUI::class),
                    strtolower(ilMStShowUserGUI::class),
                    strtolower(ilEmployeeTalkMyStaffUserGUI::class),
                ], ControlFlowCommand::INDEX) . "&usr_id=$a_target_user");

            $actions->addAction($jumpToUserTalkList);
        }

        return $actions;
    }

    public function getComponentId(): string
    {
        return "etal";
    }

    public function getActionTypes(): array
    {
        return [
            self::JUMP_TO_USER_TALK_LIST => $this->language->txt('mm_org_etal')
        ];
    }

    protected function hasAccess(int $a_target_user): bool
    {
        if (!$a_target_user) {
            return false;
        }

        if (
            !$this->access->hasCurrentUserAccessToTalks() ||
            !$this->access->hasCurrentUserAccessToUser($a_target_user)
        ) {
            return false;
        }

        return true;
    }
}
