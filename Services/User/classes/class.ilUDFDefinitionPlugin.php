<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Component/classes/class.ilPlugin.php';

/**
 * UDF type deefinition plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
abstract class ilUDFDefinitionPlugin extends ilPlugin
{
    const UDF_SLOT = 'UDFDefinition';
    const UDF_SLOT_ID = 'udfd';
    const UDF_C_NAME = 'User';
    const UDF_C_TYPE = IL_COMP_SERVICE;
    
    
    

    /**
     * Get udf type
     */
    abstract public function getDefinitionType();
    
    /**
     * Get udf type name
     */
    abstract public function getDefinitionTypeName();

    /**
     * Add udf type options to radio option
     */
    abstract public function addDefinitionTypeOptionsToRadioOption(ilRadioOption $option, $field_id);
    
    /**
     * get title for update form
     */
    abstract public function getDefinitionUpdateFormTitle();
    
    /**
     * Update definition from form input
     */
    abstract public function updateDefinitionFromForm(ilPropertyFormGUI $form, $a_definition_id);
    
    /**
     * Get form property for definition
     * Context: edit user; registration; edit user profile
     * @return ilFormPropertyGUI
     */
    abstract public function getFormPropertyForDefinition($definition, $a_default_value = null);


    /**
     * If user data data is not stored in table udf_text, return an array with user data for each
     * udf field
     * [
     *    USER_ID => ['FIELD_ID' => custom_value]
     * ]
     *
     * @return array
     */
    abstract public function lookupUserData($a_user_ids, $a_field_ids);
    
    
    /**
     * Get component name
     * @return string
     */
    final public function getComponentName()
    {
        return self::UDF_C_NAME;
    }

    /**
     * get component type
     * @return string
     */
    final public function getComponentType()
    {
        return self::UDF_C_TYPE;
    }
    

    /**
     * Get slot
     * @return string
     */
    public function getSlot()
    {
        return self::UDF_SLOT;
    }

    /**
     * Get slot id
     * @return string
     */
    public function getSlotId()
    {
        return self::UDF_SLOT_ID;
    }
    
    /**
     * implemented
     */
    public function slotInit()
    {
    }
}
