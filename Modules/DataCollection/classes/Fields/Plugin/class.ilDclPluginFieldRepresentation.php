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

/**
 * Class ilDclPluginFieldRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclPluginFieldRepresentation extends ilDclBaseFieldRepresentation
{
    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ilRadioOption
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        // only show, when element is created
        if (get_called_class() == 'ilDclPluginFieldRepresentation') {
            $plugins = $this->component_repository->getPluginSlotById(ilDclFieldTypePlugin::SLOT_ID)->getActivePlugins();
            $options = array();
            foreach ($plugins as $plugin) {
                $plugin_data = $this->component_factory->getPlugin($plugin->getId());
                $options[$plugin_data->getPluginName()] = $plugin_data->getPluginName();
            }

            if (count($options) > 0) {
                $plugin_selection = new ilSelectInputGUI(
                    $this->lng->txt('dcl_plugin_hooks'),
                    'prop_' . ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME
                );
                $plugin_selection->setOptions($options);
                $opt->addSubItem($plugin_selection);
                if ($mode == "edit") {
                    $plugin_selection->setDisabled(true);
                } else {
                }
            } else {
                $plugin_selection = new ilNonEditableValueGUI(
                    $this->lng->txt('dcl_plugin_no_hooks_available'),
                    'prop_' . ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME
                );
                $opt->addSubItem($plugin_selection);
            }
        }

        return $opt;
    }
}
