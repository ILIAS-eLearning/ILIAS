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

declare(strict_types=1);
/**
 * Component logger with individual log levels by component id
 *
 * @deprecated Please use {@see \ILIAS\Logging\Logger\LoggerInterface} via
 *   {@see \ILIAS\Logging\Logger\LoggerFactoryInterface} instead.
 *   Ideally in your Component.php. If that's not possible then via $DIC['logging.factory'].
 *
 * @author Stefan Meyer
 */
class ilComponentLogger extends ilLogger
{
}
