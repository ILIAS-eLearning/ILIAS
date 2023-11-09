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

use ILIAS\components\Dashboard\Block\BlockDTO;

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
        $mbox = $mbox->withLinks(
            [
                $this->ui->factory()->link()->standard(
                    $this->getRepositoryTitle(),
                    ilLink::_getStaticLink(1, 'root', true)
                )
            ]
        );
        return $this->renderer->render(
            $this->factory->panel()->standard(
                $this->getTitle(),
                $this->factory->legacy($this->renderer->render($mbox))
            )
        );
    }

    public function initData(): void
    {
        $provider = new ilPDSelectedItemsBlockSelectedItemsProvider($this->user);
        $data = $provider->getItems();
        $data = array_map(static function (array $item): BlockDTO {
            $start = isset($item['start']) && $item['start'] instanceof ilDateTime ? $item['start'] : null;
            $end = isset($item['end']) && $item['end'] instanceof ilDateTime ? $item['end'] : null;
            return new BlockDTO(
                $item['type'],
                (int) $item['ref_id'],
                (int) $item['obj_id'],
                $item['title'],
                $item['description'],
                $start,
                $end,
            );
        }, $data);

        $this->setData(['' => $data]);
    }

    public function getBlockType(): string
    {
        return 'pditems';
    }

    public function confirmedRemoveObject(): void
    {
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if ($refIds === []) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            $this->favourites_manager->remove($this->user->getId(), (int) $ref_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('pd_remove_multi_confirm'), true);
        $this->ctrl->returnToParent($this);
    }

    public function removeMultipleEnabled(): bool
    {
        return true;
    }

    public function getRemoveMultipleActionText(): string
    {
        return $this->lng->txt('pd_remove_multiple');
    }
}
