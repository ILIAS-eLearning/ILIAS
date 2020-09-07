<?php

namespace ILIAS\Filesystem\Exception;

/**
 * Class IllegalStateException
 *
 * The IllegalStateException indicates a wrong state of the object.
 *
 * Example:
 * A tape recorder can't record and seek at the same time because of the sequential access of the tape. Therefore an
 * IllegalStateException is thrown to inform the programmer about the fact that the object is not ready to perform the requested operation due to
 * its internal state. The reason why the exception is called illegal state is due to the fact that the state "play + seek" would be illegal.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
class IllegalStateException extends \RuntimeException
{
}
