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
 ********************************************************************
 */

use ILIAS\UI\Component\Input\Container\Filter\Standard;

/**
 * Class ilPluginsOverviewTableFilterGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPluginsOverviewTableFilterGUI
{
    protected \ILIAS\UI\Renderer $renderer;
    protected ilUIFilterService $filter_service;
    protected Standard $filter;

    /**
     * ilPluginsOverviewTableFilterGUI constructor.
     * @param ilObjComponentSettingsGUI $parent
     */
    public function __construct(ilObjComponentSettingsGUI $parent)
    {
        global $DIC;
        $this->renderer = $DIC->ui()->renderer();
        $this->filter_service = $DIC->uiService()->filter();
        $field_factory = $DIC->ui()->factory()->input()->field();
        $txt = static function (string $id) use ($DIC): string {
            return $DIC->language()->txt($id);
        };

        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC["component.repository"];
        $slots = [];
        $components = [];
        foreach ($component_repository->getPluginSlots() as $slot) {
            $slots[$slot->getName()] = $slot->getName();
            $component = $slot->getComponent();
            $components[$component->getQualifiedName()] = $component->getQualifiedName();
        }

        $inputs = [
            ilPluginsOverviewTable::F_PLUGIN_NAME => $field_factory->text($txt('cmps_plugin')),
            ilPluginsOverviewTable::F_PLUGIN_ID => $field_factory->text($txt('id')),
            ilPluginsOverviewTable::F_SLOT_NAME => $field_factory->multiSelect($txt('cmps_plugin_slot'), $slots)->withValue($slots),
            ilPluginsOverviewTable::F_COMPONENT_NAME => $field_factory->multiSelect($txt('cmps_component'), $components)->withValue($components),
            ilPluginsOverviewTable::F_PLUGIN_ACTIVE => $field_factory->select($txt('active'), [-1 => $txt('inactive'), 1 => $txt('active')]),
        ];
        $this->filter = $this->filter_service->standard(
            'plugin_table',
            $DIC->ctrl()->getLinkTarget($parent, ilObjComponentSettingsGUI::CMD_DEFAULT),
            $inputs,
            [
                true,
                false,
                true,
                true,
                true,
            ],
            true,
            true
        );
    }

    public function getHTML(): string
    {
        return $this->renderer->render($this->filter);
    }

    public function getFilter(): Standard
    {
        return $this->filter;
    }

    public function getData(): array
    {
        try {
            return $this->filter_service->getData($this->filter) ?? [];
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}
