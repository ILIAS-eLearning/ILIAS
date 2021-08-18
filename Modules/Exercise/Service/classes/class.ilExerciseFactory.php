<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseFactory
{
    public function __construct()
    {
    }

    /**
     * Internal services, do not use from other components
     */
    public function internal() : ilExerciseInternalFactory
    {
        return new ilExerciseInternalFactory();
    }
}
