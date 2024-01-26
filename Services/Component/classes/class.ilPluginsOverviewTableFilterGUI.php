<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Container\Filter\Standard;

/**
 * Class ilPluginsOverviewTableFilterGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPluginsOverviewTableFilterGUI
{
    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $renderer;
    /**
     * @var ilUIFilterService
     */
    protected $filter_service;
    /**
     * @var Standard
     */
    protected $filter;

    /**
     * ilPluginsOverviewTableFilterGUI constructor.
     * @param ilObjComponentSettingsGUI $parent
     */
    public function __construct(ilObjComponentSettingsGUI $parent)
    {
        global $DIC;
        $this->renderer       = $DIC->ui()->renderer();
        $this->filter_service = $DIC->uiService()->filter();
        $field_factory        = $DIC->ui()->factory()->input()->field();
        $txt                  = static function (string $id) use ($DIC): string {
            return $DIC->language()->txt($id);
        };

        $slots = [];
        $components = [];
        $slot_ids = [];

        $available_slots = ilPluginSlot::getAvailableSlots();
        array_walk($available_slots, static function (array $d) use (&$slots, &$components, &$slot_ids) {
            $slots[$d['slot_name']]                                        = $d['slot_name'];
            $slot_ids[$d['slot_id']]                                       = $d['slot_id'];
            $components[$d['component_type'] . '/' . $d['component_name']] = $d['component_type'] . '/' . $d['component_name'];
        });

        $inputs       = [
            ilPluginsOverviewTableGUI::F_PLUGIN_NAME    => $field_factory->text($txt('cmps_plugin')),
            ilPluginsOverviewTableGUI::F_PLUGIN_ID      => $field_factory->text($txt('id')),
            ilPluginsOverviewTableGUI::F_SLOT_NAME      => $field_factory->multiSelect($txt('cmps_plugin_slot'), $slots)->withValue($slots),
            ilPluginsOverviewTableGUI::F_COMPONENT_NAME => $field_factory->multiSelect($txt('cmps_component'), $components)->withValue($components),
            ilPluginsOverviewTableGUI::F_PLUGIN_ACTIVE => $field_factory->select($txt('active'), [-1 => $txt('inactive'), 1 => $txt('active')]),
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
            true);
    }

    public function getHTML() : string
    {
        return $this->renderer->render($this->filter);
    }

    public function getFilter() : Standard
    {
        return $this->filter;
    }

    public function getData() : array
    {
        try {
            return $this->filter_service->getData($this->filter) ?? [];
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}
