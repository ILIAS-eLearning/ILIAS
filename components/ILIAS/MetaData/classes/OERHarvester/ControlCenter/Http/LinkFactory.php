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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Http;

use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;
use ilCtrlInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\Command;
use ILIAS\MetaData\OERHarvester\ControlCenter\ControlCenterGUI;

class LinkFactory implements LinkFactoryInterface
{
    public function __construct(
        protected ilCtrlInterface $ctrl
    ) {
    }

    public function getViewLink(int $ref_id, int $obj_id, string $type): string
    {
        return $this->getLinkForCommand(Command::VIEW, $ref_id, $obj_id, $type);
    }

    public function getLinkForAction(Action $action, int $ref_id, int $obj_id, string $type): string
    {
        $cmd = match ($action) {
            Action::BLOCK => Command::BLOCK,
            Action::UNBLOCK => Command::UNBLOCK,
            Action::PUBLISH => Command::PUBLISH,
            Action::WITHDRAW => Command::WITHDRAW,
            Action::SUBMIT => Command::SUBMIT,
            Action::ACCEPT => Command::ACCEPT,
            Action::REJECT => Command::REJECT
        };
        return $this->getLinkForCommand($cmd, $ref_id, $obj_id, $type);
    }

    public function getLinkForConfirmationOfAction(Action $action, int $ref_id, int $obj_id, string $type): string
    {
        $cmd = match ($action) {
            Action::WITHDRAW => Command::CONFIRM_WITHDRAW,
            Action::ACCEPT => Command::CONFIRM_ACCEPT,
            Action::REJECT => Command::CONFIRM_REJECT,
            default => null
        };
        if ($cmd === null) {
            return '';
        }
        return $this->getLinkForCommand($cmd, $ref_id, $obj_id, $type);
    }

    protected function getLinkForCommand(Command $cmd, int $ref_id, int $obj_id, string $type): string
    {
        $this->ctrl->setParameterByClass(ControlCenterGUI::class, RequestParserInterface::REF_ID_PARAM, $ref_id);
        $this->ctrl->setParameterByClass(ControlCenterGUI::class, RequestParserInterface::OBJ_ID_PARAM, $obj_id);
        $this->ctrl->setParameterByClass(ControlCenterGUI::class, RequestParserInterface::TYPE_PARAM, $type);
        $link = $this->ctrl->getLinkTargetByClass(ControlCenterGUI::class, $cmd->value, null, true);
        $this->ctrl->clearParameterByClass(ControlCenterGUI::class, RequestParserInterface::REF_ID_PARAM);
        $this->ctrl->clearParameterByClass(ControlCenterGUI::class, RequestParserInterface::OBJ_ID_PARAM);
        $this->ctrl->clearParameterByClass(ControlCenterGUI::class, RequestParserInterface::TYPE_PARAM);

        return $link;
    }
}
