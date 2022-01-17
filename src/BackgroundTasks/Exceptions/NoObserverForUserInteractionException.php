<?php

namespace ILIAS\BackgroundTasks\Exceptions;

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
 * Class Exception
 * @package ILIAS\BackgroundTasks
 * A bucket that contains a user interaction needs at least one user that observes it.
 */
class NoObserverForUserInteractionException extends Exception
{
}
