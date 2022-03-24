<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentAccessInfoInterface
{
    /**
     * Is access granted due to exercise assignment conditions?
     */
    public function isGranted() : bool;

    /**
     * Get reasons why access is not granted.
     *
     * @return string[]
     */
    public function getNotGrantedReasons() : array;
}
