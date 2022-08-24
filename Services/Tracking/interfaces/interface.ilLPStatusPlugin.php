<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
