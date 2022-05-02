<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Interface for ilObject dependency
 * @author  killing@leifos.de
 * @ingroup ServicesConditions
 */
interface ilConditionObjectAdapterInterface
{
    /**
     * Get object id for reference id
     */
    public function getObjIdForRefId(int $a_ref_id) : int;

    /**
     * Get object type for object id
     */
    public function getTypeForObjId(int $a_obj_id) : string;
}
