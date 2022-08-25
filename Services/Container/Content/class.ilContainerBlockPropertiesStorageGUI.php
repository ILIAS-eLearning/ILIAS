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

use ILIAS\Container\StandardGUIRequest;
use ILIAS\Container\Content\BlockSessionRepository;

/**
 * Save container block property
 * Mainly used for item group expand/collapse
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContainerBlockPropertiesStorageGUI: ilContainerBlockPropertiesStorageGUI
 */
class ilContainerBlockPropertiesStorageGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected StandardGUIRequest $request;
    protected BlockSessionRepository $block_repo;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->request =
            $DIC->container()->internal()->gui()->standardRequest();
        $this->block_repo =
            $DIC->container()->internal()->repo()->content()->block();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd();
        if (in_array($cmd, ["store"], true)) {
            $this->$cmd();
        }
    }

    public function store(): void
    {
        $ilUser = $this->user;

        switch ($this->request->getBlockAction()) {
            case "expand":
                $this->block_repo->setProperty(
                    $this->request->getBlockId(),
                    $ilUser->getId(),
                    "opened",
                    "1"
                );
                break;

            case "collapse":
                $this->block_repo->setProperty(
                    $this->request->getBlockId(),
                    $ilUser->getId(),
                    "opened",
                    "0"
                );
                break;
        }
    }
}
