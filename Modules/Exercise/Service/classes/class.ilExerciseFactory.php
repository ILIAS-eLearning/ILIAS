<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilExerciseFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Internal services, do not use from other components
     *
     * @param
     * @return
     */
    public function internal()
    {
        return new ilExerciseInternalFactory();
    }
}
