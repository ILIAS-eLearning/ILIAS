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

/**
 * @ilCtrl_IsCalledBy ilSelectedItemsBlockGUI: ilColumnGUI
 */
class ilSelectedItemsBlockGUI extends ilDashboardBlockGUI
{
    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_SELECTED_ITEMS
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        $this->lng->loadLanguageModule('rep');
        $txt = $this->lng->txt("rep_fav_intro1") . "<br>";
        $txt .= sprintf(
            $this->lng->txt('rep_fav_intro2'),
            $this->getRepositoryTitle()
        ) . "<br>";
        $txt .= $this->lng->txt("rep_fav_intro3");
        $mbox = $this->ui->factory()->messageBox()->info($txt);
        $mbox = $mbox->withLinks([$this->ui->factory()->link()->standard($this->getRepositoryTitle(), ilLink::_getStaticLink(1, 'root', true))]);
        return $this->renderer->render($this->factory->panel()->standard(
            $this->getTitle(),
            $this->factory->legacy($this->renderer->render($mbox))
        ));
    }

    public function initData(): void
    {
        $provider = new ilPDSelectedItemsBlockSelectedItemsProvider($this->user);
        $data = $provider->getItems();

        $this->setData(['' => $data]);
    }

    public function getBlockType(): string
    {
        return 'pditems';
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, mixed $ref_id): void
    {
        return;
    }
}
