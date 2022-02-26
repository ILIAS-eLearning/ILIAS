<?php

namespace ILIAS\FileUpload\Exception;

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
