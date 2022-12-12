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

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Button\Shy;

class ilPluginsOverviewTable
{
    public const F_PLUGIN_NAME = "plugin_name";
    public const F_PLUGIN_ID = "plugin_id";
    public const F_SLOT_NAME = "slot_name";
    public const F_COMPONENT_NAME = "component_name";
    public const F_PLUGIN_ACTIVE = "plugin_active";

    protected ilObjComponentSettingsGUI $parent_gui;
    protected ilCtrl $ctrl;
    protected Factory $ui;
    protected Renderer $renderer;
    protected ilLanguage $lng;
    protected array $filter;
    protected array $data = [];

    public function __construct(
        ilObjComponentSettingsGUI $parent_gui,
        ilCtrl $ctrl,
        Factory $ui,
        Renderer $renderer,
        ilLanguage $lng,
        array $filter
    ) {
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ctrl;
        $this->ui = $ui;
        $this->renderer = $renderer;
        $this->lng = $lng;
        $this->filter = $filter;
    }

    public function getTable(): string
    {
        return $this->renderer->render($this->ui->table()->presentation(
            'Plugins',
            [],
            function ($row, ilPluginInfo $record, $ui_factory) {
                return $row
                    ->withHeadline($record->getName())
                    ->withSubHeadline($record->getPluginSlot()->getName())
                    ->withImportantFields($this->getImportantFields($record))
                    ->withContent(
                        $ui_factory->listing()->descriptive($this->getContent($record))
                    )
                    ->withAction($this->getActions($record))
                ;
            }
        )->withData($this->filterData($this->getData())));
    }

    protected function getImportantFields(ilPluginInfo $plugin_info): array
    {
        $fields = [];

        if ($plugin_info->isInstalled()) {
            $fields[] = $this->lng->txt("installed");
        } else {
            $fields[] = $this->lng->txt("not_installed");
        }

        if ($plugin_info->isActive()) {
            $fields[] = $this->lng->txt("cmps_active");
        } else {
            $fields[] = $this->lng->txt("inactive");
        }

        if ($plugin_info->isUpdateRequired()) {
            $fields[] = $this->lng->txt("cmps_needs_update");
        }

        return $fields;
    }

    protected function getContent(ilPluginInfo $plugin_info): array
    {
        return [
            $this->lng->txt("cmps_is_installed") => $this->boolToString($plugin_info->isInstalled()),
            $this->lng->txt("cmps_is_active") => $this->boolToString($plugin_info->isActive()),
            $this->lng->txt("cmps_needs_update") => $this->boolToString($plugin_info->isUpdateRequired()),
            $this->lng->txt("cmps_id") => $plugin_info->getId(),
            $this->lng->txt("cmps_plugin_slot") => $plugin_info->getPluginSlot()->getName(),
            $this->lng->txt("cmps_current_version") => (string) $plugin_info->getCurrentVersion(),
            $this->lng->txt("cmps_available_version") => (string) $plugin_info->getAvailableVersion(),
            $this->lng->txt("cmps_current_db_version") => (string) $plugin_info->getCurrentDBVersion(),
            $this->lng->txt("cmps_ilias_min_version") => (string) $plugin_info->getMinimumILIASVersion(),
            $this->lng->txt("cmps_ilias_max_version") => (string) $plugin_info->getMaximumILIASVersion(),
            $this->lng->txt("cmps_responsible") => $plugin_info->getResponsible(),
            $this->lng->txt("cmps_responsible_mail") => $plugin_info->getResponsibleMail(),
            $this->lng->txt("cmps_supports_learning_progress") => $this->boolToString($plugin_info->supportsLearningProgress()),
            $this->lng->txt("cmps_supports_export") => $this->boolToString($plugin_info->supportsExport()),
            $this->lng->txt("cmps_supports_cli_setup") => $this->boolToString($plugin_info->supportsCLISetup())
        ];
    }

    protected function boolToString(bool $value): string
    {
        if ($value) {
            return $this->lng->txt("yes");
        }
        return $this->lng->txt("no");
    }

