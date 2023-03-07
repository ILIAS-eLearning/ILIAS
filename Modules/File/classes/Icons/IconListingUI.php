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

namespace ILIAS\File\Icon;

use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\UI\Component\Input\Container\Filter\Filter;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class IconListingUI
{
    private \ilCtrl $ctrl;
    private \ilLanguage $lng;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\ResourceStorage\Services $storage;
    private array $deletion_modals = [];
    private \ILIAS\UI\Component\Panel\Listing\Standard $icon_list;
    private \ilUIFilterService $filter_service;
    private \ILIAS\UI\Component\Input\Container\Filter\Standard $filter;
    private \ILIAS\HTTP\Services $http;

    public function __construct(
        private IconRepositoryInterface $icon_repo,
        private object $gui
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('file');
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();
        $this->storage = $DIC->resourceStorage();
        $this->http = $DIC->http();
        $this->filter_service = $DIC->uiService()->filter();

        $this->initFilter();
        $this->initListing();
    }

    private function initFilter(): void
    {
        // Filters
        $this->filter = $this->filter_service->standard(
            $this->gui::class . '_filter7',
            $this->ctrl->getLinkTarget($this->gui),
            [
                'suffixes' => $this->ui_factory->input()->field()->text($this->lng->txt('suffixes')),
                'active' => $this->ui_factory->input()->field()->select($this->lng->txt('status'), [
                    '1' => $this->lng->txt('active'),
                    '0' => $this->lng->txt('inactive'),
                ])->withValue('1'),
                'is_default_icon' => $this->ui_factory->input()->field()->select($this->lng->txt('default'), [
                    '1' => $this->lng->txt('yes'),
                    '0' => $this->lng->txt('no'),
                ])
            ],
            [
                'suffix' => true,
                'status' => true,
                'type' => true,
            ],
            true,
            true
        );
    }

    public function getFilter(): \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
        return $this->filter;
    }

    protected function initListing(): void
    {
        $icon_items = [];
        $this->icon_repo->getIcons();

        // Filterting
        $filter_data = $this->filter_service->getData($this->filter) ?? [];

        foreach ($this->icon_repo->getIconsForFilter($filter_data) as $icon) {
            $this->ctrl->setParameterByClass(ilObjFileIconsOverviewGUI::class, ilObjFileIconsOverviewGUI::P_RID, $icon->getRid());
            $edit_target = $this->ctrl->getLinkTargetByClass(
                ilObjFileIconsOverviewGUI::class,
                ilObjFileIconsOverviewGUI::CMD_OPEN_UPDATING_FORM
            );
            $change_activation_target = $this->ctrl->getLinkTargetByClass(
                ilObjFileIconsOverviewGUI::class,
                ilObjFileIconsOverviewGUI::CMD_CHANGE_ACTIVATION
            );
            $this->deletion_modals[] = $deletion_modal = $this->getDeletionConfirmationModal($icon);

            $item_action_entries = [
                $this->ui_factory->button()->shy(
                    $this->lng->txt('de_activate_icon'),
                    $change_activation_target
                )
            ];
            if (!$icon->isDefaultIcon()) {
                $item_action_entries[] = $this->ui_factory->button()->shy($this->lng->txt('edit'), $edit_target);
                $item_action_entries[] = $this->ui_factory->button()->shy($this->lng->txt('delete'), '#')->withOnClick(
                    $deletion_modal->getShowSignal()
                );
            }
            $item_actions = $this->ui_factory->dropdown()->standard($item_action_entries);

            $id = $this->storage->manage()->find($icon->getRid());
            $icon_src = "";
            if ($id !== null) {
                $icon_src = $this->storage->consume()->src($id)->getSrc();
            }

            $suffixes_array_into_string = $this->icon_repo->turnSuffixesArrayIntoString(
                $icon->getSuffixes()
            );

            $icon_image = $this->ui_factory->symbol()->icon()->custom(
                $icon_src,
                "Icon $suffixes_array_into_string"
            )->withSize('large');

            $icon_items[] = $this->ui_factory->item()
                                             ->standard($this->lng->txt('icon') . " $suffixes_array_into_string")
                                             ->withActions($item_actions)
                                             ->withProperties(
                                                 [
                                                     $this->lng->txt('active') => $icon->isActive() ? $this->lng->txt(
                                                         'yes'
                                                     ) : $this->lng->txt('no'),
                                                     $this->lng->txt('default') => $icon->isDefaultIcon(
                                                     ) ? $this->lng->txt('yes') : $this->lng->txt('no'),
                                                     $this->lng->txt(
                                                         'suffixes'
                                                     ) => $suffixes_array_into_string
                                                 ]
                                             )
                                             ->withLeadIcon($icon_image);
        }

        $this->icon_list = $this->ui_factory->panel()->listing()->standard(
            $this->lng->txt('suffix_specific_icons'),
            [$this->ui_factory->item()->group("", $icon_items)]
        );
    }

    public function getIconList(): \ILIAS\UI\Component\Panel\Listing\Standard
    {
        return $this->icon_list;
    }

    /**
     * @return mixed[]
     */
    public function getDeletionModals(): array
    {
        return $this->deletion_modals;
    }

    private function getDeletionConfirmationModal(Icon $a_icon): Interruptive
    {
        $target = $this->ctrl->getLinkTargetByClass(
            ilObjFileIconsOverviewGUI::class,
            ilObjFileIconsOverviewGUI::CMD_DELETE
        );
        $rid = $this->refinery->kindlyTo()->string()->transform($a_icon->getRid());

        $id = $this->storage->manage()->find($rid);
        $icon_src = "";
        if ($id !== null) {
            $icon_src = $this->storage->consume()->src($id)->getSrc();
        }
        $img_icon = $this->ui_factory->image()->standard(
            $icon_src,
            "Icon"
        );
        $txt_suffixes = $this->lng->txt('suffixes') . ": " . $this->icon_repo->turnSuffixesArrayIntoString(
            $a_icon->getSuffixes()
        );

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt("delete"),
            $this->lng->txt('msg_confirm_entry_deletion'),
            $target
        )->withAffectedItems([
            $this->ui_factory->modal()->interruptiveItem(
                $rid,
                $txt_suffixes,
                $img_icon
            )
        ]);
    }
}
