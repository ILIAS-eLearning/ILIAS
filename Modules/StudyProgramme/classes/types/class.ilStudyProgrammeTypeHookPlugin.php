<?php

require_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all StudyProgrammeTypeHook plugin classes.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilStudyProgrammeTypeHookPlugin extends ilPlugin
{
    /**
     * Get Component Type
     *
     * @return        string        Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_MODULE;
    }

    /**
     * Get Component Name.
     *
     * @return        string        Component Name
     */
    final public function getComponentName()
    {
        return 'StudyProgramme';
    }

    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return 'StudyProgrammeTypeHook';
    }

    /**
     * Get Slot ID.
     *
     * @return        string        Slot Id
     */
    final public function getSlotId()
    {
        return 'prgtypehk';
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }


    /**
     * The following methods can be overridden by plugins
     */


    /**
     * Return false if setting a title is not allowed
     *
     * @param int $a_type_id
     * @param string $a_lang_code
     * @param string $a_title
     * @return bool
     */
    public function allowSetTitle($a_type_id, $a_lang_code, $a_title)
    {
        return true;
    }

    /**
     * Return false if setting a description is not allowed
     *
     * @param int $a_type_id
     * @param string $a_lang_code
     * @param string $a_description
     * @return bool
     */
    public function allowSetDescription($a_type_id, $a_lang_code, $a_description)
    {
        return true;
    }

    /**
     * Return false if setting a default language is not allowed
     *
     * @param int $a_type_id
     * @param string $a_lang_code
     * @return bool
     */
    public function allowSetDefaultLanguage($a_type_id, $a_lang_code)
    {
        return true;
    }

    /**
     * Return false if StudyProgramme type cannot be deleted
     *
     * @param int $a_type_id
     * @return bool
     */
    public function allowDelete($a_type_id)
    {
        return true;
    }

    /**
     * Return false if StudyProgramme type is locked and no updates are possible
     *
     * @param int $a_type_id
     * @return bool
     */
    public function allowUpdate($a_type_id)
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be assigned to an StudyProgramme type
     *
     * @param int $a_type_id
     * @param int $a_record_id
     * @return bool
     */
    public function allowAssignAdvancedMDRecord($a_type_id, $a_record_id)
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be deassigned from an StudyProgramme type
     *
     * @param int $a_type_id
     * @param int $a_record_id
     * @return bool
     */
    public function allowDeassignAdvancedMDRecord($a_type_id, $a_record_id)
    {
        return true;
    }
}
