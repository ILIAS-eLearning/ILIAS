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

class ilDclPluginFieldModel extends ilDclBaseFieldModel
{
    public function getValidFieldProperties(): array
    {
        return array_merge([ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME], $this->getCustomValidFieldProperties());
    }

    /**
     * Method for adding custom fields to plugins
     */
    public function getCustomValidFieldProperties(): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function allowFilterInListView(): bool
    {
        return false;
    }

    public function getPresentationTitle(): string
    {
        global $DIC;
        $plugin = $DIC["component.factory"]->getPlugin(ilDclFieldTypePlugin::getPluginId($this->getDatatype()->getTitle()));
        if (str_ends_with($plugin->txt('field_type_name'), 'field_type_name-')) {
            return $plugin->getPluginName();
        }
        return $plugin->txt('field_type_name');
    }

    public function getPresentationDescription(): string
    {
        global $DIC;
        $plugin = $DIC["component.factory"]->getPlugin(ilDclFieldTypePlugin::getPluginId($this->getDatatype()->getTitle()));
        if (str_ends_with($plugin->txt('field_type_info'), 'field_type_info-')) {
            return '';
        }
        return $plugin->txt('field_type_info');
    }
}
