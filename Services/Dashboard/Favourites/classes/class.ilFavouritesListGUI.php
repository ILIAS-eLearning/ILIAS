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
 * Favourites UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFavouritesListGUI
{
    protected ILIAS\DI\UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

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
        $this->lng->loadLanguageModule('rep');
    }

    public function render(): string
    {
        $favoritesManager = new ilSelectedItemsBlockGUI();
        $f = $this->ui->factory();
        $item_groups = [];
        $ctrl = $this->ctrl;
        foreach ($favoritesManager->getItemGroups() as $key => $group) {
            $items = [];
            foreach ($group as $item) {
                $items[] = $f->item()->standard(
                    $f->link()->standard($item->getTitle(), ilLink::_getLink($item->getRefId()))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon((int) $item->getObjId()), $item->getTitle()));
            }
            if (count($items) > 0) {
                $item_groups[] = $f->item()->group($key, $items);
            }
        }
        if (count($item_groups) > 0) {
            $ctrl->setParameterByClass(ilSelectedItemsBlockGUI::class, 'view', '0');
            $ctrl->setParameterByClass(ilSelectedItemsBlockGUI::class, 'col_side', 'center');
            $ctrl->setParameterByClass(ilSelectedItemsBlockGUI::class, 'block_type', 'pditems');
            // see PR discussion at https://github.com/ILIAS-eLearning/ILIAS/pull/5247/files
            $configureModal = $favoritesManager->getConfigureModal();

            $config_item = $f->item()->standard(
                $f->button()->shy(
                    $this->lng->txt('rep_configure'),
                    $configureModal->getShowSignal()
                )
            );
            array_unshift($item_groups, $f->item()->group($this->lng->txt(''), [$config_item]));
            $panel = $f->panel()->secondary()->listing('', $item_groups);

            return $this->ui->renderer()->render([$panel, $configureModal]);
        }

        return $favoritesManager->getNoItemFoundContent();
    }
}
