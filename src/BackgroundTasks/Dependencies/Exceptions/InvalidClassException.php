<?php

namespace ILIAS\BackgroundTasks\Dependencies\Exceptions;

/**
 * Class InvalidClassException
 *
 * @package ILIAS\BackgroundTasks\Exceptions
 *
 * This method is thrown when the DI tries to instantiate a class that somehow cannot be
 * instantiated.
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class InvalidClassException extends Exception
{
}
