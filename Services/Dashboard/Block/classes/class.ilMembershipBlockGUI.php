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

class ilMembershipBlockGUI extends ilDashboardBlockGUI
{
    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_MY_MEMBERSHIPS
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        return $this->renderer->render($this->factory->panel()->standard(
            $this->getTitle(),
            $this->factory->legacy($this->lng->txt("rep_mo_mem_dash"))
        ));
    }

    public function initData(): void
    {
        $provider = new ilPDSelectedItemsBlockMembershipsProvider($this->user);
        $data = $provider->getItems();

        $this->setData(['' => $data]);
    }

    public function getBlockType(): string
    {
        return 'pdmem';
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, mixed $ref_id): void
    {
        return;
    }
}
