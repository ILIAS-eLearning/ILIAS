<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Exception;

/**
 * Class IllegalArgumentException
 *
 * Indicates that a passed argument is not allowed for the method call.
 * This is most likely a logic error within the consumer code of the Filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class IllegalArgumentException extends \Exception {

}