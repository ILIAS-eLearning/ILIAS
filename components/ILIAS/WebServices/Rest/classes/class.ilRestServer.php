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
use Slim\App;

/**
 * Slim rest server
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilRestServer extends App
{
    /**
     * Init server / add handlers
     */
    public function init(): void
    {
        $callback_obj = new ilRestFileStorage();
        $this->get('/fileStorage', array($callback_obj,'getFile'));
        $this->post('/fileStorage', array($callback_obj,'createFile'));
        $callback_obj->deleteDeprecated();
    }
}
