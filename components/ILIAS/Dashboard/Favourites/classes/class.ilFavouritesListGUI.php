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

class ilFavouritesListGUI extends ilSelectedItemsBlockGUI
{
    protected ILIAS\DI\UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilSelectedItemsBlockGUI $favoritesManager;

    public function __construct(?ilObjUser $user = null)
    {
        global $DIC;

        if (is_null($user)) {
            $user = $DIC->user();
        }

        $settings = new ilPDSelectedItemsBlockViewSettings($user);
        $settings->parse();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->favoritesManager = new ilSelectedItemsBlockGUI();
        $this->lng->loadLanguageModule('rep');

    }

    public function render(): string
    {
        $f = $this->ui->factory();
        $item_groups = [];
        $ctrl = $this->ctrl;
        foreach ($this->favoritesManager->getItemGroups() as $key => $group) {
            $items = [];
            foreach ($group as $item) {
                $items[] = $f->item()->standard(
                    $f->link()->standard($item->getTitle(), ilLink::_getLink($item->getRefId()))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon((int) $item->getObjId()), $item->getTitle()));
            }
            if (count($items) > 0) {
                $item_groups[] = $f->item()->group((string) $key, $items);
            }
        }
        if (count($item_groups) > 0) {
            $configureModal = $this->favoritesManager->getRemoveModal();

            $config_item = $f->item()->standard(
                $f->button()->shy(
                    $this->favoritesManager->getRemoveMultipleActionText(),
                    $configureModal->getShowSignal()
                )
            );
            array_unshift($item_groups, $f->item()->group($this->lng->txt(''), [$config_item]));
            $panel = $f->panel()->secondary()->listing('', $item_groups);

            return $this->ui->renderer()->render([$panel, $configureModal]);
        }

        return $this->favoritesManager->getNoItemFoundContent();
    }
}
