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

// still needed, since constants are defined in ilUserDefinedFields
include_once("./Services/User/classes/class.ilUserDefinedFields.php");

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCustomUserFieldsHelper
{
    private static ?ilCustomUserFieldsHelper $instance = null;
    private ilLanguage $lng;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->component_repository = $DIC['component.repository'];
        $this->component_factory = $DIC['component.factory'];
    }

    public static function getInstance(): ilCustomUserFieldsHelper
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    /**
     * @return array<int,string>
     */
    public function getUDFTypes(): array
    {
        $types = array(
            UDF_TYPE_TEXT => $this->lng->txt('udf_type_text'),
            UDF_TYPE_SELECT => $this->lng->txt('udf_type_select'),
            UDF_TYPE_WYSIWYG => $this->lng->txt('udf_type_wysiwyg')
        );
        foreach ($this->getActivePlugins() as $plugin) {
            $types[$plugin->getDefinitionType()] = $plugin->getDefinitionTypeName();
        }
        return $types;
    }

    /**
     * Get plugin for udf type
     */
    public function getPluginForType(string $a_type): ?ilUDFDefinitionPlugin
    {
        foreach ($this->getActivePlugins() as $plugin) {
            if ($plugin->getDefinitionType() == $a_type) {
                return $plugin;
            }
        }
        return null;
    }

    /**
     * Get plugins for fields
     * @return ilUDFDefinitionPlugin[]
     */
    public function getActivePlugins(): array
    {
        return iterator_to_array($this->component_factory->getActivePluginsInSlot(ilUDFDefinitionPlugin::UDF_SLOT_ID));
    }

    /**
     * Get form property for definition
     */
    public function getFormPropertyForDefinition(
        array $definition,
        bool $a_changeable = true,
        string $a_default_value = null
    ): ?ilFormPropertyGUI {
        $fprop = null;

        switch ($definition['field_type']) {
            case UDF_TYPE_TEXT:
                $fprop = new ilTextInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);
                $fprop->setValue((string) $a_default_value);
                $fprop->setSize(40);
                $fprop->setMaxLength(255);
                $fprop->setRequired((bool) $definition['required']);
                break;

            case UDF_TYPE_WYSIWYG:
                $fprop = new ilTextAreaInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);
                $fprop->setValue((string) $a_default_value);
                $fprop->setUseRte(true);
                $fprop->setRequired((bool) $definition['required']);
                break;

            case UDF_TYPE_SELECT:
                $fprop = new ilSelectInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);

                $user_defined_fields = ilUserDefinedFields::_getInstance();

                $fprop->setOptions($user_defined_fields->fieldValuesToSelectArray($definition['field_values']));
                $fprop->setValue($a_default_value);
                $fprop->setRequired((bool) $definition['required']);
                break;

            default:
                // should be a plugin
                foreach ($this->getActivePlugins() as $plugin) {
                    if ($plugin->getDefinitionType() == $definition['field_type']) {
                        $fprop = $plugin->getFormPropertyForDefinition($definition, $a_changeable);
                        break;
                    }
                }
                break;
        }

        return $fprop;
    }
}
