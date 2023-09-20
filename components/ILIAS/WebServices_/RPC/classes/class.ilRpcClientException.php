<?php

declare(strict_types=1);

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Exceptions_/classes/class.ilException.php');

/**
 * Class ilRpcClientException
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @ingroup ServicesWebServicesRPC
 */
class ilRpcClientException extends ilException
{
}
