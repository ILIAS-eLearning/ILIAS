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
 * Only for testing purposes...
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCategoryConditionController implements ilConditionControllerInterface
{
    public function isContainerConditionController(int $a_container_ref_id) : bool
    {
        return false;
    }

    public function getConditionSetForRepositoryObject(int $a_container_child_ref_id) : ilConditionSet
    {
        global $DIC;

        $f = $DIC->conditions()->factory();

        $conditions = [];
        return $f->set($conditions);
    }
}
