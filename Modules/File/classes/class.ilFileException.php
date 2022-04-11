<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilFileException extends ilException
{
    public static int $ID_MISMATCH = 0;
    public static int $ID_DEFLATE_METHOD_MISMATCH = 1;
    public static int $DECOMPRESSION_FAILED = 2;
}
