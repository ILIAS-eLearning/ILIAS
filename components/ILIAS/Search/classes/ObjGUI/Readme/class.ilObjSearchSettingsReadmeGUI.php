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

use ILIAS\FileDelivery\Services as FileDelivery;
use ILIAS\Filesystem\Stream\Streams;

class ilObjSearchSettingsReadmeGUI
{
    protected const string PATH = __DIR__ . '/../../../../WebServices/RPC/lib/README.md';

    protected ilCtrlInterface $ctrl;
    protected FileDelivery $file_delivery;

    public function __construct(
        ilCtrlInterface $ctrl,
        FileDelivery $file_delivery
    ) {
        $this->ctrl = $ctrl;
        $this->file_delivery = $file_delivery;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'deliverFile':
                $this->deliverFile();
                break;

            default:
                throw new ilObjSearchSettingsGUIException(
                    'Invalid command for ilObjSearchSettingsReadmeGUI: ' . $cmd
                );
        }
    }

    protected function deliverFile(): void
    {
        $this->file_delivery->delivery()->inline(
            Streams::ofResource(fopen(self::PATH, 'rb')),
            basename(self::PATH),
            'text/markdown'
        );
    }
}
