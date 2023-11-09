<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\BackgroundTasks\Dependencies\Exceptions;

/**
 * Class NoSuchServiceException
 * @package ILIAS\BackgroundTasks\Exceptions
 * If the DIC does not contain a service that is required.
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class NoSuchServiceException extends Exception
{
}
