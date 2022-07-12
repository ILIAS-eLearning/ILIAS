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

/**
 * UDF type definition plugin
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilUDFDefinitionPlugin extends ilPlugin
{
    public const UDF_SLOT_ID = 'udfd';
    abstract public function getDefinitionType() : int;
    
    abstract public function getDefinitionTypeName() : string;

    /**
     * Add udf type options to radio option
     */
    abstract public function addDefinitionTypeOptionsToRadioOption(
        ilRadioOption $option,
        int $field_id
    ) : void;
    
    /**
     * get title for update form
     */
    abstract public function getDefinitionUpdateFormTitle() : string;
    
    /**
     * Update definition from form input
     */
    abstract public function updateDefinitionFromForm(
        ilPropertyFormGUI $form,
        int $a_definition_id
    ) : void;
    
    /**
     * Get form property for definition
     * Context: edit user; registration; edit user profile
     * @param mixed $a_default_value
     */
    abstract public function getFormPropertyForDefinition(
        array $definition,
        $a_default_value = null
    ) : ilFormPropertyGUI;


    /**
     * If user data data is not stored in table udf_text, return an array with user data for each
     * udf field
     * [
     *    USER_ID => ['FIELD_ID' => custom_value]
     * ]
     */
    abstract public function lookupUserData(array $a_user_ids, array $a_field_ids) : array;
}
