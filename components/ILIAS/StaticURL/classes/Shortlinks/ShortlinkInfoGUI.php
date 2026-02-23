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
use ILIAS\GlobalScreen\GUI\AbstractPonsGUI;
use ILIAS\GlobalScreen\GUI\Flow\Command;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ShortlinkInfoGUI: ilObjStaticUrlServiceGUI
 */
class ShortlinkInfoGUI extends AbstractPonsGUI
{
    #[Command('read')]
    private function index(): void
    {
        $refinery = $this->pons->in()->refinery();
        $content = file_get_contents(__DIR__ . '/../../CONFIGURATION.md');

        $this->pons->out()->outString(
            $refinery->string()->markdown(false)->toHTML()->transform($content)
        );
    }

    public function executeCommand(): bool
    {
        if ($this->pons->handle(ilObjStaticUrlServiceGUI::TAB_INFO)) {
            return true;
        }

        match ($cmd = $this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_DEFAULT => $this->index(),
            default => $this->pons->out()->outString(
                'Unknown command: ' . $cmd
            ),
        };
        return true;
    }

    public function getTokensToKeep(): array
    {
        return [];
    }

}
