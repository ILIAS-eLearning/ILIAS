<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
* A Whoops error handler that delegates calls on it self to another handler that is created only in the
* case an error is thrown. This is necessary to make it possible to use another handler when the DEVMODE
* is activated.
*
* During the Init-Dance (see ilInitialisation), the error handling is initialized before the client ini file
* is read and the DEVMODE is determined. Thus we can't initialize a handler based on the DEVMODE and need this
* workaround.
*
* This class is not ment to be extended, as the definition of error handlers should be handled in one place
* in ilErrorHandling, so this class acts rather dump and asks ilErrorHandling for a handler.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*
*
*/

use Whoops\Handler\Handler;

final class ilDelegatingHandler extends Handler
{
    /**
     * @var ilErrorHandling
     */
    private $error_handling;
    
    public function __construct(ilErrorHandling $error_handling)
    {
        $this->error_handling = $error_handling;
    }
    
    /**
     * Last missing method from HandlerInterface.
     *
     * Asks ilErrorHandling for the appropriate Handler and delegates it's tasks to
     * that handler.
     *
     * @return int|null A handler may return nothing, or a Handler::HANDLE_* constant
     */
    public function handle()
    {
        if (defined("IL_INITIAL_WD")) {
            chdir(IL_INITIAL_WD);
        }
        $handler = $this->error_handling->getHandler();
        $handler->setRun($this->getRun());
        $handler->setException($this->getException());
        $handler->setInspector($this->getInspector());
        $handler->handle();
    }
}
