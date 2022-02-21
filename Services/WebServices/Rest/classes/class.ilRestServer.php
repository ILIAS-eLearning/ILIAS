<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Slim\App;

/**
 * Slim rest server
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilRestServer extends App
{
    /**
     * ilRestServer constructor.
     * @param array $container
     */
    public function __construct($container = [])
    {
        parent::__construct($container);
    }


    /**
     * Init server / add handlers
     */
    public function init() : void
    {
        $callback_obj = new ilRestFileStorage();
        $this->get('/fileStorage', array($callback_obj,'getFile'));
        $this->post('/fileStorage', array($callback_obj,'createFile'));
        $callback_obj->deleteDeprecated();
    }
}
