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
 * Abstract parent class for all OrgUnitTypeHook plugin classes.
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 * @ingroup ServicesEventHandling
 */
abstract class ilOrgUnitTypeHookPlugin extends ilPlugin
{
    /**
     * The following methods can be overridden by plugins
     */
    public function allowSetTitle(int $a_type_id, string $a_lang_code, string $a_title): bool
    {
        return true;
    }

    /**
     * Return false if setting a description is not allowed
     */
    public function allowSetDescription(int $a_type_id, string $a_lang_code, string $a_description): bool
    {
        return true;
    }

    /**
     * Return false if setting a default language is not allowed
     */
    public function allowSetDefaultLanguage(int $a_type_id, string $a_lang_code): bool
    {
        return true;
    }

    /**
     * Return false if OrgUnit type cannot be deleted
     */
    public function allowDelete(int $a_type_id): bool
    {
        return true;
    }

    /**
     * Return false if OrgUnit type is locked and no updates are possible
     */
    public function allowUpdate(int $a_type_id): bool
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be assigned to an OrgUnit type
     */
    public function allowAssignAdvancedMDRecord(int $a_type_id, int $a_record_id): bool
    {
        return true;
    }

    /**
     * Return false if an AdvancedMDRecord cannot be deassigned from an OrgUnit type
     */
    public function allowDeassignAdvancedMDRecord(int $a_type_id, int $a_record_id): bool
    {
        return true;
    }
}
