<?php

namespace ILIAS\BackgroundTasks\Exceptions;

/**
 * Class Exception
 *
 * @package ILIAS\BackgroundTasks
 *
 * A bucket that contains a user interaction needs at least one user that observes it.
 */
class NoObserverForUserInteractionException extends Exception
{
}
