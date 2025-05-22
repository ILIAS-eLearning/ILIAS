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
 * Interface for plugin classes that want to support Learning Progress
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
interface ilLPStatusPluginInterface
{
    /**
     * Get all user ids with LP status completed
     * @return int[]
     */
    public function getLPCompleted(): array;

    /**
     * Get all user ids with LP status not attempted
     * @return int[]
     */
    public function getLPNotAttempted(): array;

    /**
     * Get all user ids with LP status failed
     * @return array
     */
    public function getLPFailed(): array;

    /**
     * Get all user ids with LP status in progress
     * @return array
     */
    public function getLPInProgress(): array;

    /**
     * Get current status for given user
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser(int $a_user_id): int;
}
