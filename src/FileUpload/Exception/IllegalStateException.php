<?php

namespace ILIAS\FileUpload\Exception;

/**
 * Class IllegalStateException
 *
 * Indicates that the invocation of a method is not possible due to an illegal state of the current
 * object. For example FileUpload which needs to process the files before they can be moved throws
 * an illegal state exception if the move got called without processing the files first.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
class IllegalStateException extends \Exception
{
}
