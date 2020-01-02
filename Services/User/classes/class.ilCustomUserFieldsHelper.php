<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCustomUserFieldsHelper
{
    private static $instance = null;


    /**
     * @var ilLanguage
     */
    private $lng = null;
    
    /**
     * @var ilPluginAdmin
     */
    private $plugin_admin = null;
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    public function __construct()
    {
        global $DIC;
        
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->usr();
        $this->plugin_admin = $DIC['ilPluginAdmin'];
    }
    
    /**
     * Get instance
     * @return ilCustomUserFieldsHelper
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }
    
    /**
     * Get udf types
     * @return type
     */
    public function getUDFTypes()
    {
        $types = array(
            UDF_TYPE_TEXT => $this->lng->txt('udf_type_text'),
            UDF_TYPE_SELECT => $this->lng->txt('udf_type_select'),
            UDF_TYPE_WYSIWYG => $this->lng->txt('udf_type_wysiwyg')
        );
        include_once './Services/User/classes/class.ilUDFDefinitionPlugin.php';
        foreach ($this->getActivePlugins() as $plugin) {
            $types[$plugin->getDefinitionType()] = $plugin->getDefinitionTypeName();
        }
        return $types;
    }
    
    /**
     * Get plugin for udf type
     * @return ilUDFDefinitionPlugin
     */
    public function getPluginForType($a_type)
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
     * @param array $def_ids
     * @return ilUDFDefinitionPlugin[]
     */
    public function getActivePlugins()
    {
        $plugins = array();
        
        include_once './Services/User/classes/class.ilUDFDefinitionPlugin.php';
        foreach (
            $this->plugin_admin->getActivePluginsForSlot(
                ilUDFDefinitionPlugin::UDF_C_TYPE,
                ilUDFDefinitionPlugin::UDF_C_NAME,
                ilUDFDefinitionPlugin::UDF_SLOT_ID
            )
            as $plugin) {
            $plug = $this->plugin_admin->getPluginObject(
                ilUDFDefinitionPlugin::UDF_C_TYPE,
                ilUDFDefinitionPlugin::UDF_C_NAME,
                ilUDFDefinitionPlugin::UDF_SLOT_ID,
                $plugin
            );
            if ($plug instanceof ilUDFDefinitionPlugin) {
                $plugins[] = $plug;
            }
        }
        return $plugins;
    }
    
    /**
     * Get form property for definition
     * @param array $definition
     * @return ilFormPropertyGUI
     */
    public function getFormPropertyForDefinition($definition, $a_changeable = true, $a_default_value = null)
    {
        $fprop = null;
        
        switch ($definition['field_type']) {
            case UDF_TYPE_TEXT:
                $fprop = new ilTextInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);
                $fprop->setValue($a_default_value);
                $fprop->setSize(40);
                $fprop->setMaxLength(255);
                $fprop->setRequired($definition['required'] ? true : false);
                break;
            
            case UDF_TYPE_WYSIWYG:
                $fprop = new ilTextAreaInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);
                $fprop->setValue($a_default_value);
                $fprop->setUseRte(true);
                $fprop->setRequired($definition['required'] ? true : false);
                break;
            
            case UDF_TYPE_SELECT:
                $fprop = new ilSelectInputGUI(
                    $definition['field_name'],
                    'udf_' . $definition['field_id']
                );
                $fprop->setDisabled(!$a_changeable);
                
                include_once './Services/User/classes/class.ilUserDefinedFields.php';
                $user_defined_fields = ilUserDefinedFields::_getInstance();
                
                $fprop->setOptions($user_defined_fields->fieldValuesToSelectArray($definition['field_values']));
                $fprop->setValue($a_default_value);
                $fprop->setRequired($definition['required'] ? true : false);
                break;
            
            default:
                // should be a plugin
                foreach ($this->getActivePlugins() as $plugin) {
                    if ($plugin->getDefinitionType() == $definition['field_type']) {
                        $fprop = $plugin->getFormPropertyForDefinition($definition, $a_changeable, $a_default_value);
                        break;
                    }
                }
                break;
        }
        
        return $fprop;
    }
}
