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

namespace ILIAS\Services\ResourceStorage\Resources\UI\Actions;

use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\UI\Component\Modal\Modal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @internal
 */
class OverviewActionGenerator implements ActionGenerator
{
    private \ILIAS\UI\Factory $ui_factory;
    private \ilLanguage $language;
    private \ilCtrlInterface $ctrl;
    private array $collected_modals = [];
    private \ILIAS\ResourceStorage\Services $irss;

    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->language = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->irss = $DIC->resourceStorage();
    }

    public function getActionsForRevision(
        Revision $revision
    ): array {
        $this->ctrl->setParameterByClass(
            \ilResourceOverviewGUI::class,
            \ilResourceOverviewGUI::P_RESOURCE_ID,
            $revision->getIdentification()->serialize()
        );
        $actions = [
            $this->ui_factory->button()->shy(
                $this->language->txt('action_show_revisions'),
                $this->ctrl->getLinkTargetByClass(
                    \ilResourceOverviewGUI::class,
                    \ilResourceOverviewGUI::CMD_SHOW_REVISIONS
                )
            ),
            $this->ui_factory->button()->shy(
                $this->language->txt('action_goto'),
                $this->ctrl->getLinkTargetByClass(
                    \ilResourceOverviewGUI::class,
                    \ilResourceOverviewGUI::CMD_GOTO_RESOURCE
                )
            ),
            $this->ui_factory->button()->shy(
                $this->language->txt('action_download'),
                $this->ctrl->getLinkTargetByClass(
                    \ilResourceOverviewGUI::class,
                    \ilResourceOverviewGUI::CMD_DOWNLOAD
                )
            ),
        ];
        $resource = $this->irss->manage()->getResource($revision->getIdentification());
        if ($resource->getStakeholders() === []) {
            $this->collected_modals[] = $modal = $this->getRemoveConfirmationModal($revision);
            $actions[] = $this->ui_factory->button()->shy(
                $this->language->txt('action_remove_resource'),
                '#'
            )->withOnClick($modal->getShowSignal());
        }

        return $actions;
    }


    public function getCollectedModals(): array
    {
        return $this->collected_modals;
    }


    private function getRemoveConfirmationModal(Revision $revision): Modal
    {
        $action = $this->ctrl->getLinkTargetByClass(
            \ilResourceOverviewGUI::class,
            \ilResourceOverviewGUI::CMD_REMOVE
        );
        return $this->ui_factory->modal()->interruptive(
            $this->language->txt(\ilResourceOverviewGUI::CMD_REMOVE),
            $this->language->txt('confirm_delete'),
            $action
        )->withAffectedItems([
            $this->ui_factory->modal()->interruptiveItem(
                $revision->getIdentification()->serialize(),
                $revision->getInformation()->getTitle(),
            )
        ]);
    }
}
