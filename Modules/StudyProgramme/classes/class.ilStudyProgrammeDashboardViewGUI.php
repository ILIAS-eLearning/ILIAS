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

use ILIAS\UI\Component\Item\Item;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\Services\Dashboard\Block\BlockDTO;

class ilStudyProgrammeDashboardViewGUI extends ilDashboardBlockGUI
{
    protected ?string $visible_on_pd_mode = null;

    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_MY_STUDYPROGRAMME
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        return '';
    }

    public function initData(): void
    {
        $user_table = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserTable'];
        $user_table->disablePermissionCheck(true);
        $rows = $user_table->fetchSingleUserRootAssignments($this->user->getId());

        $items = [];
        foreach ($rows as $row) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($row->getNodeId());
            if (!$this->isReadable($prg) || !$prg->isActive()) {
                continue;
            }

            $items[] = new BlockDTO(
                $prg->getType(),
                $prg->getRefId(),
                $prg->getId(),
                $prg->getTitle(),
                $prg->getDescription(),
                null,
                null,
            );
        }

        $this->setData(['' => $items]);
    }

    protected function isReadable(ilObjStudyProgramme $prg): bool
    {
        if ($this->getVisibleOnPDMode() === ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }
        return $this->access->checkAccess('read', "", $prg->getRefId(), "prg", $prg->getId());
    }

    protected function getVisibleOnPDMode(): string
    {
        if (is_null($this->visible_on_pd_mode)) {
            $this->visible_on_pd_mode =
                $this->settings->get(
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD,
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ
                );
        }
        return $this->visible_on_pd_mode;
    }

    public function getBlockType(): string
    {
        return 'pdprg';
    }

    public function confirmedRemoveObject(): void
    {
    }

    public function removeMultipleEnabled(): bool
    {
        return false;
    }

    public function getRemoveMultipleActionText(): string
    {
        return '';
    }
}
