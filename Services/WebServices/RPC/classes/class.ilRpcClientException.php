<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Exceptions/classes/class.ilException.php');

/**
 * Class ilRpcClientException
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @ingroup ServicesWebServicesRPC
 */
class ilRpcClientException extends ilException
{
    /**
     * Constructor
     *
     * @access public
     * @param string $a_message
     * @param int $a_errno
     *
     */
    public function __construct($a_message, $a_errno = 0)
    {
        parent::__construct($a_message, $a_errno);
    }
}
