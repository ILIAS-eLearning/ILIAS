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

namespace ILIAS\FileDelivery\FileDeliveryTypes;

/**
 * Class DeliveryMethod
 *
 * This interface provides all existing delivery types.
 * Only the types provided by this interface are valid for use with the FileDeliveryTypeFactory.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0
 * @since 5.3
 *
 * @Internal
 */
interface DeliveryMethod
{
    public const PHP = 'php';
    public const PHP_CHUNKED = 'php_chunked';
    public const XACCEL = 'x-accel-redirect';
    public const XSENDFILE = 'mod_xsendfile';
}
