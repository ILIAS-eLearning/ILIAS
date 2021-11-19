<?php declare(strict_types=1);

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
    final public function getComponentType() : string
    {
        return IL_COMP_MODULE;
    }

    final public function getComponentName() : string
    {
        return 'StudyProgramme';
    }

    final public function getSlot() : string
    {
        return 'StudyProgrammeTypeHook';
    }

    final public function getSlotId() : string
    {
        return 'prgtypehk';
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit() : void
    {
        // nothing to do here
    }


    /**
     * The following methods can be overridden by plugins
     */


    /**
     * Return false if setting a title is not allowed
     */
    public function allowSetTitle(int $type_id, string $lang_code, string $title) : bool
    {
        return true;
    }

    /**
     * Return false if setting a description is not allowed
     */
    public function allowSetDescription(int $type_id, string $lang_code, string $description) : bool
    {
        return true;
    }

    /**
     * Return false if setting a default language is not allowed
     */
    public function allowSetDefaultLanguage(int $type_id, string $lang_code) : bool
    {
        return true;
    }

    /**
     * Return false if StudyProgramme type cannot be deleted
     */
    public function allowDelete(int $type_id) : bool
    {
        return true;
    }

    /**
     * Return false if StudyProgramme type is locked and no updates are possible
     */
    public function allowUpdate(int $type_id) : bool
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be assigned to an StudyProgramme type
     */
    public function allowAssignAdvancedMDRecord(int $type_id, int $record_id) : bool
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be deassigned from an StudyProgramme type
     */
    public function allowDeassignAdvancedMDRecord(int $type_id, int $record_id) : bool
    {
        return true;
    }
}
