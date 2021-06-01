<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Derived task provider
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilDerivedTaskProvider
{
    /**
     * Get providers
     *
     * @return ilDerivedTask[]
     */
    public function getTasks(int $user_id) : array;

    /**
     * Is provider active?
     */
    public function isActive() : bool;
}
