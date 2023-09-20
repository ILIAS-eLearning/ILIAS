<?php

declare(strict_types=1);
require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Exceptions_/classes/class.ilException.php');

/**
 * Class ilSoapPluginException
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilSoapPluginException extends ilException
{
}
