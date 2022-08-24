<?php

// declare(strict_types=1);

namespace ILIAS\WebAccessChecker;

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
/**
 * Interface PathType
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
interface PathType
{
    public const FILE = 1;
    public const FOLDER = 2;
}
