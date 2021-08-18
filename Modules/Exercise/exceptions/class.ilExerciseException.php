<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise exceptions class
 *
 * @author Roland Küstermann <roland@kuestermann.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseException extends ilException
{
    public static int $ID_MISMATCH = 0;
    public static int $ID_DEFLATE_METHOD_MISMATCH = 1;
}
