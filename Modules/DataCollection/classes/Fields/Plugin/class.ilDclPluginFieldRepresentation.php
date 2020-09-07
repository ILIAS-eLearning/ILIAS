<?php

/**
 * Class ilDclPluginFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclPluginFieldRepresentation extends ilDclBaseFieldRepresentation
{

    /**
     * @inheritDoc
     */
    protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        // only show, when element is created
        if (get_called_class() == 'ilDclPluginFieldRepresentation') {
            $plugins = ilPluginAdmin::getActivePluginsForSlot(IL_COMP_MODULE, ilDclFieldTypePlugin::COMPONENT_NAME, ilDclFieldTypePlugin::SLOT_ID);
            $options = array();
            foreach ($plugins as $plugin_name) {
                $plugin_data = ilPluginAdmin::getPluginObject(IL_COMP_MODULE, ilDclFieldTypePlugin::COMPONENT_NAME, ilDclFieldTypePlugin::SLOT_ID, $plugin_name);
                $options[$plugin_data->getPluginName()] = $plugin_data->getPluginName();
            }

            if (count($options) > 0) {
                $plugin_selection = new ilSelectInputGUI($this->lng->txt('dcl_plugin_hooks'), 'prop_' . ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME);
                $plugin_selection->setOptions($options);
                $opt->addSubItem($plugin_selection);
                if ($mode == "edit") {
                    $plugin_selection->setDisabled(true);
                } else {
                }
            } else {
                $plugin_selection = new ilNonEditableValueGUI($this->lng->txt('dcl_plugin_no_hooks_available'), 'prop_' . ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME);
                $opt->addSubItem($plugin_selection);
            }
        }

        return $opt;
    }
}
