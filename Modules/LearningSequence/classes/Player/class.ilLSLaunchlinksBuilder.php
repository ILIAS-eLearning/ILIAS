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


/**
 * Builds the links to join/(re-)start the LearningSequence.
 *
 * @author   Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSLaunchlinksBuilder
{
    public const PERM_PARTICIPATE = 'participate';
    public const PERM_UNPARTICIPATE = 'unparticipate';

    public const CMD_STANDARD = ilObjLearningSequenceLearnerGUI::CMD_STANDARD;
    public const CMD_EXTRO = ilObjLearningSequenceLearnerGUI::CMD_EXTRO;
    public const CMD_START = ilObjLearningSequenceLearnerGUI::CMD_START;
    public const CMD_VIEW = ilObjLearningSequenceLearnerGUI::CMD_VIEW;
    public const CMD_UNSUBSCRIBE = ilObjLearningSequenceLearnerGUI::CMD_UNSUBSCRIBE;

    protected ilLanguage $lng;
    protected ilAccess $access;
    protected ilCtrl $ctrl;
    protected ILIAS\UI\Factory $ui_factory;
    protected int $lso_ref_id;
    protected int $usr_id;
    protected $first_access;
    protected ilLearningSequenceRoles $roles;

    public function __construct(
        ilLanguage $language,
        ilAccess $access,
        ilCtrl $ctrl,
        ILIAS\UI\Factory $ui_factory,
        int $lso_ref_id,
        int $usr_id,
        $first_access,
        ilLearningSequenceRoles $roles
    ) {
        $this->lng = $language;
        $this->access = $access;
        $this->ctrl = $ctrl;
        $this->ui_factory = $ui_factory;

        $this->lso_ref_id = $lso_ref_id;
        $this->usr_id = $usr_id;
        $this->first_access = $first_access;
        $this->roles = $roles;
    }

    protected function mayJoin(): bool
    {
        return $this->access->checkAccess(self::PERM_PARTICIPATE, '', $this->lso_ref_id);
    }


    public function currentUserMayUnparticipate(): bool
    {
        return $this->mayUnparticipate();
    }

    protected function mayUnparticipate(): bool
    {
        return $this->access->checkAccess(self::PERM_UNPARTICIPATE, '', $this->lso_ref_id);
    }

    protected function isMember(): bool
    {
        return $this->roles->isMember($this->usr_id);
    }

    protected function hasCompleted(): bool
    {
        return $this->roles->isCompletedByUser($this->usr_id);
    }

    protected function getLink(string $cmd): string
    {
        return $this->ctrl->getLinkTargetByClass('ilObjLearningSequenceLearnerGUI', $cmd);
    }

    public function getLinks(): array
    {
        $cmd = $this->ctrl->getCmd();
        $links = [];

        if (!$this->isMember() && $this->mayJoin()) {
            $links[] = [
                $this->lng->txt("lso_player_start"),
                $this->getLink(self::CMD_START)
            ];
            return $links;
        }

        if (!$this->hasCompleted()) {
            $label = "lso_player_resume";
            if ($this->first_access === -1) {
                $label = "lso_player_start";
            }
            $links[] = [
                $this->lng->txt($label),
                $this->getLink(self::CMD_VIEW)
            ];
        } else {
            $links[] = [
                $this->lng->txt("lso_player_review"),
                $this->getLink(self::CMD_VIEW)
            ];

            if ($cmd === self::CMD_STANDARD) {
                $links[] = [
                    $this->lng->txt("lso_player_extro"),
                    $this->getLink(self::CMD_EXTRO)
                ];
            }
            if ($cmd === self::CMD_EXTRO) {
                $links[] = [
                    $this->lng->txt("lso_player_abstract"),
                    $this->getLink(self::CMD_STANDARD)
                ];
            }
        }

        if ($this->mayUnparticipate()) {
            $links[] = [
                $this->lng->txt("unparticipate"),
                $this->getLink(self::CMD_UNSUBSCRIBE)
            ];
        }

        return $links;
    }

    public function getLaunchbuttonsComponent(): array
    {
        $buttons = [];
        foreach ($this->getLinks() as $idx => $entry) {
            list($label, $link) = $entry;
            $buttons[] = $this->ui_factory->button()->standard($label, $link);
        }
        return $buttons;
    }
}