    /**
     * @param ilPluginInfo[] $data
     * @return ilPluginInfo[]
     */
    protected function filterData(array $data): array
    {
        $active_filters = array_filter($this->filter, static function ($value): bool {
            return !empty($value);
        });
        $plugins = array_filter($data, static function (ilPluginInfo $plugin_info) use ($active_filters): bool {
            $matches_filter = true;
            if (isset($active_filters[self::F_PLUGIN_NAME])) {
                $matches_filter = strpos($plugin_info->getName(), $active_filters[self::F_PLUGIN_NAME]) !== false;
            }
            if (isset($active_filters[self::F_PLUGIN_ID])) {
                $matches_filter = strpos($plugin_info->getId(), $active_filters[self::F_PLUGIN_ID]) !== false;
            }
            if (isset($active_filters[self::F_PLUGIN_ACTIVE])) {
                $v = (int) $active_filters[self::F_PLUGIN_ACTIVE] === 1;
                $matches_filter = $plugin_info->isActive() === $v && $matches_filter;
            }
            if (isset($active_filters[self::F_SLOT_NAME])) {
                $matches_filter = $matches_filter && in_array(
                    $plugin_info->getPluginSlot()->getName(),
                    $active_filters[self::F_SLOT_NAME],
                    true
                );
            }
            if (isset($active_filters[self::F_COMPONENT_NAME])) {
                $matches_filter = $matches_filter && in_array(
                    $plugin_info->getComponent()->getQualifiedName(),
                    $active_filters[self::F_COMPONENT_NAME],
                    true
                );
            }

            return $matches_filter;
        });

        return $plugins;
    }

    public function withData(array $data): ilPluginsOverviewTable
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    protected function getData(): array
    {
        return $this->data;
    }

    protected function getActions(ilPluginInfo $plugin_info): Dropdown
    {
        $this->setParameter($plugin_info);

        $items = [];

        if (!$plugin_info->isInstalled()) {
            $items[] = $this->getDropdownButton("cmps_install", ilObjComponentSettingsGUI::CMD_INSTALL_PLUGIN);
            $this->clearParameter();
            return $this->ui->dropdown()->standard($items);
        }

        if (class_exists($plugin_info->getConfigGUIClassName())) {
            $items[] = $this->ui->button()->shy(
                $this->lng->txt("cmps_configure"),
                $this->ctrl->getLinkTargetByClass(
                    $plugin_info->getConfigGUIClassName(),
                    ilObjComponentSettingsGUI::CMD_CONFIGURE
                )
            );
        }

        if ($this->hasLang($plugin_info)) {
            $items[] = $this->getDropdownButton("cmps_refresh", ilObjComponentSettingsGUI::CMD_REFRESH_LANGUAGES);
        }

        if ($plugin_info->isActive()) {
            $items[] = $this->getDropdownButton("cmps_deactivate", ilObjComponentSettingsGUI::CMD_DEACTIVATE_PLUGIN);
        }

        if ($plugin_info->isActivationPossible() && !$plugin_info->isActive()) {
            $items[] = $this->getDropdownButton("cmps_activate", ilObjComponentSettingsGUI::CMD_ACTIVATE_PLUGIN);
        }

        if ($plugin_info->isUpdateRequired()) {
            $items[] = $this->getDropdownButton("cmps_update", ilObjComponentSettingsGUI::CMD_UPDATE_PLUGIN);
        }

        $items[] = $this->getDropdownButton("cmps_uninstall", ilObjComponentSettingsGUI::CMD_CONFIRM_UNINSTALL_PLUGIN);

        $this->clearParameter();

        return $this->ui->dropdown()->standard($items);
    }

    protected function setParameter(ilPluginInfo $plugin): void
    {
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_PLUGIN_ID, $plugin->getId());
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_CTYPE, $plugin->getComponent()->getType());
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_CNAME, $plugin->getComponent()->getName());
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_SLOT_ID, $plugin->getPluginSlot()->getId());
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_PLUGIN_NAME, $plugin->getName());
    }

    protected function clearParameter(): void
    {
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_CTYPE, null);
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_CNAME, null);
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_SLOT_ID, null);
        $this->ctrl->setParameter($this->parent_gui, ilObjComponentSettingsGUI::P_PLUGIN_NAME, null);
    }

    protected function getDropdownButton(string $caption, string $command): Shy
    {
        return $this->ui->button()->shy(
            $this->lng->txt($caption),
            $this->ctrl->getLinkTarget($this->parent_gui, $command)
        );
    }

    protected function hasLang(ilPluginInfo $plugin_info): bool
    {
        $language_handler = new ilPluginLanguage($plugin_info);
        return $language_handler->hasAvailableLangFiles();
    }
}
