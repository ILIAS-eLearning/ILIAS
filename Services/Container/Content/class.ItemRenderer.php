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

namespace ILIAS\Containter\Content;

use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalGUIService;

/**
 * @todo currently too fat for a renderer, more a GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemRenderer
{
    public const CHECKBOX_NONE = 0;
    public const CHECKBOX_ADMIN = 1;
    public const CHECKBOX_DOWNLOAD = 2;

    protected \ilContainerGUI $container_gui;
    protected \ilContainer $container;
    protected string $view_mode;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;
    protected array $list_gui = [];

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        string $view_mode,
        \ilContainerGUI $container_gui
    ) {
        $this->domain = $domain;    // setting and access (visible, read, write access)
        $this->gui = $gui;          // getting ilCtrl
        /** @var \ilContainer $container */
        $container = $container_gui->getObject();
        $this->view_mode = $view_mode;      // tile/list (of container)
        $this->container_gui = $container_gui;      // tile/list (of container)
        /** @var \ilContainer $container */
        $container = $container_gui->getObject();
        $this->container = $container;              // id, refid, other stuff
    }

    /**
     * Render an item
     * @return \ILIAS\UI\Component\Card\RepositoryObject|string|null
     */
    public function renderItem(
        array $a_item_data,
        int $a_position = 0,
        bool $a_force_icon = false,
        string $a_pos_prefix = "",
        string $item_group_list_presentation = "",
        int $checkbox = self::CHECKBOX_NONE,
        bool $item_ordering = false,
        int $details_level = \ilObjectListGUI::DETAILS_ALL
    ) {
        $ilSetting = $this->domain->settings();
        $ilAccess = $this->domain->access();
        $ilCtrl = $this->gui->ctrl();

        // Pass type, obj_id and tree to checkAccess method to improve performance
        /* deactivated, this should have been checked before
        if (!$ilAccess->checkAccess('visible', '', $a_item_data['ref_id'], $a_item_data['type'], $a_item_data['obj_id'], $a_item_data['tree'])) {
            return '';
        }*/

        $view_mode = $this->view_mode;
        if ($item_group_list_presentation != "") {
            $view_mode = ($item_group_list_presentation === "tile")
                ? \ilContainerContentGUI::VIEW_MODE_TILE
                : \ilContainerContentGUI::VIEW_MODE_LIST;
        }

        if ($view_mode == \ilContainerContentGUI::VIEW_MODE_TILE) {
            return $this->renderCard($a_item_data, $a_position, $a_force_icon, $a_pos_prefix);
        }

        $item_list_gui = $this->getItemGUI($a_item_data);
        if ($ilSetting->get("icon_position_in_lists") === "item_rows" ||
            $a_item_data["type"] === "sess" || $a_force_icon) {
            $item_list_gui->enableIcon(true);
        }
        if ($checkbox === self::CHECKBOX_ADMIN) {
            $item_list_gui->enableCheckbox(true);
        } elseif ($checkbox === self::CHECKBOX_DOWNLOAD) {
            // display multi download checkboxes
            $item_list_gui->enableDownloadCheckbox((int) $a_item_data["ref_id"]);
        }

        if ($item_ordering && $a_item_data['type'] !== 'sess') {
            $item_list_gui->setPositionInputField(
                $a_pos_prefix . "[" . $a_item_data["ref_id"] . "]",
                sprintf('%d', $a_position * 10)
            );
        }

        if ($a_item_data['type'] === 'sess') {
            switch ($details_level) {
                case \ilContainerContentGUI::DETAILS_TITLE:
                    $item_list_gui->setDetailsLevel(\ilObjectListGUI::DETAILS_MINIMAL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(false);
                    $item_list_gui->enableDescription(false);
                    $item_list_gui->enableProperties(true);
                    break;

                case \ilContainerContentGUI::DETAILS_ALL:
                    $item_list_gui->setDetailsLevel(\ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->setExpanded(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;

                case \ilContainerContentGUI::DETAILS_DEACTIVATED:
                    break;

                default:
                    $item_list_gui->setDetailsLevel(\ilObjectListGUI::DETAILS_ALL);
                    $item_list_gui->enableExpand(true);
                    $item_list_gui->enableDescription(true);
                    $item_list_gui->enableProperties(true);
                    break;
            }
        }

        if (method_exists($this, "addItemDetails")) {
            $this->addItemDetails($item_list_gui, $a_item_data);
        }

        // show subitems of sessions
        if ($a_item_data['type'] === 'sess' and (
            $details_level !== \ilContainerContentGUI::DETAILS_TITLE or
            $this->container_gui->isActiveAdministrationPanel() or
            $this->container_gui->isActiveItemOrdering()
        )
        ) {
            $pos = 1;

            $items = \ilObjectActivation::getItemsByEvent((int) $a_item_data['obj_id']);
            $items = \ilContainerSorting::_getInstance($this->container->getId())->sortSubItems('sess', (int) $a_item_data['obj_id'], $items);
            $items = \ilContainer::getCompleteDescriptions($items);

            $item_readable = $ilAccess->checkAccess('read', '', (int) $a_item_data['ref_id']);

            foreach ($items as $item) {
                // TODO: this should be removed and be handled by if(strlen($sub_item_html))
                // 	see mantis: 0003944
                if (!$ilAccess->checkAccess('visible', '', (int) $item['ref_id'])) {
                    continue;
                }

                $item_list_gui2 = $this->getItemGUI($item);
                $item_list_gui2->enableIcon(true);
                $item_list_gui2->enableItemDetailLinks(false);

                // unique js-ids
                $item_list_gui2->setParentRefId((int) ($a_item_data['ref_id'] ?? 0));

                // @see mantis 10488
                if (!$item_readable and !$ilAccess->checkAccess('write', '', $item['ref_id'])) {
                    $item_list_gui2->forceVisibleOnly(true);
                }

                if ($checkbox === self::CHECKBOX_ADMIN) {
                    $item_list_gui2->enableCheckbox(true);
                } elseif ($checkbox === self::CHECKBOX_DOWNLOAD) {
                    // display multi download checkbox
                    $item_list_gui2->enableDownloadCheckbox((int) $item['ref_id']);
                }

                if ($this->container_gui->isActiveItemOrdering()) {
                    $item_list_gui2->setPositionInputField(
                        "[sess][" . $a_item_data['obj_id'] . "][" . $item["ref_id"] . "]",
                        sprintf('%d', $pos * 10)
                    );
                    $pos++;
                }

                // #10611
                \ilObjectActivation::addListGUIActivationProperty($item_list_gui2, $item);

                $sub_item_html = $item_list_gui2->getListItemHTML(
                    (int) $item['ref_id'],
                    (int) $item['obj_id'],
                    $item['title'],
                    $item['description']
                );

                if (strlen($sub_item_html)) {
                    $item_list_gui->addSubItemHTML($sub_item_html);
                }
            }
        }

        $asynch = false;
        $asynch_url = '';
        if ($ilSetting->get("item_cmd_asynch")) {
            $asynch = true;
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", $a_item_data['ref_id']);
            $asynch_url = $ilCtrl->getLinkTarget(
                $this->container_gui,
                "getAsynchItemList",
                "",
                true,
                false
            );
            $ilCtrl->setParameter($this->container_gui, "cmdrefid", "");
        }

        \ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_item_data);

        $html = $item_list_gui->getListItemHTML(
            (int) $a_item_data['ref_id'],
            (int) $a_item_data['obj_id'],
            (string) $a_item_data['title'],
            (string) $a_item_data['description'],
            $asynch,
            false,
            $asynch_url
        );

        return $html;
    }

    public function renderCard(
        array $a_item_data,
        int $a_position = 0,
        bool $a_force_icon = false,
        string $a_pos_prefix = ""
    ): ?\ILIAS\UI\Component\Card\RepositoryObject {
        $item_list_gui = $this->getItemGUI($a_item_data);
        $item_list_gui->setAjaxHash(\ilCommonActionDispatcherGUI::buildAjaxHash(
            \ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            (int) $a_item_data['ref_id'],
            $a_item_data['type'],
            (int) $a_item_data['obj_id']
        ));
        $item_list_gui->initItem(
            (int) $a_item_data['ref_id'],
            (int) $a_item_data['obj_id'],
            $a_item_data['type'],
            $a_item_data['title'],
            $a_item_data['description']
        );

        // actions
        $item_list_gui->insertCommands();
        return $item_list_gui->getAsCard(
            (int) $a_item_data['ref_id'],
            (int) $a_item_data['obj_id'],
            (string) $a_item_data['type'],
            (string) $a_item_data['title'],
            (string) $a_item_data['description']
        );
    }

    public function getItemGUI(array $item_data): \ilObjectListGUI
    {
        // get item list gui object
        if (!isset($this->list_gui[$item_data["type"]])) {
            $item_list_gui = \ilObjectListGUIFactory::_getListGUIByType($item_data["type"]);
            $item_list_gui->setContainerObject($this->container_gui);
            $this->list_gui[$item_data["type"]] = $item_list_gui;
        } else {
            $item_list_gui = $this->list_gui[$item_data["type"]];
        }

        // unique js-ids
        $item_list_gui->setParentRefId((int) ($item_data["parent"] ?? 0));

        $item_list_gui->setDefaultCommandParameters(array());
        $item_list_gui->disableTitleLink(false);
        $item_list_gui->resetConditionTarget();

        if ($this->container->isClassificationFilterActive()) {
            $item_list_gui->enablePath(
                true,
                $this->container->getRefId(),
                new \ilSessionClassificationPathGUI()
            );
        }

        // show administration command buttons (or not)
        /*
        if (!$this->container_gui->isActiveAdministrationPanel()) {
            //			$item_list_gui->enableDelete(false);
//			$item_list_gui->enableLink(false);
//			$item_list_gui->enableCut(false);
        }*/

        // activate common social commands
        $item_list_gui->enableComments(true);
        $item_list_gui->enableNotes(true);
        $item_list_gui->enableTags(true);
        $item_list_gui->enableRating(true);

        // reset
        $item_list_gui->forceVisibleOnly(false);

        // container specific modifications
        $this->container_gui->modifyItemGUI($item_list_gui, $item_data);

        return $item_list_gui;
    }
}
