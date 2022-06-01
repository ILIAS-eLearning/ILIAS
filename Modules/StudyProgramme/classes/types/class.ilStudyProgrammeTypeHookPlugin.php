<?php declare(strict_types=1);

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
