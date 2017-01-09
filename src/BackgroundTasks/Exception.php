<?php

namespace ILIAS\BackgroundTasks;

require_once('Services/Exceptions/classes/class.ilException.php');

/**
 * Class Exception
 *
 * @package ILIAS\BackgroundTasks
 *
 *          The Basic Exception Class for BackgroundTasks. PLease Specify by extending
 */
class Exception extends \ilException {

	const E_BASIC = 10001;
}
