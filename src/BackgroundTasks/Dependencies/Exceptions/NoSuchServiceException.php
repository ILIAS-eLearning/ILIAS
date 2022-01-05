<?php

namespace ILIAS\BackgroundTasks\Dependencies\Exceptions;

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
 * Class NoSuchServiceException
 * @package ILIAS\BackgroundTasks\Exceptions
 * If the DIC does not contain a service that is required.
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class NoSuchServiceException extends Exception
{
}
